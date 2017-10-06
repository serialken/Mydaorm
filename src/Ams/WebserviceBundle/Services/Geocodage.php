<?php
namespace Ams\WebserviceBundle\Services;

use Ams\WebserviceBundle\Lib\SoapClientLocal;

use Ams\WebserviceBundle\Exception\GeocodageException;
use Ams\AdresseBundle\Entity\AdresseRnvp;

/**
 * Description of Geocodage
 *
 * @author aandrianiaina
 */
class Geocodage {
    private $oSoapClient;
    private $config;
    private $optionSoapClient;
    private $critereOK  = array(array('GeocodeType' => 4, "GeocodeScoreMin" => 18)) ;
    private $projection = "MAP";
    public function __construct($adresseWsdl, $loginWsdl, $mdpWsdl, $optionSoapClient=array(), $critereOK=array(), $projection="MAP") {
        $this->config = array();
        $this->oSoapClient = "";

        $this->config["ADRESSE_WSDL"] = $adresseWsdl;
        $this->config["LOGIN_WSDL"] 	= $loginWsdl;
        $this->config["MDP_WSDL"] 	= $mdpWsdl;  
        
        $this->optionSoapClient = $optionSoapClient;
        $this->setCritereOK($critereOK);
        $this->projection   = $projection;
    }
    
    
    public function setCritereOK($critereOK)
    {
        if(is_array($critereOK) && isset($critereOK[0]))
        {
            foreach($critereOK as $iK => $aArr)
            {
                if(!is_int($iK) || !isset($aArr['GeocodeType']) || !isset($aArr['GeocodeScoreMin']))
                {
                    echo "On reprend les criteres par defaut";
                    return false;
                }
            }
        }
        $this->critereOK = $critereOK;
    }
        
    /**
     * Prepare les donnees a geocoder 
     * 
     * @param array $adresse
     * @return array
     */
    private function prepareAdresseAGeocoder($adresse)
    {
        if(!is_array($adresse))
        {
            throw GeocodageException::formatTableau();
        }
        $attrObligatoire    = array('City', 'PostalCode', 'AddressLine');
        foreach($attrObligatoire as $attrOblig)
        {
            if(!isset($adresse[$attrOblig]))
            {
                $adresse[$attrOblig]   = '';
            }
        }
        
        if(!isset($adresse['CountryCode']))
        {
            $adresse['CountryCode'] = "fr";
        }
        $adresse["Projection"]  = $this->projection;
        
        return array("GeocodeRequest" => array("Address" =>  $adresse));
    }
    
    /**
     * Calcul de geocodage d'une adresse. Les cles obligatoires de ce tableau sont : 'City', 'PostalCode' & 'AddressLine'
     * Selon la valeur de $sWS, cette methode fait appel au webservice de GEOCONCEPT et/ou de GOOGLE
     * 
     * @param array $aAdr
     * @param string $sWS
     * @return array
     */
    public function geocode($aAdr, $sWS = 'GEOCONCEPT_GOOGLE') {
        switch ($sWS)
        {
            case 'GEOCONCEPT' :
                return $this->geocodeGeoconcept($aAdr);
                break;
            
            case 'GOOGLE' :
                return $this->geocodeGoogle($aAdr);
                break;
            
            default :
                $aResGeocodeGeoconcept  = $this->geocodeGeoconcept($aAdr);
                if(!empty($aResGeocodeGeoconcept) && $aResGeocodeGeoconcept['GeocodeEtat']=='OK' )
                {
                    return $aResGeocodeGeoconcept;
                }
                return $this->geocodeGoogle($aAdr,$aResGeocodeGeoconcept);
                break;
        }
        
        
        
    }
    
