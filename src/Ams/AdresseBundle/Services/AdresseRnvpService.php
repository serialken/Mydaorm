<?php

namespace Ams\AdresseBundle\Services;

use Ams\WebserviceBundle\Exception\RnvpLocalException;
use Ams\WebserviceBundle\Exception\GeocodageException;
use Doctrine\DBAL\DBALException;
use Ams\AdresseBundle\Entity\Adresse;
use Ams\AdresseBundle\Entity\AdresseRnvp;

/**
 * Normalisation d'une adresse & Insertion, geocodage d'une Adresse RNVP
 * Pour stocker une adresse RNVP, on essaie de supprimer le cedex.
 */
class AdresseRnvpService {

    private $_em;
    private $srv_rnvp;
    private $srv_geocodage;
    private $aCommune;

    function __construct(\Doctrine\ORM\EntityManager $em, $srv_rnvp, $srv_geocodage) {
        $this->_em = $em;
        $this->srv_rnvp = $srv_rnvp;
        $this->srv_geocodage = $srv_geocodage;
        $this->aCommune = array();
    }
    
    /**
     * Initialisation d'un tableau afin d'avoir la correspondance INSEE - Commune
     * 
     * @return array
     */
    private function initCommune()
    {
        if(empty($this->aCommune))
        {
            $aoCommune  = $this->_em->getRepository('AmsAdresseBundle:Commune')->findAll();
            foreach($aoCommune as $k => $oC)
            {
                $this->aCommune[$oC->getInsee()]    = $oC;
            }
        }
        return $this->aCommune;
    }
    
    public function estNormalisee(\Ams\AdresseBundle\Entity\Adresse $adresse)
    {
        //*
    }

    /**
     * Normalise l'adresse $adresse
     * @param \Ams\AdresseBundle\Entity\Adresse $adresse
     * @return false|Objet retourne par la RNVP
     * @throws \Ams\WebserviceBundle\Exception\RnvpLocalException
     */
    public function normaliseAdresse(\Ams\AdresseBundle\Entity\Adresse $adresse) {
        try {
            $aEntreeRNVP = array(
                "volet1" => '',
                "volet2" => '',
                "volet3" => $adresse->getVol3(),
                "volet4" => $adresse->getVol4(),
                "volet5" => $adresse->getVol5(),
                "cp"    => $adresse->getCp(),
                "ville" => $adresse->getVille()
            );
            return $this->srv_rnvp->normalise($aEntreeRNVP);
        } catch (RnvpLocalException $rnvpLocalException) {
            throw $rnvpLocalException;
        }
    }
    
    /**
     * Geocode une adresse \Ams\AdresseBundle\Entity\AdresseRnvp
     * @param \Ams\AdresseBundle\Entity\AdresseRnvp $oAdresseRnvp
     * @return type
     * @throws \Ams\WebserviceBundle\Exception\GeocodageException
     */
    public function geocodeAdresse(\Ams\AdresseBundle\Entity\AdresseRnvp $oAdresseRnvp)
    {
        try {
            $AGeocoder = array(
                "CountryCode" => 'fr',
                "City" => $oAdresseRnvp->getVille(),
                "PostalCode" => $oAdresseRnvp->getCp(),
                "AddressLine" => $oAdresseRnvp->getAdresse()
            );
            return $this->srv_geocodage->geocode($AGeocoder);
        } catch (GeocodageException $GeocodageException) {
            throw $GeocodageException;
        }
    }
    
