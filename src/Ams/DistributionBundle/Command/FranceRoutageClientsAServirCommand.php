<?php 
namespace Ams\DistributionBundle\Command;

//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Ijanki\Bundle\FtpBundle\Exception\FtpException;

use Ams\SilogBundle\Command\GlobalCommand;
use Ams\FichierBundle\Entity\FicRecap;

use Doctrine\DBAL\DBALException;

use Ams\DistributionBundle\Exception\FranceRoutageClientsAServirCommandException;

/**
 * 
 * Integration temporaire des clients a servir France Routage et export vers DAN Jade des abonnes que l'on peut classer dans une tournee
 * 
 * 
 * Table "fic_flux" =>  id=41, fic_code = "France Routage - Import Clients à servir venant de France Routage"
 *                      id=61, fic_code = "France Routage - Export Clients à servir vers Jade"
 *                      id=62, fic_code = "France Routage - Export Clients à servir vers France Routage"
                        INSERT INTO fic_flux (id, libelle) VALUES (41, 'France Routage - Import Clients à servir venant de France Routage');
                        INSERT INTO fic_flux (id, libelle) VALUES (61, 'France Routage - Export Clients à servir vers Jade');
                        INSERT INTO fic_flux (id, libelle) VALUES (62, 'France Routage - Export Clients à servir vers France Routage');
 * 
 *  Table "fic_source" 
            INSERT INTO fic_source (code, libelle, client_type)
            VALUES ('FR_ROUTAGE', 'FRANCE ROUTAGE', 0)
 *
 * Table "fic_ftp" =>   fic_code = "FR_ROUTAGE_CAS"
 *                      fic_code = "FR_ROUTAGE_EXPORT_JADE" <=> Export vers Jade des abonnes qui sont classes
 *                      fic_code = "FR_ROUTAGE_EXPORT_FR_ROUTAGE" <=> Export vers Jade des abonnes qui sont classes
            INSERT INTO fic_ftp (code, serveur, login, mdp, repertoire, rep_sauvegarde, id_soc_distrib)
            VALUES ('FR_ROUTAGE_CAS', '10.151.93.3', 'sdvp', 'sdvp', 'FTPtest/France_Routage/MROADPROD/Vers_MROAD/CAS', 'Bkp', 'FR_ROUTAGE');

            INSERT INTO fic_ftp (code, serveur, login, mdp, repertoire, rep_sauvegarde, id_soc_distrib)
            VALUES ('FR_ROUTAGE_EXPORT_JADE', '10.151.93.3', 'sdvp', 'sdvp', 'FTPtest/France_Routage/MROADPROD/Vers_JADE/CAS', 'Bkp', 'FR_ROUTAGE');

            INSERT INTO fic_ftp (code, serveur, login, mdp, repertoire, rep_sauvegarde, id_soc_distrib)
            VALUES ('FR_ROUTAGE_EXPORT_FR_ROUTAGE', '10.151.93.3', 'sdvp', 'sdvp', 'FTPtest/France_Routage/MROADPROD/Vers_FR_ROUTAGE/CAS', 'Bkp', 'FR_ROUTAGE'); 
 * 
 *  Table "fic_format_enregistrement" => fic_code = "FR_ROUTAGE_CAS"
            INSERT INTO fic_format_enregistrement (fic_code, attribut, col_debut, col_long, col_val, col_val_rplct, col_desc)
            SELECT 'FR_ROUTAGE_CAS' AS fic_code, attribut, col_debut, col_long, col_val, col_val_rplct, col_desc FROM `fic_format_enregistrement` WHERE `fic_code`='JADE_CAS' AND col_val<= 14 order by col_val
 * 
 * Table "fic_chrgt_fichiers_bdd" => fic_code = "FR_ROUTAGE_CAS" 
            INSERT INTO fic_chrgt_fichiers_bdd (`id`, `fic_ftp`, `fic_source`, `fic_code`, `regex_fic`, `format_fic`, `nb_lignes_ignorees`, `separateur`, `trim_val`, `nb_col`, `flux_id`, `ss_rep_traitement`) 
            VALUES (NULL, '13', '3', 'FR_ROUTAGE_CAS', '/^(\\.\\/)?[A-Z0-9]{2}`date_Ymd_1_10`\\.txt$/i', 'CSV', '0', '|', '1', '14', '41', 'FrRoutageCAS');
 * 
 * Table "fic_export" =>    fic_code = "FR_ROUTAGE_EXPORT_JADE"
 *                          fic_code = "FR_ROUTAGE_EXPORT_FR_ROUTAGE"
 *                      INSERT INTO fic_export (flux_id, regex_fic, format_fic, nb_lignes_ignorees, separateur, rep_sauvegarde, fic_code, trim_val, nb_col, ss_rep_traitement)
                        VALUES 
                            (61, '', 'CSV', 0, '|', 'FrRoutageCAS', 'FR_ROUTAGE_EXPORT_JADE', 1, 19, 'FrRoutageCAS')
                            , (62, '', 'CSV', 0, '|', 'FrRoutageCAS', 'FR_ROUTAGE_EXPORT_FR_ROUTAGE', 1, 6, 'FrRoutageCAS')
                            ;
 * 
 * Exemple de commande : 
 *                      php app/console france_routage_c_a_s FR_ROUTAGE_CAS --env=dev
 *                      php app/console france_routage_c_a_s FR_ROUTAGE_CAS --exportFR --exportJade --jour=J+2 --soc=TS --env=dev
 * 
 * @author aandrianiaina
 *
 */
