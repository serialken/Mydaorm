<?php

namespace Ams\DistributionBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\DBALException;

class FeuillePortageTmpRepository extends EntityRepository {

    
    public function truncateFeuillePortageTmp() {
        try {
            $sql   = "TRUNCATE TABLE feuille_portage_tmp";
            $this->_em->getConnection()->prepare($sql)->execute();
        }
        catch (DBALException $ex) {
            throw $ex;
        }
    }
    
    public function insertFeuillePortageTmp($sDepotCode,$iflux,$date) {
        try {
            $sql = 'call feuille_portage("'.$sDepotCode.'",'.$iflux.',"'.$date.'")';
            $this->_em->getConnection()->prepare($sql)->execute();
        } catch (DBALException $ex) {
            throw $ex;
        }
    }
    
    public function getFeuillePortageTmpByDepotFlux($aParam) {
        try {
            $sql = 'SELECT * FROM feuille_portage_tmp '
                  .'WHERE depot_code = "'.$aParam['depotCode'].'" '
                  .'AND flux = '.$aParam['fluxId'];
            if($aParam['tourneeId'])
                $sql.=' AND tournee_jour_id IN('.$aParam['tourneeId'].')';
            if($aParam['produitId'])
                $sql.=' AND produit_id IN('.$aParam['produitId'].')';            
            if(!empty($aParam['socCode'])) {
                if(implode(',',$aParam['socCode']) != '')
                    $sql.=" AND societe_code IN('".implode("','",$aParam['socCode'])."')";
            }
            
            $sql.=' ORDER BY point_livraison_ordre,point_livraison_id,num_abonne,societe_code,produit_code ';

            return $this->_em->getConnection()->fetchAll($sql);
        } catch (DBALException $ex) {
            throw $ex;
        }
    }
    
    public function getFeuillePortageTmpOperationByTournee($iTournee) {
        try {
            $sql = 'SELECT 
                        *
                        ,(SELECT sum(tmp.qte) from feuille_portage_tmp tmp where tmp.produit_id = fp.produit_id AND tmp.tournee_jour_code = fp.tournee_jour_code )as qte_journaux_produit 
                        ,(SELECT sum(tmp.qte) from feuille_portage_tmp tmp where tmp.tournee_jour_code = fp.tournee_jour_code )as qte_journaux
                        ,(SELECT count(distinct(point_livraison_id)) from feuille_portage_tmp tmp where tmp.produit_id = fp.produit_id AND tmp.tournee_jour_code = fp.tournee_jour_code)as qte_pointLivraison_produit
                        ,(SELECT count(distinct(point_livraison_id)) from feuille_portage_tmp tmp where tmp.tournee_jour_code = fp.tournee_jour_code) as qte_pointLivraison 
                        ,(SELECT count(distinct(num_abonne)) from feuille_portage_tmp tmp where tmp.produit_id = fp.produit_id AND tmp.tournee_jour_code = fp.tournee_jour_code)as qte_abonne_produit
                        ,(SELECT count(distinct(num_abonne)) from feuille_portage_tmp tmp where tmp.tournee_jour_code = fp.tournee_jour_code) as qte_abonne 
                    FROM feuille_portage_tmp 
                    WHERE tournee_jour_id = '.$iTournee
                  ;
            
            return $this->_em->getConnection()->fetchAll($sql);
        } catch (DBALException $ex) {
            throw $ex;
        }
    }
    
    public function getNewAbonne($dateDistrib,$depotCode,$flux) {
        $connection = $this->getEntityManager()->getConnection();
        $q =    "SELECT
                    *
                FROM
                    feuille_portage_tmp fp
                WHERE
                    depot_code = '$depotCode'
                AND 
                    flux = $flux
                AND 
                    date_service_1 >= ('$dateDistrib' - INTERVAL 7 DAY)
                ";

        $stmt = $connection->executeQuery($q);
        return $stmt->fetchAll();
    }
    
    public function getAbonneStop($dateDistrib,$depotCode,$flux) {
        $connection = $this->getEntityManager()->getConnection();
        $q =    "SELECT
                    *
                FROM
                    feuille_portage_tmp fp
                WHERE
                    depot_code = '$depotCode'
                AND 
                    flux = $flux
                AND 
                    date_stop >= ('$dateDistrib' - INTERVAL 7 DAY)
                ";

        $stmt = $connection->executeQuery($q);
        return $stmt->fetchAll();
    }
    
    public function getPointLivraisonByTournee($iTournee) {
        $connection = $this->getEntityManager()->getConnection();
        $q =    "SELECT 
                    point_livraison_id,produit_id
                FROM
                    feuille_portage_tmp
                WHERE
                    tournee_jour_id = $iTournee
                GROUP BY point_livraison_id,produit_id
                ";

        $stmt = $connection->executeQuery($q);
        return $stmt->fetchAll();
    }
    
    public function getAbonneByTournee($iTournee) {
        $connection = $this->getEntityManager()->getConnection();
        $q =    "SELECT 
                    num_abonne,produit_id
                FROM
                    feuille_portage_tmp
                where
                    tournee_jour_id = $iTournee
                ";

        $stmt = $connection->executeQuery($q);
        return $stmt->fetchAll();
    }
        
}
