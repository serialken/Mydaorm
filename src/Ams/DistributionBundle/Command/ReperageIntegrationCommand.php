<?php 
namespace Ams\DistributionBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\DBAL\DBALException;
use Ams\DistributionBundle\Exception\ReperageIntegrationCommandException;
use Ams\DistributionBundle\Exception\ReperageSQLException;
use Ams\WebserviceBundle\Exception\RnvpLocalException;
use Ams\WebserviceBundle\Exception\GeocodageException;
use Ams\SilogBundle\Lib\StringLocal;
use Ams\SilogBundle\Command\GlobalCommand;
use Ams\FichierBundle\Entity\FicRecap;

/**
 * 
 * "Command" integration des fichiers de reperages
 * 
 * Pour executer, faire : 
 *                  php app/console reperage_integration <<fic_code>> 
 *      Expl : php app/console reperage_integration JADE_REPERAGE
 *
 */
class ReperageIntegrationCommand extends GlobalCommand
{
    private $aFichierFluxParam;
    private $sRepTmp;
    private $sRepBkpLocal;
    protected $idSh;
    protected $idAi;
    
    protected function configure()
    {
    	$this->sNomCommande	= 'reperage_integration';
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console reperage_integration <<fic_code>> Expl : php app/console reperage_integration JADE_REPERAGE
        $this
            ->setDescription('Importation des fichiers de reperage')
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
        $this->oLog->info(date("d/m/Y H:i:s : ")."Debut Importation des fichiers de reperage - Commande : ".$this->sNomCommande);
    	
        $em    = $this->getContainer()->get('doctrine')->getManager();
        
    	$sFicCode 	= $input->getArgument('fic_code');	// Expl : JADE_CAS <=> Importation des reperage venant de JADE
        $oString	= new StringLocal('');  
        
    	// Repertoire ou l'on recupere les fichiers a traiter
        $this->sRepTmp	= $this->cree_repertoire($this->sRepFichiersPrinc.'/'.$this->getContainer()->getParameter('SOUSREP_FICHIERS_TMP').'/'.$this->getContainer()->getParameter('SOUSREP_FICHIERS_REPERAGE').'/'.$sFicCode);
        

        // Repertoire Backup Local
    	$this->sRepBkpLocal	= $this->cree_repertoire($this->sRepFichiersPrinc.'/'.$this->getContainer()->getParameter('SOUSREP_FICHIERS_BKP').'/'.$this->getContainer()->getParameter('SOUSREP_FICHIERS_REPERAGE').'/'.$sFicCode);
    	
        // Recuperation des parameters concernant le FTP et les fichiers a recuperer
        $oFicChrgtFichiersBdd = $this->getContainer()->get('doctrine')
                        ->getRepository('AmsFichierBundle:FicChrgtFichiersBdd')
                        ->getParamFluxByCode($sFicCode);
        if(is_null($oFicChrgtFichiersBdd))
        {
            $this->suiviCommand->setMsg("Le flux ".$sFicCode." n'est pas parametre dans 'fic_chrgt_fichiers_bdd'");
            $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
            $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
            $this->suiviCommand->setEtat("KO");
            $this->oLog->erreur("Le flux ".$sFicCode." n'est pas parametre dans 'fic_chrgt_fichiers_bdd'", E_USER_ERROR);
             $this->registerError();
            if($input->getOption('id_ai') && $input->getOption('id_sh')){
                $this->registerErrorCron($this->idAi);
            }
            throw new \Exception("Identification de flux introuvable dans 'fic_chrgt_fichiers_bdd'");
        }
       
        // Si 0, c'est abonne. Si 1, c'est Lieu de vente
        $clientType   = $oFicChrgtFichiersBdd->getFicSource()->getClientType();
        // Recuperation de l'identifiant du Flux de fichier
        $oFicFlux   = $oFicChrgtFichiersBdd->getFlux();
        
        $oFicFormatEnregistrement   = $this->getContainer()->get('doctrine')
                                        ->getRepository('AmsFichierBundle:FicFormatEnregistrement');
        $oFicFormatEnregistrement->getSQLLoadDataInFile($sFicCode, $oFicChrgtFichiersBdd->getFormatFic(), $oFicChrgtFichiersBdd->getTrimVal(), $oFicChrgtFichiersBdd->getNbCol(), 'latin1');
    	$srvAdresseRnvp    = $this->getContainer()->get('adresse_rnvp'); 
        $repoAbonneSoc = $this->getContainer()->get('doctrine')->getRepository('AmsAbonneBundle:AbonneSoc');
        
        $repoAdresse = $this->getContainer()->get('doctrine')->getRepository('AmsAdresseBundle:Adresse');
        $repoAdresseTmp = $this->getContainer()->get('doctrine')->getRepository('AmsAdresseBundle:AdresseTmp');
    	
        // Les fichiers a traiter
        $aFicIterator   = new \FilesystemIterator($this->sRepTmp);
        $aFic = array();
        foreach($aFicIterator as $oFic)
        {
            if($oFic->isFile())
            {
                $aFic[$oFic->getFilename()] = $oFic;
            }
        }
        
        ksort($aFic);
        
        
        foreach($aFic as $oFic)
        {               
            $sFicNom  = $oFic->getFilename();
            $sFicChemin  = $oFic->getFileInfo();

            $this->oLog->info(' - Debut integration du fichier "'.$sFicNom.'"');

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
                                '%%NOM_FICHIER%%'       => $this->sRepTmp.'/'.$sFicNom,
                                '%%NOM_TABLE%%'     => 'reperage_tmp',
                                '%%SEPARATEUR_CSV%%'    => ($oFicChrgtFichiersBdd->getFormatFic()=='CSV' ? $oFicChrgtFichiersBdd->getSeparateur() : ''),
                                '%%NB_LIGNES_IGNOREES%%'    => $oFicChrgtFichiersBdd->getNbLignesIgnorees(),
                        );
                $this->oLog->info("Chargement du fichier '".$sFicNom."' dans la table temporaire ".$aTransf['%%NOM_TABLE%%']);

          

                // --- Arrive ici, le fichier est considere comme conforme                


                try {
                    $this->oLog->info('Debut chargement du fichier dans la table temporaire "reperage_tmp"');
                    $chargementFichier  = $oFicFormatEnregistrement->chargeDansTableTmp($aTransf); // var_dump($chargementFichier);die();
                    if($chargementFichier!==true)
                    {
                        $this->suiviCommand->setMsg("Erreur SQL chargement de fichier : ".__FILE__);
                        $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_WARNING));
                        $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                        $this->suiviCommand->setEtat("OK");
                        $this->oLog->erreur("Erreur SQL chargement de fichier : ".print_r($chargementFichier['sql'], true), E_USER_WARNING, __FILE__, __LINE__);
                        $this->oLog->erreur("Parametres avant Load Data Infile : ".print_r($aTransf, true), E_NOTICE, __FILE__, __LINE__);

                        $bContinueTraitement = false;
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
                    $bContinueTraitement = false;
                }
            }


            $aRecapFicCourant   = array();
            if($bContinueTraitement===true)
            {
                try {
                    // Verification & Recap du fichier en cours
                    $aRecapFicCourant = $this->recapFicCourant($sFicNom, $checksumFichierCourant);
                } catch (ReperageIntegrationCommandException $ex2) {
                    $this->suiviCommand->setMsg($ex2->getMessage());
                    $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type($ex2->getCode()));
                    $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                    $this->suiviCommand->setEtat("KO");
                    $this->oLog->erreur($ex2->getMessage(), $ex2->getCode(), $ex2->getFile(), $ex2->getLine());
                    $this->registerError();
                    if($input->getOption('id_ai') && $input->getOption('id_sh')){
                        $this->registerErrorCron($this->idAi);
                    }
                    $this->enregistreFicRecap($sFicCode, $oFicFlux, $sFicNom, NULL, NULL, $ex2->getCodeFicEtat(), $ex2->getMessage());
                    $bContinueTraitement = false;
                }
            } 


            // --- Arrive ici, le fichier est considere comme conforme
            if($bContinueTraitement===true)
            {
                // --- Arrive ici, le fichier est considere comme conforme
                $repoReperageTmp            = $this->getContainer()->get('doctrine')->getRepository('AmsDistributionBundle:ReperageTmp');
                $repoReperage            = $this->getContainer()->get('doctrine')->getRepository('AmsDistributionBundle:Reperage');
                $srv_ams_carto_geoservice = $this->getContainer()->get('ams_carto.geoservice');
                
                try {

                    // Mise a jour du type de client (abonne ou LV)
                    $this->oLog->info('Debut Mise a jour du type de client (abonne ou LV)');
                    $repoReperageTmp  ->updateClientType($clientType);
                    
                    // Suppression des "\r" et "\n" pour tous les champs
                     $this->oLog->info('Debut Suppression des "\r" et "\n" pour tous les champs');
                     $repoReperageTmp  ->supprCaracteresSpeciaux();

                    // Mise en majuscule de tous les champs d'adresse
                    $this->oLog->info("Debut Mise en majuscule de tous les champs d'adresse");
                    $repoReperageTmp  ->miseEnMajusculeAdresses();

                    // -------- Infos Produit : societe, produit & sousProduit
                    // Update Infos Produit
                    $this->oLog->info("Debut Update Infos Produit");
                    $repoReperageTmp->updateInfosProduit();

                    // Update Infos Produit si le produit n'est pas connu. On recherche le produit par defaut en fonction de societeExt
                    $this->oLog->info("Debut Update Infos Produit si le produit n'est pas connu. On recherche le produit par defaut en fonction de societeExt");
                    $repoReperageTmp->updateInfosProduitBySocieteExt();
                     // -------- AbonneSoc
                    // Update AbonneSoc
                    $this->oLog->info("Debut Update AbonneSoc");
                    $repoReperageTmp->updateAbonneSoc($clientType);

                     // Insertion de tous les abonnes inconnus
                    $this->oLog->info("Debut Stockage des nouveaux AbonneSoc");
                    $repoReperageTmp->insertNouveauAbonneSoc($clientType);

                    // Update AbonneSoc
                    $this->oLog->info("Debut Update AbonneSoc");
                    $repoReperageTmp->updateAbonneSoc($clientType); 
                   
                    // Update Adresse nouvellement creees
                    $this->oLog->info("Debut Update Adresse nouvellement creees");
                    $repoReperageTmp->updateAdresse();   
                    
                    // Insertion des nouvelles Adresses
                    $this->oLog->info("Debut Insertion des nouvelles Adresses");
                    $repoReperageTmp->insertNouvelleAdresse();

                    // Mise en table temporaire des adresses deja normalisees - cette etape permet de recuperer facilement le dernier rnvp d'une adresse deja connue
                    //$this->oLog->info('Debut Mise en table temporaire ("adresse_tmp") des adresses deja normalisees');
                    //$repoAdresseTmp->init('REPER');

                    // Normalisation de toutes les nouvelles adresses 
                    $this->oLog->info('Debut Normalisation (et eventuellement mise a jour des stop de livraison connus) de toutes les nouvelles "adresse" non encore normalisees');
                    $srvAdresseRnvp->normaliseTouteAdresse($this->getContainer()->getParameter('DATE_FIN'));

                    // Geocodage de toutes les "adresse" non encore geocodees
                    $this->oLog->info('Debut Geocodage de toutes les "adresse" non encore geocodees');
                    $srvAdresseRnvp->geocodeTouteAdresse();

                    // Calcul automatique des points de livraison
                    $this->oLog->info("Debut Calcul automatique des points de livraison");
                    $repoAdresse->miseAJourAutomatiquePtLivraison();
 
                    // Update Adresse nouvellement creees
                    $this->oLog->info("Debut Update Adresse nouvellement creees");
                    $repoReperageTmp->updateAdresse();

                    // Mise a jour de commune & depot de la table temporaire
                    $this->oLog->info("Debut Mise a jour de commune & depot de la table temporaire");
                    $repoReperageTmp->updateCommuneDepot();
                    
                    // Mise a jour produit_id selon la repartition depot-commune[-societe]
                    $this->oLog->info("Debut Mise a jour de produit_id selon la repartition depot-commune[-societe]");
                    $repoReperageTmp->updateProduitSelonRepartition();
                    
                    // Mise a jour de tournees deja connues
                    $this->oLog->info("Debut Mise a jour de tournees deja connues");
                    $repoReperageTmp->updateTourneesConnues();
                    
                    // Classement automatique des non-classes
                    $aPointsAClasser  = $repoReperageTmp->pointsAClasser(2000);
                    $srv_ams_carto_geoservice->classementAuto($aPointsAClasser);
                    
                    // Suppression tournees incoherentes
                    $this->oLog->info("Debut Suppression tournees incoherentes");
                    $repoReperageTmp->supprTourneesIncoherentes();
                                     
                    // Attribution d'un numero d'abonne unique pour les nouveaux AbonneSoc 
                    $this->oLog->info("Debut Attribution d'un numero d'abonne unique pour les nouveaux AbonneSoc");
                    $repoAbonneSoc->updateAbonneUnique();                    
            
                    // ----------------- Fin traitement dans les tables temporaires                            
   
               
                    // ----- Creation de la ligne recapitulant le fichier traite

                    $aSocId = $repoReperageTmp->getSocieteId();
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
                        $this->suiviCommand->setMsg('Plusieurs "societe ID (sens M-ROAD)" trouvees. Fichier concerne "'.$sFicNom.'" : ID societe -> '.'"'.implode(', ', $aSocIdTmp).'"');
                        $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
                        $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                        $this->suiviCommand->setEtat("KO");
                        $this->registerError();
                        if($input->getOption('id_ai') && $input->getOption('id_sh')){
                            $this->registerErrorCron($this->idAi);
                        }
                        throw ReperageIntegrationCommandException::plusieursSocieteId($sFicNom, implode(', ', $aSocIdTmp));
                    }

                    // Verifie si la correspondance de la "code societe ext" existe
                    $bSocInconnue = $repoReperageTmp->isSocieteInconnue();
                    if($bSocInconnue===true)
                    {
                        $this->suiviCommand->setMsg('Le "code societe" du fichier'.'"'.$sFicNom.'"'." n'est pas parametree");
                        $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
                        $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                        $this->suiviCommand->setEtat("KO");
                        $this->registerError();
                        if($input->getOption('id_ai') && $input->getOption('id_sh')){
                            $this->registerErrorCron($this->idAi);
                        }
                        throw ReperageIntegrationCommandException::societeInconnue($sFicNom);
                    }


                    $iDernierFicRecap   = $this->enregistreFicRecap($sFicCode, $oFicFlux, $sFicNom, $aRecapFicCourant, $socId, 0, 'OK');
                    $repoReperageTmp->updateReperage($iDernierFicRecap);
                  
                    $repoReperageTmp->updateFicRecap($iDernierFicRecap);
                    //mise a jour du champ ficrecapN dans la
                    //$repoReperageTmp->updateTmpReperage($iDernierFicRecap);


                    // ---------- Remplissage des tables 
                    $this->oLog->info("Debut Transfert des donnees de la table temporaire vers les table reperage");
 
                    $repoReperage->tmpVersReperage($iDernierFicRecap);
              

                    $this->oLog->info("Mise a jour Etat Recapitulatif");
                    $this->miseAJourFicRecap($iDernierFicRecap, 0, '');

            
                } catch (RnvpLocalException $rnvpLocalException) {
                    $this->suiviCommand->setMsg($rnvpLocalException->getMessage());
                    $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type($rnvpLocalException->getCode()));
                    $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                    $this->suiviCommand->setEtat("KO");
                    $this->oLog->erreur($rnvpLocalException->getMessage(), $rnvpLocalException->getCode(), $rnvpLocalException->getFile(), $rnvpLocalException->getLine());
                    $this->registerError();
                    if($input->getOption('id_ai') && $input->getOption('id_sh')){
                        $this->registerErrorCron($this->idAi);
                    }
                    $this->enregistreFicRecap($sFicCode, $oFicFlux, $sFicNom, $aRecapFicCourant, NULL, 80, $rnvpLocalException->getMessage());
                } catch (GeocodageException $GeocodageException) {
                    $this->suiviCommand->setMsg($GeocodageException->getMessage());
                    $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type($GeocodageException->getCode()));
                    $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                    $this->suiviCommand->setEtat("KO");
                    $this->oLog->erreur($GeocodageException->getMessage(), $GeocodageException->getCode(), $GeocodageException->getFile(), $GeocodageException->getLine());
                    $this->registerError();
                    if($input->getOption('id_ai') && $input->getOption('id_sh')){
                        $this->registerErrorCron($this->idAi);
                    }
                    $this->enregistreFicRecap($sFicCode, $oFicFlux, $sFicNom, $aRecapFicCourant, NULL, 81, $GeocodageException->getMessage());
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
                    $this->enregistreFicRecap($sFicCode, $oFicFlux, $sFicNom, $aRecapFicCourant, NULL, 70, $DBALException->getMessage());
                } catch (ReperageSQLException $ReperageSQLException) {
                    $this->suiviCommand->setMsg($ReperageSQLException->getMessage());
                    $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type($ReperageSQLException->getCode()));
                    $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                    $this->suiviCommand->setEtat("KO");
                    $this->oLog->erreur($ReperageSQLException->getMessage(), $ReperageSQLException->getCode(), $ReperageSQLException->getFile(), $ReperageSQLException->getLine());
                    $this->registerError();
                    if($input->getOption('id_ai') && $input->getOption('id_sh')){
                        $this->registerErrorCron($this->idAi);
                    }
                    $this->miseAJourFicRecap($ReperageSQLException->getFicRecapId(), 70, $ReperageSQLException->getMessage());
                } catch (ReperageIntegrationCommandException $CASIntegrationException) {
                    $this->suiviCommand->setMsg($CASIntegrationException->getMessage());
                    $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type($CASIntegrationException->getCode()));
                    $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                    $this->suiviCommand->setEtat("KO");
                    $this->oLog->erreur($CASIntegrationException->getMessage(), $CASIntegrationException->getCode(), $CASIntegrationException->getFile(), $CASIntegrationException->getLine());
                    $this->registerError();
                    if($input->getOption('id_ai') && $input->getOption('id_sh')){
                        $this->registerErrorCron($this->idAi);
                    }
                    $this->enregistreFicRecap($sFicCode, $oFicFlux, $sFicNom, $aRecapFicCourant, NULL, $CASIntegrationException->getCodeFicEtat(), $CASIntegrationException->getMessage());
                }
            }

            $this->oLog->info(' - Fin integration du fichier "'.$sFicNom.'"');    

        }
        $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
        $this->endTraitement();
        $this->oLog->info(date("d/m/Y H:i:s : ")."Fin Importation des fichiers de reperage - Commande : ".$this->sNomCommande);
        $this->oLog->info("Fin commande");
        return;
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
        $oReperageTmp  = $this->getContainer()->get('doctrine')->getRepository('AmsDistributionBundle:ReperageTmp');
        $aDatesNblignesNbex = $oReperageTmp->getDatesNblignesNbex();
        
        foreach($aDatesNblignesNbex as $aO)
        {
            // Date de distribution
           /* if(!isset($aRecapFicCourant['dateDistrib']) || !in_array($aO["dateDistrib"], $aRecapFicCourant['dateDistrib']))
            {
                $aRecapFicCourant['dateDistrib'][]  = $aO["dateDistrib"];
            }*/
            // Date de parution
            if(!isset($aRecapFicCourant['dateDemar']) || !in_array($aO["dateDemar"], $aRecapFicCourant['dateDemar']))
            {
                $aRecapFicCourant['dateDemar'][]  = $aO["dateDemar"];
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
       /* if(\count($aRecapFicCourant['dateDemar'])>1 )
        {
            throw ReperageIntegrationCommandException::plusieursDates($sFic, $aRecapFicCourant);
        }*/
        if(\count($aRecapFicCourant['nbLignes'])==0)
        {
            $this->suiviCommand->setMsg('Le fichier "'.$sFic.'" est vide.');
            $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
            $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
            $this->suiviCommand->setEtat("KO");
            $this->registerError();
            if($input->getOption('id_ai') && $input->getOption('id_sh')){
                $this->registerErrorCron($this->idAi);
            }
            throw ReperageIntegrationCommandException::fichierVide($sFic, $aRecapFicCourant);
        }
        
        // checksum        
        $aRecapFicCourant['checksum']   = $checksum;
        
        // code societe exterieur
        $aSocCodeExt    = $oReperageTmp->getCodeSocExt();
        foreach($aSocCodeExt as $aSoc)
        {
            $aRecapFicCourant['socCodeExt'][]   = $aSoc['socCodeExt'];
        }
        if(\count($aRecapFicCourant['socCodeExt'])>1)
        {
            $this->suiviCommand->setMsg('Le fichier "'.$sFic.'" est vide.');
            $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
            $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
            $this->suiviCommand->setEtat("KO");
            $this->registerError();
            if($input->getOption('id_ai') && $input->getOption('id_sh')){
                $this->registerErrorCron($this->idAi);
            }
            throw ReperageIntegrationCommandException::plusieursSocCode($sFic, $aRecapFicCourant);
        }
        if(trim($aRecapFicCourant['socCodeExt'][0])=='')
        {
            $this->suiviCommand->setMsg('Le "code societe" est vide dans le fichier "'.$ficNom.'".');
            $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
            $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
            $this->suiviCommand->setEtat("KO");
            $this->registerError();
            if($input->getOption('id_ai') && $input->getOption('id_sh')){
                $this->registerErrorCron($this->idAi);
            }
            throw ReperageIntegrationCommandException::socCodeNonDefini($sFic, $aRecapFicCourant);
        }
        $this->oLog->info("Fin de la recuperation de la recapitulatif du fichier en cours");
        return $aRecapFicCourant;     
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
    private function enregistreFicRecap($sFicCode, $oFicFlux, $sFicNom, $aRecapFicCourant=NULL, $socId = NULL, $codeEtat = NULL, $msgEtat = NULL) 
    {
        try {
            $repoFicRecap = $this->getContainer()->get('doctrine')->getRepository('AmsFichierBundle:FicRecap');
            $oFicRecap = new FicRecap();
            $oFicRecap->setCode($sFicCode);
            $oFicRecap->setFlux($oFicFlux);
            $oFicRecap->setNom($sFicNom);
            $oFicRecap->setOrigine(0); // voir le fichier de config "mroad.ini. => ORIGINE[0]== origine fichier
            if(!is_null($aRecapFicCourant))
            {
                if (isset($aRecapFicCourant['socCodeExt']) && isset($aRecapFicCourant['socCodeExt'][0])) {
                    $oFicRecap->setSocCodeExt($aRecapFicCourant['socCodeExt'][0]);
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
            $oFicRecap->setFicSource($this->getContainer()->get('doctrine')
                            ->getRepository('AmsFichierBundle:FicChrgtFichiersBdd')
                            ->findOneByCode($sFicCode)->getFicSource());


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
            rename($this->sRepTmp.'/'.$sFicNom, $this->sRepBkpLocal.'/'.$this->oString->renommeFicDeSvgrde($sFicNom, $this->sDateCourantYmd, $this->sHeureCourantYmd));
            
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
    

        private function miseAJourFicRecap($ficRecapId, $codeEtat = NULL, $msgEtat = NULL) {
        try {
            $repoFicRecap = $this->getContainer()->get('doctrine')->getRepository('AmsFichierBundle:FicRecap');
            $oFicEtatRepo = $this->getContainer()->get('doctrine')->getRepository('AmsFichierBundle:FicEtat');
            $oFicEtat = $oFicEtatRepo->findOneBy(array('code' => $codeEtat));
            if (!is_null($oFicEtat)) {
                $repoFicRecap->updateEtat($ficRecapId, array("fic_etat_id" => $oFicEtat->getId(), "eta_msg" => (is_null($msgEtat) ? '' : $msgEtat)));
            }
        } catch (DBALException $ex) {
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
