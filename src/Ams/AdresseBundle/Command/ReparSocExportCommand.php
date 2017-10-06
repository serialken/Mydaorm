<?php 
namespace Ams\AdresseBundle\Command;

//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ijanki\Bundle\FtpBundle\Exception\FtpException;
use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Command\GlobalCommand;
use Ams\FichierBundle\Entity\FicExport;
use Ams\FichierBundle\Entity\FicRecap;

/**
 * 
 * "Command" export vers JADE des communes livres le JOUR pour certains produits et dont la distinction est necessaire a l'entree de JADE. Cas "Le Monde" et "Le Monde Grands Comptes" par exemple
 * 
 * !!!! NE JAMAIS METTRE de "_" dans le nom de classe
 * 
 * 
 * Table "fic_flux" =>  id=56, fic_code = "JADE - Export communes JOUR"
                        INSERT INTO fic_flux (id, libelle) VALUES (56, 'JADE - Export communes JOUR');
 * 
 * Table "fic_ftp" =>   fic_code = "JADE_EXP_COMMUNE_JOUR" <=> JADE - Export communes JOUR
 *          INSERT INTO fic_ftp (code, serveur, login, mdp, repertoire, rep_sauvegarde, id_soc_distrib)
            VALUES ('JADE_EXP_COMMUNE_JOUR', '10.151.93.2', 'MROAD', 'M245road', 'MROAD_PROD/COM_JOUR', 'Bkp', '59');
 * 
 * Table "fic_export" =>    fic_code = "JADE_EXP_COMMUNE_JOUR"
 *                      INSERT INTO fic_export (flux_id, regex_fic, format_fic, nb_lignes_ignorees, separateur, rep_sauvegarde, fic_code, trim_val, nb_col, ss_rep_traitement) 
                        VALUES (56, '', 'CSV', 0, '|', 'Bkp', 'JADE_EXP_COMMUNE_JOUR', 1, 6, 'Bkp');
 * 
 * 
 * Pour executer, faire : 
 *              php app/console repar_soc_export <<fic_code>> --soc=<<societe_code_JOUR>> 
 *                  ou  <<fic_code>> = JADE_EXP_COMMUNE_JOUR
 *                      <<societe_code_JOUR>> = Code societe concerne
 *      Expl :  php app/console repar_soc_export JADE_EXP_COMMUNE_JOUR --soc="MD,MP" --id_sh=cron_test --id_ai=1  --env=dev
 *              php app/console repar_soc_export JADE_EXP_COMMUNE_JOUR --soc="MD,MP" --id_sh=cron_test --id_ai=1  --env=prod
 * 
 * 
 * @author aandrianiaina
 *
 */
class ReparSocExportCommand extends GlobalCommand
{
    private $aFichierFluxParam;
    private $sRepTmp;
    private $sRepBkp;
    private $sSocDefaut;
    protected $idSh;
    protected $idAi;
    
