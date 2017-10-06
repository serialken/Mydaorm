<?php 
namespace Ams\DistributionBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ijanki\Bundle\FtpBundle\Exception\FtpException;
use Doctrine\DBAL\DBALException;
use Ams\DistributionBundle\Exception\CrmReclamIntegrationCommandException;
use Ams\DistributionBundle\Exception\ClientsAServirSQLException;
use Ams\WebserviceBundle\Exception\RnvpLocalException;
use Ams\WebserviceBundle\Exception\GeocodageException;
use Ams\SilogBundle\Command\GlobalCommand;
use Ams\FichierBundle\Entity\FicRecap;

/**
 * 
 * "Command" integration des reclamations 
 * 
 * Pour executer, faire : 
 *                  php app/console rem_info_neo_press_integration <<fic_code>> 
 *      Expl : php app/console rem_info_neo_press_integration NEO_IMPORT_REM_INFO
 * 
 * 
 *
 */
class CrmRemInfoNeopressIntegrationCommand extends GlobalCommand
{
    private $aFichierFluxParam;
    private $sRepTmp;
    private $sRepBkpLocal;
    protected $idSh;
    protected $idAi;
    protected function configure()
    {
    	$this->sNomCommande	= 'reminfo_neopress_integration';
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console reminfo_neopress_integration <<fic_code>> Expl : php app/console reminfo_neopress_integration NEO_IMPORT_REM_INFO
        $this
            ->setDescription('Integration des remontees d\'info neopress')
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
        $this->oLog->info(date("d/m/Y H:i:s : ")."Debut Integration des remontees d information Neopress - Commande : ".$this->sNomCommande);
        $sFicCode 	= $input->getArgument('fic_code');	// Expl : NEO_IMPORT_REM_INFO <=> Importation des remontées dinfo venant de neopress
        
    	
        $em    = $this->getContainer()->get('doctrine')->getManager();
            	
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
        
        // Repertoire ou l'on recupere les fichiers a traiter
        $this->sRepTmp	= $this->sRepFichiersPrinc.'/'.$this->getContainer()->getParameter('SOUSREP_FICHIERS_TMP').'/'.$oFicChrgtFichiersBdd->getSsRepTraitement().'/'.$sFicCode;
       
        // Repertoire Backup Local
    	$this->sRepBkpLocal	= $this->sRepFichiersPrinc.'/'.$this->getContainer()->getParameter('SOUSREP_FICHIERS_BKP').'/'.$oFicChrgtFichiersBdd->getSsRepTraitement().'/'.$sFicCode;
       
        // Si 0, c'est abonne. Si 1, c'est Lieu de vente
        $clientType   = $oFicChrgtFichiersBdd->getFicSource()->getClientType();
        
        // Recuperation de l'identifiant du Flux de fichier
        $oFicFlux   = $oFicChrgtFichiersBdd->getFlux();
       // var_dump($oFicFlux );
        $oFicFormatEnregistrement   = $this->getContainer()->get('doctrine')
                                        ->getRepository('AmsFichierBundle:FicFormatEnregistrement');
        
        $oFicFormatEnregistrement->getSQLLoadDataInFile($sFicCode, $oFicChrgtFichiersBdd->getFormatFic(), $oFicChrgtFichiersBdd->getTrimVal(), $oFicChrgtFichiersBdd->getNbCol(), 'UTF8');
        
        
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



            //$this->oLog->info('Debut verification du contenu du fichier "'.$sFicNom.'"');
             if($bContinueTraitement===true)
            {
          
                $aTransf    = array(
                                '%%NOM_FICHIER%%'	=> $this->sRepTmp.'/'.$sFicNom,
                                '%%NOM_TABLE%%'		=> 'crm_detail_neopress_tmp',
                                '%%SEPARATEUR_CSV%%'	=> ($oFicChrgtFichiersBdd->getFormatFic()=='CSV' ? $oFicChrgtFichiersBdd->getSeparateur() : ''),
                                '%%NB_LIGNES_IGNOREES%%'    => $oFicChrgtFichiersBdd->getNbLignesIgnorees(),
                        );
                $this->oLog->info("Chargement du fichier '".$sFicNom."' dans la table temporaire ".$aTransf['%%NOM_TABLE%%']);

                // --- Arrive ici, le fichier est considere comme conforme                



                try {
                    $this->oLog->info('Debut chargement du fichier dans la table temporaire "'.$aTransf['%%NOM_TABLE%%'].'"');
                    $chargementFichier  = $oFicFormatEnregistrement->chargeDansTableTmp($aTransf);
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
                    
                } catch (CrmReclamIntegrationCommandException $ex2) {
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
                $repoCrmDetailNepressTmp   = $this->getContainer()->get('doctrine')
                                            ->getRepository('AmsDistributionBundle:CrmDetailNeopressTmp');
                $repoCrmDetail   = $this->getContainer()->get('doctrine')
                                            ->getRepository('AmsDistributionBundle:CrmDetail');
                $crmDetailNeoPressTmpContent = $repoCrmDetailNepressTmp->findAll();
                try {
                    // Mise a jour du type de client (abonne ou LV)
                    $this->oLog->info('Debut Mise a jour du type de client (abonne ou LV)');
                    $repoCrmDetailNepressTmp->updateClientType($clientType);
                    
                    // Suppression des "\r" et "\n" pour tous les champs
                     //$this->oLog->info('Debut Suppression des "\r" et "\n" pour tous les champs');
                    // $repoCrmDetailNepressTmp->supprCaracteresSpeciaux();

                    // Mise en majuscule de tous les champs d'adresse
                    $this->oLog->info("Debut Mise en majuscule de tous les champs d'adresse");
                    $repoCrmDetailNepressTmp->miseEnMajusculeAdresses();
                    // Mise a jour du champ crm_demande_id
                    $this->oLog->info("Debut Mise a jour du champ crm_demande_id");
                    $repoCrmDetailNepressTmp->updateDemandeId();

                    $this->oLog->info("Debut Mise a jour du champ code_demande");
                    $repoCrmDetailNepressTmp->updateCodeDemande();

                    // Mise a jour des champs adresse, rnvp et commune pour les adresses connues
                    $this->oLog->info("Debut Mise a jour des champs adresse,rnvp, abonne_soc_id, vol1,vol2...vol5 et commune pour les adresses connues");
                    $repoCrmDetailNepressTmp->updateAdresse(true);
                    // Mise a jour des champs adresse, rnvp et commune pour les adresses inconnues
                    $this->oLog->info("Debut Mise a jour des champs adresse,rnvp, abonne_soc_id,  vol1,vol2...vol5 et commune pour les adresses connues");
                    $repoCrmDetailNepressTmp->updateAdresse(false);
                  

                    // Mise a jour du champ abonne_soc_id
                    $this->oLog->info("Debut Mise a jour des champs societe_id et soc_code_ext");
                    $repoCrmDetailNepressTmp->updateSocieteIdAndSocCodeExt();
                    
                    // Mise a jour de l'attribut de depot                    
                    $this->oLog->info("Debut Mise a jour de l'attribut de depot");
                    $repoCrmDetailNepressTmp->updateDepot();
                    
                    // ----------------- Fin traitement dans les tables temporaires
                   // ----- Creation de la ligne recapitulant le fichier traite
                    
                    // Verifie si la correspondance de la "code societe ext" existe
                    $bSocInconnue = $repoCrmDetailNepressTmp->isSocieteInconnue();
                   
                    $iDernierFicRecap   = $this->enregistreFicRecap($sFicCode, $oFicFlux, $sFicNom, $aRecapFicCourant, null, 0, 'OK');
                    
                    $repoCrmDetailNepressTmp->updateFicRecap($iDernierFicRecap);
                    
                
                    // ---------- Remplissage de la table principale
                    $this->oLog->info("Debut Transfert des donnees de la table temporaire vers la table principale crm_detail");
                    $repoCrmDetail->tmpVersCrmDetail(null, 'crm_detail_neopress_tmp');
                    
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
                } catch (RnvpLocalException $rnvpLocalException) {
                    $this->suiviCommand->setMsg($rnvpLocalException->getMessage());
                    $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type($rnvpLocalException->getCode()));
                    $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                    $this->suiviCommand->setEtat("KO");
                    $this->oLog->erreur($rnvpLocalException->getMessage(), $rnvpLocalException->getCode(), $rnvpLocalException->getFile(), $rnvpLocalException->getLine());
                    if($input->getOption('id_ai') && $input->getOption('id_sh')){
                       $this->registerErrorCron($this->idAi);
                   }
                } catch (CrmReclamIntegrationCommandException $CrmReclamIntegrationException) {
                    $this->suiviCommand->setMsg($CrmReclamIntegrationException->getMessage());
                    $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type($CrmReclamIntegrationException->getCode()));
                    $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                    $this->suiviCommand->setEtat("KO");
                    $this->oLog->erreur($CrmReclamIntegrationException->getMessage(), $CrmReclamIntegrationException->getCode(), $CrmReclamIntegrationException->getFile(), $CrmReclamIntegrationException->getLine());
                    if($input->getOption('id_ai') && $input->getOption('id_sh')){
                       $this->registerErrorCron($this->idAi);
                   }
                }
            }
            
