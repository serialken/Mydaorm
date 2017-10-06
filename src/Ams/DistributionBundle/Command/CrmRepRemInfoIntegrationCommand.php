<?php 
namespace Ams\DistributionBundle\Command;

//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Ijanki\Bundle\FtpBundle\Exception\FtpException;
use Doctrine\DBAL\DBALException;

use Ams\DistributionBundle\Exception\CrmRepRemInfoIntegrationCommandException;

use Ams\SilogBundle\Command\GlobalCommand;

use Ams\FichierBundle\Entity\FicRecap;

/**
 * 
 * "Command" integration des reponses aux remontees d'information 
 * 
 * Pour executer, faire : 
 *                  php app/console rep_reminfo_integration <<fic_code>> 
 *      Expl : php app/console rep_reminfo_integration JADE_REPREMINFO --env=dev
 * 
 * 
 * @author aandrianiaina
 *
 */
class CrmRepRemInfoIntegrationCommand extends GlobalCommand
{
    private $aFichierFluxParam;
    private $sRepTmp;
    private $sRepBkpLocal;
    protected function configure()
    {
    	$this->sNomCommande	= 'rep_reminfo_integration';
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console rep_reminfo_integration <<fic_code>> Expl : php app/console rep_reminfo_integration JADE_REPREMINFO
        $this
            ->setDescription('Integration des reponses aux remontees d information')
            ->addArgument('fic_code', InputArgument::REQUIRED, 'Code source de donnees')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
        $this->oLog->info(date("d/m/Y H:i:s : ")."Debut Integration des reponses aux remontees d information - Commande : ".$this->sNomCommande);
        $sFicCode 	= $input->getArgument('fic_code');	// Expl : JADE_REPREMINFO <=> Importation des reclamations venant de JADE
        
        $em    = $this->getContainer()->get('doctrine')->getManager();
            	
        // Recuperation des parameters concernant le FTP et les fichiers a recuperer
        $oFicChrgtFichiersBdd = $this->getContainer()->get('doctrine')
                        ->getRepository('AmsFichierBundle:FicChrgtFichiersBdd')
                        ->getParamFluxByCode($sFicCode);
        if(is_null($oFicChrgtFichiersBdd))
        {
            $this->oLog->erreur("Le flux ".$sFicCode." n'est pas parametre dans 'fic_chrgt_fichiers_bdd'", E_USER_ERROR);
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
        //echo "\r\n";
        $oFicFormatEnregistrement->getSQLLoadDataInFile($sFicCode, $oFicChrgtFichiersBdd->getFormatFic(), $oFicChrgtFichiersBdd->getTrimVal(), $oFicChrgtFichiersBdd->getNbCol(), 'latin1');
        //echo "\r\n";
        
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
                                '%%NOM_FICHIER%%'	=> $this->sRepTmp.'/'.$sFicNom,
                                '%%NOM_TABLE%%'		=> 'crm_rep_reminfo_tmp',
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
                        $this->oLog->erreur("Erreur SQL chargement de fichier : ".print_r($chargementFichier['sql'], true), E_USER_WARNING, __FILE__, __LINE__);
                        $this->oLog->erreur("Parametres avant Load Data Infile : ".print_r($aTransf, true), E_NOTICE, __FILE__, __LINE__);

                        $bContinueTraitement = false;
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
                } catch (CrmRepRemInfoIntegrationCommandException $ex2) {
                    $this->oLog->erreur($ex2->getMessage(), $ex2->getCode(), $ex2->getFile(), $ex2->getLine());
                    $this->enregistreFicRecap($sFicCode, $oFicFlux, $sFicNom, NULL, NULL, $ex2->getCodeFicEtat(), $ex2->getMessage());
                    $bContinueTraitement = false;
                }
            }            