class FranceRoutageClientsAServirCommand extends GlobalCommand
{
    protected function configure()
    {
    	$this->sNomCommande	= 'france_routage_c_a_s';
        $ficCodeDefaut  = 'FR_ROUTAGE_CAS';
        $jourDefaut = 'J+2'; // J+2
        $societesDefaut = "";
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console france_routage_c_a_s FR_ROUTAGE_CAS --env=<ENV> Expl : php app/console france_routage_c_a_s FR_ROUTAGE_CAS --env=prod
        $this
            ->setDescription('Integration des clients a servir France Routage')
            ->addArgument('fic_code', InputArgument::OPTIONAL, 'Code traitement', $ficCodeDefaut)
            ->addOption('exportFR',null, InputOption::VALUE_NONE, 'Si defini, on ne fait que l export vers France Routage')
            ->addOption('exportJade',null, InputOption::VALUE_NONE, 'Si defini, on ne fait que l export vers JADE')
            ->addOption('jour',null, InputOption::VALUE_REQUIRED, 'Si --exportFR et/ou --exportJade definis, c est le jour a exporter au format J+<int>', $jourDefaut)
            ->addOption('soc',null, InputOption::VALUE_REQUIRED, 'Si --exportFR et/ou --exportJade definis, c est le code societe a exporter', $societesDefaut)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
        
        $sFicCode 	= $input->getArgument('fic_code');
        
        $bExportFR = $bExportJade = false;
        $bQueExport = false;
        $sRegexJ    = '/^J([\-\+][0-9]+)?$/';
        if (($input->getOption('exportFR') || $input->getOption('exportJade')) && $input->getOption('soc') && $input->getOption('jour') && (preg_match($sRegexJ, $input->getOption('jour'),$aiJourATraiter)))
        {
            $bQueExport = true;
            $sSocCodeExtAExporter   = $input->getOption('soc');
            
            if(isset($aiJourATraiter[1]))
            {
                $iJourATraiter  = intval($aiJourATraiter[1]);
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
            }
            
            if ($input->getOption('exportFR')) {
                $bExportFR  = true;
            }
            if ($input->getOption('exportJade')) {
                $bExportJade  = true;
            }
        }        
        
        $sFicCodeExportFranceRoutage    = "FR_ROUTAGE_EXPORT_FR_ROUTAGE";
        $sFicCodeExportJade    = "FR_ROUTAGE_EXPORT_JADE";
        $this->aFicCodeExport    = array($sFicCodeExportFranceRoutage, $sFicCodeExportJade);
        
        $this->oLog->info(date("d/m/Y H:i:s : ")."Debut Integration des clients a servir France Routage - Commande : ".$this->sNomCommande." ".$sFicCode." ");
        
        $em    = $this->getContainer()->get('doctrine')->getManager();        
        
        // Recuperation des parameters concernant le FTP et les fichiers a recuperer
        $oFicChrgtFichiersBdd = $this->getContainer()->get('doctrine')
                        ->getRepository('AmsFichierBundle:FicChrgtFichiersBdd')
                        ->findOneByCode($sFicCode);
        
        if(is_null($oFicChrgtFichiersBdd))
        {
            $this->oLog->erreur("Le flux ".$sFicCode." n'est pas parametre dans 'fic_chrgt_fichiers_bdd'", E_USER_ERROR);
            throw new \Exception("Identification de flux introuvable dans 'fic_chrgt_fichiers_bdd'");
        }
        
        $oFicExport = $this->getContainer()->get('doctrine')
                        ->getRepository('AmsFichierBundle:FicExport');
        $oFicExportFranceRoutage = $oFicExport->findOneByFicCode($sFicCodeExportFranceRoutage);
        $oFicFluxExportFranceRoutage   = $oFicExportFranceRoutage->getFlux();
        
        $oFicExportJade = $oFicExport->findOneByFicCode($sFicCodeExportJade);
        $oFicFluxExportJade   = $oFicExportJade->getFlux();        
    	
        // Repertoire ou l'on recupere les fichiers a traiter
        $this->sRepTmp	= $this->sRepFichiersPrinc.'/'.$this->getContainer()->getParameter('SOUSREP_FICHIERS_TMP').'/'.$oFicChrgtFichiersBdd->getSsRepTraitement().'/'.$sFicCode;
        
        // Repertoire Backup Local
    	$this->sRepBkpLocal	= $this->sRepFichiersPrinc.'/'.$this->getContainer()->getParameter('SOUSREP_FICHIERS_BKP').'/'.$oFicChrgtFichiersBdd->getSsRepTraitement().'/'.$sFicCode;
        
        // Repertoire temporaire pour la generation du fichier a destination de France Routage
        $this->sRepTmpVersFranceRoutage	= $this->cree_repertoire($this->sRepFichiersPrinc.'/'.$this->getContainer()->getParameter('SOUSREP_FICHIERS_TMP').'/'.$oFicExportFranceRoutage->getSsRepTraitement().'/'.$sFicCodeExportFranceRoutage);
    	
        // Repertoire temporaire pour la generation du fichier a destination de JADE
        $this->sRepTmpVersJade	= $this->cree_repertoire($this->sRepFichiersPrinc.'/'.$this->getContainer()->getParameter('SOUSREP_FICHIERS_TMP').'/'.$oFicExportJade->getSsRepTraitement().'/'.$sFicCodeExportJade);
        
        // Repertoire Backup Local pour la generation du fichier a destination de France Routage
        $this->sRepBkpVersFranceRoutage	= $this->cree_repertoire($this->sRepFichiersPrinc.'/'.$this->getContainer()->getParameter('SOUSREP_FICHIERS_BKP').'/'.$oFicExportFranceRoutage->getRepSauvegarde().'/'.$sFicCodeExportFranceRoutage);
    	
        // Repertoire Backup Local pour la generation du fichier a destination de JADE
        $this->sRepBkpVersJade	= $this->cree_repertoire($this->sRepFichiersPrinc.'/'.$this->getContainer()->getParameter('SOUSREP_FICHIERS_BKP').'/'.$oFicExportJade->getRepSauvegarde().'/'.$sFicCodeExportJade);
    	
        
        // Si 0, c'est abonne. Si 1, c'est Lieu de vente
        $clientType   = $oFicChrgtFichiersBdd->getFicSource()->getClientType();
        
        // Recuperation de l'identifiant du Flux de fichier
        $oFicFlux   = $oFicChrgtFichiersBdd->getFlux();

        // voir le fichier de config "mroad.ini"
        // 0 => Import ; 1 => Application ; 2 => Geoconcept ; 3 => Export
        $iOrigineExport	= 3;
        
        // Recuperation des parameters concernant le FTP et les fichiers a recuperer
        
        $oFicFormatEnregistrement   = $this->getContainer()->get('doctrine')
                                        ->getRepository('AmsFichierBundle:FicFormatEnregistrement');
        $oFicFormatEnregistrement->getSQLLoadDataInFile($sFicCode, $oFicChrgtFichiersBdd->getFormatFic(), trim($oFicChrgtFichiersBdd->getTrimVal()), $oFicChrgtFichiersBdd->getNbCol(), 'latin1');
    	$srvRnvp    = $this->getContainer()->get('rnvp'); 
        $srvAmsCartoGeoservice = $this->getContainer()->get('ams_carto.geoservice');
        
        $repoCAS_FranceRoutageTmp   = $this->getContainer()->get('doctrine')
                                        ->getRepository('AmsDistributionBundle:FranceRoutageClientsAServirTmp'); 
        
        $repoCAS_FranceRoutage   = $this->getContainer()->get('doctrine')
                                        ->getRepository('AmsDistributionBundle:FranceRoutageClientsAServir'); 
        
        $repoAdresse = $this->getContainer()->get('doctrine')->getRepository('AmsAdresseBundle:Adresse');
        $repoAbonneSoc = $this->getContainer()->get('doctrine')->getRepository('AmsAbonneBundle:AbonneSoc');
        
        $repoFicFtp = $this->getContainer()->get('doctrine')->getRepository('AmsFichierBundle:FicFtp');
        
        $oFicFtpExportFranceRoutage    = $repoFicFtp->findOneBy(array('code' => $sFicCodeExportFranceRoutage));
        $oFicFtpExportJade    = $repoFicFtp->findOneBy(array('code' => $sFicCodeExportJade));
        
        $srv_ftp    = $this->getContainer()->get('ijanki_ftp');
        
        
        // Si on ne fait que des export vers France Routage ou Jade avec les donnees que l'on a deja traitees
        if($bQueExport == true)
        {
            $this->oLog->info(date("d/m/Y H:i:s : ")."Debut Export des donnees deja traitees");
            
            $aSocDatesATraiter  = array();
            $aSocDatesATraiter[]    = array("date_distrib" => $dateDistribATraiter->format('Y/m/d'), "soc_code_ext" => $sSocCodeExtAExporter);
            // Export des lignes d'abonnes dont l'adresse est livrable vers France Routage
            // $bExportFR = $bExportJade = false;
            if($bExportFR==true)
            {
                $this->oLog->info("Debut Generation fichier vers France Routage");
                $aFichiersPourFranceRoutage = $this->creeFicFranceRoutage($repoCAS_FranceRoutage, $aSocDatesATraiter);
                if(!empty($aFichiersPourFranceRoutage))
                {
                    $aFicExportesPourFranceRoutage    = array();
                    $this->oLog->info("Fichier(s) genere(s) pour France Routage : ".implode(', ', $aFichiersPourFranceRoutage). " - Voir le repertoire : ".$this->sRepTmpVersFranceRoutage);
                    $this->oLog->info("Debut Export vers FTP France Routage");

                    $srv_ftp->connect($oFicFtpExportFranceRoutage->getServeur());
                    $srv_ftp->login($oFicFtpExportFranceRoutage->getLogin(), $oFicFtpExportFranceRoutage->getMdp());
                    $srv_ftp->chdir($oFicFtpExportFranceRoutage->getRepertoire());
                    foreach($aFichiersPourFranceRoutage as $sFicV)
                    {
                        if($srv_ftp->put($sFicV, $this->sRepTmpVersFranceRoutage.'/'.$sFicV, FTP_BINARY)===false)
                        {
                            $this->oLog->info("Probleme d'export du fichier ".$sFicV.' vers le FTP '.$oFicFtpExportFranceRoutage->getServeur().'/'.$oFicFtpExportFranceRoutage->getRepertoire(), E_USER_ERROR);
                        }
                        else 
                        {
                            $checksumFicExportFranceRoutage	= md5_file($this->sRepTmpVersFranceRoutage.'/'.$sFicV);
                            $aRecapFicExportFranceRoutage	= $this->recapFicCourant($sFicV, $checksumFicExportFranceRoutage);
                            $this->enregistreFicRecap($sFicCodeExportFranceRoutage, $oFicFluxExportFranceRoutage, $sFicV, $aRecapFicExportFranceRoutage, $socId, 0, 'OK', $iOrigineExport, $this->sRepTmpVersFranceRoutage, $this->sRepBkpVersFranceRoutage);
                            $aFicExportesPourFranceRoutage[]	= $sFicV;
                            $this->oLog->info("Fichier exporte vers le FTP ".$oFicFtpExportFranceRoutage->getServeur().'/'.$oFicFtpExportFranceRoutage->getRepertoire().' : '.$sFicV);
                        }
                    }
                    $srv_ftp->close();
                    $this->oLog->info("Fichier(s) exporte(s) pour France Routage : ".implode(', ', $aFicExportesPourFranceRoutage));
                }
            }

            if ($bExportJade==true)
            {
                // Export des lignes d'abonnes dont l'adresse est livrable vers Jade
                $this->oLog->info("Debut Generation fichier vers Jade");
                $aFichiersPourJade = $this->creeFicJade($repoCAS_FranceRoutage, $aSocDatesATraiter);
                if(!empty($aFichiersPourJade))
                {
                    $aFicExportesPourJade    = array();
                    $this->oLog->info("Fichier(s) genere(s) pour Jade : ".implode(', ', $aFichiersPourJade). " - Voir le repertoire : ".$this->sRepTmpVersJade);
                    $this->oLog->info("Debut Export vers FTP France Routage");

                    $srv_ftp->connect($oFicFtpExportJade->getServeur());
                    $srv_ftp->login($oFicFtpExportJade->getLogin(), $oFicFtpExportJade->getMdp());
                    $srv_ftp->chdir($oFicFtpExportJade->getRepertoire());
                    foreach($aFichiersPourJade as $sFicV)
                    {
                        if($srv_ftp->put($sFicV, $this->sRepTmpVersJade.'/'.$sFicV, FTP_BINARY)===false)
                        {
                            $this->oLog->info("Probleme d'export du fichier ".$sFicV.' vers le FTP '.$oFicFtpExportJade->getServeur().'/'.$oFicFtpExportJade->getRepertoire(), E_USER_ERROR);
                        }
                        else 
                        {
                            $checksumFicExportJade	= md5_file($this->sRepTmpVersJade.'/'.$sFicV);
                            $aRecapFicExportJade	= $this->recapFicCourant($sFicV, $checksumFicExportJade);
                            $this->enregistreFicRecap($sFicCodeExportJade, $oFicFluxExportJade, $sFicV, $aRecapFicExportJade, $socId, 0, 'OK', $iOrigineExport, $this->sRepTmpVersJade, $this->sRepBkpVersJade);
                            $aFicExportesPourJade[]	= $sFicV;
                            $this->oLog->info("Fichier exporte vers le FTP ".$oFicFtpExportJade->getServeur().'/'.$oFicFtpExportJade->getRepertoire().' : '.$sFicV);
                        }
                    }
                    $srv_ftp->close();
                    $this->oLog->info("Fichier(s) exporte(s) pour France Routage : ".implode(', ', $aFicExportesPourJade));
                }
            }
        }
        else
        {
            

            // Les fichiers a traiter
            $aFicIterator   = new \FilesystemIterator($this->sRepTmp);
            $aFic = array();
            foreach($aFicIterator as $oFic)
            {
                // !!!! ici, verifier si le fichier a deja ete traite. Si oui, on ne le traite plus. Si non, on le traite.
                if($oFic->isFile())
                {
                    $aFic[$oFic->getFilename()] = $oFic;
                }
            }

            ksort($aFic);


            foreach($aFic as $oFic)
            {               
                $sFicNom  = $oFic->getFilename();
                $bContinueTraitement    = false;

                $this->oLog->info(date("d/m/Y H:i:s : ").' - Debut integration du fichier "'.$sFicNom.'"');

                $this->oLog->info('Debut verification du contenu du fichier "'.$sFicNom.'"');
                $checksumFichierCourant = md5_file($oFic->getFileInfo());

                $baFichierTraite    = $this->isFichierTraite($sFicCode, $sFicNom, $oFicChrgtFichiersBdd->getFicSource()->getId());

                $bContinueTraitement    = false;
                $bFicARetraiter    = false;
                if($baFichierTraite===false) // Fichier non encore traite
                {
                    $this->oLog->info("Fichier non encore traite");
                    $bContinueTraitement    = true;
                }
                else // Fichier deja traite
                {
                    // comparer le dernier fichier traite et celui courant                        
                    if($checksumFichierCourant!=$baFichierTraite['checksum'])
                    {
                        $this->oLog->info("Fichier deja traite mais checksum different ".$checksumFichierCourant." != ".$baFichierTraite['checksum']);
                        // Fichier different par rapport a celui deja traite et OK
                        $bContinueTraitement    = true;
                    }
                    else if($baFichierTraite['ficEtat']==99) // Fichier a retraiter
                    {
                        $this->oLog->info("Fichier deja traite mais demande a etre retraite");
                        // Fichier different par rapport a celui deja traite et OK
                        $bContinueTraitement    = true;
                        $bFicARetraiter = true;
                    }
                    else
                    {
                        $this->oLog->info("Fichier deja traite");
                        // Deplacement du fichier vers le repertoire de Bkp
                        rename($this->sRepTmp.'/'.$sFicNom, $this->sRepBkpLocal.'/'.$this->oString->renommeFicDeSvgrde($sFicNom, $this->sDateCourantYmd, $this->sHeureCourantYmd));
                    }
                }


                if($bContinueTraitement===true)
                {
                    $aTransf    = array(
                                    '%%NOM_FICHIER%%'           => $this->sRepTmp.'/'.$sFicNom,
                                    '%%NOM_TABLE%%'             => 'france_routage_c_a_s_tmp',
                                    '%%SEPARATEUR_CSV%%'        => ($oFicChrgtFichiersBdd->getFormatFic()=='CSV' ? $oFicChrgtFichiersBdd->getSeparateur() : ''),
                                    '%%NB_LIGNES_IGNOREES%%'    => $oFicChrgtFichiersBdd->getNbLignesIgnorees(),
                            );
                    $this->oLog->info("Chargement du fichier '".$sFicNom."' dans la table temporaire ".$aTransf['%%NOM_TABLE%%']);


                    try {
                        $this->oLog->info('Debut chargement du fichier dans la table temporaire "france_routage_c_a_s_tmp"');
                        $chargementFichier  = $oFicFormatEnregistrement->chargeDansTableTmp($aTransf);
                        if($chargementFichier!==true)
                        {
                            $this->oLog->erreur("Erreur SQL chargement de fichier : ".print_r($chargementFichier['sql'], true), E_USER_WARNING, __FILE__, __LINE__);
                            $this->oLog->erreur("Parametres avant Load Data Infile : ".print_r($aTransf, true), E_NOTICE, __FILE__, __LINE__);

                            $bContinueTraitement = false;
                        }
                        else
                        {
                            $bContinueTraitement    = true; 

                            // Mise a jour quantite si pas definie
                            $this->oLog->info('Debut Mise a jour quantite si pas definie');
                            $repoCAS_FranceRoutageTmp->initFlag();
                            $repoCAS_FranceRoutageTmp->updateQuantite(1);

                        }
                    } catch (\Exception $ex)
                    {
                        $this->oLog->erreur($ex->getMessage(), $ex->getCode(), $ex->getFile(), $ex->getLine());
                        $bContinueTraitement = false;
                    } 
                }

                $aRecapFicCourant   = array();
                if($bContinueTraitement===true)
                {
                    try {
                        // Verification & Recap du fichier en cours
                        $aRecapFicCourant = $this->recapFicCourant($sFicNom, $checksumFichierCourant);
                    } catch (FranceRoutageClientsAServirCommandException $ex2) {
                        $this->oLog->erreur($ex2->getMessage(), $ex2->getCode(), $ex2->getFile(), $ex2->getLine());
                        $this->enregistreFicRecap($sFicCode, $oFicFlux, $sFicNom, NULL, NULL, $ex2->getCodeFicEtat(), $ex2->getMessage());
                        $bContinueTraitement = false;
                    }
                }  

                // --- Arrive ici, le fichier est considere comme conforme
                if($bContinueTraitement===true)
                {
                    try {
                        // Pour chaque ligne, verifier si abonne connu ou adresse connue 
                        //      . Si oui, creer une ligne de client a servir pour Jade 
                        //          - Si abonne connu, pour le jour demande, inserer une ligne dans tournee_detail si celle ci n'existe pas encore
                        //          - Si adresse connu, inserer une ligne dans tournee_detail si celle ci n'existe pas encore
                        //      . Si non, on ne fait rien

                        // Marquage des abonnes hors Ile de France
                        //$this->oLog->info('Debut Marquage des abonnes hors Ile de France');
                        //$repoCAS_FranceRoutageTmp->marqueAbosHorsIDF($this->getContainer()->getParameter("DEPARTEMENTS")); 

                        // Suppression des abonnes hors Ile de France
                        $this->oLog->info('Debut Suppression des abonnes hors Ile de France');
                        $repoCAS_FranceRoutageTmp->supprAbosHorsIDF($this->getContainer()->getParameter("DEPARTEMENTS")); 

                        // Mise a jour des attributs sousProduit & produit & societe si le sous produit est parametre
                        $this->oLog->info('Debut Mise a jour des infos produits');
                        $repoCAS_FranceRoutageTmp->updateInfosProduit();

                        // Mise a jour du type de client (abonne ou LV)
                        $this->oLog->info('Debut Mise a jour du type de client (abonne ou LV)');
                        $repoCAS_FranceRoutageTmp->updateClientType($clientType);

                        // Suppression des "\r" et "\n" pour tous les champs
                        $this->oLog->info('Debut Suppression des "\r" et "\n" pour tous les champs');
                        $repoCAS_FranceRoutageTmp->supprCaracteresSpeciaux();

                        // Mise en majuscule de tous les champs d'adresse
                        $this->oLog->info("Debut Mise en majuscule de tous les champs d'adresse");
                        $repoCAS_FranceRoutageTmp->miseEnMajusculeAdresses();

                        // Pour les adresses connues, mise a jour des champs adresses rnvp 
                        $this->oLog->info("Debut Pour les adresses connues, mise a jour des champs adresses rnvp");
                        $repoCAS_FranceRoutageTmp->miseAJourAdrConnue(); 

                        // Normalisation des autres adresses 
                        $this->oLog->info("Debut Normalisation des autres adresses");
                        $repoCAS_FranceRoutageTmp->rnvpAutresAdresses($srvRnvp); 
                        //die();

                        // Mise a jour du champ jour_id
                        $this->oLog->info("Debut Mise a jour du champ jour_id");
                        $repoCAS_FranceRoutageTmp->updateJourId();

                        // Verification des adresses livrables
                        // Les abonnes livrables sont :
                        //      . ceux deja connus
                        //      . ceux dont l'adresse est connue
                        $this->oLog->info("Debut Verification des adresses livrables");
                        $repoCAS_FranceRoutageTmp->verifAdresssesLivrables($clientType, $srvRnvp, $srvAmsCartoGeoservice);

                        // Mise a jour des champs infos portage
                        $this->oLog->info("Debut Mise a jour des champs infos portage");
                        $repoCAS_FranceRoutageTmp->updateInfosPortage();

                        $aSocId = $repoCAS_FranceRoutageTmp->getSocieteId();
                        $aiSocId = array_keys($aSocId);
                        $socId  = 0;
                        $aSocIdTmp   = array();
                        foreach($aSocId as $aArr)
                        {
                            foreach($aArr as $val)
                            {
                                $aSocIdTmp[] = $val;
                                $socId  = $val;
                            }
                        }
                        if(count($aiSocId)>1)
                        {
                            throw FranceRoutageClientsAServirCommandException::plusieursSocieteId($sFicNom, implode(', ', $aSocIdTmp));
                        }

                        $iDernierFicRecap   = $this->enregistreFicRecap($sFicCode, $oFicFlux, $sFicNom, $aRecapFicCourant, $socId, 0, 'OK');

                        $repoCAS_FranceRoutageTmp->updateFicRecap($iDernierFicRecap);

                        // Stockage des donnees du fichier en cours de traitement
                        $this->oLog->info("Debut Stockage des donnees du fichier en cours de traitement");
                        $aSocDatesATraiter = $repoCAS_FranceRoutageTmp->enregistre();


                        // Verifie si la correspondance de la "code societe ext" existe
                        $bSocInconnue = $repoCAS_FranceRoutageTmp->isSocieteInconnue();
                        if($bSocInconnue===true)
                        {
                            throw FranceRoutageClientsAServirCommandException::societeInconnue($sFicNom);
                        }


                        // Export des lignes d'abonnes dont l'adresse est livrable vers France Routage
                        $this->oLog->info("Debut Generation fichier vers France Routage");
                        $aFichiersPourFranceRoutage = $this->creeFicFranceRoutage($repoCAS_FranceRoutage, $aSocDatesATraiter);
                        if(!empty($aFichiersPourFranceRoutage))
                        {
                            $aFicExportesPourFranceRoutage    = array();
                            $this->oLog->info("Fichier(s) genere(s) pour France Routage : ".implode(', ', $aFichiersPourFranceRoutage). " - Voir le repertoire : ".$this->sRepTmpVersFranceRoutage);
                            $this->oLog->info("Debut Export vers FTP France Routage");

                            $srv_ftp->connect($oFicFtpExportFranceRoutage->getServeur());
                            $srv_ftp->login($oFicFtpExportFranceRoutage->getLogin(), $oFicFtpExportFranceRoutage->getMdp());
                            $srv_ftp->chdir($oFicFtpExportFranceRoutage->getRepertoire());
                            foreach($aFichiersPourFranceRoutage as $sFicV)
                            {
                                if($srv_ftp->put($sFicV, $this->sRepTmpVersFranceRoutage.'/'.$sFicV, FTP_BINARY)===false)
                                {
                                    $this->oLog->info("Probleme d'export du fichier ".$sFicV.' vers le FTP '.$oFicFtpExportFranceRoutage->getServeur().'/'.$oFicFtpExportFranceRoutage->getRepertoire(), E_USER_ERROR);
                                }
                                else 
                                {
                                    $checksumFicExportFranceRoutage	= md5_file($this->sRepTmpVersFranceRoutage.'/'.$sFicV);
                                    $aRecapFicExportFranceRoutage	= $this->recapFicCourant($sFicV, $checksumFicExportFranceRoutage);
                                    $this->enregistreFicRecap($sFicCodeExportFranceRoutage, $oFicFluxExportFranceRoutage, $sFicV, $aRecapFicExportFranceRoutage, $socId, 0, 'OK', $iOrigineExport, $this->sRepTmpVersFranceRoutage, $this->sRepBkpVersFranceRoutage);
                                    $aFicExportesPourFranceRoutage[]	= $sFicV;
                                    $this->oLog->info("Fichier exporte vers le FTP ".$oFicFtpExportFranceRoutage->getServeur().'/'.$oFicFtpExportFranceRoutage->getRepertoire().' : '.$sFicV);
                                }
                            }
                            $srv_ftp->close();
                            $this->oLog->info("Fichier(s) exporte(s) pour France Routage : ".implode(', ', $aFicExportesPourFranceRoutage));
                        }


                        // Export des lignes d'abonnes dont l'adresse est livrable vers Jade
                        $this->oLog->info("Debut Generation fichier vers Jade");
                        $aFichiersPourJade = $this->creeFicJade($repoCAS_FranceRoutage, $aSocDatesATraiter);
                        if(!empty($aFichiersPourJade))
                        {
                            $aFicExportesPourJade    = array();
                            $this->oLog->info("Fichier(s) genere(s) pour Jade : ".implode(', ', $aFichiersPourJade). " - Voir le repertoire : ".$this->sRepTmpVersJade);
                            $this->oLog->info("Debut Export vers FTP France Routage");

                            $srv_ftp->connect($oFicFtpExportJade->getServeur());
                            $srv_ftp->login($oFicFtpExportJade->getLogin(), $oFicFtpExportJade->getMdp());
                            $srv_ftp->chdir($oFicFtpExportJade->getRepertoire());
                            foreach($aFichiersPourJade as $sFicV)
                            {
                                if($srv_ftp->put($sFicV, $this->sRepTmpVersJade.'/'.$sFicV, FTP_BINARY)===false)
                                {
                                    $this->oLog->info("Probleme d'export du fichier ".$sFicV.' vers le FTP '.$oFicFtpExportJade->getServeur().'/'.$oFicFtpExportJade->getRepertoire(), E_USER_ERROR);
                                }
                                else 
                                {
                                    $checksumFicExportJade	= md5_file($this->sRepTmpVersJade.'/'.$sFicV);
                                    $aRecapFicExportJade	= $this->recapFicCourant($sFicV, $checksumFicExportJade);
                                    $this->enregistreFicRecap($sFicCodeExportJade, $oFicFluxExportJade, $sFicV, $aRecapFicExportJade, $socId, 0, 'OK', $iOrigineExport, $this->sRepTmpVersJade, $this->sRepBkpVersJade);
                                    $aFicExportesPourJade[]	= $sFicV;
                                    $this->oLog->info("Fichier exporte vers le FTP ".$oFicFtpExportJade->getServeur().'/'.$oFicFtpExportJade->getRepertoire().' : '.$sFicV);
                                }
                            }
                            $srv_ftp->close();
                            $this->oLog->info("Fichier(s) exporte(s) pour France Routage : ".implode(', ', $aFicExportesPourJade));
                        }


                        $this->oLog->info("Mise a jour Etat Recapitulatif");
                        $this->miseAJourFicRecap($iDernierFicRecap, 0, '');



                    } catch (\Exception $ex)
                    {
                        $this->oLog->erreur($ex->getMessage(), $ex->getCode(), $ex->getFile(), $ex->getLine());
                    } catch (FranceRoutageClientsAServirCommandException $FR_CASIntegrationException) {
                        $this->oLog->erreur($FR_CASIntegrationException->getMessage(), $FR_CASIntegrationException->getCode(), $FR_CASIntegrationException->getFile(), $FR_CASIntegrationException->getLine());
                        $this->enregistreFicRecap($sFicCode, $oFicFlux, $sFicNom, $aRecapFicCourant, NULL, $FR_CASIntegrationException->getCodeFicEtat(), $FR_CASIntegrationException->getMessage());
                    }               
                }

                $this->oLog->info(' - Fin integration du fichier "'.$sFicNom.'"');

            }
        }
        
    	$this->oLog->info(date("d/m/Y H:i:s : ")."Fin Integration des clients a servir - Commande : ".$this->sNomCommande);
        
        $this->oLog->info("Fin commande");
    	
        return;
        
        
        
    }
    
    
    
