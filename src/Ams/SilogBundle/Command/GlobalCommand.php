<?php 
namespace Ams\SilogBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ams\SilogBundle\Lib\LogLocal;
use Ams\SilogBundle\Lib\StringLocal;
use Ijanki\Bundle\FtpBundle\Exception\FtpException;
use Ams\ExploitationBundle\Entity\SuiviCommand;
use Ams\ExploitationBundle\Repository\SuiviCommandRepository;

abstract class GlobalCommand extends ContainerAwareCommand
{	
    protected $sNomCommande;
    protected $srv_conn;
    protected $srv_param;
    protected $oFtp;
    protected $sRepFichiersPrinc;
    protected $oLog;
    protected $oString;
    protected $sDateCourantYmd;
    protected $sHeureCourantYmd;
    protected $sDateHeureCourantY_m_d;
    protected $sDateCourantd_m_Y;
    protected $sDateHeureCourantd_m_Y;
    protected $suiviCommand;
	
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
	$this->oString	= new StringLocal('');
    	$this->sDateCourantYmd	= date("Ymd");
	$this->sHeureCourantYmd	= date("His");
	$this->sDateHeureCourantY_m_d	= $this->oString->changeFormatDate('AAAAMMJJhhmmss', 'Y/m/d H:i:s', $this->sDateCourantYmd.$this->sHeureCourantYmd);
	$this->sDateCourantd_m_Y	= $this->oString->changeFormatDate('AAAAMMJJhhmmss', 'd/m/Y', $this->sDateCourantYmd.$this->sHeureCourantYmd);
	$this->sDateHeureCourantd_m_Y = $this->oString->changeFormatDate('AAAAMMJJhhmmss', 'd/m/Y H:i:s', $this->sDateCourantYmd.$this->sHeureCourantYmd);
		
