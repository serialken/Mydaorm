<?php 
namespace Ams\AdresseBundle\Command;

//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Doctrine\DBAL\DBALException;

use Ams\SilogBundle\Command\GlobalCommand;

/**
 * 
 * Test de coordonnées WGS84
 * 
 * @author maadelise
 *
 */
class TestCoordsCommand extends GlobalCommand
{
    
    protected function configure()
    {
    	$this->sNomCommande	= 'test_coords';
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console test_coords  Expl : php app/console test_coords
        $this
            ->setDescription('Teste des coordonnées GPS')
            ->addArgument('geo_x', InputArgument::REQUIRED, 'La longitude')
            ->addArgument('geo_y', InputArgument::REQUIRED, 'La latitude')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
    	$this->oLog->info('Lancement du test de coordonnées '.$this->sNomCommande);
        
        $fGeoX= $input->getArgument('geo_x');	// Ex : 2.298179
    	$fGeoY = $input->getArgument('geo_y');	// Ex : 48.876713
    	
        $geoservice = $this->getContainer()->get('ams_carto.geoservice');
        
        if ($geoservice->testPointGPS($fGeoX, $fGeoY)){
            $this->oLog->info('Le point '.$fGeoX.','.$fGeoY.' semble valide.');
        }
        else{
            $this->oLog->erreur('Le point '.$fGeoX.','.$fGeoY.' n\'est PAS valide!');
        }
        
        try {
             $em = $this->getContainer()->get('doctrine')->getManager();
        } catch (DBALException $DBALException) {
            $this->oLog->erreur($DBALException->getMessage(), $DBALException->getCode(), $DBALException->getFile(), $DBALException->getLine());
        }
    	
    	$this->oLog->info('Fin du test de coordonnees '.$this->sNomCommande);
    	
        return;
    }
}