            // --- Arrive ici, le fichier est considere comme conforme
            if($bContinueTraitement===true)
            {
                $repoCrmRepReminfoTmp   = $this->getContainer()->get('doctrine')
                                            ->getRepository('AmsDistributionBundle:CrmRepReminfoTmp');
                $repoCrmDetail   = $this->getContainer()->get('doctrine')
                                            ->getRepository('AmsDistributionBundle:CrmDetail');
                try {
                    
                    // Mise a jour du type de client (abonne ou LV)
                    $this->oLog->info('Debut Mise a jour du type de client (abonne ou LV)');
                    $repoCrmRepReminfoTmp->updateClientType($clientType);
                    
                    // Mise a jour du champ crm_demande_id
                    $this->oLog->info("Debut Mise a jour du champ crm_demande_id");
                    $repoCrmRepReminfoTmp->updateReponseId();
                    
                    // Mise a jour du champ abonne_soc_id
                    $this->oLog->info("Debut Mise a jour du champ societe_id");
                    $repoCrmRepReminfoTmp->updateSocieteId();
                    
                    // Mise a jour du champ abonne_soc_id
                    $this->oLog->info("Debut Mise a jour du champ abonne_soc_id");
                    $repoCrmRepReminfoTmp->updateAbonneSoc();
                    
                    // ----------------- Fin traitement dans les tables temporaires
                    
                    // ----- Creation de la ligne recapitulant le fichier traite
                    
                    $aSocId = $repoCrmRepReminfoTmp->getSocieteId();
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
                        throw CrmRepRemInfoIntegrationCommandException::plusieursSocieteId($sFicNom, implode(', ', $aSocIdTmp));
                    }
                    
                    // Verifie si la correspondance de la "code societe ext" existe
                    $bSocInconnue = $repoCrmRepReminfoTmp->isSocieteInconnue();
                    if($bSocInconnue===true)
                    {
                        throw CrmRepRemInfoIntegrationCommandException::societeInconnue($sFicNom);
                    }

                    $iDernierFicRecap   = $this->enregistreFicRecap($sFicCode, $oFicFlux, $sFicNom, $aRecapFicCourant, $socId, 0, 'OK');
                    
                    try{                    
                        $repoCrmRepReminfoTmp->updateFicRecap($iDernierFicRecap);
                        
                        // ---------- Remplissage de la table principale
                        $this->oLog->info("Debut Mise a jour de la table principale crm_detail a partir de la table temporaire");
                        $repoCrmDetail->updateRepRemInfo();

                        $this->oLog->info("Mise a jour Etat Recapitulatif");
                        $this->miseAJourFicRecap($iDernierFicRecap, 0, '');
                    } catch (DBALException $DBALException0) {
                        $this->oLog->erreur($DBALException0->getMessage(), $DBALException0->getCode(), $DBALException0->getFile(), $DBALException0->getLine());
                        $this->miseAJourFicRecap($iDernierFicRecap, 70, $DBALException0->getMessage());
                        //$this->enregistreFicRecap($sFicCode, $oFicFlux, $sFicNom, $aRecapFicCourant, NULL, 70, $DBALException0->getMessage());
                    }
                    
                } catch (DBALException $DBALException) {
                    $this->oLog->erreur($DBALException->getMessage(), $DBALException->getCode(), $DBALException->getFile(), $DBALException->getLine());
                    $this->enregistreFicRecap($sFicCode, $oFicFlux, $sFicNom, $aRecapFicCourant, NULL, 70, $DBALException->getMessage());
                } catch (RnvpLocalException $rnvpLocalException) {
                    $this->oLog->erreur($rnvpLocalException->getMessage(), $rnvpLocalException->getCode(), $rnvpLocalException->getFile(), $rnvpLocalException->getLine());
                    $this->enregistreFicRecap($sFicCode, $oFicFlux, $sFicNom, $aRecapFicCourant, NULL, 80, $rnvpLocalException->getMessage());
                } catch (CrmRepRemInfoIntegrationCommandException $CrmReclamIntegrationException) {
                    $this->oLog->erreur($CrmReclamIntegrationException->getMessage(), $CrmReclamIntegrationException->getCode(), $CrmReclamIntegrationException->getFile(), $CrmReclamIntegrationException->getLine());
                    $this->enregistreFicRecap($sFicCode, $oFicFlux, $sFicNom, $aRecapFicCourant, NULL, $CrmReclamIntegrationException->getCodeFicEtat(), $CASIntegrationException->getMessage());
                }
            }
            
            $this->oLog->info(' - Fin integration du fichier "'.$sFicNom.'"');
        }
        
    	$this->oLog->info(date("d/m/Y H:i:s : ")."Fin Integration des reponses aux remontees d information - Commande : ".$this->sNomCommande);
        
        $this->oLog->info("Fin commande");
    	
        return;
        
    }
    
    
    
    
    
    /**
     * 
     * Verifie si le fichier en cours de traitement est deja traite et bien insere
     * @param string $sFicCode
     * @param string $sFic
     * @param string $sSourceId
     * 
     * @return boolean/array
     */
    //private function isFichierTraite($sFicCode, $sFic, $sSourceId)
    //{
    //    $repoFicRecap   = $this->getContainer()->get('doctrine')->getRepository('AmsFichierBundle:FicRecap');
    //    $aDernierFicTraite  = $repoFicRecap->getRecapFicTraite($sFicCode, $sFic, $sSourceId);
    //    return (empty($aDernierFicTraite) ? false : $aDernierFicTraite);
    //}
    
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
        $oCrmDetailTmp  = $this->getContainer()->get('doctrine')->getRepository('AmsDistributionBundle:CrmDetailTmp');
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
        
        // code societe exterieur
        $aSocCodeExt    = $oCrmDetailTmp->getCodeSocExt();
        foreach($aSocCodeExt as $aSoc)
        {
            $aRecapFicCourant['socCodeExt'][]   = $aSoc['socCodeExt'];
        }
        if(\count($aRecapFicCourant['socCodeExt'])>1)
        {
            throw CrmRepRemInfoIntegrationCommandException::plusieursSocCode($sFic, $aRecapFicCourant);
        }
        if(trim($aRecapFicCourant['socCodeExt'][0])=='')
        {
            throw CrmRepRemInfoIntegrationCommandException::socCodeNonDefini($sFic, $aRecapFicCourant);
        }
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
    		return false;
    	}
    }
}
