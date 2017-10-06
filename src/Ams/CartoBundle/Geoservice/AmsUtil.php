<?php
/*
 * @author Jean-Baptiste
 */
namespace Ams\CartoBundle\GeoService;
use \Symfony\Component\DependencyInjection\ContainerAware;
use Ams\AdresseBundle\Entity\Adresse;

class AmsUtil extends ContainerAware {

	public function reversegeocoding($lon, $lat) {
		$url = $this->container->getParameter("GEOC_WS_RGEOCOD_BASE_URL");
		$maxDistance = $this->container->getParameter("GEOC_WS_RGEOCOD_MAX_DISTANCE");
		$maxCandidate = $this->container->getParameter("GEOC_WS_RGEOCOD_MAX_CANDIDATE");
		$srs = $this->container->getParameter("GEOC_WS_RGEOCOD_SRS");

		$urlJSON = $url . $lon . ',' . $lat . '&maxDistance=' . $maxDistance . '&maxCandidates=' . $maxCandidate . '&srs=' . $srs;
		$json = file_get_contents($urlJSON);
		return $json;
    }
    
	function getAdresseByCoordinate($lon,$lat){
		$key = 0;
		$ws = json_decode($this->reversegeocoding($lon,$lat));
		if(!$ws->listReverseGeocodingResults[$key]->addresses)
			return false;
		$sZipCode   = $ws->listReverseGeocodingResults[$key]->addresses[$key]->zipCode;
		$sCity      = $ws->listReverseGeocodingResults[$key]->addresses[$key]->cityName;
		$sAdresse   = $ws->listReverseGeocodingResults[$key]->addresses[$key]->streetLine;
		return array('ville'=>$sCity,'cp'=>$sZipCode,'adresse'=>$sAdresse);
	}
	