    /**
     * Generation des fichiers a destination de France Routage
     * @param object $repoCAS_FranceRoutage
     * @param array $aSocDatesATraiter
     * @return array
     */
    private function creeFicFranceRoutage($repoCAS_FranceRoutage, $aSocDatesATraiter)
    {
        $aFichiersGeneres   = array();
        foreach($aSocDatesATraiter as $aSocDate)
        {
            $aDonneesPourFranceRoutage  = $repoCAS_FranceRoutage->donneesPourFranceRoutage($aSocDate);
            // Format nom fichier de sortie : FR_<code_societe_ext>_<AAAAMMJJ>.txt
            $sFichierSortie    = "FR_".$aSocDate['soc_code_ext'].'_'.str_replace(array('/', '-'), '', $aSocDate['date_distrib']).".txt";
            if(file_exists($this->sRepTmpVersFranceRoutage.'/'.$sFichierSortie))
            {
                unlink($this->sRepTmpVersFranceRoutage.'/'.$sFichierSortie);
            }
            if ($oFichierSortie = fopen($this->sRepTmpVersFranceRoutage.'/'.$sFichierSortie,"w+"))
            {
                foreach($aDonneesPourFranceRoutage as $aA)
                {
                    $aLigne = array();
                    
                    $aLigne[]   = $aA['date_distrib'];
                    $aLigne[]   = $aA['numabo_ext'];
                    $aLigne[]   = $aA['depot'];
                    $aLigne[]   = $aA['modele_tournee_jour_code'];
                    $aLigne[]   = $aA['ordre'];
                    $aLigne[]   = $aA['infos_portage'];
                    fwrite($oFichierSortie, implode('|', $aLigne)."\n");
                }
                fclose($oFichierSortie);
                $aFichiersGeneres[] = $sFichierSortie;
            }
        }
        return $aFichiersGeneres;
    }
    
