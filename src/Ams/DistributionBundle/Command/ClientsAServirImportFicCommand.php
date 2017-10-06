<?php 
namespace Ams\DistributionBundle\Command;

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
 * "Command" importation des fichiers clients a servir 
 * !!!! NE JAMAIS METTRE de "_" dans le nom de classe
 * 
 * Pour executer, faire : 
 *                  php app/console clients_a_servir_import_fic <<fic_code>> 
 *      Expl : php app/console clients_a_servir_import_fic JADE_CAS
 * 
 * 
 * @author aandrianiaina
 *
 */
class ClientsAServirImportFicCommand extends GlobalCommand
{
    private $aFichierFluxParam;
    private $sRepTmp;
    private $sRepBkpLocal;
    protected function configure()
    {
    	$this->sNomCommande	= 'clients_a_servir_import_fic';
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console clients_a_servir_import_fic <<fic_code>> Expl : php app/console clients_a_servir_import_fic JADE_CAS
        $this
            ->setDescription('Importation des fichiers des clients a servir')
            ->addArgument('fic_code', InputArgument::REQUIRED, 'Code source de donnees')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
    	$this->oLog->info(date("d/m/Y H:i:s : ")."Debut Importation des fichiers des clients a servir - Commande : ".$this->sNomCommande);
        
    	
        $em    = $this->getContainer()->get('doctrine')->getManager();
        
    	$sFicCode 	= $input->getArgument('fic_code');	// Expl : JADE_CAS <=> Importation des clients a servir venant de JADE
        $oString	= new StringLocal('');  
        
    	// Repertoire ou l'on recupere les fichiers a traiter
        $this->sRepTmp	= $this->cree_repertoire($this->sRepFichiersPrinc.'/'.$this->getContainer()->getParameter('SOUSREP_FICHIERS_TMP').'/'.$this->getContainer()->getParameter('SOUSREP_FICHIERS_CLIENTS_A_SERVIR').'/'.$sFicCode);
        

        // Repertoire Backup Local
    	$this->sRepBkpLocal	= $this->cree_repertoire($this->sRepFichiersPrinc.'/'.$this->getContainer()->getParameter('SOUSREP_FICHIERS_BKP').'/'.$this->getContainer()->getParameter('SOUSREP_FICHIERS_CLIENTS_A_SERVIR').'/'.$sFicCode);
    	
        // Recuperation des parameters concernant le FTP et les fichiers a recuperer
        $oFicChrgtFichiersBdd = $this->getContainer()->get('doctrine')
                        ->getRepository('AmsFichierBundle:FicChrgtFichiersBdd')
                        ->getParamFluxByCode($sFicCode);
        if(is_null($oFicChrgtFichiersBdd))
        {
            $this->oLog->erreur("Le flux ".$sFicCode." n'est pas parametre dans 'fic_chrgt_fichiers_bdd'", E_USER_ERROR);
            throw new \Exception("Identification de flux introuvable dans 'fic_chrgt_fichiers_bdd'");
        }
        
        // Connexion au FTP
        $aFicTmp    = array(); // Fichiers importes du FTP
        $aFicNonEcrasesTmp    = array(); // Fichiers en cours de traitement et non ecrases
        try 
        {
            $oParamFTP   = $this->getContainer()->get('doctrine')
                            ->getRepository('AmsFichierBundle:FicFtp')->findOneByCode($oFicChrgtFichiersBdd->getCode());
            
            $srv_ftp    = $this->getContainer()->get('ijanki_ftp');
            
            $sRegexFic  = $oString->transformeRegex($oFicChrgtFichiersBdd->getRegexFic());
                        
            $srv_ftp->connect($oParamFTP->getServeur());
            $srv_ftp->login($oParamFTP->getLogin(), $oParamFTP->getMdp());
            $srv_ftp->chdir($oParamFTP->getRepertoire());
            $aTousFicFTP = $srv_ftp->nlist('.');
            if(!empty($aTousFicFTP))
            {
                foreach($aTousFicFTP as $sFicV)
                {
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
            $this->oLog->erreur("Probleme d'acces au FTP ".$this->aFichierFluxParam['FTP']->getSrv().' : '.$e->getMessage(), E_USER_ERROR);
        }
        
    	
    	$this->oLog->info(date("d/m/Y H:i:s : ")."Fin Importation des fichiers des clients a servir - Commande : ".$this->sNomCommande);
    	
        return;
    }
}
