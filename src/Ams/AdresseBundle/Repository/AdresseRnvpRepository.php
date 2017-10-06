<?php

namespace Ams\AdresseBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\DBALException;

class AdresseRnvpRepository extends EntityRepository {

    /**
     * Verifie si l'adresse RNVP existe deja
     * 
     * @param array $adresseRnvp
     * @return \Ams\AdresseBundle\Entity\AdresseRnvp
     */
    public function existe($adresseRnvp) {
        $sql = $this->findOneBy(array(
                    'cAdrs' => $adresseRnvp['CADRS'],
                    'adresse' => $adresseRnvp['ADRESSE'],
                    'lieuDit' => $adresseRnvp['LIEUDIT'],
                    'cp' => $adresseRnvp['CP'],
                    'ville' => $adresseRnvp['VILLE']
        ));
        //echo $sql;exit;
        return $sql;
    }

    public function insert(\Ams\AdresseBundle\Entity\AdresseRnvp $adresseRnvp) {
        $aDonnees = array();
        $aDonnees['cadrs'] = $adresseRnvp->getCAdrs();
        $aDonnees['adresse'] = $adresseRnvp->getAdresse();
        $aDonnees['lieudit'] = $adresseRnvp->getLieuDit();
        $aDonnees['cp'] = $adresseRnvp->getCp();
        $aDonnees['ville'] = $adresseRnvp->getVille();
        $aDonnees['insee'] = $adresseRnvp->getInsee();
        if (!is_null($adresseRnvp->getCommune())) {
            $aDonnees['commune_id'] = $adresseRnvp->getCommune()->getId();
        }
        $oDatetimeCourant = new \DateTime();
        $aDonnees['date_modif'] = $oDatetimeCourant->format("Y-m-d H:i:s");
        $this->_em->getConnection()->insert('adresse_rnvp', $aDonnees);
        return $this->_em->getConnection()->lastInsertId();
    }
    
