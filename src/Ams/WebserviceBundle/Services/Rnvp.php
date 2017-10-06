<?php 
namespace Ams\WebserviceBundle\Services;

use Ams\WebserviceBundle\Lib\SoapClientLocal;

use Ams\WebserviceBundle\Exception\RnvpLocalException;

use Ams\SilogBundle\Lib\StringLocal;

/**
 * 
 * Normalisation d'adresse
 * 
 * @author aandrianiaina
 *
 */
class Rnvp
{
	private $aRNVPConfig;
	private $oWSDL;
	private $oString;
	public function __construct($adresseWsdl, $loginWsdl, $mdpWsdl)
	{
		$this->aRNVPConfig = array();
		$this->oWSDL = "";
		$this->oString	= new StringLocal('');
                
                $this->aRNVPConfig["ADRESSE_WSDL"] = $adresseWsdl;
		$this->aRNVPConfig["LOGIN_WSDL"] 	= $loginWsdl;
		$this->aRNVPConfig["MDP_WSDL"] 	= $mdpWsdl;  
	}
        
        /**
         * Prepare les donnees a normaliser 
         * 
         * @param array $adresse
         * @return array
         */
        protected function adresseANormaliser($adresse)
        {
            if(!is_array($adresse))
            {
                throw RnvpLocalException::formatTableau();
            }
            $attrObligatoire    = array('volet1', 'volet2', 'volet3', 'volet4', 'volet5', 'cp', 'ville');
            $attrAbsent = array();
            foreach($attrObligatoire as $attrOblig)
            {
                if(!isset($adresse[$attrOblig]))
                {
                    $adresse[$attrOblig]   = '';
                }
            }
            return $adresse;
        }
	
