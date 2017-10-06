<?php

namespace Ams\AdresseBundle\Command;

use Ams\SilogBundle\Command\GlobalCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Doctrine\DBAL\DBALException;

/**
 * @author ydieng
 * 
 * Geocodage des adresses d'un depôt
 *
 */
class GeocodageDepotCommand extends GlobalCommand
{
    protected function configure()
    {
    	$this->sNomCommande	= 'geocodage_depot';
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console geocodage_depot  Expl : php app/console geocodage_depot
        $this->setDescription('Geocodage des adresses pour un depot')
                ->addOption('scope',null, InputOption::VALUE_OPTIONAL, 'Geocoder tous les depots ',false);
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
        $sAction= $input->getOption('scope');
        
        if ($sAction == 'all'){
            $this->oLog->info('Début de correction du géocodage des adresses de TOUS les depôts.');
        }
        else{
            $this->oLog->info('Début de correction du géocodage des adresses des depôts et activation du point de départ .');
        }
    	
        $srvGeocodage    = $this->getContainer()->get('geocodage');
        try {
             $em = $this->getContainer()->get('doctrine')->getManager();
             if ($sAction == 'all'){
                 $aODepots = $em->getRepository('AmsSilogBundle:Depot')->findAll();
             }
             else{
                 if ((int)$sAction > 0){
                     // Modification d'un dépot en particulier
                     $aODepots = $em->getRepository('AmsSilogBundle:Depot')->findById((int)$sAction);
                 }
                 else{
                     $aDepots = $em->getRepository('AmsSilogBundle:Depot')->getDepotNotStartPoint();
                     $iPtDepart = 1;
                 }
             }
             
             // Transformation des tableaux d'objets en tableaux
             if (isset($aODepots) && !empty($aODepots)){
                 $aDepots = array();
                 $iPtDepart = 1;
             }
                 foreach ($aODepots as $oDepot){
                     $aDepots[] = array(
                         'ville' => $oDepot->getCommune()->getLibelle(),
                         'cp' =>  $oDepot->getCommune()->getCp(),
                         'adresse' =>  $oDepot->getAdresse(),
                         'libelle' =>  $oDepot->getLibelle(),
                         'depotId' =>  $oDepot->getId()
                     );
             }
             
             if (!empty($aDepots)){
                 $iNbSuspects = count($aDepots);
                 foreach ($aDepots as $depot){
                     $aDep = array(
                         "City" => $depot['ville'],
                         "PostalCode" => $depot['cp'],
                         "AddressLine" => $depot['adresse']
                     );
                     $coords = $srvGeocodage->geocode($aDep);
                     if (!empty($coords['GeocodedAddress'])) {
                         $em->getRepository('AmsSilogBundle:Depot')->updateCoords($depot['depotId'], $coords['GeocodedAddress'][0]->X, $coords['GeocodedAddress'][0]->Y, $iPtDepart);
                         $this->oLog->info('Géocodage du dépôt '.$depot['libelle'].' ('.$depot['adresse'].' '.$depot['cp'].' - '.$depot['ville'].') avec '.$coords['GeocodedAddress'][0]->X.', '.$coords['GeocodedAddress'][0]->Y);
                     }
                     else {
                         $this->oLog->info('Géocodage impossible pour le dépôt '.$depot['libelle'].' ('.$depot['adresse'].' '.$depot['cp'].' - '.$depot['ville'].')');
                     }
                 }
             }
             else{
                 $this->oLog->info('Aucun depôt à géocoder trouvé.');
             }
        } catch (DBALException $DBALException) {
            $this->oLog->erreur($DBALException->getMessage(), $DBALException->getCode(), $DBALException->getFile(), $DBALException->getLine());
        }
    	
    	$this->oLog->info('Fin de correction des adresses des depôts. ');
    	
        return;
    }
    
    
}

