<?php 
namespace Ams\DistributionBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ijanki\Bundle\FtpBundle\Exception\FtpException;
use Ams\SilogBundle\Command\GlobalCommand;
use Ams\FichierBundle\Entity\FicRecap;
use Doctrine\DBAL\DBALException;

/**
 * 
 * Export vers DCS des clients a servir LP enrichis de leur tournee
 * 
 * 
 * Table "fic_flux" =>  id=71, fic_code = "DCS - Export vers DCS des CAS LP enrichis de leur tournee"
                        INSERT INTO fic_flux (id, libelle) VALUES (71, 'DCS - Export vers DCS des CAS LP enrichis de leur tournee');
 * 
 * Table "fic_ftp" =>   fic_code = "DCS_EXP_TOURNEE_CAS_LP" <=> Export vers DCS des Clients a servir LP enrichis de leur tournee
 *          INSERT INTO fic_ftp (code, serveur, login, mdp, repertoire, rep_sauvegarde, id_soc_distrib)
            VALUES ('DCS_EXP_TOURNEE_CAS_LP', '10.151.93.3', 'sdvp', 'sdvp', 'FTPtest/DCS/MROAD_RECETTE/Vers_DCS/CAS_Avec_Tournee', 'Bkp', 'DCS');

 * 
 * Table "fic_export" =>    fic_code = "DCS_EXP_TOURNEE_CAS_LP"
 *                      INSERT INTO fic_export (flux_id, regex_fic, format_fic, nb_lignes_ignorees, separateur, rep_sauvegarde, fic_code, trim_val, nb_col, ss_rep_traitement)
                        VALUES 
                            (71, '', 'CSV', 0, '|', 'DCS', 'DCS_EXP_TOURNEE_CAS_LP', 1, 9, 'DCS')
                            ;
 * 
 * Exemple de commande : 
 *                      php app/console dcs_tournee_cas_lp_export DCS_EXP_TOURNEE_CAS_LP J+1 J+1 --depots=042 --id_sh=cron_test --id_ai=1  --env=recette
 * 
 * @author aandrianiaina
 *
 */
class DCSTourneeCASLPExportCommand extends GlobalCommand
{
    protected $idSh;
    protected $idAi;
    
