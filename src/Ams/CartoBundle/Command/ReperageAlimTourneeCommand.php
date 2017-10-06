<?php 
namespace Ams\CartoBundle\Command;

//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Doctrine\DBAL\DBALException;

use Ams\SilogBundle\Command\GlobalCommand;

/**
 * 
 * "Command" classement des clients a reperer dans une tournee 
 * 
 * Pour executer, faire : 
 *              php app/console reperage_alim_tournee --env=<<env>> 
 *      Expl :  php app/console reperage_alim_tournee --env=dev
 *              php app/console reperage_alim_tournee --env=prod
 * 
 * 
 * @author aandrianiaina
 *
 */
class ReperageAlimTourneeCommand extends GlobalCommand
{
    private $aFichierFluxParam;
    private $sRepTmp;
    private $sRepBkpLocal;
    protected function configure()
    {
    	$this->sNomCommande	= 'reperage_alim_tournee';
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console reperage_alim_tournee  Expl : php app/console reperage_alim_tournee --env=prod
        $this
            ->setDescription('Classement des clients a reperer dans une tournee')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
    	$this->oLog->info(date("d/m/Y H:i:s : ")."Debut Classement des clients a reperer dans une tournee - Commande : ".$this->sNomCommande);
                
        $em    = $this->getContainer()->get('doctrine')->getManager();
        
    	// Recuperation des parameters concernant le FTP et les fichiers a recuperer
        $oReperageRepo = $this->getContainer()->get('doctrine')
                        ->getRepository('AmsDistributionBundle:Reperage');
        
        $srv_ams_carto_geoservice = $this->getContainer()->get('ams_carto.geoservice');
        
        try
        {
            $oReperageRepo ->classementAuto($srv_ams_carto_geoservice);
        }
        catch (DBALException $DBALException) {
            $this->oLog->erreur($DBALException->getMessage(), $DBALException->getCode(), $DBALException->getFile(), $DBALException->getLine());
        }
    	
    	$this->oLog->info(date("d/m/Y H:i:s : ")."Fin Classement des clients a reperer dans une tournee - Commande : ".$this->sNomCommande);
    	
        return;
    }
}