    /**
     * Generation des fichiers a destination de Jade
     * @param object $repoCAS_FranceRoutage
     * @param array $aSocDatesATraiter
     * @return array
     */
    private function creeFicJade($repoCAS_FranceRoutage, $aSocDatesATraiter)
    {
        $aFichiersGeneres   = array();
        foreach($aSocDatesATraiter as $aSocDate)
        {
            $aDonneesPourJade  = $repoCAS_FranceRoutage->donneesPourJade($aSocDate);
            // Format nom fichier de sortie : <code_societe_ext><AAAAMMJJ>.txt
            $sFichierSortie    = $aSocDate['soc_code_ext'].str_replace(array('/', '-'), '', $aSocDate['date_distrib']).".txt";
            if(file_exists($this->sRepTmpVersJade.'/'.$sFichierSortie))
            {
                unlink($this->sRepTmpVersJade.'/'.$sFichierSortie);
            }
            if ($oFichierSortie = fopen($this->sRepTmpVersJade.'/'.$sFichierSortie,"w+"))
            {
                foreach($aDonneesPourJade as $aA)
                {
                    $aLigne = array();
                    /*
                     * 
                    IFNULL(fr.num_parution, DATE_FORMAT(fr.date_distrib, '%Y%m%d')) AS num_parution,
                    DATE_FORMAT(fr.date_distrib, '%Y/%m/%d') AS date_distrib, 
                    fr.numabo_ext, 
                    IFNULL(fr.rnvp_vol1, '') AS vol1,
                    IFNULL(fr.rnvp_vol2, '') AS vol2,
                    IFNULL(fr.rnvp_vol3, '') AS vol3,
                    IFNULL(fr.rnvp_vol4, '') AS vol4,
                    IFNULL(fr.rnvp_vol5, '') AS vol5,
                    IFNULL(fr.rnvp_cp, '') AS cp,
                    IFNULL(fr.rnvp_ville, '') AS ville,
                    'R' AS type_portage,
                    fr.soc_code_ext AS code_societe,
                    fr.prd_code_ext AS code_titre,
                    fr.spr_code_ext AS code_edition,
                    fr.qte,
                    IFNULL(fr.divers1, '') AS divers1,
                    IFNULL(fr.info_comp1, '') AS info_comp1,
                    IFNULL(fr.info_comp2, '') AS info_comp2,
                    IFNULL(fr.divers2, '') AS divers2
                     */
                    $aLigne[]   = $aA['num_parution'];
                    $aLigne[]   = $aA['date_distrib'];
                    $aLigne[]   = $aA['numabo_ext'];
                    $aLigne[]   = $aA['vol1'];
                    $aLigne[]   = $aA['vol2'];
                    $aLigne[]   = $aA['vol3'];
                    $aLigne[]   = $aA['vol4'];
                    $aLigne[]   = $aA['vol5'];
                    $aLigne[]   = $aA['cp'];
                    $aLigne[]   = $aA['ville'];
                    $aLigne[]   = $aA['type_portage'];
                    $aLigne[]   = $aA['code_societe'];
                    $aLigne[]   = $aA['code_titre'];
                    $aLigne[]   = $aA['code_edition'];
                    $aLigne[]   = $aA['qte'];
                    $aLigne[]   = $aA['divers1'];
                    $aLigne[]   = $aA['info_comp1'];
                    $aLigne[]   = $aA['info_comp2'];
                    $aLigne[]   = $aA['divers2'];
                    fwrite($oFichierSortie, implode('|', $aLigne)."\n");
                }
                fclose($oFichierSortie);
                $aFichiersGeneres[] = $sFichierSortie;
            }
        }
        return $aFichiersGeneres;
    }
    
