<?php 
namespace Ams\AdresseBundle\Command;

//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Ams\SilogBundle\Command\GlobalCommand;

/**
 * 
 * "Command" de geocodage des adresses RNVP
 * 
 * @author aandrianiaina
 *
 */
class TestGeocodageCommand extends GlobalCommand
{
    
    protected function configure()
    {
    	$this->sNomCommande	= 'test_geocodage';
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console geocodage  Expl : php app/console test_geocodage
        $this
            ->setDescription('Geocodage des adresses RNVP')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
    	$this->oLog->info("Debut Test de Geocodage ".$this->sNomCommande);
        
    	
        $srvGeocodage    = $this->getContainer()->get('geocodage');
        
        $aAdr=array("City"=>"PLAILLY","PostalCode"=>"60128","AddressLine"=>"41 RUE DES LILAS");
        //$aAdr=array("City"=>"St ouen","PostalCode"=>"93","AddressLine"=>"25 Av michelet");
        //$aAdr=array("City"=>"Bagneux","PostalCode"=>"92","AddressLine"=>"52 ave aristide birand");
//        $aAdr=array("City"=>"EVRY CEDEX","PostalCode"=>"91004","AddressLine"=>"");
//        $aAdr=array("City"=>"ISSY LE MOULINEAUX","PostalCode"=>"92130","AddressLine"=>"43 rue Brossolette","Address"=>"43 rue Brossolette");
//        $aAdr=array("City"=>"Paris","PostalCode"=>"75013","AddressLine"=>"25 rue de Toldiac");
        $aAdr=array("City"=>"BOBIGNY","PostalCode"=>"93000","AddressLine"=>"31 AV DU PRESIDENT SALVADOR ALLENDE");
        //$aAdr=array("City"=>"Bagneux","PostalCode"=>"92220","AddressLine"=>"52 Avenue Aristide Briand");
        $aAdr=array("City"=>"ST CLOUD","PostalCode"=>"92210","AddressLine"=>"210 B BOULEVARD DE LA REPUBLIQUE");
        
        //$aAdr=array("City"=>"Bagneux","PostalCode"=>"92220","AddressLine"=>"52 Avenue Aristide Briand");
        
        $aRetourGeocodage = $srvGeocodage->geocode($aAdr, 'GOOGLE');
        
        print_r($aRetourGeocodage);     
        
        $d=$this->getContainer()->getParameter("GEOCODAGE_TYPE");
        print_r($d);
        if(isset($aRetourGeocodage["GeocodedAddress"]))
        {
        echo "\r\n---- ".$d[$aRetourGeocodage["GeocodedAddress"][0]->GeocodeType]." -----\r\n";
        }
        
        $aRetourGeocodage = $srvGeocodage->geocode($aAdr);
        
        print_r($aRetourGeocodage);               
    	
    	$this->oLog->info("Fin Test de Geocodage ".$this->sNomCommande);
    	
        return;
    }
}
