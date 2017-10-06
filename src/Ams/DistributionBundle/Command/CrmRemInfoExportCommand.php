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
 * "Command" exportation des fichier remontée dinfo vers jade
 * 
 * Pour executer, faire : 
 *                  php app/console rem_info_export <<fic_code>> 
 *      Expl : php app/console rem_info_export  JADE_EXP_REM_INFO
 * 
 * 
 * @author aandrianiaina
 *
 */
class CrmRemInfoExportCommand extends GlobalCommand
{
    private $aFichierFluxParam;
    private $sRepTmp;
    private $sRepBkpLocal;
    protected $idAi;
    protected $idSh;


    protected function configure()
    {
    	$this->sNomCommande	= 'rem_info_export';
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console rem_info_export <<fic_code>> Expl :  php app/console rem_info_export  JADE_EXP_REM_INFO
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
        $this->oLog->info(date("d/m/Y H:i:s : ")."Debut Export des remontees d information - Commande : ".$this->sNomCommande);
        
        $sFicCode 	= $input->getArgument('fic_code');	// Expl : JADE_EXP_REP_RECLAM <=> Exportation des remonté d'info vers  JADE
        
        $em    = $this->getContainer()->get('doctrine')->getManager();
            	
        $crmRepository  = $this->getContainer()->get('doctrine')->getRepository('AmsDistributionBundle:CrmDetail');
        
        // Repertoire ou sauvegarde le fichier généré
        $this->sRepTmp  = $this->cree_repertoire($this->sRepFichiersPrinc.'/'.$this->getContainer()->getParameter('SOUSREP_FICHIERS_TMP').'/'.$sFicCode);
   
        // Repertoire Backup Local
        $this->sRepBkpLocal = $this->cree_repertoire($this->sRepFichiersPrinc.'/'.$this->getContainer()->getParameter('SOUSREP_FICHIERS_BKP').'/'.$sFicCode);
        // Recuperation des parameters concernant le FTP et les fichiers a recuperer
       
        $ficFtp = $this->getContainer()->get('doctrine')->getRepository('AmsFichierBundle:FicFtp')->findOneBy(array('code' => $sFicCode));
        
        $ficExport = $this->getContainer()->get('doctrine')->getRepository('AmsFichierBundle:FicExport')->findOneBy(array('ficCode' =>$sFicCode));
        if(is_null($ficFtp))
        {
            $this->suiviCommand->setMsg("Le flux ".$ficFtp." n'est pas parametre dans  ficExport");
            $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
            $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
            $this->suiviCommand->setEtat("KO");
            $this->oLog->erreur("Le flux ".$ficFtp." n'est pas parametre dans  ficExport", E_USER_ERROR);
             $this->registerError();
            if($input->getOption('id_ai') && $input->getOption('id_sh')){
                $this->registerErrorCron($this->idAi);
            }
            throw new \Exception("Identification de flux introuvable dans  $ficExport");
        }
        $now = new \DateTime();
        $date = $this->formatDate($now);
        $socDistribId =   $ficFtp->getIdSocDistrib();
        //récupération des données "remonté dinformation" depuis la table crmDetail pour les éxporter vers jade
        $result = $crmRepository ->getRemInformationToExport();
        if(empty($result)){
        $this->oLog->info("Pas de remontée d'information à exporter pour ce jour !!");
         $this->oLog->info("Fin de commande");
        return;
        }
        //délimiteur dans le fichier
        $delimiter =  $ficExport->getSeparateur();
        $socInfo = array();
        $socInfos =array();
        $codeEtat = 0;

        //si on doit mettre à jour ou non la table crmDetail
        $toUpdateCrmDetail = false;
        //insertion des données dans le fichier pour l'exporter en suite vers jade
        $socInfo  = $this->loadDataInFile($result, $socDistribId, $date, $sFicCode, $socInfo, $delimiter);
        
        if(!empty($socInfo))
        {
            $toUpdateCrmDetail = false;
            //connexion ftp jade
            $srv_ftp    = $this->getContainer()->get('ijanki_ftp');
            $srv_ftp->connect($ficFtp->getServeur());
            $srv_ftp->login($ficFtp->getLogin(), $ficFtp->getMdp());
            $srv_ftp->chdir($ficFtp->getRepertoire());
            
            foreach($socInfo as $sSocId => $aArr)
            {
                $sFicV  = $aArr['nom_fichier'];
                if(file_exists($this->sRepTmp.'/'.$sFicV))
                {
                    if ($srv_ftp->put('/'.$ficFtp->getRepertoire().'/'.$sFicV, $this->sRepTmp.'/'.$sFicV, FTP_BINARY)) {
                            $this->oLog->info("envoi du fichier ".$sFicV.'du FTP '.$ficFtp->getServeur().'/'.$ficFtp->getRepertoire());
                            
                            $this->enregistreFicRecap($sFicCode,  $ficExport->getFlux(), $sFicV, $aArr, $codeEtat, 'OK');
                            $this->oLog->info("enregistrement du fihier ".$sFicV." dans la table ficRecap");
                            $toUpdateCrmDetail = true;
                    } else {
                        $toUpdateCrmDetail = false;
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
            // des que l'on rencontre une erreur, on arrete tous les traitements
            if( $toUpdateCrmDetail ){
                $crmRepository->UpdateDateExoptInCrmDetail($result);
                $this->oLog->info("Mise à jour du champ date_export dans la table crmDetail terminée avec succes");
            }
        }
        
        $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
        $this->endTraitement();
        $this->oLog->info(date("d/m/Y H:i:s : ")."Fin Export des remontees d information - Commande : ".$this->sNomCommande);
        
        /*
        
        
        

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
                                
                                $this->enregistreFicRecap($sFicCode,  $ficExport->getFlux(), $sFicV, $socInfos, $codeEtat, 'OK');
                                 $this->oLog->info("enregistrement du fihier ".$sFicV." dans la table ficRecap");
                                $toUpdateCrmDetail = true;
                        } else {
                                $this->oLog->info("Probleme d'exportation du fichier ".$sFicV.'sur  FTP '.$ficFtp->getServeur().'/'.$ficFtp->getRepertoire(), E_USER_ERROR);
                        }
                            
                    }
                }
            
            }
            
            
            if( $toUpdateCrmDetail){ 
                $crmRepository->UpdateDateExoptInCrmDetail($result);
                $this->oLog->info("Mise à jour du champ date_export dans la table crmDetail terminée avec succée");
            }
           
        }
        
        $this->oLog->info(date("d/m/Y H:i:s : ")."Fin Export des remontees d information - Commande : ".$this->sNomCommande);
         * 
         */
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

    private function loadDataInFile($data, $socDistribId, $date, $sFicCode, $socInfo, $delimiter='|'){
        $content = "";
        $ligneCount = 0;
        $societeIds = array();
        $filesName  = array();
        
        $aDonnees   = array();
        $aRetourInfos   = array();
        
        foreach ($data as $key => $row) {
            $aRetourInfos[$row['societe_id']]['code_societe']   = $row['code_societe'];
            $aRetourInfos[$row['societe_id']]['societe_id']     = $row['societe_id'];
            if(!isset($aRetourInfos[$row['societe_id']]['countRow']))
            {
                $aRetourInfos[$row['societe_id']]['countRow']   = 1;
            }
            else
            {
                $aRetourInfos[$row['societe_id']]['countRow']++;
            }
            $aDonnees[$row['code_societe']][]   = $row;
        }
        
        if(!empty($aDonnees))
        {
            foreach($aDonnees as $sCodeSocV => $aArrays)
            {
                $sFicNom = $socDistribId.'_'.$sCodeSocV.'_REMINFO_'.$date.'.txt';
                $oFichierSortie = fopen($this->sRepTmp.'/'.$sFicNom, "w+");
                if($oFichierSortie===false)
                {
                    echo "\nERREUR lors de la creation du fichier ".$this->sRepTmp.'/'.$sFicNom."\n";
                    // des que l'on rencontre une erreur, on arrete tous les traitements
                    return array();
                }
                else
                {
                    foreach($aArrays as $aArr)
                    {
                        if(!isset($aRetourInfos[$aArr['societe_id']]['nom_fichier'])) 
                        {
                            $aRetourInfos[$aArr['societe_id']]['nom_fichier']   = $sFicNom;
                        }
                        
                        $aLigne = array();
                        $aLigne[]   = $socDistribId;
                        $aLigne[]   = $aArr['id_info_soc_distrib'];
                        $aLigne[]   = '';
                        $aLigne[]   = $aArr['num_abonne'];
                        $aLigne[]   = utf8_decode($aArr['volet1']);
                        $aLigne[]   = utf8_decode($aArr['volet2']);
                        $aLigne[]   = utf8_decode($aArr['volet3']);
                        $aLigne[]   = utf8_decode($aArr['volet4']);
                        $aLigne[]   = utf8_decode($aArr['volet5']);
                        $aLigne[]   = $aArr['code_postal'];
                        $aLigne[]   = utf8_decode($aArr['ville']);
                        $aLigne[]   = $aArr['insee'];
                        $aLigne[]   = $aArr['code_societe'];
                        $aLigne[]   = '';
                        $aLigne[]   = $aArr['code_remonte_info'];
                        $aLigne[]   = utf8_decode($aArr['commentaire_demande']);
                        $aLigne[]   = $this->trimDate($aArr['date_creation']);
                        $aLigne[]   = $aArr['date_debut'];
                        $aLigne[]   = $aArr['date_fin'];
                        $aLigne[]   = utf8_decode($aArr['saisie_par']);

                        fwrite($oFichierSortie, implode($delimiter, $aLigne)."\n");
                    }
                    fclose($oFichierSortie);
                }
            }
        }
        
        return $aRetourInfos;
        
        /*
         * Ramzi
        foreach ($data as $key => $row) {
                  
            if(!in_array($row['societe_id'], $societeIds)){
                $societeIds[] = $row['societe_id'];

               if($key != 0){
                    file_put_contents($file,  $content);
                    $socInfo[$lastSocieteId]['countRow'] = $ligneCount;
                    $content ="";$ligneCount=0;
                }
                $lastSocieteId = $row['societe_id'];
                $socInfo[$lastSocieteId]['code_societe'] = $row['code_societe'];
                $socInfo[$lastSocieteId]['societe_id'] =    $row['societe_id'];
                $fileName = $socDistribId.'_'.$row['code_societe'].'_REMINFO_'.$date;
                $file = $this->sRepTmp.'/'.$fileName.'.txt';
               
            }
            $content .= "".$socDistribId.$delimiter.$row['id_info_soc_distrib'].$delimiter.''.$delimiter.$row['num_abonne'].$delimiter.$row['volet1'].$delimiter.$row['volet2'].$delimiter.$row['volet3'].$delimiter.$row['volet4'].$delimiter.$row['volet5'].$delimiter.
                        $row['code_postal'].$delimiter.$row['ville'].$delimiter.$row['insee'].$delimiter.$row['code_societe'].$delimiter.''.$delimiter.
                        $row['code_remonte_info'].$delimiter.utf8_decode($row['commentaire_demande']).$delimiter.$this->trimDate($row['date_creation']).
                        $delimiter.$row['date_debut'].$delimiter.$row['date_fin'].$delimiter.utf8_decode($row['saisie_par'])."\n";

            $ligneCount++;
            if($key == count($data) -1){
                file_put_contents($file,  $content);
                $socInfo[$lastSocieteId]['countRow'] = $ligneCount;
            }
                 
        }
       
        return $socInfo;
         
         */
    }

    
    public function trimDate($date){

        $date = str_replace(array(':','-',' '),'',$date);
        return substr($date, 0,12);
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
           // $iDernierFicRecap = $repoFicRecap->insert($oFicRecap);
            
            // En Local - Sauvegarde du fichier
            rename($this->sRepTmp.'/'.$sFicNom, $this->sRepBkpLocal.'/'.$this->oString->renommeFicDeSvgrde($sFicNom, $this->sDateCourantYmd, $this->sHeureCourantYmd));
            
            //return $iDernierFicRecap;
        } 
        catch (DBALException $ex) {
            throw $ex;
        }
    }
    




}
