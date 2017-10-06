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
 * Réparation des adresses mal géocodées
 * 
 * @author maadelise
 *
 */
class GeocodageRepairCommand extends GlobalCommand
{
    
    protected function configure()
    {
    	$this->sNomCommande	= 'geocodage_reparation';
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console geocodage_reparation  Expl : php app/console geocodage_reparation
        $this
            ->setDescription('Reparation du géocodage des adresses RNVP')
                ->addOption('id', NULL, InputOption::VALUE_OPTIONAL, 'ID de l\'adresse RNVP a corriger', NULL)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
    	$this->oLog->info('Debut de correction du geocodage des adresses mal geocodees '.$this->sNomCommande);
        $srvGeocodage    = $this->getContainer()->get('geocodage');
        $em = $this->getContainer()->get('doctrine')->getManager();
        
        // Récupération de l'ID de l'adresse à réparer
        if ($input->getOption('id')){
            $aAdresses = $em->getConnection()->fetchAll('SELECT * from adresse_rnvp WHERE id='.(int)$input->getOption('id'));   
        }
        else{
            $aAdresses = $em->getRepository('AmsAdresseBundle:AdresseRnvp')->geocodageSuspect();
        }
    	
        try {
             if (!empty($aAdresses)){
                 $iNbSuspects = count($aAdresses);
                 $this->oLog->info($iNbSuspects.' adresses à réparer...');
                 foreach ($aAdresses as $adresse){
                     $aAdr = array(
                         "City" => $adresse['ville'],
                         "PostalCode" => $adresse['cp'],
                         "AddressLine" => $adresse['adresse']
                     );
                     $coords = $srvGeocodage->geocode($aAdr);
                     if (!empty($coords['GeocodedAddress'])){
                         $em->getRepository('AmsAdresseBundle:AdresseRnvp')->updateCoords($adresse['id'], $coords['GeocodedAddress'][0]);
                         $this->oLog->info('Tentative de reparation de l\'adresse RNVP '.$adresse['id'].' ('.$adresse['adresse'].' '.$adresse['cp'].' '.$adresse['ville'].') avec '.$coords['GeocodedAddress'][0]->X.','.$coords['GeocodedAddress'][0]->Y);
                     }
                     else{
                         $this->oLog->info('Réparation impossible de l\'adresse RNVP '.$adresse['id'].' ('.$adresse['adresse'].' '.$adresse['cp'].' '.$adresse['ville'].')');
                     }
                 }
             }
             else{
                 echo "Aucune adresse suspecte trouvée";
                 $this->oLog->info('Aucune adresse à réparer trouvée.');
             }
        } catch (DBALException $DBALException) {
            $this->oLog->erreur($DBALException->getMessage(), $DBALException->getCode(), $DBALException->getFile(), $DBALException->getLine());
        }
    	
    	$this->oLog->info('Fin de reparation du geocodage des adresses mal geocodees '.$this->sNomCommande);
    	
        return;
    }
}
