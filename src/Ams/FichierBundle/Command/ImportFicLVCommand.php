<?php 
namespace Ams\FichierBundle\Command;

//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Ijanki\Bundle\FtpBundle\Exception\FtpException;
use Ams\SilogBundle\Lib\StringLocal;

use Ams\SilogBundle\Command\GlobalCommand;

/**
 * 
 * "Command" importation des fichiers des Lieux de Ventes Le Parisien venant directement de DCS
 * !!!! NE JAMAIS METTRE de "_" dans le nom de classe
 * 
 * Table "fic_flux" =>  id=20, fic_code = "DCS - Import des fichiers des Lieux de Ventes a servir"
                        INSERT INTO fic_flux (id, libelle) VALUES (20, 'DCS - Import des fichiers des Lieux de Vente a servir');
 * 
 * Table "fic_ftp" =>   fic_code = "DCS_LV" <=> DCS - Import des fichiers des Lieux de Vente a servir
 *          INSERT INTO fic_ftp (code, serveur, login, mdp, repertoire, rep_sauvegarde, id_soc_distrib)
            VALUES ('DCS_LV', '10.151.93.2', 'SDVP-LP', 'Sdvp753', 'RCT/LV/CAS', 'Bkp', 'DCS');
 * 
 * Table "fic_source" =>   fic_code = "DCS_LV" <=> DCS - Import des fichiers des Lieux de Ventes a servir
 *          INSERT INTO fic_source (code, libelle, client_type)
            VALUES ('DCS_LV', 'DCS - Lieux de vente', 1);
 * 
 * Table "fic_format_enregistrement" =>   Format du fichier a integrer
 *          INSERT INTO fic_format_enregistrement (fic_code, attribut, col_debut, col_long, col_val, col_val_rplct, col_desc)
            SELECT 'DCS_LV' AS fic_code, attribut, col_debut, col_long, col_val, NULL col_val_rplct, col_desc 
            FROM fic_format_enregistrement WHERE fic_code='JADE_CAS' ORDER BY col_val;
 * 
 * Table "fic_chrgt_fichiers_bdd" =>   parametre global du chargement de fichier
 *          INSERT INTO fic_chrgt_fichiers_bdd (fic_ftp, fic_source, fic_code, regex_fic, format_fic, nb_lignes_ignorees, separateur, trim_val, nb_col, flux_id, ss_rep_traitement)
            VALUES (18, 4, 'DCS_LV', '/^(\\.\\/)?LPLV_`date_Ymd_-2_10`_[0-9]{12}\\.TXT$/i', 'CSV', 0, '|', 1, 19, 20, 'LVAServir');
 *
 * Pour executer, faire : 
 *                  php app/console import_fic_lv <<fic_code>> 
 *      Expl :  php app/console import_fic_lv DCS_LV --regex=/.+20150215.+/ --env=dev
 * 
 * 
 * @author aandrianiaina
 *
 */