    /**
     * Insertion de l'objet - retour RNVP - dans la table "adresse_rnvp"
     * 
     * @param object $rnvp  retour RNVP
     * @param \Ams\SilogBundle\Entity\Utilisateur $utilisateur
     * @return int
     * @throws \Ams\RnvpBundle\Lib\RnvpLocalException
     */
    public function insertRnvp($rnvp, $utilisateur=NULL) {
        try {
            // initialise le tableau des communes (array[insee]=commune_id)
            $this->initCommune();
            
            // Verifie si Ville avec CEDEX. Si oui, on essaie de le supprimer
            if(preg_match('/.+CEDEX.*/', $rnvp->po_ville))
            {
                // on recalcule le RNVP avec la ville sans CEDEX
                if(isset($this->aCommune[$rnvp->po_insee])) {
                    $cpTmp  = $this->aCommune[$rnvp->po_insee]->getCp();
                    $villeTmp  = $this->aCommune[$rnvp->po_insee]->getLibelle();
                    
                    $oTmp = new Adresse();
                    $oTmp->setVol3($rnvp->pio_cadrs);
                    $oTmp->setVol4($rnvp->pio_adresse);
                    $oTmp->setVol5($rnvp->pio_lieudit);
                    $oTmp->setCp($cpTmp);
                    $oTmp->setVille($villeTmp);                    
                    try {
                        $rnvp  = $this->normaliseAdresse($oTmp);
                    } catch (RnvpLocalException $rnvpLocalException) {
                        throw $rnvpLocalException;
                    }
                }    
            }
            
            $repoAdresseRnvp = $this->_em->getRepository('AmsAdresseBundle:AdresseRnvp');
            $idRnvp = 0;
            $oAdresseRnvpBDD = $repoAdresseRnvp->findOneBy(
                    array(
                        'cAdrs' => strtoupper($rnvp->pio_cadrs),
                        'adresse' => strtoupper($rnvp->pio_adresse),
                        'lieuDit' => strtoupper($rnvp->pio_lieudit),
                        'cp' => strtoupper($rnvp->po_cp),
                        'ville' => strtoupper($rnvp->po_ville)
                    )
            );

            if (is_null($oAdresseRnvpBDD)) {
                $oDateDuJour = new \DateTime();
                $oTmpRnvp = new AdresseRnvp();
                $oTmpRnvp->setCAdrs(strtoupper($rnvp->pio_cadrs));
                $oTmpRnvp->setAdresse(strtoupper($rnvp->pio_adresse));
                $oTmpRnvp->setLieuDit(strtoupper($rnvp->pio_lieudit));
                $oTmpRnvp->setCp(strtoupper($rnvp->po_cp));
                $oTmpRnvp->setVille(strtoupper($rnvp->po_ville));
                $oTmpRnvp->setInsee(strtoupper($rnvp->po_insee));
                if(isset($this->aCommune[$rnvp->po_insee]))
                {
                    $oTmpRnvp->setCommune( (isset($this->aCommune[$rnvp->po_insee]) ? $this->aCommune[$rnvp->po_insee] : NULL) );
                }
                if(!is_null($utilisateur))
                {
                    $oTmpRnvp->setUtilisateurModif($utilisateur);
                }
                $oTmpRnvp->setDateModif($oDateDuJour);

                $idRnvp = $repoAdresseRnvp->insert($oTmpRnvp);
            } else {
                $idRnvp = $oAdresseRnvpBDD->getId();
            }
          
            return $idRnvp;
        } catch (RnvpLocalException $rnvpLocalException) {
            throw $rnvpLocalException;
        }
    }
    
    
    public function miseAJourGeocodage($geocodage, $adresse_id, \Ams\AdresseBundle\Entity\AdresseRnvp $oAdresseRnvp, $saisie=0)
    {
        try {
            // geo_etat [0 si KO, 1 si OK Automatique, 2 si OK Manuel, NULL si non encore geocode]
            
            $geoEtat = 0;
            if($saisie==1) {
                $geoEtat = 2; // cas ou geoodage valide manuellement
            }
            else if ($geocodage["GeocodeEtat"]=="OK") {
                $geoEtat = 1; // cas ou geocodage valide automatiquement
            }
            $iStopLivraisonPossible = 0;
            if( ($geoEtat == 1 && $oAdresseRnvp->getCAdrs()=='' && $oAdresseRnvp->getLieuDit()=='') || $geoEtat == 2 ) {
                $iStopLivraisonPossible = 1;
            }
                    
            if(isset($geocodage["X"]))
			{
            $this->_em->getConnection()
                    ->executeQuery("UPDATE
                                        adresse_rnvp 
                                    SET geox=".$geocodage["X"]."
                                        , geoy=".$geocodage["Y"]."
                                        , geo_score=".$geocodage["GeocodeScore"]."
                                        , geo_type=".$geocodage["GeocodeType"]."
                                        , geo_etat=".$geoEtat."
                                        , stop_livraison_possible='".$iStopLivraisonPossible."'
                                    WHERE 
                                        id = ".$adresse_id." ")
            ;
            }
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Mise a jour des champs "rnvp_id, commune_id, adresse_rnvp_etat_id, utl_id_modif, date_modif" d'une ligne identifiee par "$adresse_id" de la table "adresse" 
     * @param integer $adresse_id
     * @param object $rnvp
     * @param integer $rnvp_id
     * @param boolean $traitementRejet
     * @param integer $utilisateur_id
     * @throws \Doctrine\DBAL\DBALException
     */
    public function miseAJourAdresse($adresse_id, $rnvp, $rnvp_id, $traitementRejet = false, $utilisateur_id = NULL)
    {
        try {
            $aMiseAJour = array();
            $oDateDuJour = new \DateTime();
            if (!is_null($utilisateur_id)) {
                $aMiseAJour['utl_id_modif'] = $utilisateur_id;
            }
            $aMiseAJour['rnvp_id'] = $rnvp_id;

            if($traitementRejet === true) {
                 $code  = 'SAISIE_OK';
                 $aMiseAJour['type_changement_id'] =2; //type rejet à vérifier avec Andry
   
            }
            else {
                $code = $rnvp->etatRetourRnvp;
            }
            if(isset($this->aCommune[$rnvp->po_insee]))
            {
                $aMiseAJour['commune_id'] = $this->aCommune[$rnvp->po_insee]->getId();
            }
            $repoAdresseRnvpEtat = $this->_em->getRepository('AmsAdresseBundle:AdresseRnvpEtat');
            $aMiseAJour['adresse_rnvp_etat_id'] = $repoAdresseRnvpEtat->findOneBy(array('code' => $code))->getId();
            $aMiseAJour['date_modif'] = $oDateDuJour->format("Y-m-d H:i:s");
            $repoAdresse = $this->_em->getRepository('AmsAdresseBundle:Adresse');
            $repoAdresse->updateRnvp($adresse_id, $aMiseAJour);
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

    /**
     * RNVP et/ou Stop livraison de toutes les lignes non normalisees (RNVP) de la table ADRESSE
     * @throws \Ams\RnvpBundle\Lib\RnvpLocalException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function normaliseTouteAdresse() {
        try {   
            // RNVP & Stop livraison des adresses deja connues
            $repoAdresseTmp = $this->_em->getRepository('AmsAdresseBundle:AdresseTmp');
            $repoAdresseTmp->truncate();
            $repoAdresseTmp->init();
            
            $this->_em->getConnection()
                    ->executeQuery("UPDATE
                                        adresse a
                                        LEFT JOIN adresse_tmp tmp ON a.vol3=tmp.vol3 AND a.vol4=tmp.vol4 AND a.vol5=tmp.vol5 AND a.cp=tmp.cp AND a.ville=tmp.ville
                                    SET a.commune_id=tmp.commune_id
                                        , a.adresse_rnvp_etat_id=tmp.adresse_rnvp_etat_id
                                        , a.rnvp_id=tmp.rnvp_id
                                        , a.point_livraison_id=tmp.point_livraison_id
                                    WHERE 
                                        a.rnvp_id IS NULL
                                        AND tmp.rnvp_id IS NOT NULL ")
            ;
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
        
        
        $repoAdresse = $this->_em->getRepository('AmsAdresseBundle:Adresse');
        $aANormaliser = $repoAdresse->findBy(array('rnvp' => null));
        if (!is_null($aANormaliser)) {
            foreach ($aANormaliser as $oANormaliser) {
                try {
                    $rnvp  = $this->normaliseAdresse($oANormaliser);
                    $rnvp_id    = $this->insertRnvp($rnvp);
                    $this->miseAJourAdresse($oANormaliser->getId(), $rnvp, $rnvp_id);
                } catch (RnvpLocalException $rnvpLocalException) {
                    throw $rnvpLocalException;
                }
            }
        }
    }
    
    /**
     * Geocode toutes les adresses non encore geocodees
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Ams\WebserviceBundle\Exception\GeocodageException
     */
    public function geocodeTouteAdresse()
    {
        try { 
            // Les adresses a normaliser
            $repoAdresseRnvp = $this->_em->getRepository('AmsAdresseBundle:AdresseRnvp');
            $aAGeocoder = $repoAdresseRnvp->touteAdresseNonGeocodees();
            if (!is_null($aAGeocoder)) {
                $iI = 0;
                $t0 = time();
                $tInit = $t0;
                foreach($aAGeocoder as $aAdr)
                {
                    try {
                        if($iI==0)
                        {
                            $oAdresseRnvp = new AdresseRnvp();
                        }
                        $oAdresseRnvp->setCAdrs($aAdr['cadrs']);
                        $oAdresseRnvp->setAdresse($aAdr['adresse']);
                        $oAdresseRnvp->setLieuDit($aAdr['lieudit']);
                        $oAdresseRnvp->setCp($aAdr['cp']);
                        $oAdresseRnvp->setVille($aAdr['ville']); 
                        
                        $iI++;
                        $geocode  = $this->geocodeAdresse($oAdresseRnvp);                        
                        $this->miseAJourGeocodage($geocode, $aAdr['id'], $oAdresseRnvp);
                        if($iI%100==0)
                        {
                            echo $iI." ";
                        }
                        if($iI%1000==0)
                        {
                            $t1 = time();
                            //sleep(30); // Test pour permettre au serveur de geoodage de liberer de memoire. 
                            echo "(".($t1-$t0)." s)"."\r\n";
                            $t0 = $t1;
                        }
                    } catch (GeocodageException $GeocodageException) {
                        throw $GeocodageException;
                    }
                }
                echo "\r\nTemps total : $iI adresses => ".(time()-$tInit)."\r\n";
            }
        } catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    public function supprCedex()
    {
        try { 
            // Les adresses RNVP avec le nom de la ville avec CEDEX
            $repoAdresse = $this->_em->getRepository('AmsAdresseBundle:AdresseRnvp');
            $aAdresseCedex = $repoAdresse->adresseRnvpCedex();
            if (!is_null($aAdresseCedex)) {
                foreach($aAdresseCedex as $aAdrRnvp)
                {
                    $oTmp = new Adresse();
                    $oTmp->setVol3($aAdrRnvp["cadrs"]);
                    $oTmp->setVol4($aAdrRnvp["adresse"]);
                    $oTmp->setVol5($aAdrRnvp["lieudit"]);
                    $oTmp->setCp($aAdrRnvp["cp"]);
                    $oTmp->setVille($aAdrRnvp["ville"]);                    
                    try {
                        $rnvp  = $this->normaliseAdresse($oTmp);
                        $repoAdresse->miseAJourCPVille($aAdrRnvp['id'], $rnvp);
                    } catch (RnvpLocalException $rnvpLocalException) {
                        throw $rnvpLocalException;
                    }
                }
            }
        } catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
}
