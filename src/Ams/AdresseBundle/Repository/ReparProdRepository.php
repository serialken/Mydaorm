<?php

namespace Ams\AdresseBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ReparProdRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ReparProdRepository extends EntityRepository
{
    
    
    /**
     * Récupère la liste des exceptions en fonction du depot
     * @param int $depotId L'ID du dépot
     * @return mixed Le tableau d'enregistrements
     */
    public function getRepartitionByDepot($depotId) {
        $connection = $this->getEntityManager()->getConnection();  
        $q = "
            SELECT 
                rp.date_debut date_debut,
                rp.date_fin date_fin,
                c.libelle ville,
                c.insee insee,
                c.cp,
                p.libelle produit,
                if(rp.flux_id=1, 'Nuit', 'Jour') as flux
            FROM repar_prod rp
            INNER JOIN produit p ON  rp.produit_id = p.id
            INNER JOIN commune c ON rp.commune_id =  c.id
                   
            WHERE depot_id = $depotId ORDER BY rp.date_fin desc
        
        ;";
        
        $stmt = $connection->executeQuery($q);
        return $stmt->fetchAll();
    }
    
  
    public function getExceptions($zipCode,$socId,$fluxId) {
        $connection = $this->getEntityManager()->getConnection();
        $q = "
             SELECT 
                rp.*, c.libelle as ville, c.insee, c.cp,
                d.id AS depot_id, d.libelle AS depot_nom, d.adresse
            FROM
                repar_prod rp
            JOIN commune c ON c.id = rp.commune_id
            JOIN produit p ON p.id = rp.produit_id
            JOIN depot d ON d.id = rp.depot_id
            WHERE ";
                
        if ($fluxId > 0){
            $q .= " rp.flux_id = $fluxId 
                AND ";
        }
        
        $q .= " LEFT(c.cp,2) = $zipCode
                AND p.societe_id = $socId
            ;";
          $stmt = $connection->executeQuery($q);
          return $stmt->fetchAll();
    }
    
    /**
     * Récupère la liste des exceptions en fonction du produit
     * @param int $zipCode Le code de département
     * @param int $prodId L'ID de produit
     * @param int $fluxId L'ID de flux
     * @return mixed Le tableau d'enregistrements
     */
    public function getExceptionsByProd($zipCode,$prodId, $fluxId = 0) {
        $connection = $this->getEntityManager()->getConnection();
        $q = "
             SELECT 
                rp.*, c.libelle as ville, c.insee, c.cp,
                d.id AS depot_id, d.libelle AS depot_nom, d.adresse,
                c.id AS communeId
            FROM
                repar_prod rp
            JOIN commune c ON c.id = rp.commune_id
            JOIN depot d ON d.id = rp.depot_id
            WHERE";
                
        if ($fluxId > 0){
            $q .= " rp.flux_id = $fluxId 
                AND ";
        }
        
        $q .= " LEFT(c.cp,2) = $zipCode
                AND rp.produit_id = $prodId
            ;";
          $stmt = $connection->executeQuery($q);
          return $stmt->fetchAll();
    }
    
    /**
     * Récupère la liste des exceptions en fonction du produit et du dépot pour un département et un flux donné
     * @param int $zipCode Le code de département
     * @param int $prodId L'ID de produit
     * @param int $depotId L'ID du dépot
     * @param int $fluxId L'ID de flux
     * @return mixed Le tableau d'enregistrements
     */
    public function getExceptionsByProdAndDepot($zipCode,$prodId, $depotId, $fluxId = 0) {
        $connection = $this->getEntityManager()->getConnection();
        $q = "
             SELECT 
                rp.*, c.libelle as ville, c.insee, c.cp,
                d.id AS depot_id, d.libelle AS depot_nom, d.adresse,
                c.id AS communeId
            FROM
                repar_prod rp
            JOIN commune c ON c.id = rp.commune_id
            JOIN depot d ON d.id = rp.depot_id
            WHERE ";
                
        if ($fluxId > 0){
            $q .= " rp.flux_id = $fluxId 
                AND ";
        }
        
        $q .= " LEFT(c.cp,2) = $zipCode
                AND rp.produit_id = $prodId 
                AND rp.depot_id = $depotId
            ;";
          $stmt = $connection->executeQuery($q);
          return $stmt->fetchAll();
    }
   

    /**
     * 
     * @param string $dept Le code du département
     * @param array $aCommuneId Un tableau contenant les ID des communes
     * @param int $depotId L'ID du dépot
     * @param int $prodId L'ID du produit
     * @param int $fluxId L'ID du flux
     * @param type $userId
     * @param date $dateDebut
     * @param date $date_fin
     */
    public function insertExceptions($aCommuneId,$depotId,$prodId,$fluxId, $userId, $dateDebut, $dateFin) {
       
        $value = '';
        foreach($aCommuneId as $key=>$commune){
            $value.="($fluxId,$commune,$prodId,$depotId, $userId, NOW(), '$dateDebut', '$dateFin')";
            if($key + 1 < count($aCommuneId))
                $value.=",";
        }
            
        if($value != ''){
            $q = "
                INSERT INTO repar_prod
                    (flux_id,commune_id,produit_id,depot_id, utilisateur_id, date_modif, date_debut, date_fin)
                VALUES
                    $value
            ";
            return $this->_em->getConnection()->prepare($q)->execute();
        }
                            
    }
    
    
    
      
     /**
     * mettre une date de fin a une exception
     * @param int $prodId l'ID de la societe
     * @param int $communeId L'ID de la commune
     * @return mixed Le tableau d'enregistrements
     */
    public function UpdateExceptions($prodId, $listInsee, $date_fin, $userId) {
        $connection = $this->getEntityManager()->getConnection();
        $q = "  
            UPDATE 
                repar_prod set date_fin = '".$date_fin."', 
                date_modif = NOW(),
                utilisateur_id = $userId
            WHERE  commune_id IN  (".$listInsee.")
            AND produit_id = $prodId
            AND date_fin >  NOW()
        ";

       return  $connection->executeQuery($q);
        
    }

}