            $this->oLog->info(' - Fin integration du fichier "'.$sFicNom.'"');
        }
        
        $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
        $this->endTraitement();
    	$this->oLog->info(date("d/m/Y H:i:s : ")."Fin Integration des remontees d information Neopress - Commande : ".$this->sNomCommande);
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
        $oCrmDetailTmp  = $this->getContainer()->get('doctrine')->getRepository('AmsDistributionBundle:CrmDetailNeopressTmp');
        $aNblignes = $oCrmDetailTmp->getNblignes();
      
        foreach($aNblignes as $aO)
        {
            // Nombre de lignes
            if(!isset($aRecapFicCourant['nbLignes']))
            {
                $aRecapFicCourant['nbLignes']   = $aO["nbLignes"];
            }
            else
            {
                $aRecapFicCourant['nbLignes']   += $aO["nbLignes"];
            }
        }
        
        // checksum        
        $aRecapFicCourant['checksum']   = $checksum;
        $this->oLog->info("Fin de la recuperation de la recapitulatif du fichier en cours");
        //print_r($aRecapFicCourant);
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
            $oFicRecap->setNom($sFicNom);//var_dump($sFicNom);die();
            $aFicNom = explode('_', $sFicNom);

            $date   = $aFicNom[2];
            $hour   = $aFicNom[3];
            $sY     = substr( $date, 0,4);
            $sMonth = substr( $date, 4,2);
            $sDay   = substr( $date, 6,2);
            $sHour  = substr( $hour, 0,2);
            $sMinute  = substr( $hour, 2,2);
            $dateParution = new \DateTime($sY.'-'.$sMonth.'-'.$sDay.' '.$sHour.':'.$sMinute);
           // var_dump( $dateParution);die();
            $oFicRecap->setOrigine(0); // voir le fichier de config "mroad.ini. => ORIGINE[0]== origine fichier
            if(!is_null($aRecapFicCourant))
            {

                $oFicRecap->setDateParution($dateParution);
                $oFicRecap->setDateDistrib($dateParution);
                $oFicRecap->setChecksum($aRecapFicCourant['checksum']);
                $oFicRecap->setNbLignes($aRecapFicCourant['nbLignes']);
                //$oFicRecap->setNbExemplaires($aRecapFicCourant['nbEx']);
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