	function getInsee($lon,$lat){
		$aAdresse = $this->getAdresseByCoordinate($lon,$lat);
		if(!$aAdresse) return false;
		$em = $this->container->get('doctrine.orm.entity_manager');
		$search = ($aAdresse['cp'] >= 75000 && $aAdresse['cp'] <=75999)? intval($aAdresse['cp']) : $aAdresse['ville'];
		if(is_int($search)){
			$query = $em->getRepository('AmsAdresseBundle:Commune')->getInseeByZip($search);
		}
		else{
			$search = str_replace('-',' ',$search);
			$query = $em->getRepository('AmsAdresseBundle:Commune')->getInseeByLibelle($search);
		}
		return array('insee'=>$query['insee'],'zipcode'=>$aAdresse['cp']);
	}
	
	
	/** 
	 * DETERMINATION DU POINT LE PLUS PROCHE
	 * AVEC L'UTILISATION WS SEARCHAROUND
	 **/
	function getMinPoint($lon,$lat,$codeJour){
		$data = $this->getInsee($lon, $lat);
		$insee = $data['insee'];
		$zipcode = $data['zipcode'];
		if(!$insee) return false;
		$em = $this->container->get('doctrine.orm.entity_manager');
		$aCoordinates = $em->getRepository('AmsAdresseBundle:TourneeDetail')->getPointsByTournee($insee,$codeJour,$zipcode);
		if(!$aCoordinates) return false;
		
		$geoservice = $this->container->get('ams_carto.geoservice');
		$atarget = array(
				'id' => 'target',
				'x' => $lon,
				'y' => $lat,
		);
		 
		$aPoints = array();
		foreach($aCoordinates as $point){
			$aPoints[] = array(
					'id' => $point['id'],
					'x'  => $point['lon'],
					'y'  => $point['lat']
			);
		}
		 
		$aOptions= array(
				'Projection' => 'WGS84'
		);
		 
		$classement = $geoservice->callSearchAround($atarget, $aPoints, $aOptions);
		$oMinDistance = $classement->SearchAroundResult[0];
		 
		/**  DETERMINATION DU POINT LE PLUS PROCHE **/
		foreach($classement->SearchAroundResult as $result){
			if($oMinDistance->Distance > $result->Distance)
				$oMinDistance = $result;
		}
		return array('point'=>$oMinDistance,'insee'=>$insee);
	}
	
	
	function insertPointTournee($date){
		$em = $this->container->get('doctrine.orm.entity_manager');
		$day = date('w', strtotime($date));
		$oRefDay = $em->getRepository('AmsReferentielBundle:RefJour')->find($day +1); 

		$error =0;
		$success = 0;
		$aCoordinateCasl = $em->getRepository('AmsAdresseBundle:TourneeDetail')->getCoordinatesCasl($date);
// 		var_dump($aCoordinateCasl);exit;
		
		/** X = longitude; Y = latitude **/
		foreach($aCoordinateCasl as $data){
	    $aMinPoint = $this->getMinPoint($data['geox'], $data['geoy'],$oRefDay->getCode());
	   	$insee = $aMinPoint['insee'];
	   	$aMinPoint = $aMinPoint['point'];
			$aData = array();

	    if($aMinPoint){
				$pointMin = $em->getRepository('AmsAdresseBundle:TourneeDetail')->find($aMinPoint->Id);
				$sTourneeCode = $pointMin->getmodeleTourneeJourCode();
				$idModeleTournee = $em->getRepository('AmsModeleBundle:ModeleTourneeJour')->findByCodeDateValid($sTourneeCode,$date); 
				$oTournee = $em->getRepository('AmsAdresseBundle:TourneeDetail')->findTourneeIdByTourneeCode($sTourneeCode);
				$ordre = $pointMin->getOrdre();

				$geoservice = $this->container->get('ams_carto.geoservice');
				$param = array('Before' => $pointMin->getId(), 'Longitude'=> $data['geox'], 'Latitude' => $data['geoy']);
				$geoservice_time_1= $geoservice->callRouteService($oTournee,$param);
				$param = array('After' => $pointMin->getId(),'Longitude'=> $data['geox'], 'Latitude' => $data['geoy']);
				$geoservice_time_2 = $geoservice->callRouteService($oTournee,$param);
				$min = array($geoservice_time_1->ROUTE->Time,$geoservice_time_2->ROUTE->Time);
				$key = array_keys($min,min($min));
				$geoservice = ($key[0] == 0)? $geoservice_time_1 : $geoservice_time_2;
				$ordre = ($key[0] == 0)? $ordre : $ordre + 1;
				 
				$nb_stop = count($geoservice->ROUTE->WAYPOINT);
				$sTourneeTime = $geoservice->ROUTE->WAYPOINT[($nb_stop -1)]->FoundTime;
				//     	function aDuree($iNbLivraison,$iNbArret,$sTourneeTime){
				 
				$aData = array(
						'modele_tournee_jour_id'		=> $idModeleTournee->getId(),
						'id_casl'										=> $data['id'],
						'ordre' 										=> $ordre,
						'longitude' 								=> $data['geox'],
						'latitude' 									=> $data['geoy'],
						'modele_tournee_jour_code' 	=> $sTourneeCode,
						'num_abonne_id' 						=> $data['abonne_soc_id'],
						'nb_stop' 									=> $nb_stop,
						'flux_id'										=> $data['flux_id'],
						'soc'												=> $data['soc_code_ext'],
						'insee'											=> $insee,
						'num_abonne_soc'						=> $data['numabo_ext'],
						'titre'											=> $data['prd_code_ext'],
						'debut_plage_horaire'				=> $pointMin->getDebutplageHoraire()->format( 'H:i:s' ),  
						'fin_plage_horaire'					=> $pointMin->getFinplageHoraire()->format( 'H:i:s' ),
						'duree_viste_fixe'					=> $pointMin->getDureeVisteFixe()->format( 'H:i:s' ),
				);
				$em->getRepository('AmsAdresseBundle:TourneeDetail')->geocodeInsertData($aData);
	    }
	    var_dump($aData);
	    if(empty($aData))$error++; else $success++;
			echo 'ERROR=>'.$error. ' SUCCESS=>'.$success."\n";
		}
	}
}
