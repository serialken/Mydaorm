<?php 
namespace Ams\AdresseBundle\Command;

//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Doctrine\DBAL\DBALException;
use Ams\WebserviceBundle\Exception\GeocodageException;

use Ams\SilogBundle\Command\GlobalCommand;

/**
 * RENSEIGNE LES "STOP_LIVRAISON_POSSIBLE" A UNE ADRESSE DISTINCT DANS LA TABLE ADRESSE RNVP
 * GEOCODAGE VIA GOOGLE DES INCOHERENCES DANS ADRESSE RNVP 
 * @author KJeanBaptiste
 */
class RepairAdresseRnvpCommand extends GlobalCommand
{
    
    protected function configure()
    {
        // php app/console repair_adresse_rnvp --repair_address_depot true --dateMin 2015-04-06 --dateMax 2015-04-15 --depot 19 --env local
        // php app/console repair_adresse_rnvp --stop_livraison_possible true --env local
        // php app/console repair_adresse_rnvp --repair_trn true --date 2014-11-14 --trn 042NXK041VE --env local
        //php app/console repair_adresse_rnvp --tmp true --env local
        /** NE PAS UTILISER POUR L INSTANT **/
        // php app/console repair_adresse_rnvp --repair_coordinate true --trn 042NXK041VE --env local  
    	$this->sNomCommande	= 'repair_adresse_rnvp';
    	$this->setName($this->sNomCommande)
             ->addOption('stop_livraison_possible',null, InputOption::VALUE_OPTIONAL, 'true or false')
             ->addOption('repair_coordinate',null, InputOption::VALUE_OPTIONAL, 'true or false')
             ->addOption('repair_trn',null, InputOption::VALUE_OPTIONAL, 'true or false')
             ->addOption('repair_address_depot',null, InputOption::VALUE_OPTIONAL, 'true or false')
             ->addOption('tmp',null, InputOption::VALUE_OPTIONAL, 'true or false')
             ->addOption('trn',null, InputOption::VALUE_OPTIONAL, 'code tournee')
             ->addOption('date',null, InputOption::VALUE_OPTIONAL, 'date distrib')
             ->addOption('dateMax',null, InputOption::VALUE_OPTIONAL, 'date max')
             ->addOption('dateMin',null, InputOption::VALUE_OPTIONAL, 'date min')
             ->addOption('depot',null, InputOption::VALUE_OPTIONAL, 'depot id')
             ->setDescription('Harmonisation de la table adresse_rnvp')
        ;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
        /** ATTRIBUE UN STOP LIVRAISON POSSIBLE A UNE ADRESSE UNIQUE**/
        
        if($input->getOption('tmp') == TRUE){
            $em = $this->getContainer()->get('doctrine')->getManager();
            $datas = $em->getRepository('AmsAdresseBundle:AdresseRnvp')->getResultTmp();
            $this->repairCoordinateGoogle($datas);
        }
        
        if($input->getOption('repair_address_depot') == TRUE){
            $depotId = $input->getOption('depot'); 
            $dateMin = $input->getOption('dateMin'); 
            $dateMax = $input->getOption('dateMax');
            $this->TraitmentImport($dateMin,$dateMax,$depotId);
        }
            
        if($input->getOption('repair_trn') == TRUE){
            $this->repairByTourneeJour();
        }
        
        if($input->getOption('stop_livraison_possible') == TRUE){
            $this->repairAdresseRnvp();
        }
        
        if($input->getOption('repair_coordinate') == TRUE){
            $tournee = ($input->getOption('trn'))? $input->getOption('trn') : false;
            $this->sameCoordsDifferentAddress($tournee);
            $this->sameAddressDifferentCoords();
        }

    	$this->oLog->info('Fin de traitement');
        return;
    }
    
    
    public function TraitmentImport($dateMin,$dateMax,$depotId){
        $em = $this->getContainer()->get('doctrine')->getManager();
        $aAdr = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getRejectedAddressByDepot($depotId,$dateMin,$dateMax);
        $this->repairCoordinateAdresseRnvp($aAdr,true);
        $aAdrNormalise = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getRejectedNormaliseAddressByDepot($depotId);
        $this->repairCoordinateAdresseRnvp($aAdrNormalise,true);
        /** ON MET A JOUR LES ETAT DES ADRESSES A NORMALISER "CORRIGER" PAR GOOGLE**/
        $em->getRepository('AmsAdresseBundle:AdresseRnvp')->setAddressNormaliseEtat();
    }
    
