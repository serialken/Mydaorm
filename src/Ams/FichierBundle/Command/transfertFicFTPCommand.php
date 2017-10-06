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
 * "Command" de transfert d'un fichier sur un serveur FTP
 * !!!! NE JAMAIS METTRE de "_" dans le nom de classe
 * 
 * Pour executer, faire : 
 *                  php app/console transfert_ftp <<fic_code>> 
 *      Expl :  php app/console transfert_ftp JADE_CAS
 *              php app/console transfert_ftp JADE_RECLAM
 * 
 * 
 * @author maadelise
 *
 */
class transfertFicFTPCommand extends GlobalCommand
{
    private $aFichierFluxParam;
    private $sRepTmp;
    private $sRepBkpLocal;
    
    protected function configure()
    {
    	$this->sNomCommande	= 'transfert_ftp';
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console transfert_ftp <<input_file>> <<server>> Expl : php app/console transfert_ftp ../fichiers/test.txt 127.0.0.1
        $this
            ->setDescription('Transfert de fichier via FTP')
            ->addArgument('input_file', InputArgument::REQUIRED, 'Le chemin vers le fichier à transférer')
            ->addArgument('server', InputArgument::REQUIRED, 'IP ou FQDN du serveur FTP de destination')
            ->addOption('auth_mode',NULL, InputOption::VALUE_REQUIRED, 'Le mode d\'authentification','')
            ->addOption('port','p', InputOption::VALUE_REQUIRED, 'Le port du serveur FTP ',21)
            ->addOption('mode','m', InputOption::VALUE_REQUIRED, 'Le mode de transfert ASCII ou BINARY ','ASCII')
            ->addOption('dest_dir',NULL, InputOption::VALUE_OPTIONAL, 'Le dossier de destination ','.')
            ->addOption('user',NULL, InputOption::VALUE_OPTIONAL, 'Le nom de l\'utilisateur', NULL)
            ->addOption('pwd',NULL, InputOption::VALUE_OPTIONAL, 'Le mot de passe', NULL)
            ->addOption('pingtest',NULL, InputOption::VALUE_REQUIRED, 'Effectué un test de PING au préalable (y|n) - "y" par défaut', 'y')
            ->addOption('id_sh',null, InputOption::VALUE_REQUIRED, 'Libelle du CRON')
            ->addOption('id_ai',null, InputOption::VALUE_REQUIRED, 'Id du CRON')
        ;
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
        $sFile= $input->getArgument('input_file');	// Expl : test.txt
    	$sServer = $input->getArgument('server');	// Expl : @IP ex. 127.0.0.1 ou FQDN ex. ftp.site.fr
    	$sAuthMode = $input->getOption('auth_mode');	// Expl : anonymous ou login
    	$iPort = $input->getOption('port');	// Expl : port du serveur (21 par défaut)
    	$sMode = $input->getOption('mode');	// Expl : Le mode de transfert du fichier (ASCII par défaut)
    	$sDestDir = $input->getOption('dest_dir');	// Expl : Le dossier de destination sur le serveur
    	$sUser = $input->getOption('user');	// Expl : Le login à utiliser en mode "login"
    	$sPwd = $input->getOption('pwd');	// Expl : Le mot de passe à utiliser en mode "login"
    	$sPingTest = $input->getOption('pingtest');	// Expl : Le mot de passe à utiliser en mode "login"
        
        $this->oLog->info("Debut de transfert du fichier ".$sFile. " vers " .$sServer . " (".$sDestDir.")" );
        
        // Ping du serveur
        if ($sPingTest == 'y'){
            exec("ping -n 3 $sServer", $output, $status);
            if ($status != 0){
                $this->suiviCommand->setMsg("Le serveur \"".$sServer."\" n'a pas répondu au PING, exécution arrêtée.");
                $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
                $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                $this->suiviCommand->setEtat("KO");
                $this->oLog->erreur("Le serveur \"".$sServer."\" n'a pas répondu au PING, exécution arrêtée.",E_USER_ERROR);
                $this->registerError();
                if($input->getOption('id_ai') && $input->getOption('id_sh')){
                    $this->registerErrorCron($idAi);
                }
                exit();
            }
        }
        
        // Le fichier est accessible ?
        if (!file_exists($sFile)){
             $this->suiviCommand->setMsg("Le fichier \"".$sFile."\" n'existe pas ou est inaccessible, exécution arrêtée.");
            $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
            $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
            $this->suiviCommand->setEtat("KO");
            $this->oLog->erreur("Le fichier \"".$sFile."\" n'existe pas ou est inaccessible, exécution arrêtée.",E_USER_ERROR);
            $this->registerError();
            if($input->getOption('id_ai') && $input->getOption('id_sh')){
                $this->registerErrorCron($idAi);
            }
            exit();
        }
        
        // Le fichier n'est pas un répertoire ?
        if (is_dir($sFile)){
            $this->suiviCommand->setMsg("Le script n'est pas autorisé à transférer des répertoires \"".$sFile."\" est un répertoire, exécution arrêtée.");
            $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
            $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
            $this->suiviCommand->setEtat("KO");
            $this->oLog->erreur("Le script n'est pas autorisé à transférer des répertoires \"".$sFile."\" est un répertoire, exécution arrêtée.",E_USER_ERROR);
            $this->registerError();
            if($input->getOption('id_ai') && $input->getOption('id_sh')){
                $this->registerErrorCron($idAi);
            }
            exit();
        }
        
        // Création de la connexion FTP
        $conn_id = ftp_connect($sServer, $iPort);
        
        // Authentification
        switch ($sAuthMode){
            case "login":
                $login_result = ftp_login($conn_id, $sUser, $sPwd); 
                if ($login_result !== true){
                    $this->suiviCommand->setMsg("L'authentification sur \"".$sServer."\" a échoué, exécution arrêtée.");
                    $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
                    $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                    $this->suiviCommand->setEtat("KO");
                    $this->oLog->erreur("L'authentification sur \"".$sServer."\" a échoué, exécution arrêtée.",E_USER_ERROR);
                    $this->registerError();
                    if($input->getOption('id_ai') && $input->getOption('id_sh')){
                        $this->registerErrorCron($idAi);
                    }
                    ftp_close($conn_id);
                    exit();
                }
            break;
            case 'anonymous':
                $login_result = ftp_login($conn_id, 'anonymous', ''); 
            break;
            default:
                 $login_result = ftp_login($conn_id, "", ""); 
            break;
        }
    
        if ($login_result !== true){
            $this->suiviCommand->setMsg("L'authentification sur \"".$sServer."\" a échoué, exécution arrêtée.");
            $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
            $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
            $this->suiviCommand->setEtat("KO");
            $this->oLog->erreur("L'authentification sur \"".$sServer."\" a échoué, exécution arrêtée.",E_USER_ERROR);
            $this->registerError();
            if($input->getOption('id_ai') && $input->getOption('id_sh')){
                $this->registerErrorCron($idAi);
            }
            ftp_close($conn_id);
            exit();
        }
        
        // Mode de transfert du fichier
        $sModeTransfert = ($sMode == 'BINARY') ? FTP_BINARY : FTP_ASCII;       
        
        // Changement de répertoire ?
        if ($sDestDir != '.'){
            if (!ftp_chdir($conn_id, $sDestDir)){
                $this->suiviCommand->setMsg("Impossible de se placer dans le répertoire distant \"".$sDestDir."\", exécution arrêtée.");
                $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
                $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                $this->suiviCommand->setEtat("KO");
                $this->oLog->erreur("Impossible de se placer dans le répertoire distant \"".$sDestDir."\", exécution arrêtée.",E_USER_ERROR);
                $this->registerError();
                if($input->getOption('id_ai') && $input->getOption('id_sh')){
                    $this->registerErrorCron($idAi);
                }
                ftp_close($conn_id);
                exit();
            }
        }
        
        // Transfert du fichier
        if (ftp_put($conn_id, basename($sFile), $sFile, $sModeTransfert)) {
            $this->oLog->info("Le fichier \"".$sDestDir."\", a bien été uploadé.");
        }
        else{
            $this->suiviCommand->setMsg("Une erreur a été rencontrée durant le transfert du fichier \"".$sDestDir."\", fin d'exécution.");
            $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
            $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
            $this->suiviCommand->setEtat("KO");
            $this->oLog->erreur("Une erreur a été rencontrée durant le transfert du fichier \"".$sDestDir."\", fin d'exécution.",E_USER_ERROR);
            $this->registerError();
            if($input->getOption('id_ai') && $input->getOption('id_sh')){
                $this->registerErrorCron($idAi);
            }
        }
        
        // Fermeture de la connexion FTP
        ftp_close($conn_id);
        $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
        $this->endTraitement();
        $this->oLog->info("Fermeture de la connexion FTP");
        exit();
    }
}
