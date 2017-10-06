<?php

namespace Ams\DistributionBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ams\SilogBundle\Command\GlobalCommand;

/**
 * Envoi du premier fichier du suivi de production par email
 *          Ex: php app/console sendmail_suivi_production --env=[prod|recette]
 * 
 * @author Yannick Dieng
 */
class SendMailSuiviProductionCommand extends GlobalCommand {
    
    private $repBkpLocal;
    
    protected function configure() {
        $this->sNomCommande = 'sendmail_suivi_production';
        $this->setName($this->sNomCommande);
        $this->setDescription('Envoi par e-mail du premier fichier (non vide) du suivi de production.')
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
            $this->oLog->info(date("d/m/Y H:i:s : ") . "Debut Gestion Mail Suivi De Production - Commande : " . $this->sNomCommande);

            //Recuperation des différents  repository & service
            $this->container = $this->getContainer();
            $emailService = $this->getContainer()->get('email');
            $suiviDeProductionMailRepo = $this->getContainer()->get('doctrine')->getRepository('AmsDistributionBundle:SuiviDeProductionMail');
            $suiviDeProductionRepo = $this->getContainer()->get('doctrine')->getRepository('AmsDistributionBundle:SuiviDeProduction');
            $ficRecapRepo = $this->getContainer()->get('doctrine')->getRepository('AmsFichierBundle:FicRecap');
            $ficChrgtFichiersBddRepo = $this->getContainer()->get('doctrine')->getRepository('AmsFichierBundle:FicChrgtFichiersBdd');
            $societeRepo = $this->getContainer()->get('doctrine')->getRepository('AmsProduitBundle:Societe');
            
            //construction de l'url pour le telechargement de la PJ
            $urlBase = $this->container->getParameter('MROAD_VERSION_'.  strtoupper($this->container->get('kernel')->getEnvironment()).'_URL');
            $urlBase .= (substr($urlBase, -1) == '/') ? '' : '/';
            
            $ficCode = "SUIVI_PRODUCTION";
            $socCode = "LP";
             
            // Identifiant de la societe  societe 'LP'
            $tabSocId = $societeRepo->getIdsocByCode($socCode);
            // Recuperation des parameters concernant le FTP et les sous repertoire de configuration
            $ficChrgtFichiersBdd = $ficChrgtFichiersBddRepo->findOneByCode($ficCode);

            if (is_null($ficChrgtFichiersBdd)) {
                $this->suiviCommand->setMsg("Le flux " . $ficCode . " n'est pas un parametre dans 'fic_chrgt_fichiers_bdd'");
                $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
                $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                $this->suiviCommand->setEtat("KO");
                $this->oLog->erreur("Le flux " . $ficCode . " n'est pas un parametre dans 'fic_chrgt_fichiers_bdd'", E_USER_ERROR);
                $this->registerError();
                if($input->getOption('id_ai') && $input->getOption('id_sh')){
                    $this->registerErrorCron($idAi);
                }
                throw new \Exception("Identification de flux introuvable dans la table 'fic_chrgt_fichiers_bdd'");
            }
            
            // Repertoire ou l'on recupere(ou se trouve) les fichiers  qui on été intégrés  aprés avoir été importés
            $this->repBkpLocal = $this->sRepFichiersPrinc . DIRECTORY_SEPARATOR . $this->getContainer()->getParameter('SOUSREP_FICHIERS_BKP') . DIRECTORY_SEPARATOR . $ficChrgtFichiersBdd->getSsRepTraitement() . DIRECTORY_SEPARATOR . $ficCode;
             
            //on cherche les editions à J+1 , le CRON tourne a J-1 de l'edition jusqu'a 23h59
            $dateParution = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")+1, date("Y")));
            
            //verification si le mail a deja été envoyé
            $alreadySent = $suiviDeProductionMailRepo->getDatasMailSentByDateEdition($dateParution);
            if (count($alreadySent) > 0){
                $dest = $this->container->getParameter('CRON_MAIL_SUIVI_PRODUCTION_DEST');
                $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
        $this->endTraitement();
                $this->oLog->info(date("d/m/Y H:i:s : ") . "Le mail a déja été envoyé à  '".$dest."' avec le fichier suivant '".$alreadySent[0]['nom']."' le '".$alreadySent[0]['date_envoi']."' ");
                $this->oLog->info(date("d/m/Y H:i:s : ") . "Fin Gestion Mail Suivi De Production - Commande : " . $this->sNomCommande);
                return;
            }
            
            // verification de l'existence de fichier suivi de Production integre et non vide
            $ficIntegrate = $ficRecapRepo->getFirstFicNameByCodeAndDateParutionAndEtat($ficCode, $dateParution);
            if(count($ficIntegrate) == 0){
                //pas de fichier encore integre
                $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                $this->endTraitement();
                $this->oLog->info(date("d/m/Y H:i:s : ") . "Aucun fichier pour le '".$dateParution."' n'a encore ete integre. ");
                $this->oLog->info(date("d/m/Y H:i:s : ") . "Fin Gestion Mail Suivi De Production - Commande : " . $this->sNomCommande);
                return;
            }else{
                // Recuperation nom du fichier
                $ficIntegrateName = $ficIntegrate[0]['nom'];
                $ficIntegrateFullPath = $this->repBkpLocal."/".$ficIntegrateName;
                
                //Construction de l'url de la PJ
                if ($this->container->hasParameter('CRON_MAIL_SUIVI_PRODUCTION_CSV_FS_FOLDER'))
                {
                    $dlFolderPath = $this->container->getParameter('CRON_MAIL_SUIVI_PRODUCTION_CSV_FOLDER');
                    $pjUrl = (substr($urlBase.$dlFolderPath, -1)) == DIRECTORY_SEPARATOR ? $urlBase . $dlFolderPath . $ficIntegrateName : $urlBase . $dlFolderPath. DIRECTORY_SEPARATOR . $ficIntegrateName ;
                }
                
                //copy du fichier dans le repertoire de download
                $tmpFolder = $this->container->get('kernel')->getRootDir(). DIRECTORY_SEPARATOR . $this->container->getParameter('CRON_MAIL_SUIVI_PRODUCTION_CSV_FS_FOLDER');
                $ficToDlFullPath = (substr($tmpFolder, -1)) == DIRECTORY_SEPARATOR ? $tmpFolder . $ficIntegrateName : $tmpFolder . DIRECTORY_SEPARATOR . $ficIntegrateName;
                if (!copy($ficIntegrateFullPath, $ficToDlFullPath)){
                        $this->oLog->info('Le fichier n\'a pas été copié dans le dossier des telechargements');
                }
                
                //envoi du mail
                if (file_exists($ficToDlFullPath)) {
                    $mailDatas = array(
                        'sMailDest' => $this->container->getParameter('CRON_MAIL_SUIVI_PRODUCTION_DEST'),
                        'sSubject' => $this->container->getParameter('CRON_MAIL_SUIVI_PRODUCTION_SUBJECT'),
                        'cc' => array($this->container->getParameter('EMAIL_SERVICE_DEFAULT_REPLYTO')),
                        'sContentHTML' => '<strong>Suivi de Production</strong><br/><br/>
                                        Le premier fichier de production: <strong>'.$ficIntegrateName.' </strong> a bien été intégré dans MROAD.<br/>'
                    );
                    $template = 'AmsDistributionBundle:Emails:mail_suivi_de_production.mail.twig';
                    
                    // Ajout de la  PJ
                    $mailDatas['Attachment'] = array(
                        'sFichier' => $ficToDlFullPath,
                        'sNomFichier' => $ficIntegrateName,
                        'sMimeType' =>'text/csv',
                    );
                    
                    if (isset($pjUrl)){
                        $mailDatas['sUrl'] = $pjUrl;
                        $mailDatas['sContentHTML'] = '<strong>Suivi de Production</strong><br/><br/>
                                                    Le premier fichier de production: <strong>'.$ficIntegrateName.' </strong> a bien été intégré dans MROAD.<br/>
                                                    Vous pouvez le télécharger en cliquant sur le lien ci-dessous. <br/><br/>
                                                    <a href="'.$pjUrl.'">Télécharger '.$ficIntegrateName.'</a>';
                    }
                    
                    // l'envoi de mail n'est actif qu'en Prod
                    if ($emailService->send($template, $mailDatas)) 
                    {
                        $etat = "Ok";
                        $dateSent = date("Y-m-d H:i:s");
                        $id = $suiviDeProductionMailRepo->insertValues($ficIntegrateName,$dateParution,1,$dateSent,$etat);
                        $this->oLog->info('Le fichier ' . $ficIntegrateName . ' a été envoyé à ' . $mailDatas['sMailDest']);
                    } else {
                        $etat = "Erreur lors de l'envoi";
                        $dateSent = date("Y-m-d H:i:s");
                        $id = $suiviDeProductionMailRepo->insertValues($ficIntegrateName,$dateParution,0,$dateSent,$etat);
                        $this->oLog->info('Le fichier ' . $ficIntegrateName.' n\'a pas pu être envoyé.');
                    }
                    
                }else{
                    //error d'emplacement
                    $mailDatas = array(
                        'sMailDest' => $this->container->getParameter('CRON_MAIL_SUIVI_PRODUCTION_DEST'),
                        'sSubject' => $this->container->getParameter('CRON_MAIL_SUIVI_PRODUCTION_SUBJECT_ERR'),
                        'cc' => array($this->container->getParameter('EMAIL_SERVICE_DEFAULT_REPLYTO')),
                        'sContentHTML' => '<strong>Suivi de Production</strong><br/><br/>
                            Le premier fichier de production:  <strong>'.$ficIntegrateName.' </strong> a bien été intégré dans MROAD.<br/>
                            Malheuresement une erreur est survenu lors du transfert <br\>
                            Merci de récupérez ce dernier sur le FTP SIMGAM : ftp2.simgam.fr dans le dossier Archives.<br/><br/>
                            Veuillez nous excuser pour la géne occasionnée.<br/>'
                    );
                    $template = 'AmsDistributionBundle:Emails:mail_suivi_de_production.mail.twig';
                    
                    // l'envoi de mail n'est actif qu'en Prod
                    if ($emailService->send($template, $mailDatas)) 
                    {
                        $etat = "Mauvais emplacement Fichier";
                        $dateSent = date("Y-m-d H:i:s");
                        $id = $suiviDeProductionMailRepo->insertValues($ficIntegrateName,$dateParution,1,$dateSent,$etat); 
                        $this->oLog->info('Le fichier ' . $ficIntegrateName . ' a été envoyé à ' . $mailDatas['sMailDest']. ' avec une erreur sur l\'emplacement du fichier');
                        
                    } else {
                        $etat = "Erreur lors de l'envoi - Mauvais emplacement Fichier";
                        $dateSent = date("Y-m-d H:i:s");
                        $id = $suiviDeProductionMailRepo->insertValues($ficIntegrateName,$dateParution,0,$dateSent,$etat);
                        $this->oLog->info('Le fichier ' . $ficIntegrateName.' n\'a pas pu être envoyé.');
                    }
                }
            }
            $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
            $this->endTraitement();
            $this->oLog->info(date("d/m/Y H:i:s : ") . "Fin Gestion Mail Suivi De Production - Commande : " . $this->sNomCommande);
            return;
        }
}