    /**
     * Calcul de geocodage faisant appel au webservice de GEOCONCEPT
     * @param type $aAdr
     * @return string
     * @throws Ams\WebserviceBundle\Exception\GeocodageException
     */
    public function geocodeGeoconcept($aAdr) {
        
        if($this->oSoapClient=="") // $this->oSoapClient n'est pas defini
        {
            $socket_context = stream_context_create(
                                                    array('http' => array('protocol_version'  => 1.0))
                                             );
            
            // Afin d'eviter les erreurs du genre "SoapClient::__doRequest(): send of 704 bytes failed with errno=32 Broken pipe"
            // Ajouter les deux options suivantes
            $this->optionSoapClient['stream_context']   = $socket_context;
            $this->optionSoapClient['keep_alive']   = false;
            
            $this->oSoapClient = new SoapClientLocal ($this->config["ADRESSE_WSDL"], $this->optionSoapClient);
        }
        
        $aGeocoder = $this->prepareAdresseAGeocoder($aAdr);
        
        try
        {	
            $retourGeocodage = $this->oSoapClient->__call ('Geocode', $aGeocoder);
        } catch (\SoapFault $f)
        {
            var_dump($f);
            throw GeocodageException::erreurWebservice("Erreur connexion WebService => ".serialize($aGeocoder)." - ".$f->getMessage(), E_USER_WARNING);
        }
        
        /*
        Etat geocodage :
            - AUCUNE_PROPOSITION : Aucune proposition => KO ()
            - GEO_VILLE : Géocodage au niveau de la ville => KO (1 : )
            - GEO_RUE : Géocodage au niveau de la rue => A_STATUER (2 : )
            - GEO_NUM_INTERPOLE_OK : Numéro interpolé => OK (3 : 17)
            - GEO_NUM_INTERPOLE_A_STATUER : Numéro interpolé => A_STATUER (3 : <17)
            - GEO_NUM_EXACT_OK : Géocodage au niveau de la rue => OK (4 : 15)
            - GEO_NUM_EXACT_A_STATUER : Géocodage au niveau de la rue, à statuer car la note n'est pas suffisante => A_STATUER (4 : <15)
         */
        /*
        Etat geocodage :
            - AUCUNE_PROPOSITION : Aucune proposition => KO ()
            - GEO_VILLE : Géocodage au niveau de la ville => KO (1 : )
            - GEO_RUE : Géocodage au niveau de la rue => A_STATUER (2 : )
            - GEO_NUM_INTERPOLE_OK : Numéro interpolé => OK (3 : 17)
            - GEO_NUM_INTERPOLE_A_STATUER : Numéro interpolé => A_STATUER (3 : <17)
            - GEO_NUM_EXACT_OK : Géocodage au niveau de la rue => OK (4 : 15)
            - GEO_NUM_EXACT_A_STATUER : Géocodage au niveau de la rue, à statuer car la note n'est pas suffisante => A_STATUER (4 : <15)
         */
        $aRetour    = array();
        if(!isset($retourGeocodage->GeocodedAddress))
        {
            $aRetour['GeocodeEtat'] = 'AUCUNE_PROPOSITION';
        }
        else
        {
            if((!is_array($retourGeocodage->GeocodedAddress)))
            {
                $aRetour['GeocodedAddress'][0]  = $retourGeocodage->GeocodedAddress;
            }
            else
            {
                $aRetour['GeocodedAddress'] = $retourGeocodage->GeocodedAddress;
            }
            
            // verification des etats de la premiere proposition
            $GeocodeTypeAVerifier   = $aRetour['GeocodedAddress'][0]->GeocodeType;
            $GeocodeScoreAVerifier   = $aRetour['GeocodedAddress'][0]->GeocodeScore;
            foreach($this->critereOK as $iK => $aCritere)
            {
                if($GeocodeTypeAVerifier==$aCritere['GeocodeType'] && $GeocodeScoreAVerifier>=$aCritere['GeocodeScoreMin'])
                {
                    $aRetour['GeocodeEtat'] = "OK";
                    break;
                }
            }            
            
            $aRetour['X']    = $aRetour['GeocodedAddress'][0]->X;
            $aRetour['Y']    = $aRetour['GeocodedAddress'][0]->Y;
            $aRetour['GeocodeType']    = $GeocodeTypeAVerifier;
            $aRetour['GeocodeScore']    = $GeocodeScoreAVerifier;
            if(!isset($aRetour['GeocodeEtat']))
            {
                $aRetour['GeocodeEtat']    = "A_STATUER";
            }
            $aRetour['WSSource']    = "GEOCONCEPT";
        }
        
        return $aRetour;
    }
    