    /**
     * Recupere les donnees recapitulant le fichier (checksum, date de distrib, date de parution, code soc. ext., ...)
     * @param string $sFic
     * @param string $checksum
     * @return array
     * @throws type
     */
    private function recapFicCourant($sFic, $checksum)
    {
        $this->oLog->info("Debut de la recuperation de la recapitulatif du fichier en cours");
        $aRecapFicCourant   = array();
        $oFranceRoutageClientsAServirTmp  = $this->getContainer()->get('doctrine')->getRepository('AmsDistributionBundle:FranceRoutageClientsAServirTmp');
        $aDatesNblignesNbex = $oFranceRoutageClientsAServirTmp->getDatesNblignesNbex();
        
        foreach($aDatesNblignesNbex as $aO)
        {
            // Date de distribution
            if(!isset($aRecapFicCourant['dateDistrib']) || !in_array($aO["dateDistrib"], $aRecapFicCourant['dateDistrib']))
            {
                $aRecapFicCourant['dateDistrib'][]  = $aO["dateDistrib"];
            }
            // Date de parution
            if(!isset($aRecapFicCourant['dateParution']) || !in_array($aO["dateParution"], $aRecapFicCourant['dateParution']))
            {
                $aRecapFicCourant['dateParution'][]  = $aO["dateParution"];
            }
            // Nombre de lignes
            if(!isset($aRecapFicCourant['nbLignes']))
            {
                $aRecapFicCourant['nbLignes']   = $aO["nbLignes"];
            }
            else
            {
                $aRecapFicCourant['nbLignes']   += $aO["nbLignes"];
            }
            // Nombre d'e lignes'exemplaires
            if(!isset($aRecapFicCourant['nbEx']))
            {
                $aRecapFicCourant['nbEx']   = $aO["nbEx"];
            }
            else
            {
                $aRecapFicCourant['nbEx']   += $aO["nbEx"];
            }
        }
        if(\count($aRecapFicCourant['dateDistrib'])>1 || \count($aRecapFicCourant['dateParution'])>1)
        {
            throw FranceRoutageClientsAServirCommandException::plusieursDates($sFic, $aRecapFicCourant);
        }
        if(\count($aRecapFicCourant['nbLignes'])==0)
        {
            throw FranceRoutageClientsAServirCommandException::fichierVide($sFic, $aRecapFicCourant);
        }
        
        // checksum        
        $aRecapFicCourant['checksum']   = $checksum;
        
        // code societe exterieur
        $aSocCodeExt    = $oFranceRoutageClientsAServirTmp->getCodeSocExt();
        foreach($aSocCodeExt as $aSoc)
        {
            $aRecapFicCourant['socCodeExt'][]   = $aSoc['socCodeExt'];
        }
        if(\count($aRecapFicCourant['socCodeExt'])>1)
        {
            throw FranceRoutageClientsAServirCommandException::plusieursSocCode($sFic, $aRecapFicCourant);
        }
        if(trim($aRecapFicCourant['socCodeExt'][0])=='')
        {
            throw FranceRoutageClientsAServirCommandException::socCodeNonDefini($sFic, $aRecapFicCourant);
        }
        $this->oLog->info("Fin de la recuperation de la recapitulatif du fichier en cours");
        //print_r($aRecapFicCourant);
        return $aRecapFicCourant;     
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
                if (isset($aRecapFicCourant['socCodeExt']) && isset($aRecapFicCourant['socCodeExt'][0])) {
                    $oFicRecap->setSocCodeExt($aRecapFicCourant['socCodeExt'][0]);
                }
                if (isset($aRecapFicCourant['dateParution']) && isset($aRecapFicCourant['dateParution'][0])) {
                    $oFicRecap->setDateParution($aRecapFicCourant['dateParution'][0]);
                }
                if (isset($aRecapFicCourant['dateDistrib']) && isset($aRecapFicCourant['dateDistrib'][0])) {
                    $oFicRecap->setDateDistrib($aRecapFicCourant['dateDistrib'][0]);
                }
                $oFicRecap->setChecksum($aRecapFicCourant['checksum']);
                $oFicRecap->setNbLignes($aRecapFicCourant['nbLignes']);
                $oFicRecap->setNbExemplaires($aRecapFicCourant['nbEx']);
            }
            if (!is_null($socId)) {
                $oSocieteRepo = $this->getContainer()->get('doctrine')->getRepository('AmsProduitBundle:Societe');
                $oFicRecap->setSociete($oSocieteRepo->findOneById($socId));
            }

            
            // Afin de contourner une erreur bizzare d'allocation memoire, on recupere de nouveau l'entite FicSource
            if(!in_array($sFicCode, $this->aFicCodeExport))
            {
                $oFicRecap->setFicSource($this->getContainer()->get('doctrine')
                            ->getRepository('AmsFichierBundle:FicChrgtFichiersBdd')
                            ->findOneByCode($sFicCode)->getFicSource());
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
            $sRepTmp    = $this->sRepTmp;
            $sRepBkpLocal    = $this->sRepBkpLocal;
            if(!is_null($repTmp))
            {
                $sRepTmp    = $repTmp;
            }
            if(!is_null($repBkp))
            {
                $sRepBkpLocal    = $repBkp;
            }
            
            rename($sRepTmp.'/'.$sFicNom, $sRepBkpLocal.'/'.$this->oString->renommeFicDeSvgrde($sFicNom, $this->sDateCourantYmd, $this->sHeureCourantYmd));
            
            return $iDernierFicRecap;
        } 
        catch (DBALException $ex) {
            throw $ex;
        }
    }
    
    
    private function miseAJourFicRecap($ficRecapId, $codeEtat = NULL, $msgEtat = NULL) {
        try {
            $repoFicRecap = $this->getContainer()->get('doctrine')->getRepository('AmsFichierBundle:FicRecap');
            $oFicEtatRepo = $this->getContainer()->get('doctrine')->getRepository('AmsFichierBundle:FicEtat');
            $oFicEtat = $oFicEtatRepo->findOneBy(array('code' => $codeEtat));
            if (!is_null($oFicEtat)) {
                $repoFicRecap->updateEtat($ficRecapId, array("fic_etat_id" => $oFicEtat->getId(), "eta_msg" => (is_null($msgEtat) ? '' : $msgEtat)));
            }
        } catch (DBALException $ex) {
            throw $ex;
        }
    }
    
		
    /**
     * Suuppression des accents
     */
    private function suppr_accent($str, $encodage='utf-8')
    {
        $str = htmlentities($str, ENT_NOQUOTES, $encodage);
        $str = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
        $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
        $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères  
        return $str;
    }

    
}