    protected function configure()
    {
    	$this->sNomCommande	= 'repar_soc_export';
        $this->recapFic  = array();
        $this->sSocDefaut = "MD,MP";
        //$this->sSocDefaut = "LM,LG";
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console repar_soc_export <<fic_code>> --soc="MD,MP"
        // Expl : 
        //      php app/console repar_soc_export JADE_EXP_COMMUNE_JOUR --soc="MD,MP" --env=prod
        $this
            ->setDescription('Importation des fichiers via FTP')
            ->addArgument('fic_code', InputArgument::REQUIRED, 'Code source de donnees')
            ->addOption('soc',null, InputOption::VALUE_REQUIRED, 'Codes societes (separes par ",") concernes par l export vers JADE ?', $this->sSocDefaut)
            ->addOption('id_sh',null, InputOption::VALUE_REQUIRED, 'Libelle du CRON')
            ->addOption('id_ai',null, InputOption::VALUE_REQUIRED, 'Id du CRON')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
    	$sFicCode 	= $input->getArgument('fic_code');	// Expl : JADE_EXP_COMMUNE_JOUR <=> Export vers JADE des communes livrant des produits/societes du JOUR a distinguer avant son integration dans JADE <=> Exportation vers JADE des communes JOUR
        if ($input->getOption('soc')) {
            $sSocCodeExt   = $input->getOption('soc');
        }
        if ($input->getOption('env')) {
            $sEnvironnement   = $input->getOption('env');
        }
        if($input->getOption('id_sh')){
            $this->idSh = $input->getOption('id_sh');
        }
        if($input->getOption('id_ai')){
            $this->idAi = $input->getOption('id_ai');
        }
        if($input->getOption('id_ai') && $input->getOption('id_sh')){
            $this->associateToCron($idAi,$idSh);
            $this->oLog->info(date("d/m/Y H:i:s : ")."Debut Export vers JADE des communes livrant des produits du JOUR a distinguer avant son integration dans JADE - Commande : ".$this->sNomCommande.' '.$sFicCode.' --soc="'.$sSocCodeExt.'"'.' --id_sh='.$this->idSh.' --id_ai='.$this->idAi.' --env='.$sEnvironnement);
        }else{
             $this->oLog->info(date("d/m/Y H:i:s : ")."Debut Export vers JADE des communes livrant des produits du JOUR a distinguer avant son integration dans JADE - Commande : ".$this->sNomCommande.' '.$sFicCode.' --soc="'.$sSocCodeExt.'"'.' --env='.$sEnvironnement);
        }
       
                
        $em    = $this->getContainer()->get('doctrine')->getManager();
        
        $repoFicExport = $this->getContainer()->get('doctrine')->getRepository('AmsFichierBundle:FicExport');
        $oFicExport = $repoFicExport->findOneBy(array('ficCode' =>$sFicCode));
        $oFicFluxExport = $oFicExport->getFlux();
        $sSeparateurCSV = $oFicExport->getSeparateur();
        
        // Repertoire temporaire pour la generation du fichier a destination de JADE
        $this->sRepTmp	= $this->cree_repertoire($this->sRepFichiersPrinc.'/'.$this->getContainer()->getParameter('SOUSREP_FICHIERS_TMP').'/'.$sFicCode);
    	
        // Repertoire Backup Local pour la generation du fichier a destination de JADE
        $this->sRepBkp	= $this->cree_repertoire($this->sRepFichiersPrinc.'/'.$this->getContainer()->getParameter('SOUSREP_FICHIERS_BKP').'/'.$sFicCode);
        
        $srv_ftp    = $this->getContainer()->get('ijanki_ftp');
                
        $sFicNom = "";
        
        try
        {
            $repoFicFtp   = $this->getContainer()->get('doctrine')
                                ->getRepository('AmsFichierBundle:FicFtp');
            $oFicFtpExport    = $repoFicFtp->findOneBy(array('code' => $sFicCode));
            if(is_null($oFicFtpExport))
            {
                $this->suiviCommand->setMsg("Le flux ".$sFicCode." n'est pas parametre dans ".'"fic_ftp"');
                $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
                $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                $this->suiviCommand->setEtat("KO");
                $this->oLog->erreur("Le flux ".$sFicCode." n'est pas parametre dans ".'"fic_ftp"', E_USER_ERROR);
                $this->registerError();
                if($input->getOption('id_ai') && $input->getOption('id_sh')){
                    $this->registerErrorCron($this->idAi);
                }
                $exception = new \Exception('Identification de flux introuvable dans "fic_ftp"');
                throw $exception;
            }
            
            $repoReparSoc   = $this->getContainer()->get('doctrine')->getRepository('AmsAdresseBundle:ReparSoc');    
            $this->recapFic['socCodeExt'] = $sSocCodeExt;
            $aFichiersAExporter   = $this->creeFic($repoReparSoc->getCommuneJour($sSocCodeExt), $oFicFtpExport, $sSeparateurCSV);
            if(!empty($aFichiersAExporter))
            {
                $aFicExportes    = array();
                $this->oLog->info("Fichier(s) genere(s) pour JADE : ".implode(', ', $aFichiersAExporter). " - Voir le repertoire : ".$this->sRepTmp);
                $this->oLog->info("Debut Export vers FTP JADE");

                $srv_ftp->connect($oFicFtpExport->getServeur());
                $srv_ftp->login($oFicFtpExport->getLogin(), $oFicFtpExport->getMdp());
                $srv_ftp->chdir($oFicFtpExport->getRepertoire());
                foreach($aFichiersAExporter as $sFicV)
                {
                    if($srv_ftp->put($sFicV, $this->sRepTmp.'/'.$sFicV, FTP_BINARY)===false)
                    {
                        $this->suiviCommand->setMsg("Probleme d'export du fichier ".$sFicV.' vers le FTP '.$oFicFtpExport->getServeur().'/'.$oFicFtpExport->getRepertoire());
                        $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
                        $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                        $this->suiviCommand->setEtat("KO");
                        $this->oLog->erreur("Probleme d'export du fichier ".$sFicV.' vers le FTP '.$oFicFtpExport->getServeur().'/'.$oFicFtpExport->getRepertoire(), E_USER_ERROR);
                        $this->registerError();
                        if($input->getOption('id_ai') && $input->getOption('id_sh')){
                            $this->registerErrorCron($this->idAi);
                        }
                    }
                    else 
                    {
                        $this->enregistreFicRecap($sFicCode, $oFicFluxExport, $sFicV, $this->recapFic, 0, 'OK');
                        $aFicExportes[]	= $sFicV;
                        $this->oLog->info("Fichier exporte vers le FTP ".$oFicFtpExport->getServeur().'/'.$oFicFtpExport->getRepertoire().' : '.$sFicV);
                    }
                }
                $srv_ftp->close();
                $this->oLog->info("Fichier(s) exporte(s) : ".implode(', ', $aFicExportes));
            }            
        } catch (DBALException $DBALException) {
            $this->suiviCommand->setMsg($DBALException->getMessage());
            $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type($DBALException->getCode()));
            $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
            $this->suiviCommand->setEtat("KO");
            $this->oLog->erreur($DBALException->getMessage(), $DBALException->getCode(), $DBALException->getFile(), $DBALException->getLine());
            $this->registerError();
            if($input->getOption('id_ai') && $input->getOption('id_sh')){
                $this->registerErrorCron($this->idAi);
            }
            $this->enregistreFicRecap($sFicCode, $oFicFluxExport, "", $this->recapFic, 70, $DBALException->getMessage());
        } catch (FtpException $e) 
        {
            $this->suiviCommand->setMsg("Probleme d'acces au FTP ".$this->aFichierFluxParam['FTP']->getSrv().' : '.$e->getMessage());
            $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
            $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
            $this->suiviCommand->setEtat("KO");
            $this->oLog->erreur("Probleme d'acces au FTP ".$this->aFichierFluxParam['FTP']->getSrv().' : '.$e->getMessage(), E_USER_ERROR);
            $this->registerError();
            if($input->getOption('id_ai') && $input->getOption('id_sh')){
                $this->registerErrorCron($this->idAi);
            }
        } catch (\Exception $ex)
        {
            $this->suiviCommand->setMsg($ex->getMessage());
            $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type($ex->getCode()));
            $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
            $this->suiviCommand->setEtat("KO");
            $this->oLog->erreur($ex->getMessage(), $ex->getCode(), $ex->getFile(), $ex->getLine());
            $this->registerError();
            if($input->getOption('id_ai') && $input->getOption('id_sh')){
                $this->registerErrorCron($this->idAi);
            }
        }
        
    	$this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
        $this->endTraitement();
        if($input->getOption('id_ai') && $input->getOption('id_sh')){
            $this->oLog->info(date("d/m/Y H:i:s : ")."Fin Export vers JADE des communes livrant des produits du JOUR a distinguer avant son integration dans JADE - Commande : ".$this->sNomCommande.' '.$sFicCode.' --soc="'.$sSocCodeExt.'"'.' --id_sh='.$this->idSh.' --id_ai='.$this->idAi.'--env='.$sEnvironnement);
        }else{
            $this->oLog->info(date("d/m/Y H:i:s : ")."Fin Export vers JADE des communes livrant des produits du JOUR a distinguer avant son integration dans JADE - Commande : ".$this->sNomCommande.' '.$sFicCode.' --soc="'.$sSocCodeExt.'"'.' --env='.$sEnvironnement);
        }
        $this->oLog->info("Fin commande");
        return;
    }
    
    private function creeFic($aDonnees, $oFicFtpExport, $sSeparateurCSV)
    {
        $aFichiersGeneres = array();
        $oDateDuJour = new \DateTime();
        $sFichierSortie    = $oFicFtpExport->getIdSocDistrib().'_COMMUNE_JOUR_'.$oDateDuJour->format("Ymd").".txt";
        if(file_exists($this->sRepTmp.'/'.$sFichierSortie))
        {
            unlink($this->sRepTmp.'/'.$sFichierSortie);
        }
        if ($oFichierSortie = fopen($this->sRepTmp.'/'.$sFichierSortie, "w+"))
        {
            $iNbLignes  = 0;
            foreach($aDonnees as $aA)
            {
                $aLigne = array();
                $iNbLignes++;
                /*
                DISTINCT
                s.code AS soc_code_ext
                , c.insee
                , d.code AS depot_code
                , c.insee
                , IFNULL(DATE_FORMAT(rs.date_debut, '%Y/%m/%d'), '2099-12-31') AS date_debut
                , IFNULL(DATE_FORMAT(rs.date_fin, '%Y/%m/%d'), '2099-12-31') AS date_fin
                 */
                $aLigne[]   = $oFicFtpExport->getIdSocDistrib();
                $aLigne[]   = $aA['soc_code_ext'];
                $aLigne[]   = $aA['insee'];
                $aLigne[]   = $aA['depot_code'];
                $aLigne[]   = $aA['date_debut'];
                $aLigne[]   = $aA['date_fin'];
                fwrite($oFichierSortie, implode($sSeparateurCSV, $aLigne)."\n");
            }
            fclose($oFichierSortie);

            $aFichiersGeneres[] = $sFichierSortie;
            $this->recapFic['dateDistrib'] = $oDateDuJour;
            $this->recapFic['dateParution'] = $oDateDuJour;
            $this->recapFic['nbLignes'] = $iNbLignes;
            $this->recapFic['checksum'] = md5_file($this->sRepTmp.'/'.$sFichierSortie);
        }
        return $aFichiersGeneres;
    }
    
    
    /**
     * Enregistre le recapitulatif du traitement en cas d'erreur
     * @param string $sFicCode
     * @param \Ams\FichierBundle\Entity\FicFlux
     * @param string $sFicNom
     * @param array $aRecapFicCourant
     * @param integer $codeEtat
     * @param string $msgEtat
     * @throws \Doctrine\DBAL\DBALException
     */
    private function enregistreFicRecap($sFicCode, $oFicFluxExport, $sFicNom, $aRecapFicCourant=NULL, $codeEtat = NULL, $msgEtat = NULL) 
    {
        try {
            $repoFicRecap = $this->getContainer()->get('doctrine')->getRepository('AmsFichierBundle:FicRecap');
            $oFicRecap = new FicRecap();
            $oFicRecap->setCode($sFicCode);
            $oFicRecap->setFlux($oFicFluxExport);
            $oFicRecap->setNom($sFicNom);
            $oFicRecap->setOrigine(3); // voir le fichier de config "mroad.ini. => ORIGINE[0]== origine fichier --- si "3", c'est export
            if(!is_null($aRecapFicCourant))
            {
                if (isset($aRecapFicCourant['socCodeExt'])) {
                    $oFicRecap->setSocCodeExt($aRecapFicCourant['socCodeExt']);
                }
                $oFicRecap->setChecksum($aRecapFicCourant['checksum']);
                $oFicRecap->setNbLignes($aRecapFicCourant['nbLignes']);
                $oFicRecap->setNbExemplaires($aRecapFicCourant['nbLignes']);
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
            if(file_exists($this->sRepTmp.'/'.$sFicNom))
            {
                rename($this->sRepTmp.'/'.$sFicNom, $this->sRepBkp.'/'.$this->oString->renommeFicDeSvgrde($sFicNom, $this->sDateCourantYmd, $this->sHeureCourantYmd));
            }
            return $iDernierFicRecap;
        } 
        catch (DBALException $ex) {
            $this->suiviCommand->setMsg($ex->getMessage());
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
