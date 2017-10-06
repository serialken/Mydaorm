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
 * "Command" exportation des fichiers de comptes rendus de réception vers jade
 * 
 * Pour executer, faire : 
 *                  php app/console cr_reception_export <<fic_code>> 
 *      Expl : php app/console cr_reception_export  JADE_EXP_CR_RECEPTION
 *       
 * Parametre : 
 *          Par defaut, on ne fait le calcul que le jour J-1 et J+0
 *               1.Jour minimum a calculer. C'est optionnel
 *               2.Jour maximum a calculer. C'est optionnel
 *                   Expl : J+1 J+5
 *                      Si les parametres sont renseignes, le traitement concerne les jours de distribution de "jour minimum" a "jour maximum"
 *                   Expl : J-1. => calculs a faire concernent J-1, J, J+1, J+2 & J+3
 * 
 *    php app/console cr_reception_export  JADE_EXP_CR_RECEPTION J-1 J+0 --pqr_id=39 --jn=nuit --soc=tout --id_sh=cron_test --id_ai=1 --env=prod
 *    php app/console cr_reception_export  JADE_EXP_CR_RECEPTION J-1 J+0 --pqr_id=59 --jn=jour --soc=MD,MP --id_sh=cron_test --id_ai=1 --env=prod
 *    php app/console cr_reception_export  JADE_EXP_CR_RECEPTION J+0 J+0 --pqr_id=39 --jn=tout --soc=tout --id_sh=cron_test --id_ai=1 --env=prod
 *
 */
class CrReceptionExportCommand extends GlobalCommand
{
    private $aFichierFluxParam;
    private $sRepTmp;
    private $sRepBkpLocal;
    protected $idAi;
    protected $idSh;

    /**
     * [configure description]
     * @return [type] [description]
     */
    protected function configure()
    {    
        $sJourATraiterMinParDefaut = "J-1";
        $sJourATraiterMaxParDefaut = "J+0";
    	$this->sNomCommande	= 'cr_reception_export';
        $sJourOuNuitDefaut = "tout";
        $sPQRIdDefaut = "39";
        $sSocDefaut = "tout";
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console cr_reception_export <<fic_code>> Expl :  php app/console cr_reception_export  JADE_EXP_CR_RECEPTION J-0 J+1
        $this
            ->setDescription('Exportation des comptes rendus de réception')
            ->addArgument('fic_code', InputArgument::REQUIRED, 'Code source de donnees');
        $this->addArgument('jour_a_traiter_min', InputArgument::OPTIONAL, 'Jour a traiter Min. Format : J<+Numerique> (Expl : J+1 ou J+2 ou J ...)', $sJourATraiterMinParDefaut);
        $this->addArgument('jour_a_traiter_max', InputArgument::OPTIONAL, 'Jour a traiter Max. Format : J<+Numerique> (Expl : J+1 ou J+2 ou J ...)', $sJourATraiterMaxParDefaut);
        $this->addOption('pqr_id',null, InputOption::VALUE_REQUIRED, 'ID PQR dans JADE : 39 pour Proximy, 59 pour MEDIA PRESSE', $sPQRIdDefaut);
        $this->addOption('jn',null, InputOption::VALUE_REQUIRED, 'jour ou nuit ?', $sJourOuNuitDefaut);
        $this->addOption('soc',null, InputOption::VALUE_REQUIRED, 'code_societe separes de ","', $sSocDefaut);
        $this->addOption('id_sh',null, InputOption::VALUE_REQUIRED, 'Libelle du CRON');
        $this->addOption('id_ai',null, InputOption::VALUE_REQUIRED, 'Id du CRON');
    }
    
