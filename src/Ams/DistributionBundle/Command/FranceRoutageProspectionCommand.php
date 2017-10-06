<?php 
namespace Ams\DistributionBundle\Command;

//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Ams\SilogBundle\Command\GlobalCommand;

use Doctrine\DBAL\DBALException;

use Ams\DistributionBundle\Exception\FranceRoutageProspectionCommandException;

/**
 * 
 * Integration temporaire des clients a servir France Routage et export vers DAN Jade des abonnes que l'on peut classer dans une tournee
 * 
 * 
 * Table "fic_flux" =>  id=42, fic_code = "France Routage - Import Fichier pour prospection"
                        INSERT INTO fic_flux (id, libelle) VALUES (42, 'France Routage - Import Fichier pour prospection');
 *
 * Table "fic_ftp" =>   fic_code = "FR_ROUTAGE_PROSPECTION"
            INSERT INTO fic_ftp (code, serveur, login, mdp, repertoire, rep_sauvegarde, id_soc_distrib)
            VALUES ('FR_ROUTAGE_PROSPECTION', '10.151.93.2', 'MROAD', 'M245road', 'FRANCE_ROUTAGE/Vers_MROAD/CAS', 'Bkp', 'FR_ROUTAGE');
 * 
 *  Table "fic_format_enregistrement" => fic_code = "FR_ROUTAGE_PROSPECTION"
            INSERT INTO fic_format_enregistrement (fic_code, attribut, col_debut, col_long, col_val, col_val_rplct, col_desc)
            SELECT 'FR_ROUTAGE_PROSPECTION' AS fic_code, attribut, col_debut, col_long, col_val, col_val_rplct, col_desc FROM `fic_format_enregistrement` WHERE `fic_code`='FR_ROUTAGE_CAS' AND attribut NOT IN ('DATE_PARUTION', 'TYPE_PORTAGE', 'PRD_CODE_EXT', 'SPR_CODE_EXT') order by col_val
 * 
 * Table "fic_chrgt_fichiers_bdd" => fic_code = "FR_ROUTAGE_PROSPECTION" 
            INSERT INTO fic_chrgt_fichiers_bdd (`id`, `fic_ftp`, `fic_source`, `fic_code`, `regex_fic`, `format_fic`, `nb_lignes_ignorees`, `separateur`, `trim_val`, `nb_col`, `flux_id`, `ss_rep_traitement`) 
            VALUES (NULL, '--22', '3', 'FR_ROUTAGE_PROSPECTION', '/^(\\.\\/)?[A-Z0-9]{2}`date_Ymd_1_10`\\.txt$/i', 'CSV', '0', '|', '1', '14', '42', 'FrRoutageProspection');
 * 
 * Exemple de commande : [ php app/console import_fic_ftp FR_ROUTAGE_PROSPECTION <prospection_id> --env=dev ]
 *                      php app/console france_routage_prospection FR_ROUTAGE_PROSPECTION 27 --env=dev
 * 
 * -- End DEV : Dates a tester : 2015-03-28, 2015-03-05, 2015-03-12, 2015-03-19, 2015-04-02
 * @author aandrianiaina
 *
 */