	/**
         * Normaliser une adresse. $aAdr doit contenir les cles suivantes : volet1, volet2, volet3, volet4, volet4, volet5, cp et ville
         * 
         * @param array $aAdr
         * @param boolean $bRetourMajuscule
         * @return boolean/array
         */
	public function normalise($aAdr, $bRetourMajuscule=true)
	{
		if($this->oWSDL=="") // $this->oWSDL n'est pas defini
		{
                    $socket_context = stream_context_create(
                                                    array('http' => array('protocol_version'  => 1.0))
                                             );
            
                        // Afin d'eviter les erreurs du genre "SoapClient::__doRequest(): send of 704 bytes failed with errno=32 Broken pipe"
                        // Ajouter les options 'stream_context' & 'keep_alive'
			$this->oWSDL = new SoapClientLocal ($this->aRNVPConfig["ADRESSE_WSDL"], array(  'connection_timeout' => 6000, 
                                                                                                        'cache_wsdl' => WSDL_CACHE_BOTH,
                                                                                                        'stream_context'=>$socket_context,
                                                                                                        'keep_alive'=>false,
                                                                                                        'encoding'=>'UTF-8'
                                                                                                    )
                                                            );
		}
                
                // Preparation de l'adresse a normaliser
                $aAdr = $this->adresseANormaliser($aAdr);
                
                // Remplacement des "'", et '"' par une espace
                $aARemplacer    = array("'", '"');
                $sValRplct  = " ";
		$aAdr["volet1"] = str_replace($aARemplacer, $sValRplct, $aAdr["volet1"]);
                $aAdr["volet2"] = str_replace($aARemplacer, $sValRplct, $aAdr["volet2"]);
                $aAdr["volet3"] = str_replace($aARemplacer, $sValRplct, $aAdr["volet3"]);
                $aAdr["volet4"] = str_replace($aARemplacer, $sValRplct, $aAdr["volet4"]);
                $aAdr["volet5"] = str_replace($aARemplacer, $sValRplct, $aAdr["volet5"]);
                $aAdr["cp"] = str_replace($aARemplacer, $sValRplct, $aAdr["cp"]);
                $aAdr["ville"] = str_replace($aARemplacer, $sValRplct, $aAdr["ville"]);
		
		$volet1 = (isset($aAdr["volet1"]))?trim($this->oString->supprQuotesAccents_Majuscule($aAdr["volet1"])):"";
		$volet2 = (isset($aAdr["volet2"]))?trim($this->oString->supprQuotesAccents_Majuscule($aAdr["volet2"])):"";
		$volet3 = (isset($aAdr["volet3"]))?trim($this->oString->supprQuotesAccents_Majuscule($aAdr["volet3"])):"";
		$volet4 = (isset($aAdr["volet4"]))?trim($this->oString->supprQuotesAccents_Majuscule($aAdr["volet4"])):"";
		$volet5 = (isset($aAdr["volet5"]))?trim($this->oString->supprQuotesAccents_Majuscule($aAdr["volet5"])):"";
		$cp = (isset($aAdr["cp"]))?trim($this->oString->supprQuotesAccents_Majuscule($aAdr["cp"])):"";
		$ville = (isset($aAdr["ville"]))?trim($this->oString->supprQuotesAccents_Majuscule($aAdr["ville"])):"";
		
		$aRNVPEntreeAdr	= array (
									'pi_session'        => '-1', 
									'pi_commande'        => 'New', 
									'pi_user'          	=> $this->aRNVPConfig["LOGIN_WSDL"],
									'pi_password'      	=> $this->aRNVPConfig["MDP_WSDL"],
									'pi_codedossier'   	=> '1',
                                                                        /*'pi_codedossier'   	=> '0007',*/
									'pi_numfichier'    	=> '1',
									'pi_rsoc'           => '',	/*Raison sociale*/
									'pio_civ'           => '',
									'pio_nom'           => $volet1,
									'pio_prenom'        => '',
									'pio_cnom'          => $volet2,
									'pio_cadrs'         => $volet3,
									'pio_adresse'       => $volet4,
									'pio_lieudit'       => $volet5,
									'pio_cpville'       => $cp." ".$ville,
									/*'pio_pays'          => '',
									'po_tnp'            => '',
									'po_sex'            => '',
									'po_civlong'        => '',
									'po_cp'             => '',
									'po_ville'          => '',
									'po_insee'          => '',
									'po_cqtnp'          => '',
									'po_cqadrs'         => '',
									'po_risquerestru'  	=> '',
									'po_poidsmodif'    	=> '',
									'po_rejet'          => '',
									'po_etranger'       => '',*/
								);
		try
		{	
			$aRNVPSortieAdr = $this->oWSDL->__call ('Elfyweb_RNVP_Expert', array ($aRNVPEntreeAdr));
		} catch (\SoapFault $f)
		{
                    throw RnvpLocalException::erreurWebservice(50, "Erreur connexion WebService => ".serialize($aRNVPEntreeAdr)." - ".$f->getMessage(), E_USER_WARNING);
			//trigger_error("Erreur connexion WebService => ".serialize($aRNVPEntreeAdr)." - ".$f->getMessage(), E_USER_WARNING);
		}
		
		/*
		
		A l'entree
			Array
			(
			    [pi_session] => -1
			    [pi_commande] => New
			    [pi_user] => SDVP
			    [pi_password] => 76310SDVP
			    [pi_codedossier] => 1
			    [pi_numfichier] => 1
			    [pi_rsoc] => 
			    [pio_civ] => 
			    [pio_nom] => M SELLAM RAYMOND
			    [pio_prenom] => 
			    [pio_cnom] => 
			    [pio_cadrs] => LA BOISSIERE BAT 2 ESC 2
			    [pio_adresse] => 40 RUE DE LA RENARDIERE
			    [pio_lieudit] => 
			    [pio_cpville] => 93100 MONTREUIL
			)
			
			
		A la sortie	du RNVP
			stdClass Object
			(
			    [Elfyweb_RNVP_ExpertResult] => 0
			    [pio_civ] => M
			    [pio_nom] => SELLAM
			    [pio_prenom] => Raymond
			    [pio_cnom] => ESCALIER 2
			    [pio_cadrs] => La Boissiere Batiment 2
			    [pio_adresse] => 40 Rue de la Renardiere
			    [pio_lieudit] => 
			    [pio_cpville] => 93100 MONTREUIL
			    [pio_pays] => FRA
			    [po_seq] => 47012001
			    [po_tnp] => Monsieur SELLAM Raymond
			    [po_sex] => M
			    [po_civlong] => Monsieur
			    [po_numvoie] => 40
			    [po_bister] => 
			    [po_typvoie] => R
			    [po_typvoielong] => Rue
			    [po_libvoie] => De la Renardiere
			    [po_motdirecteur] => RENARDIERE
			    [po_cp] => 93100
			    [po_ville] => MONTREUIL
			    [po_insee] => 93048
			    [po_cqtnp] => 1
			    [po_cqadrs] => 1
			    [po_villenonfiabilisee] =>  
			    [po_correctionimportante] =>  
			    [po_correctiondouteuse] =>  
			    [po_statutenvp] => 01000000010000000000
			    [po_risquerestru] => 1
			    [po_risquev4] => 0
			    [po_risquev6] => 0
			    [po_poidsmodif] => 2
			    [po_poidsv4] => 0
			    [po_poidsv6] => 0
			    [po_rejet] =>  
			    [po_statage] => 78
			    [po_taille] => 1
			    [po_domtom] =>  
			    [po_etranger] =>  
			    [po_armee] =>  
			    [po_ste] =>  
			    [po_cedex] =>  
			    [po_hexacle] => 
			    [po_communetdm] =>  
			    [po_codetourneefacteur] => 
			)


		 */
		if($bRetourMajuscule===true) // Si on veut que l'objet retour ait des valeurs en majuscule
                {
                    $aRNVPSortieAdr->pio_prenom = $this->oString->supprQuotesAccents_Majuscule($aRNVPSortieAdr->pio_prenom);
                    $aRNVPSortieAdr->pio_cnom = $this->oString->supprQuotesAccents_Majuscule($aRNVPSortieAdr->pio_cnom);
                    $aRNVPSortieAdr->pio_cadrs = $this->oString->supprQuotesAccents_Majuscule($aRNVPSortieAdr->pio_cadrs);
                    $aRNVPSortieAdr->pio_adresse = $this->oString->supprQuotesAccents_Majuscule($aRNVPSortieAdr->pio_adresse);
                    $aRNVPSortieAdr->pio_lieudit = $this->oString->supprQuotesAccents_Majuscule($aRNVPSortieAdr->pio_lieudit);
                    $aRNVPSortieAdr->po_tnp = $this->oString->supprQuotesAccents_Majuscule($aRNVPSortieAdr->po_tnp);
                    $aRNVPSortieAdr->po_civlong = $this->oString->supprQuotesAccents_Majuscule($aRNVPSortieAdr->po_civlong);
                    $aRNVPSortieAdr->po_typvoielong = $this->oString->supprQuotesAccents_Majuscule($aRNVPSortieAdr->po_typvoielong);
                    $aRNVPSortieAdr->po_libvoie = $this->oString->supprQuotesAccents_Majuscule($aRNVPSortieAdr->po_libvoie);
                }
		switch ($aRNVPSortieAdr->Elfyweb_RNVP_ExpertResult)
		{
			case "0":
				return $this->etatRetourRnvp($aRNVPSortieAdr);
				break;
			case "1":
                                throw RnvpLocalException::erreurNormalisation($aRNVPSortieAdr->Elfyweb_RNVP_ExpertResult, "Erreur RNVP : Session inconnue ou probleme appel service", E_USER_WARNING);
				//trigger_error("Erreur RNVP : Session inconnue ou probleme appel service", E_USER_WARNING);
				//return false;
				//break;
			case "2":
                                throw RnvpLocalException::erreurNormalisation($aRNVPSortieAdr->Elfyweb_RNVP_ExpertResult, "Erreur RNVP : Dossier / fichier inconnu", E_USER_WARNING);
				//trigger_error("Erreur RNVP : Dossier / fichier inconnu", E_USER_WARNING);
				//return false;
				//break;
			case "3":
                                throw RnvpLocalException::erreurNormalisation($aRNVPSortieAdr->Elfyweb_RNVP_ExpertResult, "Erreur RNVP : Credits depasses (ou date limite atteinte)", E_USER_WARNING);
				//trigger_error("Erreur RNVP : Credits depasses (ou date limite atteinte)", E_USER_WARNING);
				//return false;
				//break;
			case "8":
                                throw RnvpLocalException::erreurNormalisation($aRNVPSortieAdr->Elfyweb_RNVP_ExpertResult, "Erreur RNVP : Session non libere dans les 5 s (trop long) - Fonction en boucle", E_USER_WARNING);
				//trigger_error("Erreur RNVP : Session non libere dans les 5 s (trop long) - Fonction en boucle", E_USER_WARNING);
				//return false;
				//break;
			case "9":
                                throw RnvpLocalException::erreurNormalisation($aRNVPSortieAdr->Elfyweb_RNVP_ExpertResult, "Erreur RNVP : Syntaxe incorrecte", E_USER_WARNING);
				//trigger_error("Erreur RNVP : Syntaxe incorrecte", E_USER_WARNING);
				//return false;
				//break;
			case "99":
                                throw RnvpLocalException::erreurNormalisation($aRNVPSortieAdr->Elfyweb_RNVP_ExpertResult, "Erreur RNVP : Service Elfy-Res non lance", E_USER_WARNING);
				//trigger_error("Erreur RNVP : Service Elfy-Res non lance", E_USER_WARNING);
				//return false;
				//break;
                        default :
                                throw RnvpLocalException::erreurNormalisation($aRNVPSortieAdr->Elfyweb_RNVP_ExpertResult, 'Erreur RNVP : Elfyweb_RNVP_ExpertResult=Erreur RNVP : Elfyweb_RNVP_ExpertResult="'.$aRNVPSortieAdr->Elfyweb_RNVP_ExpertResult.'"', E_USER_WARNING);
                                //break;
		}
		return false;
	}
        