    /**
     * [execute description]
     * @param  InputInterface  $input  [description]
     * @param  OutputInterface $output [description]
     * @return [type]                  [description]
     */
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
            $this->associateToCron($idAi,$idSh);
        }
        $this->oLog->info(date("d/m/Y H:i:s : ")."Debut Export des comptes rendus de reception - Commande : ".$this->sNomCommande);
        $sFicCode 	= $input->getArgument('fic_code');// Expl : JADE_EXP_CR_RECEPTION <=> exportation des fichiers de comptes rendus de réception vers jade
        
        $sPQRId = "39";
        if ($input->getOption('pqr_id')) {
            $sPQRId   = $input->getOption('pqr_id');
        }
        
        $sJourOuNuit    = "tout";
        if ($input->getOption('jn')) {
            $sJourOuNuit   = $input->getOption('jn');
        }
        $sFluxCode    = ( ($sJourOuNuit!='tout') ? strtoupper(substr($sJourOuNuit, 0, 1)) : $sJourOuNuit );
        
        $sSoc = "tout";
        if ($input->getOption('soc')) {
            $sSoc   = $input->getOption('soc');
        }
        
        $em    = $this->getContainer()->get('doctrine')->getManager();
            	
        $cptrRepository  = $this->getContainer()->get('doctrine')->getRepository('AmsDistributionBundle:CptrReception');
        
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
            $exception =  new \Exception("Identification de flux introuvable dans  $ficExport");
            throw $exception;
        }
        $now = new \DateTime();
        $date = $this->formatDate($now);
        $socDistribId =   $sPQRId;


        $sJourATraiterMin  = $input->getArgument('jour_a_traiter_min');
        $sJourATraiterMax  = $input->getArgument('jour_a_traiter_max');
       

        $iJourATraiter  = 0;
        $aiJourATraiter   = array();
        $aoJourATraiter   = array();
       
        $this->oLog->info("Verification des parametres ");
        if(preg_match('/^J([\-\+][0-9]+)?$/', $sJourATraiterMin, $aiJourATraiterMin) && preg_match('/^J([\-\+][0-9]+)?$/', $sJourATraiterMax, $aiJourATraiterMax))
        {
            $iJourATraiterMin = 0;
            $iJourATraiterMax = 0;

            if(isset($aiJourATraiterMin[1]))
            {
                $iJourATraiterMin  = intval($aiJourATraiterMin[1]);
            }
            
            if(isset($aiJourATraiterMax[1]))
            {
                $iJourATraiterMax  = intval($aiJourATraiterMax[1]);
            }
           
            if($iJourATraiterMax >= $iJourATraiterMin)
            {
                for($i=$iJourATraiterMin; $i<=$iJourATraiterMax; $i++)
                {
                    $aiJourATraiter[]    = $i;
                }
            }
            else
            {
                $this->suiviCommand->setMsg("Le jour MAX est anterieur au Jour MIN (Jour min : J".(($iJourATraiterMin>=0)?"+":"-").abs($iJourATraiterMin).". Jour max : J".(($iJourATraiterMax>=0)?"+":"-").abs($iJourATraiterMax).").");
                $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_WARNING));
                $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                $this->suiviCommand->setEtat("OK");
                $this->oLog->erreur("Le jour MAX est anterieur au Jour MIN (Jour min : J".(($iJourATraiterMin>=0)?"+":"-").abs($iJourATraiterMin).". Jour max : J".(($iJourATraiterMax>=0)?"+":"-").abs($iJourATraiterMax).").", E_USER_WARNING);
            }
        }
        else
        {
            $this->suiviCommand->setMsg("Jour a traiter. Format : J<+Numerique> (Expl : J-1 ou J-2 ou J ...)");
            $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_WARNING));
            $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
            $this->suiviCommand->setEtat("OK");
            $this->oLog->erreur("Jour a traiter. Format : J<+Numerique> (Expl : J-1 ou J-2 ou J ...)", E_USER_WARNING);
        }


        foreach($aiJourATraiter as $iJourATraiter)
        {
            $oDateDuJour    = new \DateTime();
            $oDateDuJour->setTime(0, 0, 0);
            $dateDistribATraiter   = $oDateDuJour;
            if($iJourATraiter<0)
            {
                $dateDistribATraiter   = $oDateDuJour->sub(new \DateInterval('P'.abs($iJourATraiter).'D'));
            }
            else
            {
                $dateDistribATraiter   = $oDateDuJour->add(new \DateInterval('P'.$iJourATraiter.'D'));
            }
            
            $aoJourATraiter[$iJourATraiter] = $dateDistribATraiter;
        }
     
        
        $aDatesYmdARecuperer    = array();
        foreach ($aoJourATraiter as $iJourATraiter => $oDateATraiterV)
        {
            $aDatesYmdARecuperer[]  = $oDateATraiterV->format('Y-m-d');
        }
        $this->oLog->info("Debut de recuperation des donnees depuis la base ");

         //récupération des données (compte rendu de reception) a exporter vers Jade
        $result = $cptrRepository->getCptrReceptionToExport($aDatesYmdARecuperer, $sFluxCode, $sSoc);
        //fin d'opération si pas de données
        if(empty($result)){
            $this->oLog->info("Pas de compte rendu de reception à exporter pour ce jour !!");
            $this->oLog->info("Fin de commande");
            return;
        }
     
        $socInfo = array();
        $socInfos =array();
        $codeEtat = 0;
        $msgEtat  ='Ok';
        $delimiter =  $ficExport->getSeparateur();
        $format =  $ficExport->getFormatFic();
       //si on doit mettre à jour ou non  le champ date_export dans la table cptrReception
        $toUpdateDateExportl = false;
        //insertion des données dans le fichier pour l'exporter en suite vers jade
        $socInfo  = $this->loadDataInFile($result, $socDistribId, $date, $sFicCode, $socInfo,  $delimiter, $format);
     

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
                                $prdFluxId = $this->getFluxId($sFicV); 
                                if(is_null( $prdFluxId)){
                                    $this->suiviCommand->setMsg("impossible de récupérer la valeur rpdiot_flux a partir du nom du fichier  ".$sFicV);
                                    $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
                                    $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                                    $this->suiviCommand->setEtat("KO");
                                    $this->oLog->erreur("impossible de récupérer la valeur rpdiot_flux a partir du nom du fichier  ".$sFicV, E_USER_ERROR);
                                    $msgEtat = "impossible de récupérer la valeur rpdiot_flux a partir du nom du fichier  ".$sFicV ;
                                    $this->registerError();
                                    if($input->getOption('id_ai') && $input->getOption('id_sh')){
                                        $this->registerErrorCron($this->idAi);
                                    }
                               }
                                if(array_key_exists($prdFluxId , $socInfo)){
                                    //fichier par prd_flux
                                    $socInfos  = $socInfo[$prdFluxId];
                                }
                                
                                $this->enregistreFicRecap($sFicCode,  $ficExport->getFlux(), $sFicV, $socInfos,$codeEtat, $msgEtat);
                                $this->oLog->info("enregistrement du fihier ".$sFicV." dans la table ficRecap");
                                $toUpdateCrmDetail = true;
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
            if( $toUpdateCrmDetail){
                $cptrRepository->UpdateDateExopt($result);
                $this->oLog->info("Mise à jour du champ date_export dans la table CptrReception terminée avec succée");
            }
        }
        
        $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
        $this->endTraitement();
        $this->oLog->info(date("d/m/Y H:i:s : ")."Fin Export des comptes rendus de reception - Commande : ".$this->sNomCommande);
    }


    /**
     * [getFluxId description]
     * @param  [type] $sFicV [description]
     * @return [type]        [description]
     */
    private function getFluxId($sFicV){
        $fileNameAsArray = explode('_', $sFicV);
        if(!empty($fileNameAsArray[3])){
            $fluxId = explode('.', $fileNameAsArray[3]);
            return $fluxId[0];
        }
        return null;

    }


    /**
     * [formatDate description]
     * @param  [type] $date [description]
     * @return [type]       [description]
     */
    private function formatDate($date){
        $now = $date->format( 'Y-m-d');
        return str_replace(array(':','-',' '), '',$now);
    }

    /**
     * [loadDataInFile description]
     * @param  [type] $data         [description]
     * @param  [type] $socDistribId [description]
     * @param  [type] $date         [description]
     * @param  [type] $sFicCode     [description]
     * @param  [type] $socInfo      [description]
     * @param  [type] $delimiter    [description]
     * @param  [type] $format       [description]
     * @return [type]               [description]
     */
    private function loadDataInFile($data, $socDistribId, $date, $sFicCode, $socInfo, $delimiter,$format){
        $content = "";
        $ligneCount = 0;
        $fluxIds = array();
        $filesName  = array();
        
        foreach ($data as $key => $row) {
            if(!in_array($row['prd_flux_id'], $fluxIds)){
                $fluxIds[] = $row['prd_flux_id'];
               if($key != 0){
                    file_put_contents($file,  $content);
                    $socInfo[$lastFluxId]['countRow'] = $ligneCount;
                    $content ="";$ligneCount=0;
                }
                $lastFluxId = $row['prd_flux_id'];
                $socInfo[$lastFluxId]['prd_flux_id'] = $row['prd_flux_id'];
                $fileName = $socDistribId.'_CRR_'.$date.'_'.$row['prd_flux_id'].'.'.$format;
                $file = $this->sRepTmp.'/'.$fileName;
            }
            $content .= $socDistribId.$delimiter.$row['date_distrib'].$delimiter.$row['depot_id'].$delimiter.$row['code_societe'].$delimiter.$row['code_produit'].$delimiter.
            $row['code_edition'].$delimiter.$row['prd_flux_id'].$delimiter.$row['date_reception'].$delimiter.$row['qte_recue']."\n";
            $ligneCount++;
            if($key == count($data) -1){
                file_put_contents($file,  $content);
                $socInfo[$lastFluxId]['countRow'] = $ligneCount;
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
                if (isset($socInfo['countRow'])) {
                    $oFicRecap->setNbLignes($socInfo['countRow']);
                }
            }

           /* if (!empty($socInfo['societe_id'])) {
                $oSocieteRepo = $this->getContainer()->get('doctrine')->getRepository('AmsProduitBundle:Societe');
                $oFicRecap->setSociete($oSocieteRepo->findOneById($socInfo['societe_id']));
            }*/
            $oFicRecap->setChecksum(0);
            $oFicRecap->setNbExemplaires(0);
            $oFicRecap->setFicSource($this->getContainer()->get('doctrine')->getRepository('AmsFichierBundle:FicSource')->findOneByCode('JADE'));
            if (!is_null($codeEtat)) {
                $oFicEtatRepo = $this->getContainer()->get('doctrine')->getRepository('AmsFichierBundle:FicEtat');
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
            throw $ex;
        }
    }
}
