<?php

namespace Ams\DistributionBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Command\GlobalCommand;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * 
 * Envoi du récapitulatif des tournées paie par e-mail
 * 
 * @author Marc-Antoine Adélise
 *
 */
class SendMailRecapTourneesPaieCommand extends GlobalCommand {

    protected function configure() {
        $this->sNomCommande = 'sendmail_recap_tournees';
        $this->setName($this->sNomCommande);
        // Pour executer, faire : php app/console sendmail_recap_tournees  Expl : php app/console sendmail_recap_tournees
        $this
                ->setDescription('Envoie par e-mail un recapitulatif paye/tournees pour le mois courant.')
                ->addOption('date_debut', 'db', InputOption::VALUE_OPTIONAL, 'Date de debut de la periode JJ/MM/AAAA', NULL)
                ->addOption('date_fin', 'df', InputOption::VALUE_OPTIONAL, 'Date de fin de la periode JJ/MM/AAAA', NULL)
                ->addOption('fichier_export', 'fic', InputOption::VALUE_OPTIONAL, 'Le chemin vers le fichier de sortie', NULL)
                ->addOption('no_update', 'noupd', InputOption::VALUE_OPTIONAL, 'Permet de ne pas mettre a jour les statistiques si VRAI', FALSE)
                ->addOption('no_email', 'nomail', InputOption::VALUE_OPTIONAL, 'Permet de ne pas envoyer de mail si VRAI', FALSE)
                ->addOption('no_copy', 'nocopy', InputOption::VALUE_OPTIONAL, 'Permet de ne pas copier le fichier genere sur le dossier de batch si VRAI', FALSE)
                 ->addOption('id_sh',null, InputOption::VALUE_REQUIRED, 'Libelle du CRON')
                ->addOption('id_ai',null, InputOption::VALUE_REQUIRED, 'Id du CRON')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
        if($input->getOption('id_sh')){
            $idSh = $input->getOption('id_sh');
        }
        if($input->getOption('id_ai')){
            $idAi = $input->getOption('id_ai');
        }
        if($input->getOption('id_ai') && $input->getOption('id_sh')){
            $this->associateToCron($idAi,$idSh);
        }
        // Permet d'éviter les erreurs 500 sur le chargement de données volumineuses ou de les tronquer lors de leur insertion en bdd
        set_time_limit(0);
        ini_set("memory_limit", "-1");
        ini_set('mysql.connect_timeout', '0');
        ini_set('max_execution_time', '0');
        
        $this->oLog->info('Lancement de la generation du recapitulatif paye/tournees pour le mois courant via ' . $this->sNomCommande);
        $this->container = $this->getContainer();

        try {
            // Traitement des arguments
            $sArgDateDebut  = $input->getOption('date_debut');
            $sArgDateFin  = $input->getOption('date_fin');
            $sArgFicExport  = $input->getOption('fichier_export');
            $bNoUpdate  = $input->getOption('no_update');
            $bNoEmail  = $input->getOption('no_email');
            $bNoCopy  = $input->getOption('no_copy');

            $oEmailService = $this->container->get('email');
            /* @var $oEmailService \Ams\SilogBundle\Services\Amsemail */

            $sUrlBase = $this->container->getParameter('MROAD_VERSION_'.  strtoupper($this->container->get('kernel')->getEnvironment()).'_URL');
            $sUrlBase .= (substr($sUrlBase, -1) == '/') ? '' : '/';
            
            $em = $this->container->get('doctrine')->getManager();
            $sCodeMois = $em->getRepository('AmsPaieBundle:PaiRefMois')->getMoisCourant();
            
            $oDateDebut = new \DateTime();
            $oDateFin = new \DateTime();
            
            // Date de début
            if (!is_null($sArgDateDebut)){
                $aDateDebutParts = explode('/',$sArgDateDebut);
                $oDateDebut->setDate($aDateDebutParts[2], $aDateDebutParts[1], $aDateDebutParts[0]);
                $oDateDebut->setTime(0, 0, 0);
            }
            else{
                $oDateDebut = $em->getRepository('AmsPaieBundle:PaiRefMois')->findOneByAnneemois($sCodeMois)->getDateDebut();
            }
            
            // Date de fin
            if (!is_null($sArgDateFin)){
                 $aDateFinParts = explode('/',$sArgDateFin);
                 $oDateFin->setDate($aDateFinParts[2], $aDateFinParts[1], $aDateFinParts[0]);
            }
            
            // Récupération des flux pour l'affichage
            $aObjFlux = $em->getRepository('AmsReferentielBundle:RefFlux')->findAll();
            $aFlux = array();
            if (!empty($aObjFlux)) {
                foreach ($aObjFlux as $oFlux) {
                    $aFlux[$oFlux->getId()] = $oFlux->getLibelle();
                }
            }

            $aRecapResults = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getPaieRecapInfo($oDateDebut, $oDateFin);
            
            if (!empty($aRecapResults)) {
                
                // @TODO Alimenter la table de reporting
                try
                {
                    if (!$bNoUpdate){  // Mise à jour des données de reporting
                        $em->getRepository('AmsReportingBundle:ReportPilotageCentre')->setToDelete($oDateDebut,$oDateFin);
                        $em->getRepository('AmsReportingBundle:ReportPilotageCentre')->insertDataReporting($aRecapResults);
                        $em->getRepository('AmsReportingBundle:ReportPilotageCentre')->deleteDataReporting();
                    }
                    
                } catch (\Exception $ex) {
                    $this->suiviCommand->setMsg($ex->getMessage());
                    $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type($ex->getCode()));
                    $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                    $this->suiviCommand->setEtat("KO");
                    $this->oLog->erreur("Une erreur est survenu lors de l'alimentation de la table de reporting", $ex->getCode());
                    $this->registerError();
                    if($input->getOption('id_ai') && $input->getOption('id_sh')){
                        $this->registerErrorCron($idAi);
                    }
                    echo 'Exception reçue : ',  $ex->getMessage(), "\n";
                }
                
                // Construction du chemin vers le fichier CSV
                $sFileName = uniqid($this->container->getParameter('CRON_MAIL_RECAP_PAIE_TOURNEE_CSV_PREFIX')) . '-' . $sCodeMois . '.csv';
                if ($this->container->get('kernel')->getEnvironment() == 'prod' 
                        && $this->container->hasParameter('CRON_MAIL_RECAP_PAIE_TOURNEE_CSV_FS_FOLDER')){
                    $sBaseFolder = $this->container->get('kernel')->getRootDir();
                    $sTmpFolder = (substr($sBaseFolder, -1)) == DIRECTORY_SEPARATOR ? $sBaseFolder . $this->container->getParameter('CRON_MAIL_RECAP_PAIE_TOURNEE_CSV_FS_FOLDER') : $sBaseFolder . DIRECTORY_SEPARATOR . $this->container->getParameter('CRON_MAIL_RECAP_PAIE_TOURNEE_CSV_FS_FOLDER');
                    $sDlFolderPath = $this->container->getParameter('CRON_MAIL_RECAP_PAIE_TOURNEE_CSV_FOLDER');
                    $sPjUrl = (substr($sUrlBase.$sDlFolderPath, -1)) == '/' ? $sUrlBase . $sDlFolderPath . $sFileName : $sUrlBase . $sDlFolderPath.'/' . $sFileName ;
                }
                else{
                    $sBaseFolder = $this->container->getParameter('REP_FICHIERS_CMD');
                    $sTmpFolder = (substr($sBaseFolder, -1)) == DIRECTORY_SEPARATOR ? $sBaseFolder . $this->container->getParameter('SOUSREP_FICHIERS_TMP') : $sBaseFolder . DIRECTORY_SEPARATOR . $this->container->getParameter('SOUSREP_FICHIERS_TMP');
                }
                
                if (!$sArgFicExport){
                    $sCsvFile = (substr($sTmpFolder, -1)) == DIRECTORY_SEPARATOR ? $sTmpFolder . $sFileName : $sTmpFolder . DIRECTORY_SEPARATOR . $sFileName;
                }
                else{
                    $sCsvFile = $sArgFicExport;
                }
                
                // Ecriture du fichier
                $fp = fopen($sCsvFile, 'w');

                // Titres de colonnes
                $aTitres = array(
                    'Nb_client_Abo',
                    'Nb_Ex_Abo',
                    'Nb_Diff',
                    'Nb_clients_DIV',
                    'Nb_Ex_DIV',
                    'Nb_ex_en_supplements',
                    'Nb_adresses',
                    'Etalon',
                    'nb_Heure',
                    'nb_km',
                    'nombre_reclam_brut',
                    'nombre_reclam_net',
                    'nombre_reclam_Div_brut',
                    'nombre_reclam_Div_Net',
                    'code_tournee',
                    'depot',
                    'date_distrib',
                    'flux',
                    'nb_Heure_decimal'
                );
                array_unshift($aRecapResults, $aTitres);
                
                foreach ($aRecapResults as $sField) {
                    
                    // Remplacement de l'ID de flux par son libellé
                    if ($sField['flux_id']){
                        $sField['flux_id'] = $aFlux[(int)$sField['flux_id']];
                    }
                    $sField['Etalon'] = str_replace('.', ',', $sField['Etalon']);
                    $sField['nb_km'] = str_replace('.', ',', $sField['nb_km']);
                    list($hour, $min, $sec) = explode(':', $sField['nb_Heure']);
                    $duree =  ($hour*60 + $min +$sec/60)/60;
                    $sField['nb_Heure_decimal'] = number_format($duree, 2, ',', ' ');
                    
                    // $sLigne = implode($sField, $this->container->getParameter('CRON_MAIL_RECAP_PAIE_TOURNEE_CSV_SEPARATOR'));
                    $sLigne = implode($this->container->getParameter('CRON_MAIL_RECAP_PAIE_TOURNEE_CSV_SEPARATOR'), $sField);
                    $sLigne = str_replace("\n", ' ',$sLigne);
                    $sLigne = str_replace("\r", ' ',$sLigne);
                    $sLigne .= "\n";
                    fputs($fp,  $sLigne);
                }

                fclose($fp);
                
                // Envoi du mail
                if (file_exists($sCsvFile)) {
                    $aMailDatas = array(
                        'sMailDest' => $this->container->getParameter('CRON_MAIL_RECAP_PAIE_TOURNEE_DEST_ADRESSES'),
                        'sSubject' => $this->container->getParameter('CRON_MAIL_RECAP_PAIE_TOURNEE_SUBJECT'),
                        'cc' => array($this->container->getParameter('EMAIL_SERVICE_DEFAULT_REPLYTO')),
                        'sContentHTML' => '<strong>MAJ CRM Distribution</strong><br/><br/>
Le fichier a bien été généré.<br/>Vous pouvez le télécharger en cliquant sur le lien ci-dessous.<br/><br/>'
                    );
                    
                    $sTemplate = 'AmsDistributionBundle:Emails:mail_recap_paie_tournee.mail.twig';

                    // Ajout d'une PJ
                    $aMailDatas['aAttachment'] = array(
                        'sFichier' => $sCsvFile,
                        'sNomFichier' => $sFileName,
                        'sMimeType' =>'text/csv',
                    );
                    
                    if (isset($sPjUrl)){
                        $aMailDatas['sUrl'] = $sPjUrl;
                        $aMailDatas['sContentHTML'] = '<a href="'.$sPjUrl.'">Télécharger '.$sFileName.'</a>';
                        $sTemplate = 'AmsDistributionBundle:Emails:mail_recap_paie_tournee_prod.mail.twig';
                    }
                    
                    // Désactivation de l'envoi de mail si nécessaire
                    if (!$bNoEmail){
                        if ($oEmailService->send($sTemplate, $aMailDatas)) {
                            $this->oLog->info('Le fichier ' . $sFileName . ' a été envoyé à ' . $aMailDatas['sMailDest']);
                            // Suppression du fichier
                            if ($this->container->get('kernel')->getEnvironment() != "prod"){
                                unlink($sCsvFile);
                            }
                        } else {
                            $this->oLog->info('Le fichier ' . $sFileName.' n\'a pas pu être envoyé.');
                        }
                    }
                    else{
                        $this->oLog->info('Demande d\'annulation d\'envoi d\'email.');
                    }
                    
                    // Copie du fichier sur le dossier des batchs
                    if (!$bNoCopy){
                        $sBatchRep = $this->container->getParameter('CRON_MAIL_RECAP_PAIE_TOURNEE_DEST_FOLDER');
                        $sBatchFname = $this->container->getParameter('CRON_MAIL_RECAP_PAIE_TOURNEE_BATCH_FNAME');
                        if (!copy($sCsvFile, $sBatchRep.$sBatchFname)){
                            $this->oLog->info('Le fichier n\'a pas été sauvegardé dans le dossier des batchs');
                        }
                    }
                    else{
                        $this->oLog->info('Demande d\'annulation de copie de fichier batch.');
                    }
                    
                } else {
                    // @ Todo: Fichier non trouvé -> Envoyer un e-mail
                    $this->oLog->info('Fichier non trouvé: ' . $sCsvFile);
                }
            } else {
                // @Todo: Envoyer un e-mail pour dire qu'aucun enregistrement n'a été trouvé
                $this->oLog->info('Aucun enregistrement disponible.');
            }
        } catch (DBALException $DBALException) {
            $this->suiviCommand->setMsg($DBALException->getMessage());
            $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type($DBALException->getCode()));
            $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
            $this->suiviCommand->setEtat("KO");
            $this->oLog->erreur($DBALException->getMessage(), $DBALException->getCode(), $DBALException->getFile(), $DBALException->getLine());
            $this->registerError();
            if($input->getOption('id_ai') && $input->getOption('id_sh')){
                $this->registerErrorCron($idAi);
            }
        }
        
        $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
        $this->endTraitement();
        $this->oLog->info('Fin de la generation du recapitulatif paye/tournees pour le mois courant ' . $this->sNomCommande);
        return;
    }
}
