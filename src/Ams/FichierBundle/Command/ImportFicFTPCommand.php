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
 * "Command" importation des fichiers venant d'un serveur FTP
 * !!!! NE JAMAIS METTRE de "_" dans le nom de classe
 * 
 * Pour executer, faire : 
 *                  php app/console import_fic_ftp <<fic_code>> 
 *      Expl :  php app/console import_fic_ftp JADE_CAS --id_sh=cron_test --id_ai=1  --env=dev
 *              php app/console import_fic_ftp JADE_RECLAM --id_sh=cron_test --id_ai=1  --env=dev
 *              php app/console import_fic_ftp JADE_REPERAGE --id_sh=cron_test --id_ai=1  --env=dev
 *              php app/console import_fic_ftp JADE_CAS --regex_fic="/^(\.\/)?[A-Z0-9]{2}`date_Ymd_1_1`\.txt$/i" --id_sh=cron_test --id_ai=1  --env=prod
 *              php app/console import_fic_ftp JADE_CAS --regex_fic="/^(\.\/)?LP`date_Ymd_1_1`\.txt$/i" --id_sh=cron_test --id_ai=1  --env=prod
 * 
 * 
 * @author aandrianiaina
 *
 */
class ImportFicFTPCommand extends GlobalCommand
{
    private $aFichierFluxParam;
    private $sRepTmp;
    private $sRepBkpLocal;
    private $sRegexFicDefaut;
    protected function configure()
    {
    	$this->sNomCommande	= 'import_fic_ftp';
        $this->sRegexFicDefaut = "valeur_par_defaut";
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console import_fic_ftp <<fic_code>> 
        // Expl : 
        //          php app/console import_fic_ftp JADE_RECLAM --id_sh=cron_test --id_ai=1  --env=dev
        //          php app/console import_fic_ftp JADE_CAS --regex_fic="/^(\.\/)?[A-Z0-9]{2}`date_Ymd_1_1`\.txt$/i" --id_sh=cron_test --id_ai=1  --env=prod
        //          php app/console import_fic_ftp JADE_CAS --regex_fic="/^(\.\/)?LP`date_Ymd_1_1`\.txt$/i" --id_sh=cron_test --id_ai=1  --env=prod
        $this
            ->setDescription('Importation des fichiers via FTP')
            ->addArgument('fic_code', InputArgument::REQUIRED, 'Code source de donnees')
            ->addOption('regex_fic',null, InputOption::VALUE_REQUIRED, 'regex ? Expression reguliere des fichiers a importer', $this->sRegexFicDefaut)
            ->addOption('id_sh',null, InputOption::VALUE_REQUIRED, 'Libelle du CRON')
            ->addOption('id_ai',null, InputOption::VALUE_REQUIRED, 'Id du CRON')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
    	$sFicCode 	= $input->getArgument('fic_code');	// Expl : JADE_RECLAM <=> Importation des reclamations venant de JADE. JADE_CAS <=> Importation des clients a servir venant de JADE 
        //association de la commande avec le cron
        if($input->getOption('id_sh')){
            $idSh = $input->getOption('id_sh');
        }
        if($input->getOption('id_ai')){
            $idAi = $input->getOption('id_ai');
        }
        if($input->getOption('id_ai') && $input->getOption('id_sh')){
            $this->associateToCron($idAi,$idSh);
        }
        
        $this->oLog->info(date("d/m/Y H:i:s : ")."Debut Importation des fichiers - Commande : ".$sFicCode);
                
        $em    = $this->getContainer()->get('doctrine')->getManager();
        
    	
        $oString	= new StringLocal(''); 
    	
        // Recuperation des parameters concernant le FTP et les fichiers a recuperer
        $oFicChrgtFichiersBdd = $this->getContainer()->get('doctrine')
                        ->getRepository('AmsFichierBundle:FicChrgtFichiersBdd')
                        ->getParamFluxByCode($sFicCode);
        if(is_null($oFicChrgtFichiersBdd))
        {
            $e = new \Exception("Identification de flux introuvable dans 'fic_chrgt_fichiers_bdd'");
            $this->suiviCommand->setMsg("Le flux ".$sFicCode." n'est pas parametre dans 'fic_chrgt_fichiers_bdd'");
            $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
            $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
            $this->suiviCommand->setEtat("KO");
            $this->oLog->erreur("Le flux ".$sFicCode." n'est pas parametre dans 'fic_chrgt_fichiers_bdd'", E_USER_ERROR);
            $this->registerError();
            if($input->getOption('id_ai') && $input->getOption('id_sh')){
                $this->registerErrorCron($idAi);
            }
            throw $e;
        } 
        
    	// Repertoire ou l'on dépose les fichiers a traiter
        $this->sRepTmp	= $this->cree_repertoire($this->sRepFichiersPrinc.'/'.$this->getContainer()->getParameter('SOUSREP_FICHIERS_TMP').'/'.$oFicChrgtFichiersBdd->getSsRepTraitement().'/'.$sFicCode);
        

        // Repertoire Backup Local
    	$this->sRepBkpLocal	= $this->cree_repertoire($this->sRepFichiersPrinc.'/'.$this->getContainer()->getParameter('SOUSREP_FICHIERS_BKP').'/'.$oFicChrgtFichiersBdd->getSsRepTraitement().'/'.$sFicCode);
        
        // Connexion au FTP
        $aFicTmp    = array(); // Fichiers importes du FTP
        $aFicNonEcrasesTmp    = array(); // Fichiers en cours de traitement et non ecrases
        try 
        {
            $oParamFTP   = $this->getContainer()->get('doctrine')
                            ->getRepository('AmsFichierBundle:FicFtp')->findOneByCode($oFicChrgtFichiersBdd->getCode());
            
            $srv_ftp    = $this->getContainer()->get('ijanki_ftp');            
            
            $sRegexFicInit  = $oFicChrgtFichiersBdd->getRegexFic();
            if ($input->getOption('regex_fic') && $input->getOption('regex_fic') != $this->sRegexFicDefaut) {
                $sRegexFicInit   = $input->getOption('regex_fic');
            }
            $this->oLog->info("Regex des fichiers a importer ".$sRegexFicInit);
            
            $sRegexFic  = $oString->transformeRegex($sRegexFicInit);
                        
            $srv_ftp->connect($oParamFTP->getServeur());
            $srv_ftp->login($oParamFTP->getLogin(), $oParamFTP->getMdp());
            $srv_ftp->chdir($oParamFTP->getRepertoire());
            $aTousFicFTP = $srv_ftp->nlist('.');
            if(!empty($aTousFicFTP))
            {
                foreach($aTousFicFTP as $sFicV)
                {
                   // var_dump($sRegexFic);
                   // var_dump(preg_match($sRegexFic, $sFicV));die();
                    if(preg_match($sRegexFic, $sFicV))
                    {
                        if(file_exists($this->sRepTmp.'/'.$sFicV) && is_writable($this->sRepTmp.'/'.$sFicV))
                        {
                            unlink($this->sRepTmp.'/'.$sFicV);
                        }
                        if(!file_exists($this->sRepTmp.'/'.$sFicV))
                        {
                            if($srv_ftp->get($this->sRepTmp.'/'.$sFicV, $sFicV, FTP_BINARY)===false)
                            {
                                $this->oLog->info("Probleme d'importation du fichier ".$sFicV.'du FTP '.$oParamFTP->getServeur().'/'.$oParamFTP->getRepertoire(), E_USER_ERROR);
                            }
                            else 
                            {
                                $aFicTmp[]	= $sFicV;
                                $this->oLog->info("Fichier importe du FTP ".$oParamFTP->getServeur().'/'.$oParamFTP->getRepertoire().' : '.$sFicV);
                            }
                        }
                        else
                        {
                            $aFicNonEcrasesTmp[] = $sFicV;
                            $this->oLog->info("Fichier en cours de traitement et non ecrase : ".$this->sRepTmp.'/'.$sFicV);
                        }
                    }
                }
            }
            if(!empty($aFicTmp))
            {
                    $this->oLog->info("Nombre total de fichiers importes du FTP ".$oParamFTP->getServeur().'/'.$oParamFTP->getRepertoire().' : '.count($aFicTmp));
            }
            if(!empty($aFicNonEcrasesTmp))
            {
                    $this->oLog->info("Nombre total de fichiers en cours de traitement et non ecrases : ".count($aFicNonEcrasesTmp));
            }

            $srv_ftp->close();

        } catch (FtpException $e) 
        {
            $this->suiviCommand->setMsg("Probleme d'acces au FTP ".$oParamFTP->getServeur().' : '.$e->getMessage());
            $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
            $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
            $this->suiviCommand->setEtat("KO");
            $this->oLog->erreur("Probleme d'acces au FTP ".$oParamFTP->getServeur().' : '.$e->getMessage(), E_USER_ERROR);
            $this->registerError();
            if($input->getOption('id_ai') && $input->getOption('id_sh')){
                $this->registerErrorCron($idAi);
            }
        }
        
    	
    	$this->oLog->info(date("d/m/Y H:i:s : ")."Fin Importation des fichiers - Commande : ".$sFicCode);
    	$this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
        $this->endTraitement();
        return;
    }
}