class FranceRoutageProspectionCommand extends GlobalCommand
{
    protected function configure()
    {
    	$this->sNomCommande	= 'france_routage_prospection';
        $ficCodeDefaut  = 'FR_ROUTAGE_PROSPECTION';
        $jourDefaut = 'J+2'; // J+2
        $societesDefaut = "";
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console france_routage_prospection FR_ROUTAGE_PROSPECTION 8 --env=<ENV> Expl : php app/console france_routage_prospection FR_ROUTAGE_PROSPECTION 8 --env=prod
        $this
            ->setDescription('France Routage Propection')
            ->addArgument('fic_code', InputArgument::REQUIRED, 'Code source de donnees')
            ->addArgument('id', InputArgument::REQUIRED, 'ID de la prospection')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
        
        $sFicCode 	= $input->getArgument('fic_code');
        
        if($sFicCode != 'FR_ROUTAGE_PROSPECTION')
        {
            $this->oLog->erreur("L'argument ".'"'.$sFicCode.'" est inconnu. Utiliser plutot "FR_ROUTAGE_PROSPECTION"');
            $this->oLog->info("Fin commande");
            exit();
        }
        
        $FRProspectionListeId = $input->getArgument('id'); 
        if(!preg_match('/^(0|[1-9][0-9]*)$/', $FRProspectionListeId))
        {
            $this->oLog->erreur("L'argument ".'"'.$FRProspectionListeId.'" n'."est pas conforme. On attend un entier");
            $this->oLog->info("Fin commande");
            exit();
        }
        else
        {
            $FRProspectionListeId= intval($FRProspectionListeId);
        }
        
        $this->oLog->info(date("d/m/Y H:i:s : ")."Debut France Routage Propection - Commande : ".$this->sNomCommande." ".$sFicCode." ".$FRProspectionListeId);
        
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
    	
        // Repertoire ou l'on recupere les fichiers a traiter
        $this->sRepTmp	= $this->sRepFichiersPrinc.'/'.$this->getContainer()->getParameter('SOUSREP_FICHIERS_TMP').'/'.$oFicChrgtFichiersBdd->getSsRepTraitement().'/'.$sFicCode;
        
        // Repertoire Backup Local
    	$this->sRepBkpLocal	= $this->sRepFichiersPrinc.'/'.$this->getContainer()->getParameter('SOUSREP_FICHIERS_BKP').'/'.$oFicChrgtFichiersBdd->getSsRepTraitement().'/'.$sFicCode;
        
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
        
        $repoFR_ProspectionTmp   = $this->getContainer()->get('doctrine')
                                        ->getRepository('AmsDistributionBundle:FranceRoutageProspectionTmp'); 
        
        $repoFR_ProspectionAdrNormRefTmp   = $this->getContainer()->get('doctrine')
                                        ->getRepository('AmsDistributionBundle:FranceRoutageProspectionAdrNormRefTmp'); 
        
        $repoFR_Prospection   = $this->getContainer()->get('doctrine')
                                        ->getRepository('AmsDistributionBundle:FranceRoutageProspection');
        
        $repoFR_ProspectionListe   = $this->getContainer()->get('doctrine')
                                        ->getRepository('AmsDistributionBundle:FranceRoutageProspectionListe');
        $aFR_ProspectionListeCritere = array(
                                                'id' => $FRProspectionListeId,
                                                'dateAnnulation' => NULL
                                              );
        $oFR_ProspectionListe   = $repoFR_ProspectionListe->findOneBy($aFR_ProspectionListeCritere);
        
        if(!empty($oFR_ProspectionListe))
        {
            $sNomFicTransforme  = $oFR_ProspectionListe->getFicTransforme(); // Nom de fichier attendu
            $regexFicTransforme = '/'.str_replace('.', "\.", $sNomFicTransforme).'$/';
        
              
                // Les fichiers a traiter ------- A modifier afin de ne prendre en compte que les fichiers a traiter
            $aFicIterator   = new \FilesystemIterator($this->sRepTmp);
            $aFic = array();
            foreach($aFicIterator as $oFic)
            {
                if($oFic->isFile())
                {
                    if(preg_match($regexFicTransforme, $oFic->getFilename()))
                    {
                        $aFic[$oFic->getFilename()] = $oFic;
                    }
                }
            }

            ksort($aFic);

            if(!empty($aFic))
            {
                foreach($aFic as $oFic)
                {               
                    $sFicNom  = $oFic->getFilename();

                    $this->oLog->info(date("d/m/Y H:i:s : ").' - Debut integration du fichier "'.$sFicNom.'"');

                    $this->oLog->info('Debut verification du contenu du fichier "'.$sFicNom.'"');

                    $bContinueTraitement    = true;


                    $aTransf    = array(
                                    '%%NOM_FICHIER%%'           => $this->sRepTmp.'/'.$sFicNom,
                                    '%%NOM_TABLE%%'             => 'france_routage_prospection_tmp',
                                    '%%SEPARATEUR_CSV%%'        => ($oFicChrgtFichiersBdd->getFormatFic()=='CSV' ? $oFicChrgtFichiersBdd->getSeparateur() : ''),
                                    '%%NB_LIGNES_IGNOREES%%'    => $oFicChrgtFichiersBdd->getNbLignesIgnorees(),
                            );
                    $this->oLog->info("Chargement du fichier '".$sFicNom."' dans la table temporaire ".$aTransf['%%NOM_TABLE%%']);


                    try {
                        $this->oLog->info('Debut chargement du fichier dans la table temporaire "france_routage_prospection_tmp"');
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
                            $repoFR_ProspectionTmp->initFlag();
                            // $repoFR_ProspectionTmp->updateQuantite(1);

                        }
                    } catch (\Exception $ex)
                    {
                        $this->oLog->erreur($ex->getMessage(), $ex->getCode(), $ex->getFile(), $ex->getLine());
                        $bContinueTraitement = false;
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
                            //$repoFR_ProspectionTmp->marqueAbosHorsIDF($this->getContainer()->getParameter("DEPARTEMENTS")); 

                            // Suppression des abonnes dont CP ou Ville est vide
                            $this->oLog->info('Debut Suppression des abonnes dont CP ou Ville est vide');
                            $repoFR_ProspectionTmp->supprAbosCPVilleVide(); 

                            // Suppression des abonnes hors Ile de France
                            $this->oLog->info('Debut Suppression des abonnes hors Ile de France');
                            $repoFR_ProspectionTmp->supprAbosHorsIDF($this->getContainer()->getParameter("DEPARTEMENTS")); 

                            // Mise a jour des attributs sousProduit & produit & societe si le sous produit est parametre
                            $this->oLog->info('Debut Mise a jour des infos produits');
                            $repoFR_ProspectionTmp->updateSoc();

                            // Mise a jour du type de client (abonne ou LV)
                            $this->oLog->info('Debut Mise a jour du type de client (abonne ou LV)');
                            $repoFR_ProspectionTmp->updateClientType($clientType);

                            // Marquage des lignes d origine
                            $this->oLog->info('Debut Marquage des lignes d origine');
                            $repoFR_ProspectionTmp->updateOrigine();

                            // Suppression des "\r" et "\n" pour tous les champs
                            $this->oLog->info('Debut Suppression des "\r" et "\n" pour tous les champs');
                            $repoFR_ProspectionTmp->supprCaracteresSpeciaux();

                            // Mise en majuscule de tous les champs d'adresse
                            $this->oLog->info("Debut Mise en majuscule de tous les champs d'adresse");
                            $repoFR_ProspectionTmp->miseEnMajusculeAdresses();

                            // Reorganisation des adresses a normaliser
                            //$this->oLog->info("Debut Reorganisation des adresses a normaliser");
                            //$repoFR_ProspectionTmp->reorganisationAdresseANormaliser();

                            // Normalisation des autres adresses 
                            //$this->oLog->info("Debut Normalisation des adresses");
                            //$repoFR_ProspectionTmp->rnvpAdresses($srvRnvp);
                            
                            // Reorganisation des adresses a normaliser + Normalisation  
                            $this->oLog->info("Debut Reorganisation des adresses a normaliser + Normalisation");
                            $repoFR_ProspectionTmp->reorganisationAdresseEtRNVP($srvRnvp);

                            // Mise en table temporaire des adresses livrees le jour de la date de reference
                            $this->oLog->info("Debut Mise en table temporaire des adresses livrees le jour de la date de reference");
                            $repoFR_ProspectionAdrNormRefTmp->init($oFR_ProspectionListe->getDateRef(), $oFR_ProspectionListe->getFlux()); 

                            // Reorganisation des adresses a normaliser
                            $this->oLog->info("Debut Marquage des adresses livrables");
                            $repoFR_ProspectionTmp->marquageAdresssesLivrables($oFR_ProspectionListe->getFlux());

                            // Stockage des adresses a normaliser
                            $this->oLog->info("Debut Stockage des adresses livrables"); // Si "deuxieme parametre" == 0 => On stocke tout. Sinon, on ne stocke que les adresses livrables
                            $repoFR_ProspectionTmp->stockageAdresssesLivrables($FRProspectionListeId, 1);





                            $this->oLog->info(' - Fin Integration du fichier "'.$sFicNom.'"');



                        } catch (\Exception $ex)
                        {
                            $this->oLog->erreur($ex->getMessage(), $ex->getCode(), $ex->getFile(), $ex->getLine());
                        } catch (FranceRoutageProspectionCommandException $FR_CASIntegrationException) {
                            $this->oLog->erreur($FR_CASIntegrationException->getMessage(), $FR_CASIntegrationException->getCode(), $FR_CASIntegrationException->getFile(), $FR_CASIntegrationException->getLine());
                        }               
                    }
                    else
                    {
                        $this->oLog->info(' - Fichier "'.$sFicNom.'" non traite');
                    }

                }
            }
            else
            {
                $this->oLog->info('ID Prospection:'.$FRProspectionListeId.' - Le fichier attendu "'.$sNomFicTransforme.'" '."n'est pas encore pret");
            }
        }
        else
        {
            $this->oLog->info('ID Prospection:'.$FRProspectionListeId." - C'est annulee surement");
        }
        
        
        
        
        
            

        
        
    	$this->oLog->info(date("d/m/Y H:i:s : ")."Fin France Routage Propection - Commande : ".$this->sNomCommande." ".$FRProspectionListeId);
        
        $this->oLog->info("Fin commande");
    	
        return;
        
        
        
    }

    
}
