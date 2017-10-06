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

use Ams\SilogBundle\Command\GlobalCommand;
use Ams\FichierBundle\Entity\FicExport;
use Ams\FichierBundle\Entity\FicRecap;
use Ams\FichierBundle\Entity\FicFtp;

/**
 * 
 * "Command" 
 * 
 * Pour executer, faire : 
 *                  php app/console cr_distribution_export <<fic_code>> 
 *      Expl : php app/console cr_distribution_export JADE_EXP_CR_DISTRIBUTION
 * 
 * 
 *
 */
/**
 * 
 * Exportation des fichiers de comptes rendus de distribution vers jade
 * 
 * Parametre : 
 *          Par defaut, on ne fait le calcul que le jour J-1 et J+0
 *               1.Jour minimum a calculer. C'est optionnel
 *               2.Jour maximum a calculer. C'est optionnel
 *                   Expl : J+1 J+5
 *                      Si les parametres sont renseignes, le traitement concerne les jours de distribution de "jour minimum" a "jour maximum"
 *                   Expl : J-1. => calculs a faire concernent J-1, J, J+1, J+2 & J+3
 *          Par defaut, on fait le calcule pour le flux nuit
 *              3. code flux (jn=jour ou jn=nuit) est optionnel
 * 
 * Exemple de commande : php app/console cr_distribution_export JADE_EXP_CR_DISTRIBUTION J+0 J+0 --jn=jour --pqr_id=59 --soc=MD,MP --id_sh=cron_test --id_ai=1  --env=prod
 * php app/console cr_distribution_export JADE_EXP_CR_DISTRIBUTION J+0 J+0 --jn=nuit --pqr_id=39 --soc=tout --id_sh=cron_test --id_ai=1  --env=prod
 * 
 * @author RAA
 *
 */
class CrDistributionExportCommand extends GlobalCommand
{
    private $aFichierFluxParam;
    private $sRepTmp;
    private $sRepBkpLocal;
    protected $idSh;
    protected$idAi;

    /**
     * [configure description]
     * @return [type] [description]
     */
    protected function configure()
    { 
        // Pour executer, faire : php app/console cr_distribution_export <<fic_code>> <<J+ou- >> Expl :  php app/console cr_distribution_export JADE_EXP_CR_DISTRIBUTION J-1 J+0 --jn=nuit --env=prod
        $sJourATraiterMinParDefaut = "J-1";
        $sJourATraiterMaxParDefaut = "J+0";
        $sJourOuNuitDefaut = "nuit";
        $sPQRIdDefaut = "39";
        $sSocDefaut = "tout";
        $fluxIds = "";
        $this->sNomCommande = 'cr_distribution_export';
        $this->setName($this->sNomCommande);
      
        $this->setDescription('Exportation des comptes rendus de distribution')->addArgument('fic_code', InputArgument::REQUIRED, 'Code source de donnees');
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
            $this->associateToCron($this->idAi ,$this->idSh);
        }
        
        $this->oLog->info(date("d/m/Y H:i:s : ")."Debut Export des comptes rendus de distribution - Commande : ".$this->sNomCommande);
        $sFicCode   = $input->getArgument('fic_code');// Expl : JADE_EXP_CR_DISTRIBUTION <=> exportation des fichiers de comptes rendus de reception vers jade
        
        $sJourOuNuit    = "nuit";
        if ($input->getOption('jn')) {
            $sJourOuNuit   = $input->getOption('jn');
        }
        $sJourOuNuit    = strtoupper($sJourOuNuit);
        $sFluxCode    = substr($sJourOuNuit, 0, 1);
        
        
        $repoRefFlux   = $this->getContainer()->get('doctrine')->getRepository('AmsReferentielBundle:RefFlux');
        $oFluxCode    = $repoRefFlux->findOneByCode($sFluxCode);
        
        $sSoc = "tout";
        if ($input->getOption('soc')) {
            $sSoc   = $input->getOption('soc');
        }
        
