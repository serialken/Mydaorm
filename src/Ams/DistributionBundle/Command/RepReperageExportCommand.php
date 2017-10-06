<?php 
namespace Ams\DistributionBundle\Command;

//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Ijanki\Bundle\FtpBundle\Exception\FtpException;
use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Lib\StringLocal;
use Ams\DistributionBundle\Exception\CrmReclamIntegrationCommandException;
use Ams\DistributionBundle\Exception\ClientsAServirSQLException;
use Ams\WebserviceBundle\Exception\RnvpLocalException;
use Ams\WebserviceBundle\Exception\GeocodageException;

use Ams\SilogBundle\Command\GlobalCommand;
use Ams\FichierBundle\Entity\FicExport;
use Ams\FichierBundle\Entity\FicRecap;

/**
 * 
 * "Command" exportation des fichier de réponse des réclamations vers jade
 * 
 * Pour executer, faire : 
 *                  php app/console rep_reperage_export <<fic_code>> 
 *      Expl : php app/console rep_reperage_export  JADE_EXP_REP_REPERAGE
 * 
 * 
 * @author aandrianiaina
 *
 */
class RepReperageExportCommand extends GlobalCommand
{
    private $aFichierFluxParam;
    private $sRepTmp;
    private $sRepBkpLocal;
    protected $idSh;
    protected $idAi;
    
    protected function configure()
    {
    	$this->sNomCommande	= 'rep_reperage_export';
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console rep_reperage_export <<fic_code>> Expl :  php app/console rep_reperage_export  JADE_EXP_REP_REPERAGE
        $this
            ->setDescription('Exportation des réponses des reclamations')
            ->addArgument('fic_code', InputArgument::REQUIRED, 'Code source de donnees')
            ->addOption('id_sh',null, InputOption::VALUE_REQUIRED, 'Libelle du CRON')
            ->addOption('id_ai',null, InputOption::VALUE_REQUIRED, 'Id du CRON')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
        if($input->getOption('id_sh')){
            $this->idSh = $input->getOption('id_sh');
        }
        if($input->getOption('id_ai')){
            $this->idAi = $input->getOption('id_ai');
        }
        if($input->getOption('id_ai') && $input->getOption('id_sh')){
            $this->associateToCron($this->idAi,$this->idSh);
        }
        
        $this->oLog->info(date("d/m/Y H:i:s : ")."Debut Export des reponses aux reperages - Commande : ".$this->sNomCommande);
        $sFicCode 	= $input->getArgument('fic_code');	// Expl : JADE_EXP_REP_RECLAM <=> Exportation des réponse au  reperage vers  JADE
        
        $em    = $this->getContainer()->get('doctrine')->getManager();
            	
        $crmRepository  = $this->getContainer()->get('doctrine')->getRepository('AmsDistributionBundle:CrmDetail');
        $reperageRepository  = $this->getContainer()->get('doctrine')->getRepository('AmsDistributionBundle:Reperage');
        // Repertoire ou sauvegarde le fichier généré
        $this->sRepTmp  = $this->cree_repertoire($this->sRepFichiersPrinc.'/'.$this->getContainer()->getParameter('SOUSREP_FICHIERS_TMP').'/'.$sFicCode);
   
        // Repertoire Backup Local
        $this->sRepBkpLocal = $this->cree_repertoire($this->sRepFichiersPrinc.'/'.$this->getContainer()->getParameter('SOUSREP_FICHIERS_BKP').'/'.$sFicCode);
        // Recuperation des parameters concernant le FTP et les fichiers a recuperer
       
        $ficFtp = $this->getContainer()->get('doctrine')->getRepository('AmsFichierBundle:FicFtp')->findOneBy(array('code' => $sFicCode));
        
        $ficExport = $this->getContainer()->get('doctrine')->getRepository('AmsFichierBundle:FicExport')->findOneBy(array('ficCode' =>$sFicCode));
        if(is_null($ficFtp))
        {
            $this->suiviCommand->setMsg("Le flux ".$ficFtp." n'est pas parametre dans  $ficExport");
            $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
            $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
            $this->suiviCommand->setEtat("KO");
            $this->oLog->erreur("Le flux ".$ficFtp." n'est pas parametre dans  $ficExport", E_USER_ERROR);
            $this->registerError();
            if($input->getOption('id_ai') && $input->getOption('id_sh')){
                $this->registerErrorCron($this->idAi);
            }
            $execption = new \Exception("Identification de flux introuvable dans  $ficExport");
            throw $execption;
        }
        $now = new \DateTime();
        $date = $this->formatDate($now);
        $socDistribId =   $ficFtp->getIdSocDistrib();
        $result = $reperageRepository->getReperageRespenseToExport();
        if(empty($result)){
            $this->oLog->info("Pas de réponse au repérage à exporter pour ce jour !!");
            $this->oLog->info("Fin de commande");
            return;
        }

        $socInfo = array();
        $socInfos =array();
        $codeEtat = 0;
        $toUpdateReperage = false;
        $delimiter =  $ficExport->getSeparateur();
        //récupération des données
        $socInfo  = $this->loadDataInFile($result, $socDistribId, $date, $sFicCode, $socInfo,  $delimiter);
        $filesToExport = scandir($this->sRepTmp);
        //connexion ftp jade
        $srv_ftp    = $this->getContainer()->get('ijanki_ftp');
        $srv_ftp->connect($ficFtp->getServeur());
        $srv_ftp->login($ficFtp->getLogin(), $ficFtp->getMdp());
        $srv_ftp->chdir($ficFtp->getRepertoire());
        
        if(!empty($filesToExport))
        {
            foreach($filesToExport as $sFicV)
            {
                if(file_exists($this->sRepTmp.'/'.$sFicV))
                {
                    if($sFicV !='.' and $sFicV !='..'){     
                        if ($srv_ftp->put('/'.$ficFtp->getRepertoire().'/'.$sFicV,$this->sRepTmp.'/'.$sFicV, FTP_BINARY)) {
                                $this->oLog->info("envoi du fichier ".$sFicV.'du FTP '.$ficFtp->getServeur().'/'.$ficFtp->getRepertoire());
                                $socId = $this->getSocieteId($sFicV);
                                if(array_key_exists($socId , $socInfo)){
                                    $socInfos  = $socInfo[$socId];
                                }
                                
                                if(file_exists($this->sRepTmp.'/'.$sFicV))
                                {
                                    rename($this->sRepTmp.'/'.$sFicV, $this->sRepBkpLocal.'/'.$this->oString->renommeFicDeSvgrde($sFicV, $this->sDateCourantYmd, $this->sHeureCourantYmd));
                                }
                                
                                 $this->oLog->info("enregistrement du fihier ".$sFicV." dans la table ficRecap");
                                 $toUpdateReperage = true;
                        } else {
                            $this->suiviCommand->setMsg("Probleme d'exportation du fichier ".$sFicV.'sur  FTP '.$ficFtp->getServeur().'/'.$ficFtp->getRepertoire());
                            $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
                            $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                            $this->suiviCommand->setEtat("KO");
                            $this->oLog->erreur("Probleme d'exportation du fichier ".$sFicV.'sur  FTP '.$ficFtp->getServeur().'/'.$ficFtp->getRepertoire(), E_USER_ERROR);
                            $this->registerError();
                            if($input->getOption('id_ai') && $input->getOption('id_sh')){
                                $this->registerErrorCron($this->idAi);
                            }
                        }
                    }
                }
            }
            if( $toUpdateReperage){
               $this->UpdateReperage($result);
            }
        }
        
        $reperageRepository->majDateFinAdresse();
        $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
        $this->endTraitement();
        $this->oLog->info(date("d/m/Y H:i:s : ")."Fin Export des reponses aux reperages - Commande : ".$this->sNomCommande);
    }