    /**
     * Calcul de geocodage faisant appel au webservice de GOOGLE
     *  ROOFTOP              => RESULTAT PRECIS           => 4
     *  RANGE_INTERPOLATED   => AU NUMERO INTERPOLLE      => 3
     *  GEOMETRIC_CENTER     => CENTRER (ROUTE,REGION)    => 2
     *  APPROXIMATE          => RESULTAT APPROXIMATIF     => 1
     * 
     * @param array $aAdr
     * @return string
     * @throws Ams\WebserviceBundle\Exception\GeocodageException
     */
    public function geocodeGoogle($aAdr,$resultGeoconcept = false) {
        $aGeoType = array('ROOFTOP'=>4,'RANGE_INTERPOLATED' => 3,'GEOMETRIC_CENTER' => 2,'APPROXIMATE' => 1);
        $file = 'https://maps.googleapis.com/maps/api/geocode/xml?address='.(isset($aAdr['AddressLine']) ? $aAdr['AddressLine'] : '').' '.(isset($aAdr['PostalCode']) ? $aAdr['PostalCode'] : '').' '.(isset($aAdr['City']) ? $aAdr['City'] : '').',+FR';
        $xml=simplexml_load_file($file);
        // Avec GOOGLE, on n'a droit qu'a quatre appels par seconde
        usleep(250000);
        if((string)$xml->status == 'OVER_QUERY_LIMIT'){
            die($xml->error_message);
        }
        if((string)$xml->status == 'OK'){
            $tmp = array();
            $nbResult = 0;
            foreach($xml->result as $data){
                $address = $zipcode = '';
                foreach($data->address_component as $component){
                    if($component->type == 'locality')
                        $tmp[$nbResult]['City'] = (string)$component->long_name;
                    if($component->type == 'postal_code'){
                        $tmp[$nbResult]['PostalCode'] = (string)$component->long_name;
                        $zipcode = substr((string)$component->long_name, 0, 2);
                    }
                    if($component->type == 'street_number')
                        $address .= (string)$component->long_name.' ';
                    if($component->type == 'point_of_interest')
                        $address .= (string)$component->long_name.' ';
                    if($component->type == 'route')
                        $address .= (string)$component->long_name;
                }
                $tmp[$nbResult]['AddressLine'] = $address;
                $tmp[$nbResult]['CountryCode']= 'FR';
                $tmp[$nbResult]['X']= (string)$data->geometry->location->lng;
                $tmp[$nbResult]['Y']= (string)$data->geometry->location->lat;
                $tmp[$nbResult]['GeocodeScore']= '99';
                $geoType = (isset($aAdr['PostalCode']) && $zipcode == substr($aAdr['PostalCode'], 0, 2))?(string)$data->geometry->location_type : 'APPROXIMATE';
                $tmp[$nbResult]['GeocodeType']= $aGeoType[$geoType];
                $nbResult++;
            }    
            $lat = (string)$xml->result->geometry->location->lat;
            $lng = (string)$xml->result->geometry->location->lng;
            $geoType = (string)$xml->result->geometry->location_type;
            $geoEtat = ($aGeoType[$geoType] >= 3)? 'OK' : 'A_STATUER';
            
            return array(
                'X'               => $lng,
                'Y'               => $lat ,
                'GeocodeEtat'     => $geoEtat,
                'GeocodeScore'    =>'99',
                'GeocodeType'     =>$aGeoType[$geoType],
                'WSSource'        => AdresseRnvp::GOOGLE,
                'GeocodedAddress' => $tmp
            );
        }
        if($resultGeoconcept)
            return $resultGeoconcept;
        return array('GeocodeEtat' => 'AUCUNE_PROPOSITION');
    }
}
