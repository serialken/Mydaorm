<?php 
namespace Ams\DistributionBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ijanki\Bundle\FtpBundle\Exception\FtpException;
use Doctrine\DBAL\DBALException;
use Ams\DistributionBundle\Exception\ClientsAServirIntegrationCommandException;
use Ams\DistributionBundle\Exception\ClientsAServirSQLException;
use Ams\WebserviceBundle\Exception\RnvpLocalException;
use Ams\WebserviceBundle\Exception\GeocodageException;
use Ams\SilogBundle\Command\GlobalCommand;
use Ams\FichierBundle\Entity\FicRecap;

/**
 * 
 * "Command" integration des clients a servir 
 * On doit traiter les fichiers par date en ordre croissant.
 * 
 * Si des fichiers de meme societe et qui ont une date de distribution ulterieure que le fichier courant sont deja traites, on les supprime de la base de donnees... Puis on les traite de nouveau.
 * Afin de bien gerer les changements d'adresse et d'info portage
 * 
 * Pour executer, faire : 
 *                  php app/console clients_a_servir <<fic_code>> 
 *      Expl :  php app/console clients_a_servir_integration JADE_CAS --id_sh=cron_test --id_ai=1  --env=dev
 *              php app/console clients_a_servir_integration DCS_LV --id_sh=cron_test --id_ai=1  --env=dev
 * 
 * 
 * @author aandrianiaina
 *
 */
class ClientsAServirIntegrationCommand extends GlobalCommand
{
    private $aFichierFluxParam;
    private $sRepTmp;
    private $sRepBkpLocal;
    protected $idAi;
    protected $idSh;
    
    protected function configure()
    {
    	$this->sNomCommande	= 'clients_a_servir_integration';
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console clients_a_servir <<fic_code>> Expl : php app/console clients_a_servir_integration JADE_CAS
        $this
            ->setDescription('Integration des clients a servir')
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
        $this->oLog->info(date("d/m/Y H:i:s : ")."Debut Integration des clients a servir - Commande : ".$this->sNomCommande);
        
    	
        $em    = $this->getContainer()->get('doctrine')->getManager();
        
    	$sFicCode 	= $input->getArgument('fic_code');	// Expl : JADE_CAS <=> Importation des clients a servir venant de JADE
        
        
        // Recuperation des parameters concernant le FTP et les fichiers a recuperer
        $oFicChrgtFichiersBdd = $this->getContainer()->get('doctrine')
                        ->getRepository('AmsFichierBundle:FicChrgtFichiersBdd')
                        ->findOneByCode($sFicCode);
        
        $repoFicRecap   = $this->getContainer()->get('doctrine')->getRepository('AmsFichierBundle:FicRecap');
                
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
        
        // Repertoire ou l'on recupere les fichiers a traiter
        $this->sRepTmp	= $this->sRepFichiersPrinc.'/'.$this->getContainer()->getParameter('SOUSREP_FICHIERS_TMP').'/'.$oFicChrgtFichiersBdd->getSsRepTraitement().'/'.$sFicCode;
        
        // Repertoire Backup Local
    	$this->sRepBkpLocal	= $this->sRepFichiersPrinc.'/'.$this->getContainer()->getParameter('SOUSREP_FICHIERS_BKP').'/'.$oFicChrgtFichiersBdd->getSsRepTraitement().'/'.$sFicCode;
    	
        
        // Si 0, c'est abonne. Si 1, c'est Lieu de vente
        $clientType   = $oFicChrgtFichiersBdd->getFicSource()->getClientType();
        
        // Recuperation de l'identifiant du Flux de fichier
        $oFicFlux   = $oFicChrgtFichiersBdd->getFlux();
        
        $oFicFormatEnregistrement   = $this->getContainer()->get('doctrine')
                                        ->getRepository('AmsFichierBundle:FicFormatEnregistrement');
        $oFicFormatEnregistrement->getSQLLoadDataInFile($sFicCode, $oFicChrgtFichiersBdd->getFormatFic(), $oFicChrgtFichiersBdd->getTrimVal(), $oFicChrgtFichiersBdd->getNbCol(), 'latin1');
    	$srvAdresseRnvp    = $this->getContainer()->get('adresse_rnvp');        
        
        $repoClientAServirSrc   = $this->getContainer()->get('doctrine')
                                        ->getRepository('AmsDistributionBundle:ClientAServirSrc');
        
        $repoAdresse = $this->getContainer()->get('doctrine')->getRepository('AmsAdresseBundle:Adresse');
        $repoAbonneSoc = $this->getContainer()->get('doctrine')->getRepository('AmsAbonneBundle:AbonneSoc');
        
        
        
        $repoAdresseTmp = $this->getContainer()->get('doctrine')->getRepository('AmsAdresseBundle:AdresseTmp');
        
        $repoClientAServirLogist   = $this->getContainer()->get('doctrine')
                                        ->getRepository('AmsDistributionBundle:ClientAServirLogist');
    	
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
                                '%%NOM_FICHIER%%'		=> $this->sRepTmp.'/'.$sFicNom,
                                '%%NOM_TABLE%%'		=> 'client_a_servir_src_tmp1',
                                '%%SEPARATEUR_CSV%%'	=> ($oFicChrgtFichiersBdd->getFormatFic()=='CSV' ? $oFicChrgtFichiersBdd->getSeparateur() : ''),
                                '%%NB_LIGNES_IGNOREES%%'    => $oFicChrgtFichiersBdd->getNbLignesIgnorees(),
                        );
                $this->oLog->info("Chargement du fichier '".$sFicNom."' dans la table temporaire ".$aTransf['%%NOM_TABLE%%']);