    private function UpdateReperage($data){
        $em = $this->getContainer()->get('doctrine')->getManager();
        $now = new \DateTime();
        $repoReperage   = $em->getRepository('AmsDistributionBundle:Reperage');
        foreach ($data as $key => $row) {
            $rep = $repoReperage->findOneById($row['rep_id']);
            $this->oLog->info("Information de la colone reperage.date_export correspondant a reperage.id = ".$rep->getId());
            $rep->setDateExport($now);
            
            $this->oLog->info("Transformation de commentaire en info portage pour les tope A. -> reperage.id = ".$rep->getId());
            $repoReperage->integrationInfoPortage($row['rep_id'], $this->getContainer()->getParameter('DATE_FIN'));            
        }
        $em->flush();
        $this->oLog->info("Mise à jour de la table Reperage terminée avec succée");
    }

    private function getSocieteId($sFicV){

        $fileNameAsArray = explode('_', $sFicV);
        if(!empty($fileNameAsArray[1])){
            $crmDetail = $this->getContainer()->get('doctrine')->getRepository('AmsDistributionBundle:CrmDetail')->findOneBy(array('socCodeExt' =>$fileNameAsArray[1]));
        }
 
        if(!empty($crmDetail)){
           return  $crmDetail->getSociete()->getId();
        }

        return null;
    }


    private function formatDate($date){
        $now = $date->format( 'Y-m-d H:i');
        return str_replace(array(':','-',' '), '',$now);
    }