        /**
         * En fonction du retour 
         * 
         * @param type $aSortieRNVP
         * @return string
         */
        private function etatRetourRnvp($aSortieRNVP)
        {
            // Correction a la suite de l'arrivee de la version V5.1
            if($aSortieRNVP->po_statutenvp && strlen($aSortieRNVP->po_statutenvp)>5)
            {
                $etatRetour = 'RNVP_KO';
                $iEtatAdr   = intval(substr($aSortieRNVP->po_statutenvp, 0, 2));
                $sCorrectionDouteuse    = trim($aSortieRNVP->po_correctiondouteuse);
                
                /*
                $iEtatAdr 
                    Si 1 -> Adresse OK
                    Si 2 -> Adresse modifiee OK
                    Si 5 -> Adresse modifiee ambigue
                    Si 8 -> Adresse etrangere
                    Si 9 -> Adresse rejetee (selon parametre)
                 */
                
                if($iEtatAdr<3)
                {
                    if(strlen($sCorrectionDouteuse) == 0)
                    {
                        if(in_array(substr($aSortieRNVP->po_cqadrs, 0, 1), array(1, 2)))
                        {
                            $etatRetour = 'RNVP_OK';
                        }
                        else
                        {
                            $etatRetour = 'RNVP_AVEC_RISQUE';
                        }
                    }
                    else
                    {
                        $etatRetour = 'RNVP_AVEC_RISQUE';
                    }
                }
                else if($iEtatAdr<6)
                {
                    $etatRetour = 'RNVP_AVEC_RISQUE';
                }
                else
                {
                    $etatRetour = 'RNVP_KO';
                }
            }
            else
            {            
                // Avant --- les OK (po_rejet !='R' && po_etranger !='E' && po_cqadrs in array ('1') && po_risquerestru <= 3)
                $etatRetour = 'RNVP_KO';
                if($aSortieRNVP->po_rejet!='R' && $aSortieRNVP->po_etranger!='E' && in_array(intval($aSortieRNVP->po_cqadrs), array(1, 2)) && intval($aSortieRNVP->po_risquerestru)<=7)
                {
                    $etatRetour = 'RNVP_OK';
                }
                else if($aSortieRNVP->po_rejet!='R' && $aSortieRNVP->po_etranger!='E' && in_array(intval($aSortieRNVP->po_cqadrs), array(1)) && intval($aSortieRNVP->po_risquerestru)>7 && intval($aSortieRNVP->po_risquerestru)<=20)
                {
                    $etatRetour = 'RNVP_AVEC_RISQUE';
                }
                // Avant --- les OK mais avec ville partielles referencee ou voie inconnue (po_rejet !='R' && po_etranger !='E' && po_cqadrs in array ('2') && po_risquerestru <= 3)
                else if($aSortieRNVP->po_rejet!='R' && $aSortieRNVP->po_etranger!='E' && in_array(intval($aSortieRNVP->po_cqadrs), array(2)) && intval($aSortieRNVP->po_risquerestru)>7 && intval($aSortieRNVP->po_risquerestru)<=20)
                {
                    $etatRetour = 'RNVP_INFO_VILLE_VOIE_INCOMPLET';
                }
                // Avant --- Adresse proposee OK mais a verifier de pres
                else if($aSortieRNVP->po_rejet!='R' && $aSortieRNVP->po_etranger!='E' && (intval($aSortieRNVP->po_cqadrs)>2 || intval($aSortieRNVP->po_risquerestru)>20))
                {
                    $etatRetour = 'RNVP_AVEC_RISQUE';
                }
                else if($aSortieRNVP->po_rejet=='R' || $aSortieRNVP->po_etranger=='E')
                {
                    $etatRetour = 'RNVP_KO';
                }
                // po_cqadrs > 2
                //          po_cqadrs (>2 <=> 3 -> Pte ville, voie inconnue
                // 		4 -> Pte ville, voie absente
                // 		5 -> Gde ville, voie inconnue
                // 		6 -> Gde ville, voie absente
                // 		7 -> Cp / Ville non corrigeable
                // 		8 -> Cp / Ville non corrigeable + voie absente
                // 		9 -> Etranger detecte )
                else 
                {
                    $etatRetour = 'RNVP_KO';
                }
            }
            $aSortieRNVP->etatRetourRnvp    = $etatRetour;
            return $aSortieRNVP;
        }
}