class ImportFicLVCommand extends GlobalCommand
{
    private $aFichierFluxParam;
    private $sRepTmp;
    private $sRepBkpLocal;
    protected function configure()
    {
    	$this->sNomCommande	= 'import_fic_lv';
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console import_fic_lv <<fic_code>> Expl : php app/console import_fic_lv DCS_LV --env=dev
        $this
            ->setDescription('Importation des fichiers des Lieux de Ventes Le Parisien venant directement de DCS')
            ->addArgument('fic_code', InputArgument::REQUIRED, 'Code source de donnees')
            ->addOption('regex',null, InputOption::VALUE_REQUIRED, 'Regex des fichiers a importer')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
    	$sFicCode 	= $input->getArgument('fic_code');	// Expl : DCS_LV 
        $sRegexFicAConsiderer = '';
        if ($input->getOption('regex')) {
            $sRegexFicAConsiderer   = $input->getOption('regex');
        }
        $sEnvironnement = "";
        if ($input->getOption('env')) {
            $sEnvironnement   = $input->getOption('env');
        }
        $sCodeSocieteLV = 'LV'; // On remplace par ce code le code societe "LP"
        
        $this->oLog->info(date("d/m/Y H:i:s : ")."Debut Importation des fichiers des Lieux de Ventes Le Parisien venant directement de DCS - Commande : ".$sFicCode." ".$sFicCode. ( $sRegexFicAConsiderer!='' ? ' --regex='.$sRegexFicAConsiderer : '' ) ." --env=".$sEnvironnement);
                
        $em    = $this->getContainer()->get('doctrine')->getManager();
        
    	
        $oString	= new StringLocal(''); 
    	
        // Recuperation des parameters concernant le FTP et les fichiers a recuperer
        $oFicChrgtFichiersBdd = $this->getContainer()->get('doctrine')
                        ->getRepository('AmsFichierBundle:FicChrgtFichiersBdd')
                        ->getParamFluxByCode($sFicCode);
        if(is_null($oFicChrgtFichiersBdd))
        {
            $this->oLog->erreur("Le flux ".$sFicCode." n'est pas parametre dans 'fic_chrgt_fichiers_bdd'", E_USER_ERROR);
            throw new \Exception("Identification de flux introuvable dans 'fic_chrgt_fichiers_bdd'");
        } 
        
    	// Repertoire ou l'on recupere les fichiers a integrer dans M-ROAD
        $this->sRepTmp	= $this->cree_repertoire($this->sRepFichiersPrinc.'/'.$this->getContainer()->getParameter('SOUSREP_FICHIERS_TMP').'/'.$oFicChrgtFichiersBdd->getSsRepTraitement().'/'.$sFicCode);
        
    	// Repertoire ou l'on recupere les fichiers non transformes
        $this->sRepTmp2	= $this->cree_repertoire($this->sRepFichiersPrinc.'/'.$this->getContainer()->getParameter('SOUSREP_FICHIERS_TMP').'/'.$oFicChrgtFichiersBdd->getSsRepTraitement().'/'.$sFicCode.'/Tmp2');
        

        // Repertoire Backup Local
    	$this->sRepBkpLocal	= $this->cree_repertoire($this->sRepFichiersPrinc.'/'.$this->getContainer()->getParameter('SOUSREP_FICHIERS_BKP').'/'.$oFicChrgtFichiersBdd->getSsRepTraitement().'/'.$sFicCode);
        
        // Connexion au FTP
        $aFicFtpTraites    = array(); // Fichiers importes du FTP
        $aFicTmp    = array(); // Fichiers transformes 
        
        
            $oParamFTP   = $this->getContainer()->get('doctrine')
                            ->getRepository('AmsFichierBundle:FicFtp')->findOneByCode($oFicChrgtFichiersBdd->getCode());
            
            $srv_ftp    = $this->getContainer()->get('ijanki_ftp');
            
            $sRegexFic  = $oString->transformeRegex($oFicChrgtFichiersBdd->getRegexFic());
            $sSepSourceCSV  = $oFicChrgtFichiersBdd->getSeparateur();
            if($sRegexFicAConsiderer!='') // Si le regex des fichiers a recuperer est defini parmi les parametrages de la commande, ceci est prioritaire par rapport celui deja defini dans la BDD
            {
                $sRegexFic  = $sRegexFicAConsiderer;
            }
                        
            $srv_ftp->connect($oParamFTP->getServeur());
            $srv_ftp->login($oParamFTP->getLogin(), $oParamFTP->getMdp());
            $srv_ftp->chdir($oParamFTP->getRepertoire());
            $aTousFicFTP = $srv_ftp->nlist('.');
            //print_r($aTousFicFTP);
            rsort($aTousFicFTP); // On ne va traiter que le dernier fichier recu pour un jour donne
            if(!empty($aTousFicFTP))
            {
                $sRegexFicDate  = '/^(\.\/)?LPLV_([0-9]{8})_.+$/i';
                foreach($aTousFicFTP as $sFicV)
                {
                   // var_dump($sRegexFic);
                   // var_dump(preg_match($sRegexFic, $sFicV));die();
                    if(preg_match($sRegexFic, $sFicV))
                    {
                        //echo "\n sRegexFic : $sRegexFic -> $sFicV\n";
                        if(preg_match_all($sRegexFicDate, $sFicV, $aArrRegex))
                        {
                            //echo "\n sRegexFicDate : $sRegexFicDate -> $sFicV\n";
                            //print_r($aArrRegex);

                            if(isset($aArrRegex[2][0]))
                            {
                                if(!isset($aFicFtpTraites[$aArrRegex[2][0]]))
                                {
                                    $aFicFtpTraites[$aArrRegex[2][0]]   = $sFicV;
                                }
                            }
                        }
                    }
                }
                        
                foreach($aFicFtpTraites as $sFicV)         
                {
                    if(file_exists($this->sRepTmp2.'/'.$sFicV) && is_writable($this->sRepTmp2.'/'.$sFicV))
                    {
                        unlink($this->sRepTmp2.'/'.$sFicV);
                    }
                    if(!file_exists($this->sRepTmp2.'/'.$sFicV))
                    {
                        if($srv_ftp->get($this->sRepTmp2.'/'.$sFicV, $sFicV, FTP_BINARY)===false)
                        {
                            $this->oLog->info("Probleme d'importation du fichier ".$sFicV.'du FTP '.$oParamFTP->getServeur().'/'.$oParamFTP->getRepertoire(), E_USER_ERROR);
                        }
                        else 
                        {
                            // Transformation des fichiers 
                            $this->oLog->info(date("d/m/Y H:i:s : ")."Debut Transformation du fichier ".$sFicV);
                            $aFichiersSortie    = array();
                            if ($oFichierSource = fopen($this->sRepTmp2.'/'.$sFicV,"r"))
                            {
                                while(!feof($oFichierSource))
                                {
                                    $sLigneSource = fgets($oFichierSource);
                                    if(trim($sLigneSource) != "")
                                    {
                                        $aLigneSource = explode($sSepSourceCSV, $sLigneSource);
                                        foreach($aLigneSource as $iIK=> $sV)
                                        {
                                            $aLigneSource[$iIK] = trim($sV);
                                        }

                                        $aLigneSortie = array();
                                        $sDateParutionYmd   = str_replace('/', '', $aLigneSource[1]);  
                                        // 0  -> N° de parution
                                        // 1  -> Date de parution [AAAA/MM/JJ]
                                        // 2  -> N° d'abonne
                                        // 3  -> Volet 1
                                        // 4  -> Volet 2
                                        // 5  -> Volet 3
                                        // 6  -> Volet 4
                                        // 7  -> Volet 5
                                        // 8  -> CP
                                        // 9  -> Ville
                                        // 10 -> Type de portage
                                        // 11 -> Societe (LP)
                                        // 12 -> N° du titre (01|02)
                                        // 13 -> N° d'edition (771,772,780,...)
                                        // 14 -> Nombre d'exemplaires
                                        // 15 -> Divers
                                        // 16 -> Digicode
                                        // 17 -> Consigne de portage
                                        // 18 -> Message pour le porteur

                                        $aLigneSortie = $aLigneSource;
                                        $aLigneSortie[11]   = $sCodeSocieteLV; // Seul le champ "code societe" change                                            

                                        $aFichiersSortie[$sDateParutionYmd][]   = $aLigneSortie;
                                    }
                                }

                                fclose($oFichierSource);

                                // Supprimer le fichier dans $this->sRepTmp2 en cours de traitement
                                if(file_exists($this->sRepTmp2.'/'.$sFicV))
                                {
                                    unlink($this->sRepTmp2.'/'.$sFicV);
                                }

                                if(!empty($aFichiersSortie))
                                {
                                    foreach($aFichiersSortie as $sDateParK => $aDonneesV)
                                    {
                                        $sFichierSortie	= $sCodeSocieteLV.$sDateParK.'.txt';
                                        if ($oFichierSortie = fopen($this->sRepTmp.'/'.$sFichierSortie,"w+"))
                                        {
                                            foreach($aDonneesV as $aLigneSortieV)
                                            {
                                                $sLigneSortie = implode($sSepSourceCSV, $aLigneSortieV);
                                                $sLigneSortie   .= "\n";    // Caractere de fin de ligne
                                                fwrite($oFichierSortie, $sLigneSortie);
                                            }
                                            fclose($oFichierSortie);
                                            $aFicTmp[]	= $sFichierSortie;
                                        }
                                        else 
                                        {
                                            $this->oLog->erreur("Erreur lors de la creation du fichier ".$this->sRepTmp.'/'.$sFichierSortie, E_USER_ERROR);
                                        }
                                    }

                                    $this->oLog->info("Fichier importe du FTP ".$oParamFTP->getServeur().'/'.$oParamFTP->getRepertoire().' et transforme : '.$sFicV);
                                }                                    
                            }
                            else 
                            {
                                $this->oLog->erreur("Erreur lors de la lecture du fichier ".$this->sRepTmp2.'/'.$sFicV, E_USER_ERROR);
                            }
                        }
                    }
                }
            }
            if(!empty($aFicFtpTraites))
            {
                $this->oLog->info("Nombre total de fichiers importes du FTP ".$oParamFTP->getServeur().'/'.$oParamFTP->getRepertoire().' : '.count($aFicFtpTraites));
            }
            if(!empty($aFicTmp))
            {
                $this->oLog->info('Nombre total de fichiers transformes : '.count($aFicTmp));
            }

            $srv_ftp->close();
        
    	
    	$this->oLog->info(date("d/m/Y H:i:s : ")."Fin Importation des fichiers des Lieux de Ventes Le Parisien venant directement de DCS - Commande : ".$sFicCode." ".$sFicCode." --env=".$sEnvironnement);
    	
        return;
    }
}