    	$this->srv_conn = $this->getContainer()->get('doctrine.dbal.mroad_connection');
    	$this->srv_ftp = $this->getContainer()->get('ijanki_ftp');
    	$this->srv_param	= $this->getContainer()->get('param');
    	//$this->sRepFichiersPrinc	=__DIR__.'/'.$this->srv_param->get('REP_FICHIERS_CMD');    
    	$this->sRepFichiersPrinc	= $this->getContainer()->getParameter('REP_FICHIERS_CMD'); 
    	$this->initLog($this->sNomCommande);
        $this->initTraitement($this->sNomCommande);
    }
	
    /**
     * 
     * Initialisation des parametres utiles pour les "command" qui heritent de cette classe
     */
    protected function initLog($sIdCommand, $sRep='')
    {
        $this->oLog = new LogLocal(true);
        $this->oLog->setLN($this->srv_param->get('LN'));
        $this->oLog->set_id_file_log($sIdCommand);
        $this->oLog->set_path_log_info($this->sRepFichiersPrinc.'/'.$this->getContainer()->getParameter('REP_LOGS'));
        $this->oLog->set_path_log_err($this->sRepFichiersPrinc.'/'.$this->getContainer()->getParameter('REP_LOGS_ERR'));
        set_error_handler(array($this->oLog, 'error_handler'));
        set_exception_handler(array($this->oLog, 'exception_handler'));
        register_shutdown_function(array($this->oLog, 'capture_shut_down'));
    }
	
    /**
     * 
     * cree le repertoire $_sStr
     * @param string $_sStr
     * 
     * @return string
     */
    protected function cree_repertoire($_sStr)
    {
        if(is_dir($_sStr))
        {
            return $_sStr;
        }
        else 
        {
            if(mkdir($_sStr, 0777, true))
            {
            
                return $_sStr;
            }
            else 
            {
                trigger_error('Erreur lors de la creation du repertoire '.$_sStr, E_USER_ERROR);
                return "";
            }
        }
    }
    

    /**
    *trimDate supprime les espaces , : et - dans une chaine de caractere
    *@param string $date
    */
    public function trimDate($date){

        $date = str_replace(array(':','-',' '),'',$date);
        return substr($date, 0,12);
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
    protected function isFichierTraite($sFicCode, $sFic, $sSourceId)
    {
        $repoFicRecap   = $this->getContainer()->get('doctrine')->getRepository('AmsFichierBundle:FicRecap');
        $aDernierFicTraite  = $repoFicRecap->getRecapFicTraite($sFicCode, $sFic, $sSourceId);
        return (empty($aDernierFicTraite) ? false : $aDernierFicTraite);
    }
    
    
     /**
     * génére un pdf à partir d'un template 
     * @param string $pathname repertoire
     * @param string $libelle non du fichier
     * @param string $template template
     * @param array $data  tableau de variable à passer au template
     * @return  pdffile
     */
    protected function pdfCommand($pathname, $libelle, $template, $data) {
        $html = $this->getContainer()->get('templating')->render($template, $data);
        $fichier = $pathname . $libelle . ".html";
        $oFic = fopen($fichier, "w+");
        fputs($oFic, $html);
        exec("wkhtmltopdf -O landscape " . $fichier . "  " . $pathname . $libelle . ".pdf");
        fclose($oFic);
        unlink($fichier);
    }

    /**
     * création de repertoire et génération pdf
     * @param string $pathname repertoire
     * @param string $libelle non du fichier
     * @param string $template template
     * @param array $data  tableau de variable à passer au template
     * @return  pdffile
     */
    protected function generate($pathname, $libelle, $template, $data) {
        if (is_dir($pathname)) {
            if (count($data) > 0) {
                $this->pdfCommand($pathname, $libelle, $template, $data);
            } 
        } else {
            if (!mkdir($pathname, 0755, true)) {
                $this->oLog->info("Creation du répertoire de destination impossible");
                $this->oLog->erreur("Impossible de créer le répertoire de destination:" . $pathname, E_USER_ERROR);
                return;
            } else {
                if (count($data)> 0) {
                    $this->pdfCommand($pathname, $libelle, $template, $data);
                } 
            }
        }
    }

    /**
     * Suppression d'un repertoire 
     * @param string $dir
     * @return bool
     */
    protected function delTree($dir) {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }
    
    protected function initTraitement($libelle){
       $this->suiviCommand = new SuiviCommand();
       $this->suiviCommand->setLibelleCommand($libelle);
       $this->suiviCommand->setHeureDebut(new \DateTime(date("Y-m-d H:i:s")));
       $this->suiviCommand->setEtat("En cours");
       $this->startTraitement();
    }

    /**
     *  Cette fonction associe la commande lancé a un traitement et l'insere en BDD
     * 
     */
    protected function startTraitement(){
        $em = $this->getContainer()->get('doctrine')->getManager();
        $em->persist($this->suiviCommand);
        $em->flush();
    }
    
    /**
     * Cette fonction MAJ l'etat d'un CRON en cas d'erreur d'une des commandes 
     * 
     * @param type $idCron
     */
    public function registerErrorCron($idCron){
        $suiviCronRepo = $this->getContainer()->get('doctrine')->getRepository('AmsExploitationBundle:SuiviCron');
        
        // on set l 'etat du CRON a KO
        $etatCron = $suiviCronRepo->getEtatById($idCron);
//        var_dump($etatCron[0]);
        if($etatCron[0] != "KO"){
            $val['etat'] = "KO";
            $suiviCronRepo->updateEtatById($val,$idCron);
        }
    }
    
    /**
     * Cette fonction MAJ un traitement en cas d'erreur bloquante
     * clos(heure de fin) l'enregistrement
     */
    protected function registerError(){
        $em = $this->getContainer()->get('doctrine')->getManager();
        //ATTENTION : en local(windows) remplacer DIRECTORY_SEPARATOR par un "/"
//        $pathSrc = $this->oLog->get_path_log_err() . DIRECTORY_SEPARATOR . $this->oLog->get_file_log_err();
//        $errCopyFolder = $this->getContainer()->get('kernel')->getRootDir(). DIRECTORY_SEPARATOR . $this->getContainer()->getParameter('MROAD_SUIVI_CRON_LOG_ERR_FS_FOLDER');
//        $pathDest = (substr($errCopyFolder, -1)) == DIRECTORY_SEPARATOR ? $errCopyFolder . $this->oLog->get_file_log_err() : $errCopyFolder . DIRECTORY_SEPARATOR .$this->oLog->get_file_log_err() ;

        $pathSrc = $this->oLog->get_path_log_err() . "/" . $this->oLog->get_file_log_err();
        $errCopyFolder = $this->getContainer()->get('kernel')->getRootDir(). "/" . $this->getContainer()->getParameter('MROAD_SUIVI_CRON_LOG_ERR_FS_FOLDER');
        $pathDest = (substr($errCopyFolder, -1)) == "/" ? $errCopyFolder . $this->oLog->get_file_log_err() : $errCopyFolder . "/" .$this->oLog->get_file_log_err() ;
        // on copie dans un repertoire accessible par apache
        copy($pathSrc,$pathDest);
        $this->suiviCommand->setErrorFile($pathDest);
        $em->persist($this->suiviCommand);
        $em->flush();
    }
    
    /**
     *  Cette fonction MAJ un traitement à la fin de la cmd et clos (heure de fin) l'enregistrement
     * ATTENTION Parfois une erreur se produit et la commande continu donc on faite un petit test 
     * ETAT == "KO" ? set ETAT KO : persist 
     */
    protected function endTraitement(){
        $em = $this->getContainer()->get('doctrine')->getManager();
        //en local remplacer DIRECTORY_SEPARATOR par un "/"
//        $path = $this->oLog->get_path_log_info() . DIRECTORY_SEPARATOR . $this->oLog->get_file_log_info();
        $path = $this->oLog->get_path_log_info() . "/" . $this->oLog->get_file_log_info();
        $this->suiviCommand->setErrorFile($path);
        if($this->suiviCommand->getEtat() != "KO"){
             $this->suiviCommand->setEtat("OK");
        }
        $em->persist($this->suiviCommand);
        $em->flush();
    }
    
    /**
     * Cette Fonction associe la commande qui est lancé à un CRON qui a démaré au préalable
     * @param type $id
     * @param type $libelle
     */
    protected function associateToCron($id, $libelle){
        $this->suiviCommand->setIdCron($id);
        $this->suiviCommand->setLibelleCron($libelle);
        $em = $this->getContainer()->get('doctrine')->getManager();
        $em->persist($this->suiviCommand);
        $em->flush();
    }
}