    /**
     * ADRESSE RECUPERER DANS CASL
     */
    public function repairByTourneeJour(){
        $date = '2015-04-02';
        $aTournee = array('028NJB002JE','028NJB002JE','028NJT065JE','028NJC014JE','029NKB013JE');
        $this->oLog->info('Debut du traitement geocodage par google des adresses dans casl par tournee et par jour');
        $em = $this->getContainer()->get('doctrine')->getManager();
        
        /** GEOCODAGE GEOCONCEPT **/
        foreach($aTournee as $tournee){
            $aAdr = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getAddressByTourneeJour($tournee,$date);
            $this->repairCoordinateAdresseRnvp($aAdr);
        }
        
        /** GEOCODAGE GOOGLE **/
        foreach($aTournee as $tournee){
            $aAdr = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getAddressByTourneeJour($tournee,$date,true);
            $this->repairCoordinateAdresseRnvp($aAdr,true);
        }
    }
    
    public function sameCoordsDifferentAddress($tournee){
        $this->oLog->info('Debut du traitement geocodage par google des adresses differentes ayant des coordonnees semblable ');
        $em = $this->getContainer()->get('doctrine')->getManager();
        $aCoordinate = $em->getRepository('AmsAdresseBundle:AdresseRnvp')->getVariousAddressCoordinate($tournee);
        foreach($aCoordinate as $coords){
            $datas = $em->getRepository('AmsAdresseBundle:AdresseRnvp')->getAddressByCoordinate($coords['geox'],$coords['geoy']);
            $this->repairCoordinateAdresseRnvp($datas);
        }
    }
    
    public function sameAddressDifferentCoords(){
        $this->oLog->info('Debut du traitement geocodage par google des adresses semblable ayant des coordonnees differentes ');
        $em = $this->getContainer()->get('doctrine')->getManager();
        $datas = $em->getRepository('AmsAdresseBundle:AdresseRnvp')->getVariousCoordinateAddress();
        $this->repairCoordinateAdresseRnvp($datas);
    }
    
    public function repairCoordinateAdresseRnvp($datas,$GeocodeGoogle = false){
        if (!$GeocodeGoogle) {
            $this->repairCoordinateGeoconcept($datas);
        } else {
            $this->repairCoordinateGoogle($datas);
        }
    }
    
    public function repairCoordinateGoogle($datas){
        $em = $this->getContainer()->get('doctrine')->getManager();
        $aGeoType = array('ROOFTOP'=>4,'RANGE_INTERPOLATED' => 3,'GEOMETRIC_CENTER' => 2,'APPROXIMATE' => 1);
        foreach($datas as $data){
            $file = 'https://maps.googleapis.com/maps/api/geocode/xml?address='.$data['adresse'].' '.$data['cp'].' '.$data['ville'].',+FR';
            $xml=simplexml_load_file($file);
            usleep(250000);
            if((string)$xml->status == 'OVER_QUERY_LIMIT'){
                die($xml->error_message);
            }
            if((string)$xml->status == 'OK'){
                $lat = (string)$xml->result->geometry->location->lat;
                $lng = (string)$xml->result->geometry->location->lng;
                $geoType = (string)$xml->result->geometry->location_type ;
                $iGeoType = $aGeoType[$geoType];
                $iGeoEtat = ($aGeoType[$geoType] >= 3)? 1 : 0;
                $em->getRepository('AmsAdresseBundle:AdresseRnvp')->updateCoordsByAddress(trim($data['adresse']),trim($data['cp']),trim($data['ville']),$lng,$lat,$iGeoEtat,99,$iGeoType);
            }
        }
        $this->oLog->info('TRAITEMENT GOOGLE TERMINER POUR UNE TOURNEE');
    }
    