    /**
     * Toutes les adresses non encore geocodees
     * @throws \Doctrine\DBAL\DBALException
     */
    public function touteAdresseAGeocoder()
    {
        try {
            $qb = $this->createQueryBuilder('a')->where('a.geoEtat IS NULL');
            return $qb->getQuery()->getResult(); 
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Toutes les adresses non encore geocodees
     * @throws \Doctrine\DBAL\DBALException
     */
    public function touteAdresseNonGeocodees()
    {
        try {
            $slct   = ' SELECT 
                            id, cadrs, adresse, lieudit, cp, ville
                        FROM
                            adresse_rnvp
                        WHERE
                            geo_etat IS NULL
                    ';
            return $this->_em->getConnection()->fetchAll($slct);
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    public function createStopLivraison($data)
    {
        $slct   = " INSERT INTO adresse_rnvp
                        (commune_id,adresse,cp,ville,insee,geox,geoy,geo_score,geo_type,geo_etat,stop_livraison_possible,date_modif)
                    VALUES
                        (".$data['commune_id'].",'".$data['adresse']."','".$data['cp']."','".$data['ville']."','".$data['insee']."',
                         '".$data['geox']."','".$data['geoy']."','".$data['geo_score']."','".$data['geo_type']."','".$data['geo_etat']."',
                         ".(in_array(intval($data['geo_etat']), array (1, 2)) ? '1' : '0').",NOW()
                        )
                ";
        $this->_em->getConnection()->prepare($slct)->execute();
        
        return (in_array(intval($data['geo_etat']), array (1, 2)) ? $this->_em->getConnection()->lastInsertId($slct) : 0);
       
    }
    
    public function enableStopLivraison($id)
    {
        try {
            $slct   = " UPDATE adresse_rnvp
                        SET stop_livraison_possible ='1'
                        WHERE id = $id
                    ";
            return $this->_em->getConnection()->prepare($slct)->execute();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    public function disableStopLivraison($idExclu,$address,$zipCode)
    {
        try {
            $slct   = " UPDATE adresse_rnvp
                        SET stop_livraison_possible = '0'
                        WHERE id <> $idExclu
                        AND adresse = '$address'
                        AND cp = '$zipCode'
                    ";
            $this->_em->getConnection()->prepare($slct)->execute();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    public function updateCoordsByAddress($address,$zip,$city,$long,$lat,$etat,$score,$type)
    {
        try {
            $slct   = " UPDATE adresse_rnvp
                        SET geox = $long, geoy = $lat , geo_score = $score, geo_type = $type, geo_etat = $etat
                        WHERE adresse = '$address'
                        AND cp = '$zip'
                        AND ville = '$city'
                    ";
            $this->_em->getConnection()->prepare($slct)->execute();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    public function getIssetComplementAddress($address = false)
    {
        try {
            $slct   = " SELECT 
                            adresse, cp, MAX(geo_etat) as geo_etat,geox,geoy,ville,commune_id,insee,geo_score,geo_type
                        FROM
                            adresse_rnvp
                        WHERE
                            (cadrs <> '' OR lieudit <> '')
                            AND adresse <> ''
                            AND commune_id is not null
                       ";
                if($address)
                        $slct .=" AND adresse = '$address' ";
                    $slct .= " GROUP BY adresse,cp ,ville
                    ";
            return $this->_em->getConnection()->fetchAll($slct);
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    public function getDuplicateAddressId($idExclu,$address,$zipCode)
    {
        try {
            $slct   = " SELECT 
                            id
                        FROM
                            adresse_rnvp
                        WHERE
                            cp = '$zipCode'
                                AND adresse = '$address'
                                AND id <> $idExclu
                    ";
            return $this->_em->getConnection()->fetchAll($slct);
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    public function getExistStopLivraison($address,$zipCode)
    {
        $connection = $this->getEntityManager()->getConnection();
        try {
            $slct   = " SELECT 
                            *
                        FROM
                            adresse_rnvp
                        WHERE
                            adresse = '$address'
                                AND cp = '$zipCode'
                                AND cadrs = ''
                                AND lieudit = ''
                    ";
            $stmt = $connection->executeQuery($slct);
            return $stmt->fetch();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * RETURN ADRESSE BY COORDINATE
     * @return type array
     */
    public function getAddressByCoordinate($geox,$geoy){
        $slct   = "
            SELECT distinct 
                ar.adresse, c.cp, c.libelle AS ville, ar.insee
            FROM
                adresse_rnvp ar
                INNER JOIN commune c ON ar.insee = c.insee
            WHERE
                ar.geox = $geox
                AND ar.geoy = $geoy
            ";
        return $this->_em->getConnection()->fetchAll($slct);
    }
    
    /**
     * RETURN ADRESSE DIFFERENTES ET COORDONEES SIMILAIRE
     * @return type array
     */
    public function getVariousAddressCoordinate($tournee = false){
        $slct   = "
                SELECT 
                    geox,geoy, count(DISTINCT adresse) AS nb
                FROM
                    adresse_rnvp arnvp ";
        
                if($tournee)
                    $slct .= "
                        JOIN
                    client_a_servir_logist casl ON casl.point_livraison_id = arnvp.id
                        JOIN
                    modele_tournee_jour mtj ON mtj.id = casl.tournee_jour_id AND mtj.code = '$tournee' ";
               $slct .= "    
                WHERE adresse <> ''
                AND geox <> ''
                AND geoy <> ''

                GROUP BY geox,geoy
                HAVING nb > 1
                ORDER BY nb desc
            ";
        return $this->_em->getConnection()->fetchAll($slct);
    }

    /**
     * RETURN ADRESSE SIMILAIRE ET COORDONEES DIFFERENTES
     * @return type array
     */
    public function getVariousCoordinateAddress(){
        try {
            $slct   = " SELECT 
                            id,insee,adresse,ville,cp,geox,geoy,count(DISTINCT geox, geoy) as nb
                        FROM
                            adresse_rnvp
                        WHERE
                            adresse <> ''
                        GROUP BY adresse , ville , cp
                        HAVING nb > 1
                    ";
            return $this->_em->getConnection()->fetchAll($slct);
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Recupere les adresses RNVP avec le nom de la ville avec CEDEX
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function adresseRnvpCedex()
    {
        try {
            $sql = "SELECT 
                        r.id, r.commune_id, r.cadrs, r.adresse, r.lieudit, r.cp as cp_cedex, r.ville AS ville_cedex, r.insee, c.cp, c.libelle AS ville
                    FROM 
                        adresse_rnvp r
                        LEFT JOIN commune c ON r.commune_id=c.id
                    WHERE
                        r.ville LIKE '%CEDEX%' 
                        AND c.id IS NOT NULL
                        ";
            return $this->_em->getConnection()->fetchAll($sql);
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Mise a jour de CP - Ville. Utilise surtout dans le cas de suppression des CEDEX
     * @param integer $id
     * @param object $rnvp
     * @throws \Doctrine\DBAL\DBALException
     */
    public function miseAJourCPVille($id, $rnvp)
    {
        try {
            if($rnvp!==false)
            {
                $update = " UPDATE 
                                adresse_rnvp
                            SET
                                cp='".$rnvp->po_cp."'
                                , ville='".addslashes(strtoupper($rnvp->po_ville))."'
                            WHERE
                                id='".$id."' ";
                $this->_em->getConnection()->prepare($update)->execute();
            }
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    public function getAdressesPortage($portageId){
        $qb = $this->createQueryBuilder('adresse')
                ->join('adresse.adresses','adresses')
                ->join('adresses.abonneSoc','abonneSoc')
                ->join('abonneSoc.infosPortages','infos')
                ->where('infos.id = :id')
                ->setParameter('id',$portageId)
                ;
        return $qb;
    }
    
    public function getLivraisonsPortage($portageId){
        $qb = $this->createQueryBuilder('adresse')
                ->join('adresse.adresses_livraison','adresses')
                ->join('adresses.abonneSoc','abonneSoc')
                ->join('abonneSoc.infosPortages','infos')
            ->where('infos.id = :id')
            ->setParameter('id',$portageId)
                ;
        return $qb;
    }
    
    /**
     * Méthode qui retourne les adresses dont le géocodage est suspect (coordonnées nulles ou latitude = longitude)
     * @return array Les enregistrements correspondants aux critères
     * @throws DBALException
     */
    public function geocodageSuspect(){
        try{
            $select = "SELECT * from adresse_rnvp WHERE (geox IS NULL OR geoy IS NULL OR (geox = geoy));";
            return $this->_em->getConnection()->fetchAll($select);
        }
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * 
     * @param int $id L'ID de l'adresse RNVP à modifier
     * @param array $aDatas Le tableau contenant les informations à utiliser pour la mise à jour
     * @throws DBALException
     */
    public function updateCoords($id, $aDatas){
        try{
        $update = "UPDATE adresse_rnvp "
                . "SET type_rnvp = 1, "
                . "geo_etat = 1, geo_score =".$aDatas->GeocodeScore.",  "
                . "geo_type=".$aDatas->GeocodeType.", "
                . "geox=".$aDatas->X.", "
                . "geoy=".$aDatas->Y." "
                . "WHERE id=".$id.";";
        
        $this->_em->getConnection()->prepare($update)->execute();
        }
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    public function setAddressNormaliseEtat() {
        $slct = "
                UPDATE adresse_rnvp ar
                    INNER JOIN adresse a ON ar.id = a.rnvp_id
                SET 
                    a.adresse_rnvp_etat_id = 2
                WHERE
                    ar.geo_score=99
                ";
                
        $this->_em->getConnection()->prepare($slct)->execute();
    }
    
    public function getPointLivraisonByData($data){
        try {
            $slct   = 'SELECT arnvp.id,CONCAT(arnvp.adresse," ",arnvp.cp," ",arnvp.ville) as arnvp_concat,vol1,CONCAT(a.vol4," ",a.cp," ",a.ville) as abo_adress,b.state '.
                      'FROM adresse_rnvp arnvp '.
                      'RIGHT JOIN adresse a ON a.point_livraison_id = arnvp.id '.
                      'LEFT JOIN bordereau as b ON arnvp.id = b.point_livraison '.
                      'WHERE arnvp.cp = '.$data['ZIPCODE'].' '.
                      'AND arnvp.ville like "%'.$data['CITY'].'%" '.
                      'AND arnvp.adresse like "%'.$data['ADRESS'].'%" '.
                      'AND arnvp.lieudit like "%'.$data['LIEU'].'%" '.
                      'AND arnvp.cadrs like "%'.$data['COMPLEMENT'].'%" '.
                      'ORDER BY arnvp.id
                    ';
            return $this->_em->getConnection()->fetchAll($slct);
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    public function findExistAddress($address,$zip,$city){
        $connection = $this->getEntityManager()->getConnection();
        $q= "
            SELECT * FROM adresse_rnvp 
            WHERE adresse = '$address'
                AND cp = $zip
                AND ville = '$city'
                AND cadrs ='' 
                AND lieudit=''
        ";
        $stmt = $connection->executeQuery($q);
    	$result = $stmt->fetch();
        if($result !== false) return $result['id'];
        return false;
    }
    
    
    /**
     * 
     * @param type $ptLivraisonId int
     * @param type $aData array (address,zipCode,city,long,lat,utl_id_modif
     * @throws DBALException
     */
    public function updatePointLivraisonId($ptLivraisonId,$aData)
    {
        $count = 0;$paramUpdate = '';
        foreach($aData as $key=>$value){
            if($count == 0){
                $paramUpdate .=' SET '.$key .' ="'.$value.'"';
                $count++;
            }
            else 
                $paramUpdate .=','.$key .' ="'.$value.'"';
        }
        
        try {
            $q   = " 
                    UPDATE adresse_rnvp
                        $paramUpdate
                    WHERE id = $ptLivraisonId
                    ";
            $this->_em->getConnection()->prepare($q)->execute();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * A effacer apres utilisation
     * @return type array
     */
    public function getResultTmp(){
        $q = " 
            SELECT * from adresse_rnvp 
            WHERE geox = 2.3479290
            AND geoy = 48.8742090
        ";
        return $this->_em->getConnection()->fetchAll($q);
    }
    
    
    /*
     * RECUPERENT TOUS LES COORDONNEES D'ADRESSE RNVP HORS OISE
     */
    public function getAllCoordsWithoutOise()
    {
        $ParisCenterLat = '48.856614';
        $ParisCenterLon = '2.3522219';
        $q   = " 
                SELECT * FROM(
                    SELECT 
                        id,geox,geoy,get_distance_kilometre($ParisCenterLat,$ParisCenterLon, geoy, geox) as distance
                    FROM adresse_rnvp
                    WHERE insee NOT LIKE '60%'
                    )as t
                WHERE distance > 20 
                ";
        return $this->_em->getConnection()->fetchAll($q);
    }
    
    /*
     * RECUPERENT TOUS LES COORDONNEES D'ADRESSE RNVP HORS OISE
     */
    public function logAdressExceptIdf($aId)
    {
        $q="";
        foreach($aId as $id){
            $q.= "REPLACE INTO adresse_hors_idf VALUES ($id);";
        }
        if($q!='')
            $this->_em->getConnection()->prepare($q)->execute();
    }
    public function getNumrue($date,$data) {
        $point_lv=$data["point_livraison_id"];
        $num_abo=$data['num_abonne'];
       try {
            $slct   = ' SELECT 
                        arnvp.adresse
                        FROM
                            client_a_servir_logist casl 
                        INNER JOIN abonne_soc abs ON  abs.id=casl.abonne_soc_id 
                        INNER JOIN adresse_rnvp arnvp ON arnvp.id=casl.rnvp_id
                        WHERE
                            casl.point_livraison_id='.$point_lv.' AND abs.numabo_ext="'.$num_abo.'" 
                            AND casl.date_distrib="'.$date.'" order by casl.id desc limit 1;
                    ';
            return $this->_em->getConnection()->fetchAll($slct);
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
}
