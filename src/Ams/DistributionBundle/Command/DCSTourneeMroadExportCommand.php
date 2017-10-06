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
 * Export vers DCS des tournees M-ROAD
 * 
 * 
 * Table "fic_flux" =>  id=72, fic_code = "DCS - Export vers DCS des tournees M-ROAD"
                        INSERT INTO fic_flux (id, libelle) VALUES (72, 'DCS - Export vers DCS des tournees M-ROAD');
 * 
 * Table "fic_ftp" =>   fic_code = "DCS_EXP_TOURNEE_MROAD" <=> Export vers DCS des tournees M-ROAD
 *          INSERT INTO fic_ftp (code, serveur, login, mdp, repertoire, rep_sauvegarde, id_soc_distrib)
            VALUES ('DCS_EXP_TOURNEE_MROAD', '10.151.93.3', 'sdvp', 'sdvp', 'FTPtest/DCS/MROAD_RECETTE/Vers_DCS/Tournees_MROAD', 'Bkp', 'DCS');

 * 
 * Table "fic_export" =>    fic_code = "DCS_EXP_TOURNEE_MROAD"
 *                      INSERT INTO fic_export (flux_id, regex_fic, format_fic, nb_lignes_ignorees, separateur, rep_sauvegarde, fic_code, trim_val, nb_col, ss_rep_traitement)
                        VALUES 
                            (72, '', 'CSV', 0, '|', 'DCS', 'DCS_EXP_TOURNEE_MROAD', 1, 3, 'DCS')
 * 
 * 
 * Exemple de commande : 
 *                      php app/console dcs_tournee_mroad_export DCS_EXP_TOURNEE_MROAD --id_sh=cron_test --id_ai=1  --env=recette
 * 
 * @author aandrianiaina
 *
 */
class DCSTourneeMroadExportCommand extends GlobalCommand
{
    protected $idSh;
    protected $idAi;
    
    protected function configure()
    {
    	$this->sNomCommande	= 'dcs_tournee_mroad_export';         
        $ficCodeDefaut  = 'DCS_EXP_TOURNEE_MROAD';
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console dcs_tournee_mroad_export DCS_EXP_TOURNEE_MROAD --env=<ENV> Expl : php app/console dcs_tournee_mroad_export DCS_EXP_TOURNEE_MROAD --env=prod
        $this
            ->setDescription('Export vers DCS des tournees M-ROAD')
            ->addArgument('fic_code', InputArgument::OPTIONAL, 'Code traitement', $ficCodeDefaut)
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
        $sEnvironnement = "";

        // voir le fichier de config "mroad.ini"
        // 0 => Import ; 1 => Application ; 2 => Geoconcept ; 3 => Export
        $iOrigineExport	= 3;
        
        if ($input->getOption('env')) {
            $sEnvironnement   = $input->getOption('env');
        }        
        
        $this->oLog->info(date("d/m/Y H:i:s : ")."Debut Export vers DCS des tournees M-ROAD - Commande : ".$this->sNomCommande." ".$sFicCode." --env=".$sEnvironnement);
        
        $em    = $this->getContainer()->get('doctrine')->getManager();  
        $repoModeleTournee   = $this->getContainer()->get('doctrine')
                                        ->getRepository('AmsModeleBundle:ModeleTournee');
        
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
        
        $aFichiersPourDCS = $this->creeFic($repoModeleTournee);
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
                    $this->enregistreFicRecap($sFicCode, $oFicFluxExport, $sFicV, $aRecapFicExportDCS, NULL, 0, 'OK', $iOrigineExport, $this->sRepTmpVersDCS, $this->sRepBkpVersDCS);
                    $aFicExportesPourDCS[]	= $sFicV;
                    $this->oLog->info("Fichier exporte vers le FTP ".$oFicFtpExportDCS->getServeur().'/'.$oFicFtpExportDCS->getRepertoire().' : '.$sFicV);
                }
            }
            $srv_ftp->close();
            $this->oLog->info("Fichier(s) exporte(s) pour DCS : ".implode(', ', $aFicExportesPourDCS));
        }
        $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
        $this->endTraitement();
        if($input->getOption('id_ai') && $input->getOption('id_sh')){
            $this->oLog->info(date("d/m/Y H:i:s : ")."Fin Export vers DCS des tournees M-ROAD - Commande : ".$this->sNomCommande." ".$sFicCode." --id_sh=".$this->idSh." --id_ai=".$this->idAi."  --env=".$sEnvironnement);
        }else{
            $this->oLog->info(date("d/m/Y H:i:s : ")."Fin Export vers DCS des tournees M-ROAD - Commande : ".$this->sNomCommande." ".$sFicCode." --env=".$sEnvironnement);
        }
        $this->oLog->info("Fin commande");
        return;
        
    }
    
    
    /**
     * Generation des fichiers a destination de DCS
     * @param type $repoModeleTournee
     * @return string
     */
    private function creeFic($repoModeleTournee)
    {
        $aFichiersGeneres   = array();
        $iNbLignes  = 0;
        $aDonnees  = $repoModeleTournee->donneesDcsTournee();
        $sFichierSortie    = 'Libelle_Tournee.txt';
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
                /*
                 d.code AS depotDCS
                , mt.codeDCS
                , mt.libelle AS tournee_MROAD
                 */
                $aLigne[]   = $aA['depotDCS'];
                $aLigne[]   = $aA['codeDCS'];
                $aLigne[]   = $aA['tournee_MROAD'];
                fwrite($oFichierSortie, implode('|', $aLigne)."\n");
            }
            fclose($oFichierSortie);

            $aFichiersGeneres[] = $sFichierSortie;
            $this->recapFic[$sFichierSortie]['nbLignes'] = $iNbLignes;
            $this->recapFic[$sFichierSortie]['checksum'] = md5_file($this->sRepTmpVersDCS.'/'.$sFichierSortie);
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