    private function loadDataInFile($data, $socDistribId, $date, $sFicCode, $socInfo, $delimiter){
        $content = "";
        $ligneCount = 0;
        $societeIds = array();
        $filesName  = array();
        
        foreach ($data as $key => $row) {
                  
            if(!in_array($row['societe_id'], $societeIds)){
                $societeIds[] = $row['societe_id'];

               if($key != 0){
                    file_put_contents($file,  $content);
                    $socInfo[$lastSocieteId]['countRow'] = $ligneCount;
                    $content ="";$ligneCount=0;
                }
                $lastSocieteId = $row['societe_id'];
                $socInfo[$lastSocieteId]['code_societe'] =    $row['code_societe'];
                $socInfo[$lastSocieteId]['societe_id']   =    $row['societe_id'];
                $fileName = $socDistribId.'_'.$row['code_societe'].'_REPREPERAGE_'.$date;
                $file = $this->sRepTmp.'/'.$fileName.'.txt';
               
            }
            $content .=  "".$row['num_parution'].$delimiter.
                            str_replace('-', '/',$row['date_parution']).$delimiter.
                            $row['num_abonne'].$delimiter.
                            $row['volet1'].$delimiter.
                            $row['volet2'].$delimiter.
                            $row['volet3'].$delimiter.
                            $row['volet4'].$delimiter.
                            $row['volet5'].$delimiter.
                            $row['code_postal'].$delimiter.
                            $row['ville'].$delimiter.
                            $row['type_de_portage'].$delimiter.
                            $row['code_societe'].$delimiter.
                            $row['code_titre'].$delimiter.
                            $row['num_edition'].$delimiter.
                            $row['nb_exemplaire'].$delimiter.
                            $row['divers'].$delimiter.
                            $row['digicode'].$delimiter.
                            $row['consigne_de_portage'].$delimiter.
                            $row['msg_pour_porteur'].$delimiter.
                            $row['insee'].$delimiter.
                            $row['qualif_id'].$delimiter.
                            $row['topage'].$delimiter.
                            $row['commentaire_reponse'].$delimiter.
                            $row['id_demande_jade']."\n";
            $ligneCount++;

         if($key == count($data) -1){
            file_put_contents($file,  utf8_decode($content));
            $socInfo[$lastSocieteId]['countRow'] = $ligneCount;
         }
        }
        return $socInfo;
    }



    /**
     * Enregistre le recapitulatif du traitement en cas d'erreur
     * @param string $sFicCode
     * @param \Ams\FichierBundle\Entity\FicFlux
     * @param string $sFicNom
     * @param array $aRecapFicCourant
     * @param integer $socId
     * @param integer $codeEtat
     * @param string $msgEtat
     * @throws \Doctrine\DBAL\DBALException
     */
    private function enregistreFicRecap($sFicCode, $oFicFlux, $sFicNom, $socInfo, $codeEtat = NULL, $msgEtat = NULL) 
    {
        try {
         
            $em    = $this->getContainer()->get('doctrine')->getManager();
            $repoFicRecap = $this->getContainer()->get('doctrine')->getRepository('AmsFichierBundle:FicRecap');
            $oFicRecap = new FicRecap();
            $oFicRecap->setCode($sFicCode);
            $oFicRecap->setFlux($oFicFlux);
            $oFicRecap->setNom($sFicNom);
            $oFicRecap->setOrigine(3); // voir le fichier de config "mroad.ini. => ORIGINE[0]== origine fichier
            if(!empty($socInfo))
            {
                if (isset($socInfo['code_societe'])) {
                    $oFicRecap->setSocCodeExt($socInfo['code_societe']);
                }else{
                      $codeEtat = 53;
                }
                if (isset($socInfo['countRow'])) {
                    $oFicRecap->setNbLignes($socInfo['countRow']);
                }
            
            }
            if (!empty($socInfo['societe_id'])) {
                $oSocieteRepo = $this->getContainer()->get('doctrine')->getRepository('AmsProduitBundle:Societe');
                $oFicRecap->setSociete($oSocieteRepo->findOneById($socInfo['societe_id']));
            }
            $oFicRecap->setChecksum(0);
            $oFicRecap->setNbExemplaires(0);
            
             $oFicRecap->setFicSource($this->getContainer()->get('doctrine')
                            ->getRepository('AmsFichierBundle:FicSource')
                            ->findOneByCode('JADE'));


            if (!is_null($codeEtat)) {

                $oFicEtatRepo = $this->getContainer()->get('doctrine')
                        ->getRepository('AmsFichierBundle:FicEtat');
                $oEtat = $oFicEtatRepo->findOneBy(array('code' => $codeEtat));

                if (!is_null($oEtat)) {
                    $oFicRecap->setFicEtat($oEtat);
                }
            }
            if (!is_null($msgEtat)) {
                $oFicRecap->setEtaMsg($msgEtat);
            }
            
            $em->persist($oFicRecap);
            $em->flush();
            
            // En Local - Sauvegarde du fichier
            rename($this->sRepTmp.'/'.$sFicNom, $this->sRepBkpLocal.'/'.$this->oString->renommeFicDeSvgrde($sFicNom, $this->sDateCourantYmd, $this->sHeureCourantYmd));
        } 
        catch (DBALException $ex) {
            $this->suiviCommand->setMsg($ex->getMessage());
            $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type($ex->getCode()));
            $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
            $this->suiviCommand->setEtat("KO");
            $this->oLog->erreur($ex->getMessage(), E_USER_ERROR);
            $this->registerError();
            if($input->getOption('id_ai') && $input->getOption('id_sh')){
                $this->registerErrorCron($idAi);
            }
            throw $ex;
        }
    }
}
