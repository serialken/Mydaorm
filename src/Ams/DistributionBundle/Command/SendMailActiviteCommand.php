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
 * Envoi du suivi d'activité par email
 *
 * @author Marc-Antoine Adélise
 * @update  tcamara 08/12/2016 prendre par defaut les 60 derniers jours 
 */
class SendMailActiviteCommand extends GlobalCommand {

    protected function configure() {
        $this->sNomCommande = 'sendmail_activite';
        $this->setName($this->sNomCommande);
        // Pour executer, faire : php app/console sendmail_activite  Expl : php app/console sendmail_activite
        $this
                ->setDescription('Envoie par e-mail le suivi d\'activite pour le mois courant.')
                ->addOption('date_debut', 'db', InputOption::VALUE_OPTIONAL, 'Date de debut de la periode JJ/MM/AAAA', NULL)
                ->addOption('date_fin', 'df', InputOption::VALUE_OPTIONAL, 'Date de fin de la periode JJ/MM/AAAA', NULL)
                ->addOption('fichier_export', 'fic', InputOption::VALUE_OPTIONAL, 'Le chemin vers le fichier de sortie', NULL)
                ->addOption('no_email', 'nomail', InputOption::VALUE_OPTIONAL, 'Permet de ne pas envoyer de mail si VRAI', FALSE)
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
        
        $this->oLog->info('Lancement de la generation du suivi d\'activite pour le mois courant via ' . $this->sNomCommande);
        $this->container = $this->getContainer();

        try {
            
            // Traitement des arguments
            $sArgDateDebut  = $input->getOption('date_debut');
            $sArgDateFin  = $input->getOption('date_fin');
            $sArgFicExport  = $input->getOption('fichier_export');
            $bNoEmail  = $input->getOption('no_email');

            $oEmailService = $this->container->get('email');
            /* @var $oEmailService \Ams\SilogBundle\Services\Amsemail */
            
            $sUrlBase = $this->container->getParameter('MROAD_VERSION_'.  strtoupper($this->container->get('kernel')->getEnvironment()).'_URL');
            $sUrlBase .= (substr($sUrlBase, -1) == '/') ? '' : '/';
            
            $em = $this->container->get('doctrine')->getManager();
            
//            $oDateDebut = $em->getRepository('AmsPaieBundle:PaiRefMois')->findOneByAnneemois($sCodeMois);
            
            // Définition de la période
            $sCodeMois = $em->getRepository('AmsPaieBundle:PaiRefMois')->getMoisCourant();
            $oDateDebut = new \DateTime();
            $oDateFin = new \DateTime();
            
            // Date de début par defaut on prend J-60
            $now = new \DateTime();
            $now->sub(new \DateInterval('P60D'));
            $dateDebut =  $now->format('d/m/Y');
  
            $sDateDebutFormat = is_null($sArgDateDebut) ? $dateDebut : $sArgDateDebut;
            $aDateDebutParts = explode('/',$sDateDebutFormat);
            $oDateDebut->setDate($aDateDebutParts[2], $aDateDebutParts[1], $aDateDebutParts[0]);
            $oDateDebut->setTime(0, 0, 0);
            
            // Date de fin
            if (!is_null($sArgDateFin)){
                 $aDateFinParts = explode('/',$sArgDateFin);
                 $oDateFin->setDate($aDateFinParts[2], $aDateFinParts[1], $aDateFinParts[0]);
            }
            else{
                $oDateFin = new \Datetime();
            }
            $oDateFin->setTime(23, 59, 59);           
           
            $aSuiviResults = $em->getRepository('AmsPaieBundle:PaiActivite')->getSuiviActiviteInfo($oDateDebut, $oDateFin);
            
            if (!empty($aSuiviResults)) {
                // Construction du chemin vers le fichier CSV
                $sFileName = uniqid($this->container->getParameter('CRON_MAIL_SUIVI_ACTIVITE_CSV_PREFIX')) . '-' . $sCodeMois . '.csv';
                
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
                
                // Définition du nom de fichier
                if (is_null($sArgFicExport)){
                    $sCsvFile = (substr($sTmpFolder, -1)) == DIRECTORY_SEPARATOR ? $sTmpFolder . $sFileName : $sTmpFolder . DIRECTORY_SEPARATOR . $sFileName;
                }
                else{
                    $sCsvFile = $sArgFicExport;
                }
                
                // Ecriture du fichier
                $fp = fopen($sCsvFile, 'w');
                
                // Titres de colonnes
                $aTitres = array(
                    'Depot',
                    'Nom',
                    'Prenom1',
                    'Activite',
                    'Code',
                    'Date',
                    'Durée',
                    'Nb Km Paye',
                    'Flux',
                    'Commentaire',
                    'Description',
                    'duree Decimal'
                );
                array_unshift($aSuiviResults, $aTitres);
                
                foreach ($aSuiviResults as $sField) {
                    $sField = array_map('utf8_decode',$sField);
                    $sField = str_replace($this->container->getParameter('CRON_MAIL_RECAP_PAIE_TOURNEE_CSV_SEPARATOR'), '-', $sField);
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
                        'sSubject' => $this->container->getParameter('CRON_MAIL_SUIVI_ACTIVITE_SUBJECT'),
                        'cc' => array($this->container->getParameter('EMAIL_SERVICE_DEFAULT_REPLYTO')),
                        'sContentHTML' => '<strong>Suivi de l\'activité</strong><br/><br/>
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
                        $sTemplate = 'AmsDistributionBundle:Emails:mail_suivi_activite_prod.mail.twig';
                    }
                    
                    // Copie du fichier sur le dossier des batchs
                    $sBatchRep = $this->container->getParameter('CRON_MAIL_RECAP_PAIE_TOURNEE_DEST_FOLDER');
                    $sBatchFname = $this->container->getParameter('CRON_MAIL_SUIVI_ACTIVITE_BATCH_FNAME');
                    if (!copy($sCsvFile, $sBatchRep.$sBatchFname)){
                        $this->oLog->info('Le fichier n\'a pas été sauvegardé dans le dossier des batchs');
                    }
                    
                    // Envoi du rapport par email
                    
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
                } else {
                    // @ Todo: Fichier non trouvé -> Envoyer un e-mail
                    $this->oLog->info('Fichier non trouvé: ' . $sCsvFile);
                }
            }
            else {
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
        $this->oLog->info('Fin de la generation du suivi d\'activite pour le mois courant ' . $this->sNomCommande);
        return;
    }
}