                // --- Arrive ici, le fichier est considere comme conforme                



                try {
                    $this->oLog->info('Debut chargement du fichier dans la table temporaire "client_a_servir_src_tmp1"');
                    $chargementFichier  = $oFicFormatEnregistrement->chargeDansTableTmp($aTransf);
                    if($chargementFichier!==true)
                    {
                        $this->suiviCommand->setMsg("Erreur SQL chargement de fichier : ".__FILE__);
                        $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_WARNING));
                        $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                        $this->suiviCommand->setEtat("KO");
                        $this->oLog->erreur("Erreur SQL chargement de fichier : ".print_r($chargementFichier['sql'], true), E_USER_WARNING, __FILE__, __LINE__);
                        $this->oLog->erreur("Parametres avant Load Data Infile : ".print_r($aTransf, true), E_NOTICE, __FILE__, __LINE__);
                         $this->registerError();
                        if($input->getOption('id_ai') && $input->getOption('id_sh')){
                            $this->registerErrorCron($this->idAi);
                        }
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
                } catch (ClientsAServirIntegrationCommandException $ex2) {
                    	
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
                $repoClientAServirSrcTmp1   = $this->getContainer()->get('doctrine')
                                            ->getRepository('AmsDistributionBundle:ClientAServirSrcTmp1');
                try {
                    // Verification si un ou des fichiers de meme societe que le fichier courant sont deja inseres pour des dates futures
                    $iDatesUlterieures  = false;
                    $aDatesUlterieures = $repoClientAServirLogist->datesUlterieures($aRecapFicCourant['socCodeExt'][0], $aRecapFicCourant['dateDistrib'][0]);
                    if(!empty($aDatesUlterieures))
                    {
                        $iDatesUlterieures  = true;
                    }
                    
                    // Mise a jour du type de client (abonne ou LV)
                    $this->oLog->info('Debut Mise a jour du type de client (abonne ou LV)');
                    $repoClientAServirSrcTmp1->updateClientType($clientType);
                    
                    // Suppression des "\r" et "\n" pour tous les champs
                    $this->oLog->info('Debut Suppression des "\r" et "\n" pour tous les champs');
                    $repoClientAServirSrcTmp1->supprCaracteresSpeciaux();

                    // Mise en majuscule de tous les champs d'adresse
                    $this->oLog->info("Debut Mise en majuscule de tous les champs d'adresse");
                    $repoClientAServirSrcTmp1->miseEnMajusculeAdresses();

                    // -------- TRUNCATE TABLE CLIENT_A_SERVIR_SRC_TMP2
                    $repoClientAServirSrcTmp2   = $this->getContainer()->get('doctrine')
                                                ->getRepository('AmsDistributionBundle:ClientAServirSrcTmp2');
                    $this->oLog->info("Debut TRUNCATE TABLE client_a_servir_src_tmp2");
                    $repoClientAServirSrcTmp2->truncate();     

                    // -------- Insertion des infos adresse & portage dans la deuxieme table temporaire (client_a_servir_src_tmp2)
                    $this->oLog->info("Debut Insertion des infos adresse & portage dans la deuxieme table temporaire (client_a_servir_src_tmp2)");
                    $repoClientAServirSrcTmp1->infosAdressePortageTmp();

                    // -------- Infos Produit : societe, produit & sousProduit
                    // Update Infos Produit
                    $this->oLog->info("Debut Update Infos Produit");
                    $repoClientAServirSrcTmp1->updateInfosProduit();

                    // Update Infos Produit si le produit n'est pas connu. On recherche le produit par defaut en fonction de societeExt
                    $this->oLog->info("Debut Update Infos Produit si le produit n'est pas connu. On recherche le produit par defaut en fonction de societeExt");
                    $repoClientAServirSrcTmp1->updateInfosProduitBySocieteExt();
                     

                    // -------- AbonneSoc
                    // Update AbonneSoc
                    $this->oLog->info("Debut Update AbonneSoc");
                    $repoClientAServirSrcTmp2->updateAbonneSoc($clientType);


                    // Insertion de tous les abonnes inconnus
                    $this->oLog->info("Debut Stockage des nouveaux AbonneSoc");
                    $repoClientAServirSrcTmp2->insertNouveauAbonneSoc($clientType);


                    // Update AbonneSoc ou cet attribut est null
                    $this->oLog->info("Debut Update AbonneSoc nouvellement cree");
                    $repoClientAServirSrcTmp2->updateAbonneSoc($clientType);

                     // -------- Adresse & Rnvp & Commune
                    // Update Adresse
                    $this->oLog->info("Debut Mise a jour des adresses (adresse & rnvp & commune) connues");
                    $repoClientAServirSrcTmp2->updateAdresse();
                    
                    // si un ou des fichiers de meme societe que le fichier courant sont deja inseres pour des dates futures
                    if($iDatesUlterieures===true)
                    {
                        $this->oLog->info("Un ou des fichiers de meme societe que le fichier courant sont deja inseres pour des dates futures");
                        // Insertion de changement d'adresse pour celles ou des changements dans le futur ont ete deja enregistres
                        $repoClientAServirSrcTmp2->insertChangementAdresse();
                        $repoClientAServirSrcTmp2->updateAdresse();
                        
                        // Insertion des nouvelles Infos portage 
                        $this->oLog->info("Stockage des infos portage - Cas ou des fichiers de date future ont ete deja traites");
                        $repoClientAServirSrcTmp2->infoPortageAvecDateFutureInseree($aRecapFicCourant['dateDistrib'][0], $this->getContainer()->getParameter('DATE_FIN'));
                    }
                   
                    else {
                        // Fermeture des anciennes donnees d'adresse
                        $this->oLog->info("Debut Fermeture des infos adresses d'un abonne qui change d'infos adresse (vol1-5, cp, ville)");
                        $repoClientAServirSrcTmp2->fermetureAncienneAdresse($aRecapFicCourant['dateDistrib'][0]);
                        
                        // Insertion des nouvelles Infos portage 
                        $this->oLog->info("Stockage des infos portage - Cas ou aucun fichier de date future n'est pas encore traite");
                        $repoClientAServirSrcTmp2->infoPortage0DateFuture($aRecapFicCourant['dateDistrib'][0], $this->getContainer()->getParameter('DATE_FIN'));
                        
                    }
                    
                    // Insertion des nouvelles Adresses
                    $this->oLog->info("Debut Insertion des nouvelles Adresses");
                    $repoClientAServirSrcTmp2->insertNouvelleAdresse();
                    
                    // Vide la table "adresse_tmp"
                    $this->oLog->info('Debut Vide la table "adresse_tmp"');
                    $repoAdresseTmp->truncate();
                    
                    // Mise en table temporaire des adresses deja normalisees - cette etape permet de recuperer facilement le dernier rnvp d'une adresse deja connue
                    //$this->oLog->info('Debut Mise en table temporaire ("adresse_tmp") des adresses deja normalisees');
                    //$repoAdresseTmp->init();

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
                    $repoClientAServirSrcTmp2->updateAdresse();
                    
                    // Suppression des lignes de tournee_detail suite a un changement d adresse
                    //$this->oLog->info("Debut Suppression des lignes de tournee_detail suite a un changement d adresse");
                    //$repoClientAServirSrcTmp2->supprTourneeDetailChgtAdr();
                    

                    // Mise a jour de commune & depot de la table temporaire
                    $this->oLog->info("Debut Mise a jour de commune de la table temporaire");
                    $repoClientAServirSrcTmp2->updateCommune();
                                        
                    // Attribution d'un numero d'abonne unique pour les nouveaux AbonneSoc 
                    $this->oLog->info("Debut Attribution d'un numero d'abonne unique pour les nouveaux AbonneSoc");
                    $repoAbonneSoc->updateAbonneUnique();                    
                    
                    // Attribution d'un numero d'abonne unique
                    $this->oLog->info("Debut Attribution d'un numero d'abonne unique");
                    $repoClientAServirSrcTmp2->updateAbonneUnique();
                    
                    // 
                    //$this->oLog->info("Debut Mise a jour des champs tournees");
                    //$repoClientAServirSrcTmp2->updateTournee();                    

                    // -------- Commune

                    // Mise a jour des attributs AbonneSoc, Adresse, RNVP, Pt de livraison & Commune, depot et Flux (NUIT ou JOUR) de client_a_servir_src_tmp1
                    $this->oLog->info("Mise a jour des attributs AbonneSoc, Adresse, RNVP, Pt de livraison & Commune, depot et Flux (NUIT ou JOUR) de client_a_servir_src_tmp1");
                    $repoClientAServirSrcTmp1->updateAbonneAdresseRnvp();
                    
                    
                    // -------- abonne_soc

                    // Mise a jour de la colonne societe_id de la table abonne_soc
                    $this->oLog->info("Mise a jour de la colonne societe_id de la table abonne_soc");
                    $repoClientAServirSrcTmp1->updateSocAbonneSoc();

                    // ----------------- Fin traitement dans les tables temporaires                            

                    if($bFicARetraiter===true)
                    {
                        // Fichier a retraiter => Suppression des anciennes donnees
                        $this->oLog->info("Fichier a retraiter => Suppression des anciennes donnees");
                    }
                    // Suppression des donnees de meme date et de meme code soc. ext des tables client_a_servir_logist & client_a_servir_src
                    $this->oLog->info("Suppression des donnees des tables client_a_servir_logist & client_a_servir_src ou date_distrib=".$aRecapFicCourant['dateDistrib'][0]->format('d/m/Y')." et socCodeExt=".$aRecapFicCourant['socCodeExt'][0]);
                    $repoClientAServirLogist->suppressionAvecDateSoc($aRecapFicCourant['dateDistrib'][0], $aRecapFicCourant['socCodeExt'][0]);
                    $repoClientAServirSrc->suppressionAvecDateSoc($aRecapFicCourant['dateDistrib'][0], $aRecapFicCourant['socCodeExt'][0]);

                    // ----- Creation de la ligne recapitulant le fichier traite

                    $aSocId = $repoClientAServirSrcTmp1->getSocieteId();
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
                        throw ClientsAServirIntegrationCommandException::plusieursSocieteId($sFicNom, implode(', ', $aSocIdTmp));
                    }

                    // Verifie si la correspondance de la "code societe ext" existe
                    $bSocInconnue = $repoClientAServirSrcTmp1->isSocieteInconnue();
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
                        throw ClientsAServirIntegrationCommandException::societeInconnue($sFicNom);
                    }

                    $iDernierFicRecap   = $this->enregistreFicRecap($sFicCode, $oFicFlux, $sFicNom, $aRecapFicCourant, $socId, 0, 'OK');
                    
                    $repoClientAServirSrcTmp1->updateFicRecap($iDernierFicRecap);

                    // ---------- Remplissage des tables 
                    $this->oLog->info("Debut Transfert des donnees des tables temporaires vers les tables client_a_servir_logist & client_a_servir_src");

                    $this->oLog->info("Debut Transfert des donnees des tables temporaires vers la table client_a_servir_src");
                    $repoClientAServirSrc->tmpVersClientAServirSrc($iDernierFicRecap);
                    //$repoClientAServirSrc->miseAJourLogistId($aRecapFicCourant['dateDistrib'][0], $aRecapFicCourant['socCodeExt'][0]);

                    $this->oLog->info("Debut Transfert des donnees des tables temporaires vers la table client_a_servir_logist");
                    $repoClientAServirLogist->tmpVersClientAServirLogist($iDernierFicRecap);



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
                } catch (ClientsAServirSQLException $ClientsAServirSQLException) {
                    $this->suiviCommand->setMsg($ClientsAServirSQLException->getMessage());
                    $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type($ClientsAServirSQLException->getCode()));
                    $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                    $this->suiviCommand->setEtat("KO");
                    $this->oLog->erreur($ClientsAServirSQLException->getMessage(), $ClientsAServirSQLException->getCode(), $ClientsAServirSQLException->getFile(), $ClientsAServirSQLException->getLine());
                    $this->registerError();
                    if($input->getOption('id_ai') && $input->getOption('id_sh')){
                        $this->registerErrorCron($this->idAi);
                    }
                    $this->miseAJourFicRecap($ClientsAServirSQLException->getFicRecapId(), 70, $ClientsAServirSQLException->getMessage());
                } catch (ClientsAServirIntegrationCommandException $CASIntegrationException) {
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
    	$this->oLog->info(date("d/m/Y H:i:s : ")."Fin Integration des clients a servir - Commande : ".$this->sNomCommande);
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
        $oClientAServirSrcTmp1  = $this->getContainer()->get('doctrine')->getRepository('AmsDistributionBundle:ClientAServirSrcTmp1');
        $aDatesNblignesNbex = $oClientAServirSrcTmp1->getDatesNblignesNbex();
        
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
            $this->suiviCommand->setMsg('Plusieurs dates sont trouvees dans le fichier "'.$sFic.'".');
            $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
            $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
            $this->suiviCommand->setEtat("KO");
            $this->registerError();
            if($input->getOption('id_ai') && $input->getOption('id_sh')){
                $this->registerErrorCron($this->idAi);
            }
            throw ClientsAServirIntegrationCommandException::plusieursDates($sFic, $aRecapFicCourant);
        }
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
            throw ClientsAServirIntegrationCommandException::fichierVide($sFic, $aRecapFicCourant);
        }
        
        // checksum        
        $aRecapFicCourant['checksum']   = $checksum;
        
        // code societe exterieur
        $aSocCodeExt    = $oClientAServirSrcTmp1->getCodeSocExt();
        foreach($aSocCodeExt as $aSoc)
        {
            $aRecapFicCourant['socCodeExt'][]   = $aSoc['socCodeExt'];
        }
        if(\count($aRecapFicCourant['socCodeExt'])>1)
        {
            $this->suiviCommand->setMsg('Plusieurs "code societe" sont trouves dans le fichier "'.$sFic.'" : '.'"'.\implode('", "', $recapFicCourant['socCodeExt']).'"');
            $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
            $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
            $this->suiviCommand->setEtat("KO");
            $this->registerError();
            if($input->getOption('id_ai') && $input->getOption('id_sh')){
                $this->registerErrorCron($this->idAi);
            }
            throw ClientsAServirIntegrationCommandException::plusieursSocCode($sFic, $aRecapFicCourant);
        }
        if(trim($aRecapFicCourant['socCodeExt'][0])=='')
        {
            $this->suiviCommand->setMsg('Le "code societe" est vide dans le fichier "'.$sFic.'".');
            $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
            $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
            $this->suiviCommand->setEtat("KO");
            $this->registerError();
            if($input->getOption('id_ai') && $input->getOption('id_sh')){
                $this->registerErrorCron($this->idAi);
            }
            throw ClientsAServirIntegrationCommandException::socCodeNonDefini($sFic, $aRecapFicCourant);
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

    /**
     * 
     * Du cote FTP, deplace le fichier $sFic dans le repertoire de sauvegarde
     * @param string $sFic
     */
    private function sauvegardeFichierFTP($sFic)
    {
    	try {
	    	$srv_ftp->connect($this->aFichierFluxParam['FTP']->getSrv());
			$srv_ftp->login($this->aFichierFluxParam['FTP']->getLogin(), $this->aFichierFluxParam['FTP']->getMdp());
			
			if($this->aFichierFluxParam['FTP']->getRepertoire()!='')
			{
				$srv_ftp->chdir($this->aFichierFluxParam['FTP']->getRepertoire());
			}
			$sSsRepBkp	= (($this->aFichierFluxParam['FTP']->getRep_sauvegarde()!='') ? $this->aFichierFluxParam['FTP']->getRep_sauvegarde() : 'Bkp');
			
			if(!$srv_ftp->isDir($sSsRepBkp))
			{
				$srv_ftp->mkDirRecursive($sSsRepBkp);
			}
			$sFicBkp	= $sFic;
			// Si le fichier existe deja sur dans le sous repertoire de sauvegarde du cote FTP, on renomme le fichier en cours de traitement
			if($srv_ftp->size($sSsRepBkp.'/'.$sFic)!=-1) 
			{
				$sFicBkp	= $this->oString->renommeFicDeSvgrde($sFic, $this->sDateCourantYmd, $this->sHeureCourantYmd);
			}
			$srv_ftp->rename($sFic, $sSsRepBkp.'/'.$sFicBkp);
	    	$srv_ftp->close();
	    	return true;
    	}
    	catch (FtpException $e) {
             $this->suiviCommand->setMsg($e->getMessage());
            $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type($e->getCode()));
            $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
            $this->suiviCommand->setEtat("KO");
            $this->registerError();
            if($input->getOption('id_ai') && $input->getOption('id_sh')){
                $this->registerErrorCron($this->idAi);
            }
            return false;
    	}
    }
}