    public function repairCoordinateGeoconcept($datas){
        $em = $this->getContainer()->get('doctrine')->getManager();
        foreach($datas as $data){
            $srvGeocodage    = $this->getContainer()->get('geocodage');
            $aAdr = array(
                'City'         => $data['ville'], 
                'PostalCode'   => $data['cp'], 
                'AddressLine'  => $data['adresse']
            );
            $ws_geoconcept = $srvGeocodage->geocodeGeoconcept($aAdr);
            $lng = $ws_geoconcept['X'];
            $lat = $ws_geoconcept['Y'];
            $geoEtat = ($ws_geoconcept['GeocodeScore'] > 18 && $ws_geoconcept['GeocodeType'] > 2) ? 1 : 0;
            $em->getRepository('AmsAdresseBundle:AdresseRnvp')->updateCoordsByAddress(trim($data['adresse']),trim($data['cp']),trim($data['ville']),$lng,$lat,$geoEtat,$ws_geoconcept['GeocodeScore'],$ws_geoconcept['GeocodeType']);
        }
        $this->oLog->info('TRAITEMENT GEOCONCEPT TERMINER POUR UNE TOURNEE');
    }
    
    public function repairAdresseRnvp(){
        $this->oLog->info('insertion d\'un stop livraison unique par adresse,et mise a jour des points de livraison dans (reperage,adresse,casl,tournee_detail)');
        $em = $this->getContainer()->get('doctrine')->getManager();
        $aIssetComplementAddress = $em->getRepository('AmsAdresseBundle:AdresseRnvp')->getIssetComplementAddress();
        foreach($aIssetComplementAddress as $key=>$data){
            /** TEST SI POUR LES DIFFERENTES ADRESSE STOP LIVRAISON EXISTE **/
            $aExistStopLivraison = $em->getRepository('AmsAdresseBundle:AdresseRnvp')->getExistStopLivraison($data['adresse'],$data['cp']);
            $idExclu = $this->getExistStopLivraison($aExistStopLivraison, $data);
            if($idExclu == 0) continue;
            
            /** ON SUPPRIME TOUS LES STOP_LIVRAISON_POSSIBLE POUR L ADRESSE HORMIS CELUI CORRESPONDANT AUX CRITERES**/
            $em->getRepository('AmsAdresseBundle:AdresseRnvp')->disableStopLivraison($idExclu,$data['adresse'],$data['cp']);
            
            /** RECUPERATION ID DES DOUBLONS DANS ADRESSE RNVP **/
            $aDuplicateAdresseId = $em->getRepository('AmsAdresseBundle:AdresseRnvp')->getDuplicateAddressId($idExclu,$data['adresse'],$data['cp']);
            $sDuplicateAdresseId = implode(',', array_map(function ($data) {
                return $data['id'];
            }, $aDuplicateAdresseId));
            
            /** UPDATE POINT_LIVRAISON TABLE ADRESSE **/
            $em->getRepository('AmsAdresseBundle:Adresse')->UpdatePointLivraisonScriptRepair($idExclu,$sDuplicateAdresseId);
            /** UPDATE POINT_LIVRAISON TABLE REPERAGE **/
            $em->getRepository('AmsDistributionBundle:Reperage')->UpdatePointLivraisonScriptRepair($idExclu,$sDuplicateAdresseId);
            /** UPDATE POINT_LIVRAISON TABLE TOURNEE DETAIL **/
            $em->getRepository('AmsAdresseBundle:TourneeDetail')->UpdatePointLivraisonScriptRepair($idExclu,$sDuplicateAdresseId);
            /** UPDATE POINT_LIVRAISON TABLE CLIENT A SERVIR LOGIST **/
            $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->UpdatePointLivraisonScriptRepair($idExclu,$sDuplicateAdresseId);
        }
    }
    
    public function getExistStopLivraison($aExistStopLivraison,$data){
        $em = $this->getContainer()->get('doctrine')->getManager();
        if($aExistStopLivraison){
            if($aExistStopLivraison['geo_etat'] > 0)
               $em->getRepository('AmsAdresseBundle:AdresseRnvp')->enableStopLivraison($aExistStopLivraison['id']);
            else return 0 ;

            return $aExistStopLivraison['id'];
        }
        else{
            /** ON CREE UNE NOUVELLE ENTRER DANS LA TABLE ADRESSE_RNVP EN OMETTANT COMPLEMENT ADRESSE ET LIEU-DIT**/
            return $em->getRepository('AmsAdresseBundle:AdresseRnvp')->createStopLivraison($data);
        }
    }
}