        $sPQRId = "39";
        if ($input->getOption('pqr_id')) {
            $sPQRId   = $input->getOption('pqr_id');
        }
        
        
        $em    = $this->getContainer()->get('doctrine')->getManager();     
        $cptrRepository  = $this->getContainer()->get('doctrine')->getRepository('AmsDistributionBundle:CptrDetailExNonDistrib');
        // Repertoire ou sauvegarde le fichier genere
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
            throw new \Exception("Identification de flux introuvable dans  $ficExport");
        }
        
        $socDistribId =   $sPQRId;
        /***/
        $sJourATraiterMin  = $input->getArgument('jour_a_traiter_min');
        $sJourATraiterMax  = $input->getArgument('jour_a_traiter_max');
        //$sFluxIds  = $input->getArgument('flux_prd');

        $iJourATraiter  = 0;
        $aiJourATraiter   = array();
        $aoJourATraiter   = array();
        //$aFluxAtraiter    = array();
        $aCheckFluxAtraiter    = array();
        $this->oLog->info("verification des parametres ");
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
         //recuperation des donnees (compte rendu de distribution) a exporter vers Jade 
        $result = $cptrRepository->getCptrDistributionToExport($aDatesYmdARecuperer, $sFluxCode, $sSoc);
       
        //fin d'operation si pas de donnees
        if(empty($result)){
            $this->oLog->info("Pas de compte rendu de distribution a exporter pour ce jour !!");
            $this->oLog->info("Fin de commande");
            return;
        }
        $this->oLog->info("Fin de recuperation des donnees depuis la base ");
        
        $codeEtat = 0;
        $msgEtat  ='Ok';
        $delimiter =  $ficExport->getSeparateur();
        $format =  $ficExport->getFormatFic();
       //si on doit mettre a jour ou non  le champ date_export dans la table cptrReception
        $toUpdateDateExportl = false;
        $this->oLog->info("Debut de generation de(s) fichier(s)");
        //insertion des donnees dans le fichier pour l'exporter en suite vers jade
        $aFicCree  = $this->loadDataInFile($result, $socDistribId, $oFluxCode, $delimiter, $format);
        
        if(!empty($aFicCree))
        {
            //connexion ftp jade
            $srv_ftp    = $this->getContainer()->get('ijanki_ftp');
            $srv_ftp->connect($ficFtp->getServeur());
            $srv_ftp->login($ficFtp->getLogin(), $ficFtp->getMdp());
            $srv_ftp->chdir($ficFtp->getRepertoire());
            
            $sFicV = $aFicCree['nom_fichier'];

            if(file_exists($this->sRepTmp.'/'.$sFicV))
            {
                if ($srv_ftp->put('/'.$ficFtp->getRepertoire().'/'.$sFicV, $this->sRepTmp.'/'.$sFicV, FTP_BINARY)) {
                        $this->oLog->info("envoi du fichier ".$sFicV.'du FTP '.$ficFtp->getServeur().'/'.$ficFtp->getRepertoire());
                        $this->enregistreFicRecap($sFicCode,  $ficExport->getFlux(), $sFicV, $aFicCree['nb_lignes'], $codeEtat, 'OK');
                        $this->oLog->info("enregistrement du fihier ".$sFicV." dans la table ficRecap");
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
        }else{
            $nom = $socDistribId.'_CRD_'.date("Ymd").'_'.$oFluxCode->getId().'.'.$format;
            $this->suiviCommand->setMsg("Une erreur s'est produite lors de la creation du fichier ".$this->sRepTmp.'/'.$nom."  ");
            $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
            $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
            $this->suiviCommand->setEtat("KO");
            $this->oLog->erreur("Une erreur s'estproduite lors de la creation du fichier ".$this->sRepTmp.'/'.$nom."  ", E_USER_ERROR);
             $this->registerError();
            if($input->getOption('id_ai') && $input->getOption('id_sh')){
                $this->registerErrorCron($this->idAi);
            }
        }
        
        $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
        $this->endTraitement();
        $this->oLog->info(date("d/m/Y H:i:s : ")."Fin Export des comptes rendus de distribution - Commande : ".$this->sNomCommande);
        
    }
    
    
    private function loadDataInFile($data, $socDistribId, $oJourOuNuit, $delimiter, $format){
        
        $sDateJour  = date("Ymd");
        $sFicNom = $socDistribId.'_CRD_'.$sDateJour.'_'.$oJourOuNuit->getId().'.'.$format;
        $iNbLignes  = 0;
        
        $oFichierSortie = fopen($this->sRepTmp.'/'.$sFicNom, "w+");
        if($oFichierSortie===false)
        {
            echo "\nERREUR lors de la creation du fichier ".$this->sRepTmp.'/'.$sFicNom."\n";
            // des que l'on rencontre une erreur, on arrete tous les traitements
            return array();
        }
        else
        {
            $aRetourInfos['nom_fichier']   = $sFicNom;
            foreach($data as $aArr)
            {
                $aLigne = array();
                $iNbLignes++;
                
                /*
                 39|2014/09/26|60006|EC|1|1|6||2 ||14
                 39|2014/09/26|60006|EC|1|0| ||21||14
                 
                    // 0	-> ID soc. Distrib.
                    // 1	-> Date de distribution 
                    // 2	-> Code INSEE
                    // 3	-> Code societe PQR
                    // 4	-> ID Flux
                    // 5	-> Code anomalie
                    // 6	-> Code incident
                    // 7	-> Commentaire
                    // 8	-> Nb clients impactes
                    // 9	-> Heure fin tournee
                    // 10	-> ID depot PQR
                 */
                $aLigne[]   = $socDistribId;
                $aLigne[]   = $aArr['date_distrib'];
                $aLigne[]   = $aArr['code_insee'];
                $aLigne[]   = $aArr['code_societe'];
                $aLigne[]   = $aArr['flux_id'];
                $aLigne[]   = $aArr['code_anomalie'];
                $aLigne[]   = $aArr['code_incident'];
                $aLigne[]   = utf8_decode(str_replace($delimiter, '-', $aArr['cmt_incident_ab']));
                $aLigne[]   = $aArr['nb_abonnes_impactes'];
                $aLigne[]   = $aArr['heure_fin_tournee'];
                $aLigne[]   = $aArr['depot_code'];
                
                fwrite($oFichierSortie, implode($delimiter, $aLigne)."\n");
            }
            fclose($oFichierSortie);
            $aRetourInfos['nb_lignes']   = $iNbLignes;
        }
        
        return $aRetourInfos;
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
    private function enregistreFicRecap($sFicCode, $oFicFlux, $sFicNom, $nbLigne, $codeEtat = NULL, $msgEtat) 
    {
        try {
         
            $em    = $this->getContainer()->get('doctrine')->getManager();
            $repoFicRecap = $this->getContainer()->get('doctrine')->getRepository('AmsFichierBundle:FicRecap');
            $oFicRecap = new FicRecap();
            $oFicRecap->setCode($sFicCode);
            $oFicRecap->setFlux($oFicFlux);
            $oFicRecap->setNom($sFicNom);
            $oFicRecap->setOrigine(3); // voir le fichier de config "mroad.ini. => ORIGINE[0]== origine fichier
              
                $oFicRecap->setNbLignes($nbLigne);
            

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
            $this->suiviCommand->setMsg($ex->getMessage());
            $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type($ex->getCode()));
            $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
            $this->suiviCommand->setEtat("KO");
            $this->oLog->erreur($ex->getMessage(), $ex->getCode());
            $this->registerError();
            if($input->getOption('id_ai') && $input->getOption('id_sh')){
                $this->registerErrorCron($this->idAi);
            }
            throw $ex;
        }
    }
}
