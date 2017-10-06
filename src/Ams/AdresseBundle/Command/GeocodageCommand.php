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
class GeocodageCommand extends GlobalCommand
{
    
    protected function configure()
    {
    	$this->sNomCommande	= 'geocodage';
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console geocodage  Expl : php app/console geocodage
        $this
            ->setDescription('Geocodage des adresses RNVP')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
    	$this->oLog->info('Debut Geocodage de toutes les "adresse" non encore geocodees '.$this->sNomCommande);
        
    	
        $srvGeocodage    = $this->getContainer()->get('geocodage');
        $srvAdresseRnvp    = $this->getContainer()->get('adresse_rnvp');
        try {
            // Normalisation de toutes les nouvelles adresses 
            $srvAdresseRnvp->geocodeTouteAdresse();
        } catch (GeocodageException $GeocodageException) {
            $this->oLog->erreur($GeocodageException->getMessage(), $GeocodageException->getCode(), $GeocodageException->getFile(), $GeocodageException->getLine());
        } catch (DBALException $DBALException) {
            $this->oLog->erreur($DBALException->getMessage(), $DBALException->getCode(), $DBALException->getFile(), $DBALException->getLine());
        }
    	
    	$this->oLog->info('Fin Geocodage de toutes les "adresse" non encore geocodees '.$this->sNomCommande);
    	
        return;
    }
}