    protected function configure()
    {
    	$this->sNomCommande	= 'dcs_tournee_cas_lp_export';        
        $sJourATraiterMinParDefaut = "J+1";
        $sJourATraiterMaxParDefaut = "J+1";
        
        $this->aCodesSocieteATraiter  = array('LP');
        $this->socId  = 0;
        $this->recapFic  = array();
        
        $ficCodeDefaut  = 'DCS_EXP_TOURNEE_CAS_LP';
        $depotsDefaut  = 'tout';
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console dcs_tournee_cas_lp_export DCS_EXP_TOURNEE_CAS_LP J+1 J+1 --depots=<liste_codes_depot_separes_par_virgule> --env=<ENV> Expl : php app/console dcs_tournee_cas_lp_export DCS_EXP_TOURNEE_CAS_LP J+1 J+1 --depots=040,013 --env=prod
        $this
            ->setDescription('Export vers DCS des clients a servir LP enrichis de leur tournee')
            ->addArgument('fic_code', InputArgument::OPTIONAL, 'Code traitement', $ficCodeDefaut)
            ->addArgument('jour_a_traiter_min', InputArgument::OPTIONAL, 'Jour a traiter Min. Format : J<+Numerique> (Expl : J+1 ou J+2 ou J ...)', $sJourATraiterMinParDefaut)
            ->addArgument('jour_a_traiter_max', InputArgument::OPTIONAL, 'Jour a traiter Max. Format : J<+Numerique> (Expl : J+1 ou J+2 ou J ...)', $sJourATraiterMaxParDefaut)
            ->addOption('depots',null, InputOption::VALUE_REQUIRED, 'Liste de codes de depot ', $depotsDefaut)
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
        
        $sFicCode 	= $input->getArgument('fic_code');
        $sJourATraiterMin  = $input->getArgument('jour_a_traiter_min');
        $sJourATraiterMax  = $input->getArgument('jour_a_traiter_max');
        $aDepotsATraiter    = array();
        $sEnvironnement = "";
        $sListeDepots   = "";

        // voir le fichier de config "mroad.ini"
        // 0 => Import ; 1 => Application ; 2 => Geoconcept ; 3 => Export
        $iOrigineExport	= 3;
        
        if ($input->getOption('depots')) {
            $sListeDepots   = $input->getOption('depots');
            if($sListeDepots!="tout")
            {
                $aDepotsATraiter    = explode(',', $sListeDepots);
            }
        }
        if ($input->getOption('env')) {
            $sEnvironnement   = $input->getOption('env');
        }
        
        $this->oLog->info(date("d/m/Y H:i:s : ")."Debut Export vers DCS des clients a servir LP enrichis de leur tournee - Commande : ".$this->sNomCommande." ".$sFicCode." ".$sJourATraiterMin." ".$sJourATraiterMax." --depots=".$sListeDepots." --env=".$sEnvironnement);
        
        $em    = $this->getContainer()->get('doctrine')->getManager();  
        $repoClientAServirLogist   = $this->getContainer()->get('doctrine')
                                        ->getRepository('AmsDistributionBundle:ClientAServirLogist');
        
        $repoFicExport = $this->getContainer()->get('doctrine')
                        ->getRepository('AmsFichierBundle:FicExport');
        $oFicExport = $repoFicExport->findOneByFicCode($sFicCode);
        $oFicFluxExport   = $oFicExport->getFlux();
        
        $repoFicFtp = $this->getContainer()->get('doctrine')->getRepository('AmsFichierBundle:FicFtp');        
        $oFicFtpExportDCS    = $repoFicFtp->findOneBy(array('code' => $sFicCode));
                
        // Repertoire temporaire pour la generation du fichier a destination de DCS
        $this->sRepTmpVersDCS	= $this->cree_repertoire($this->sRepFichiersPrinc.'/'.$this->getContainer()->getParameter('SOUSREP_FICHIERS_TMP').'/'.$oFicExport->getSsRepTraitement().'/'.$sFicCode);
    	
        // Repertoire Backup Local pour la generation du fichier a destination de DCS
        $this->sRepBkpVersDCS	= $this->cree_repertoire($this->sRepFichiersPrinc.'/'.$this->getContainer()->getParameter('SOUSREP_FICHIERS_BKP').'/'.$oFicExport->getRepSauvegarde().'/'.$sFicCode);
    	
        $srv_ftp    = $this->getContainer()->get('ijanki_ftp');
        
        $iJourATraiter  = 0;
        $aiJourATraiter   = array();
        $aoJourATraiter   = array();
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
            
            $aoJourATraiter[$dateDistribATraiter->format("Y-m-d")] = $dateDistribATraiter;
        }
        
        ksort($aoJourATraiter);
        
        $aFichiersPourDCS = $this->creeFic($repoClientAServirLogist, $this->aCodesSocieteATraiter, $aoJourATraiter, $aDepotsATraiter);
        if(!empty($aFichiersPourDCS))
        {
            $aFicExportesPourDCS    = array();
            $this->oLog->info("Fichier(s) genere(s) pour DCS : ".implode(', ', $aFichiersPourDCS). " - Voir le repertoire : ".$this->sRepTmpVersDCS);
            $this->oLog->info("Debut Export vers FTP DCS");

            $srv_ftp->connect($oFicFtpExportDCS->getServeur());
            $srv_ftp->login($oFicFtpExportDCS->getLogin(), $oFicFtpExportDCS->getMdp());
            $srv_ftp->chdir($oFicFtpExportDCS->getRepertoire());
            foreach($aFichiersPourDCS as $sFicV)
            {
                if($srv_ftp->put($sFicV, $this->sRepTmpVersDCS.'/'.$sFicV, FTP_BINARY)===false)
                {
                    $this->suiviCommand->setMsg("Probleme d'export du fichier ".$sFicV.' vers le FTP '.$oFicFtpExportDCS->getServeur().'/'.$oFicFtpExportDCS->getRepertoire());
                    $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
                    $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                    $this->suiviCommand->setEtat("KO");
                    $this->oLog->erreur("Probleme d'export du fichier ".$sFicV.' vers le FTP '.$oFicFtpExportDCS->getServeur().'/'.$oFicFtpExportDCS->getRepertoire(), E_USER_ERROR);
                    $this->registerError();
                    if($input->getOption('id_ai') && $input->getOption('id_sh')){
                        $this->registerErrorCron($this->idAi);
                    }
                }
                else 
                {
                    $checksumFicExportDCS	= md5_file($this->sRepTmpVersDCS.'/'.$sFicV);
                    $aRecapFicExportDCS	= $this->recapFic[$sFicV];
                    $this->enregistreFicRecap($sFicCode, $oFicFluxExport, $sFicV, $aRecapFicExportDCS, (($this->socId==0)?NULL:$this->socId), 0, 'OK', $iOrigineExport, $this->sRepTmpVersDCS, $this->sRepBkpVersDCS);
                    $aFicExportesPourDCS[]	= $sFicV;
                    $this->oLog->info("Fichier exporte vers le FTP ".$oFicFtpExportDCS->getServeur().'/'.$oFicFtpExportDCS->getRepertoire().' : '.$sFicV);
                }
            }
            $srv_ftp->close();
            $this->oLog->info("Fichier(s) exporte(s) pour DCS : ".implode(', ', $aFicExportesPourDCS));
        }
        
