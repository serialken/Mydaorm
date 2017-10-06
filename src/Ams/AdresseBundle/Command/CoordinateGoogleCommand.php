<?php 
namespace Ams\AdresseBundle\Command;

//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Doctrine\DBAL\DBALException;
use Ams\WebserviceBundle\Exception\GeocodageException;

use Ams\SilogBundle\Command\GlobalCommand;

/**
 * 
 * "Command" de geocodage des adresses RNVP
 * 
 * @author aandrianiaina
 *
 */
class CoordinateGoogleCommand extends GlobalCommand
{
    
    protected function configure()
    {
    	$this->sNomCommande	= 'google_geocodage';
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console geocodage  Expl : php app/console geocodage
        $this
            ->setDescription('Geocodage des adresses RNVP par google')
            ->addOption('lon',null, InputOption::VALUE_OPTIONAL, 'Longitude')
            ->addOption('lat',null, InputOption::VALUE_OPTIONAL, 'Latitude')
            ->addOption('trn',null, InputOption::VALUE_OPTIONAL, 'Code tournee')
            ->addOption('date',null, InputOption::VALUE_OPTIONAL, 'yyyy-mm-dd')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
    	$this->oLog->info('Debut Geocodage de toutes les "adresse" non encore geocodees '.$this->sNomCommande);
        
    	$lat = $input->getOption('lat');
    	$lon = $input->getOption('lon');
    	$trn = $input->getOption('trn');
    	$date = $input->getOption('date');
        $sTournee = '042NXK002VE';
           
        $em = $this->getContainer()->get('doctrine')->getManager();
        $aCoordinate = $em->getRepository('AmsAdresseBundle:TourneeDetail')->getCoordinateByTourneeCode($sTournee);
        /** API GOOGLE LIMIT 8 WAYPOINT :( **/
        $file = 'https://maps.googleapis.com/maps/api/directions/xml?origin=2.33333348.866667,FR&destination=Paris,FR&waypoints=optimize:true';
//        $file = 'https://maps.googleapis.com/maps/api/directions/xml?origin=2.33333348.866667,FR&destination=Paris,FR&waypoints=optimize:true|2.655400048.5421050,FR|3.890687143.6064205,FR|2.632122048.5340310,FR|1.444209043.6046520,FR';
        foreach($aCoordinate as $key=>$data){
            if($key < 8)
                $file.= '|'.$data['longitude'].$data['latitude'].',FR';
        }
        $xml=simplexml_load_file($file) or die("Error: Cannot create object");
        $aOrder = (array) $xml->route->waypoint_index;
        /** UPDATE TOURNEE DETAIL **/
        foreach($aCoordinate as $key=>$data){
            $ordre = (int)$aOrder[$key] + 1 .' ';
            $em->getRepository('AmsAdresseBundle:TourneeDetail')->updateOrderTourneeGoogleApi($sTournee,$ordre,$data['longitude'],$data['latitude']);
        }
    	$this->oLog->info('Fin Geocodage');
    	
        return;
    }
}
