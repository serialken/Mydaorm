<?php 
namespace Ams\DistributionBundle\Command;

//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
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
CREATE TABLE fic_transfo (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(150) NOT NULL, fic_flux_code VARCHAR(45) NOT NULL, regex_fic VARCHAR(255) NOT NULL, nom_fic_genere VARCHAR(255) NOT NULL, commentaire VARCHAR(255) DEFAULT NULL, UNIQUE INDEX unique_code (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE fic_transfo_etape (id INT AUTO_INCREMENT NOT NULL, fic_transfo_id INT NOT NULL, methode_nom VARCHAR(255) NOT NULL, methode_param VARCHAR(255) DEFAULT NULL, ordre INT DEFAULT 1 NOT NULL, actif SMALLINT DEFAULT '1' NOT NULL, commentaire VARCHAR(255) DEFAULT NULL, INDEX IDX_8661E4931FF6A52D (fic_transfo_id), UNIQUE INDEX code_regex_methode (fic_transfo_id, ordre), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE fic_transfo_etape ADD CONSTRAINT FK_8661E4931FF6A52D FOREIGN KEY (fic_transfo_id) REFERENCES fic_transfo (id);

Table "fic_transfo" =>
 * JADE_CAS_T7
INSERT INTO fic_transfo (id, code, fic_flux_code, regex_fic, nom_fic_genere, commentaire) 
VALUES (NULL, 'TRANSFO_JADE_CAS_T7', 'JADE_CAS', '/^T7.+/', '`soc_code_ext``date_distrib_Ymd`.txt', 'Transformation de la date de distribution de Tele 7 Jours');

Table "fic_transfo" =>
 * JADE_CAS_T7
INSERT INTO fic_transfo_etape (id, fic_transfo_id, methode_nom, methode_param, ordre, actif, commentaire) 
VALUES (NULL, '1', 'updateDateDistrib($decalDate)', '$decalDate="-2";', '1', '1', 'Decaler le jour de distrib a "date_distrib - 2"');

INSERT INTO fic_transfo_etape (id, fic_transfo_id, methode_nom, methode_param, ordre, actif, commentaire) 
VALUES (NULL, '1', 'deleteDate($nbJourRef,$comp)', '$nbJourRef="+1";$comp="<";', '2', '1', 'Supprimer les lignes dont date_distrib<"J+1"');







 */

/**
 * 
 * "Command" transformation des fichiers de clients a servir 
 *      - transformation des dates
 *      - transformation de "code_societe" & "code_titre"
 * 
 * Cette "command" est a appeler, generalement, apres la "command" : php app/console import_fic_ftp .... --env=...
 * 
 * Pour executer, faire : 
 *                  php app/console clients_a_servir_transformation <<fic_transfo_code>> 
 *      Expl :  php app/console clients_a_servir_transformation TRANSFO_JADE_CAS_T7 --regex=/^T7.+/ --env=prod
 * 
 * 
 * @author aandrianiaina
 *
 */
class ClientsAServirTransformationCommand extends GlobalCommand
{
    private $aFichierFluxParam;
    private $sRepTmp;
    private $sRepBkpLocal;
    protected function configure()
    {
    	$this->sNomCommande	= 'clients_a_servir_transformation';
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console clients_a_servir <<fic_transfo_code>> Expl : php app/console clients_a_servir_transformation TRANSFO_JADE_CAS_T7
        $this
            ->setDescription('Transformation des fichiers de clients a servir')
            ->addArgument('fic_transfo_code', InputArgument::REQUIRED, 'Code de la transformation')
            ->addOption('regex',null, InputOption::VALUE_REQUIRED, 'Si defini, on ne prend pas en compte le parametrage dans la table fic_transfo');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
    	if($input->getOption('id_sh')){
            $idSh = $input->getOption('id_sh');
        }
        if($input->getOption('id_ai')){
            $idAi = $input->getOption('id_ai');
        }
        if($input->getOption('id_ai') && $input->getOption('id_sh')){
            $this->associateToCron($idAi,$idSh);
        }
        $this->oLog->info(date("d/m/Y H:i:s : ")."Debut Transformation des fichiers de clients a servir - Commande : ".$this->sNomCommande);
        
    	
        $em    = $this->getContainer()->get('doctrine')->getManager();
        
    	$sFicTransfoCode 	= $input->getArgument('fic_transfo_code');	// Expl : JADE_CAS <=> Importation des clients a servir venant de JADE
        
        $repoFicTransfo = $this->getContainer()->get('doctrine')
                                ->getRepository('AmsFichierBundle:FicTransfo');
        
        $repoFicTransfoEtape = $this->getContainer()->get('doctrine')
                                    ->getRepository('AmsFichierBundle:FicTransfoEtape');
        
        $oFicTransfo    = $repoFicTransfo->findOneByCode($sFicTransfoCode);
        
        if(is_null($oFicTransfo))
        {
            $this->suiviCommand->setMsg("Le code ".$sFicTransfoCode." n'est pas parametre dans 'fic_transfo'");
            $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
            $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
            $this->suiviCommand->setEtat("KO");
            $this->oLog->erreur("Le code ".$sFicTransfoCode." n'est pas parametre dans 'fic_transfo'", E_USER_ERROR);
            $this->registerError();
            if($input->getOption('id_ai') && $input->getOption('id_sh')){
                $this->registerErrorCron($idAi);
            }
            throw new \Exception("Identification de flux introuvable dans 'fic_transfo'");
        }
        else
        {
            $sRegexFic  = $oFicTransfo->getRegexFic();
            if($input->getOption('regex'))
            {
                $sRegexFic  = $input->getOption('regex');
            }
            $sFicFluxCode    = $oFicTransfo->getFicFluxCode();
            $sFormatNomFicGenere    = $oFicTransfo->getNomFicGenere(); // Format du nom de fichier genere (expl : `soc_code_ext``date_distrib_Ymd`.txt pour les clients a servir venant de JADE)
            
            $oFicChrgtFichiersBdd = $this->getContainer()->get('doctrine')
                        ->getRepository('AmsFichierBundle:FicChrgtFichiersBdd')
                        ->findOneByCode($sFicFluxCode);
            if(is_null($oFicChrgtFichiersBdd))
            {
                $this->suiviCommand->setMsg("Le flux ".$sFicFluxCode." n'est pas parametre dans 'fic_chrgt_fichiers_bdd'");
                $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
                $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                $this->suiviCommand->setEtat("KO");
                $this->oLog->erreur("Le flux ".$sFicFluxCode." n'est pas parametre dans 'fic_chrgt_fichiers_bdd'", E_USER_ERROR);
                $this->registerError();
                if($input->getOption('id_ai') && $input->getOption('id_sh')){
                    $this->registerErrorCron($idAi);
                }
                throw new \Exception("Identification de flux introuvable dans 'fic_chrgt_fichiers_bdd'");
            }
            
            // Repertoire ou l'on recupere les fichiers a traiter
            $this->sRepSource	= $this->sRepFichiersPrinc.'/'.$this->getContainer()->getParameter('SOUSREP_FICHIERS_TMP').'/'.$oFicChrgtFichiersBdd->getSsRepTraitement().'/'.$sFicFluxCode;
            // Repertoire temporaires des fichiers generes apres transformation
            $this->sRepTransfoTmp	= $this->cree_repertoire($this->sRepFichiersPrinc.'/'.$this->getContainer()->getParameter('SOUSREP_FICHIERS_TMP').'/'.$oFicChrgtFichiersBdd->getSsRepTraitement().'/'.$sFicFluxCode.'/TransfoTmp');

            
            
            $oFicFormatEnregistrement   = $this->getContainer()->get('doctrine')
                                        ->getRepository('AmsFichierBundle:FicFormatEnregistrement');
            $sql = $oFicFormatEnregistrement->getSQLLoadDataInFile($sFicFluxCode, $oFicChrgtFichiersBdd->getFormatFic(), $oFicChrgtFichiersBdd->getTrimVal(), $oFicChrgtFichiersBdd->getNbCol(), 'latin1');
    	
                        
            // Les fichiers a traiter
            $aFicIterator   = new \FilesystemIterator($this->sRepSource);
            $aFicATransformer = array();
            foreach($aFicIterator as $oFic)
            {
                if($oFic->isFile() && preg_match($sRegexFic, $oFic->getFilename()))
                {
                    $aFicATransformer[$oFic->getFilename()] = $oFic;
                }
            }

            ksort($aFicATransformer);
            
            switch($sFicFluxCode)
            {
                case 'JADE_CAS':
                    $repoTmp = $this->getContainer()->get('doctrine')
                                            ->getRepository('AmsDistributionBundle:ClientAServirTransfoTmp');
                    $sTableTmp  = "client_a_servir_transfo_tmp";
                    
                    // Les parametres par defaut
                    $decalDate  = "-2"; 
                    $nbJourRef  = "+1"; // <=> jour de reference = J+1
                    $comp   =   "<=";
                    $societeId='';
                    $produitId='';
                    $flux_id=2;
                    $dateMinRef='';
                    $dateMaxRef='';
                    break;
            }
            
            $aListeMethodes = $repoFicTransfoEtape->getEtapes($sFicTransfoCode); // Liste des methodes de la classe $repoTmp a appeler
            
            if(empty($aListeMethodes))
            {
                $this->suiviCommand->setMsg(" Aucune methode a lancer. Aucune modification ne sera pas fait concernant les fichiers");
                $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_NOTICE));
                $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                $this->suiviCommand->setEtat("OK");
                $this->oLog->info(date("d/m/Y H:i:s : ").' Aucune methode a lancer. Aucune modification ne sera pas fait concernant les fichiers');
            }
            
            if(!empty($aFicATransformer) && isset($repoTmp) && !empty($aListeMethodes))
            {
                try {
                    //$repoFicTransfoEtape->prepareTableTmp($sTableTmp, $sFicFluxCode);
                    $aTousFicGeneres    = array(); // Liste des fichiers generes

                    foreach($aFicATransformer as $oFic)
                    {
                        $sFicNom  = $oFic->getFilename();
                        $sFicChemin  = $oFic->getFileInfo();

                        $this->oLog->info(date("d/m/Y H:i:s : ").' - Debut transformation du fichier "'.$sFicNom.'"');

                        $this->oLog->info(date("d/m/Y H:i:s : ").' Vide la table temporaire "'.$sTableTmp.'"');
                        $repoFicTransfoEtape->truncateTableTmp($sTableTmp);

                        $aTransf    = array(
                                    '%%NOM_FICHIER%%'           => $this->sRepSource.'/'.$sFicNom,
                                    '%%NOM_TABLE%%'             => $sTableTmp,
                                    '%%SEPARATEUR_CSV%%'        => ($oFicChrgtFichiersBdd->getFormatFic()=='CSV' ? $oFicChrgtFichiersBdd->getSeparateur() : ''),
                                    '%%NB_LIGNES_IGNOREES%%'    => $oFicChrgtFichiersBdd->getNbLignesIgnorees(),
                            );

                        try {
                            $this->oLog->info(date("d/m/Y H:i:s : ").'Debut chargement du fichier dans la table temporaire "'.$sTableTmp.'"');
                            $chargementFichier  = $oFicFormatEnregistrement->chargeDansTableTmp($aTransf);
                            if($chargementFichier !== true)
                            {
                                $this->suiviCommand->setMsg("Erreur SQL chargement de fichier : ".__FILE__);
                                $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_WARNING));
                                $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                                $this->suiviCommand->setEtat("OK");
                                $this->oLog->erreur("Erreur SQL chargement de fichier : ".print_r($chargementFichier['sql'], true), E_USER_WARNING, __FILE__, __LINE__);
                                $this->oLog->erreur("Parametres avant Load Data Infile : ".print_r($aTransf, true), E_NOTICE, __FILE__, __LINE__);

                                continue;
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
                                $this->registerErrorCron($idAi);
                            }
                            $bContinueTraitement = false;
                        }

                        foreach($aListeMethodes as $aArrMeth)
                        {
                            if($aArrMeth['methode_param'])
                            {
                                eval($aArrMeth['methode_param']);
                            }

                            $this->oLog->info(date("d/m/Y H:i:s : ").' Debut '.$aArrMeth['commentaire'].'');
                            eval('$repoTmp->'.$aArrMeth['methode_nom'].';');
                        }

                        $this->oLog->info(date("d/m/Y H:i:s : ").'Debut de la generation des fichiers transformes a partir du fichier source '.$sFicNom);

                        $aFicGeneres    = $repoTmp->genereFic($this->sRepTransfoTmp, $sFormatNomFicGenere);                   

                        if(empty($aFicGeneres))
                        {
                            $this->oLog->info('0 fichier genere pour le fichier source '.$sFicNom);
                        }
                        else
                        {
                            $aNomFicGeneres = array_keys($aFicGeneres);

                            $this->oLog->info(" A partir du fichier source ".$sFicNom." (=> ".$repoTmp->getNbLignesTmp()." lignes) ".((count($aNomFicGeneres)>1)?"sont":"est")." genere".((count($aNomFicGeneres)>1)?"s":"")." ".count($aNomFicGeneres)." fichier".((count($aNomFicGeneres)>1)?"s":""));

                            $aMsgLog    = array();
                            foreach($aFicGeneres as $sNomFicV => $aFicGen)
                            {
                                $aMsgLog[]  = $sNomFicV." (=> ".$aFicGen['nbLignes']." ligne".((count($aFicGen['nbLignes'])>1)?"s":"").") ";
                            }

                            $sMsgLog    = "Fichiers generes : ".implode(' - ', $aMsgLog);

                            $aTousFicGeneres    = array_merge($aTousFicGeneres, $aNomFicGeneres);                    
                        }                    
                    }

                    // $aTousFicGeneres
                    $aTousFicGeneres    = array_unique($aTousFicGeneres);

                    $this->oLog->info(date("d/m/Y H:i:s : ").'Debut des suppressions des fichier a transformer');                
                    foreach($aFicATransformer as $oFic)
                    {
                        $sFicASuppr  = $oFic->getFilename();
                        if(file_exists($this->sRepSource.'/'.$sFicASuppr))
                        {
                            unlink($this->sRepSource.'/'.$sFicASuppr);
                        }
                    }

                    
                    $this->oLog->info(date("d/m/Y H:i:s : ").'Debut deplacement des fichiers transformes');                
                    foreach($aTousFicGeneres as $sFiv)
                    {
                        if(file_exists($this->sRepSource.'/'.$sFiv))
                        {
                            unlink($this->sRepSource.'/'.$sFiv);
                        }

                        if(rename($this->sRepTransfoTmp.'/'.$sFiv, $this->sRepSource.'/'.$sFiv)===true)
                        {
                            $this->oLog->info('Le fichier "'.$sFiv.'" (fichier transforme) est arrive dans le bon repertoire');
                        }
                    }
                } catch (DBALException $DBALException) {
                    $this->suiviCommand->setMsg($DBALException->getMessage());
                    $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type($DBALException->getCode()));
                    $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                    $this->suiviCommand->setEtat("KO");
                    $this->oLog->erreur($DBALException->getMessage(), $DBALException->getCode(), $DBALException->getFile(), $DBALException->getLine());
                    $this->registerError();
                    if($input->getOption('id_ai') && $input->getOption('id_sh')){
                        $this->registerErrorCron($idAi);
                    }
                }
            }            
        }
        $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
        $this->endTraitement();
    	$this->oLog->info(date("d/m/Y H:i:s : ")."Fin Transformation des fichiers de clients a servir - Commande : ".$this->sNomCommande);
        $this->oLog->info("Fin commande");
        return;
    }
}