        $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
        $this->endTraitement();
    	$this->oLog->info(date("d/m/Y H:i:s : ")."Fin Export vers DCS des clients a servir LP enrichis de leur tournee - Commande : ".$this->sNomCommande." ".$sFicCode." ".$sJourATraiterMin." ".$sJourATraiterMax." --depots=".$sListeDepots." --env=".$sEnvironnement);
        $this->oLog->info("Fin commande");
        return;
    }
    
    
    /**
     * Generation des fichiers a destination de  DCS
     * @param type $repoClientAServirLogist
     * @param array $aCodesSocieteATraiter
     * @param array $aoJourATraiter
     * @param array $aDepotsATraiter
     * @return array
     */
    private function creeFic($repoClientAServirLogist, $aCodesSocieteATraiter = array('LP'), $aoJourATraiter, $aDepotsATraiter=array())
    {
        $aFichiersGeneres   = array();
        foreach($aoJourATraiter as $sJourK => $oJourATraiter)
        {
            $iNbLignes  = 0;
            $aDonnees  = $repoClientAServirLogist->donneesDcsTourneeCasLPExport($aCodesSocieteATraiter, $oJourATraiter, $aDepotsATraiter);
            // Format nom fichier de sortie : <code_societe_ext><AAAAMMJJ>.txt
            $sFichierSortie    = 'R_'.implode('-', $aCodesSocieteATraiter).$oJourATraiter->format("Ymd").".txt";
            if(file_exists($this->sRepTmpVersDCS.'/'.$sFichierSortie))
            {
                unlink($this->sRepTmpVersDCS.'/'.$sFichierSortie);
            }
            if ($oFichierSortie = fopen($this->sRepTmpVersDCS.'/'.$sFichierSortie,"w+"))
            {
                foreach($aDonnees as $aA)
                {
                    $aLigne = array();
                    $iNbLignes++;
                    $this->socId    = $aA['societe_id'];
                    /*
                     csl.num_parution
                     , date_format(csl.date_distrib, '%Y/%m/%d') AS date_distrib
                     , a.numabo_ext
                     , p.soc_code_ext
                     , p.prd_code_ext
                     , p.spr_code_ext
                     , '' AS divers
                     , IFNULL(mt.codeDCS, '') AS codeDCS
                     , IFNULL(d.code, '') AS depot_code
                     , IFNULL(csl.point_livraison_ordre, '') AS ordre
                     , p.societe_id
                     */
                    $aLigne[]   = $aA['num_parution'];
                    $aLigne[]   = $aA['date_distrib'];
                    $aLigne[]   = $aA['numabo_ext'];
                    $aLigne[]   = $aA['soc_code_ext'];
                    $aLigne[]   = $aA['prd_code_ext'];
                    $aLigne[]   = $aA['spr_code_ext'];
                    $aLigne[]   = $aA['divers'];
                    $aLigne[]   = $aA['codeDCS'];
                    $aLigne[]   = $aA['depot_code'];
                    fwrite($oFichierSortie, implode('|', $aLigne)."\r\n");
                }
                fclose($oFichierSortie);
                
                $aFichiersGeneres[] = $sFichierSortie;
                $this->recapFic[$sFichierSortie]['dateDistrib'] = $oJourATraiter;
                $this->recapFic[$sFichierSortie]['dateParution'] = $oJourATraiter;
                $this->recapFic[$sFichierSortie]['nbLignes'] = $iNbLignes;
                $this->recapFic[$sFichierSortie]['checksum'] = md5_file($this->sRepTmpVersDCS.'/'.$sFichierSortie);
                $this->recapFic[$sFichierSortie]['socCodeExt'] = implode('-', $this->aCodesSocieteATraiter);
            }
        }
        return $aFichiersGeneres;
    }
    
    /**
     * Enregistre le recapitulatif du traitement en cas d'erreur
     * @param string $sFicCode
     * @param \Ams\FichierBundle\Entity\FicFlux $oFicFlux
     * @param string $sFicNom
     * @param array $aRecapFicCourant
     * @param integer $socId
     * @param integer $codeEtat
     * @param string $msgEtat
     * @param integer $origine
     * @param string $repTmp
     * @param string $repBkp
     * @return integer
     * @throws \Doctrine\DBAL\DBALException
     */
    private function enregistreFicRecap($sFicCode, $oFicFlux, $sFicNom, $aRecapFicCourant=NULL, $socId = NULL, $codeEtat = NULL, $msgEtat = NULL, $origine = NULL, $repTmp = NULL, $repBkp = NULL) 
    {
        try {
            $repoFicRecap = $this->getContainer()->get('doctrine')->getRepository('AmsFichierBundle:FicRecap');
            $oFicRecap = new FicRecap();
            $oFicRecap->setCode($sFicCode);
            $oFicRecap->setFlux($oFicFlux);
            $oFicRecap->setNom($sFicNom);
            $sOrigine   = 0;
            if(!is_null($origine))
            {
                $sOrigine = $origine;
            }
            $oFicRecap->setOrigine($sOrigine); // voir le fichier de config "mroad.ini. => ORIGINE[0]== origine fichier [0 => Import ; 1 => Application ; 2 => Geoconcept ; 3 => Export]
            if(!is_null($aRecapFicCourant))
            {
                if (isset($aRecapFicCourant['socCodeExt'])) {
                    $oFicRecap->setSocCodeExt($aRecapFicCourant['socCodeExt']);
                }
                if (isset($aRecapFicCourant['dateParution'])) {
                    $oFicRecap->setDateParution($aRecapFicCourant['dateParution']);
                }
                if (isset($aRecapFicCourant['dateDistrib'])) {
                    $oFicRecap->setDateDistrib($aRecapFicCourant['dateDistrib']);
                }
                $oFicRecap->setChecksum($aRecapFicCourant['checksum']);
                $oFicRecap->setNbLignes($aRecapFicCourant['nbLignes']);
            }
            if (!is_null($socId)) {
                $oSocieteRepo = $this->getContainer()->get('doctrine')->getRepository('AmsProduitBundle:Societe');
                $oFicRecap->setSociete($oSocieteRepo->findOneById($socId));
            }

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

            $iDernierFicRecap = $repoFicRecap->insert($oFicRecap);
            
            // En Local - Sauvegarde du fichier
            if(!is_null($repTmp) && !is_null($repBkp))
            {
                $sRepTmp    = $repTmp;
                $sRepBkpLocal    = $repBkp;
                rename($sRepTmp.'/'.$sFicNom, $sRepBkpLocal.'/'.$this->oString->renommeFicDeSvgrde($sFicNom, $this->sDateCourantYmd, $this->sHeureCourantYmd));
            }
            
            return $iDernierFicRecap;
        } 
        catch (DBALException $ex) {
            $this->suiviCommand->setMsg("une erreur s'estv produite lors de l'enregistrement en base:".$ex->getMessage());
            $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type($ex->getCode()));
            $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
            $this->suiviCommand->setEtat("KO");
            $this->registerError();
            if($input->getOption('id_ai') && $input->getOption('id_sh')){
                $this->registerErrorCron($this->idAi);
            }
            throw $ex;
        }
    }
    
}
