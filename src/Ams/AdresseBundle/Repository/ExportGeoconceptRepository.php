<?php

namespace Ams\AdresseBundle\Repository;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\DBALException;

/**
 * Description of ExportRepository
 *
 * @author madelise
 */
class ExportGeoconceptRepository extends EntityRepository implements ContainerAwareInterface {

    private $container;

    public function setContainer(ContainerInterface $container = null) {
        $this->container = $container;
    }
    
    /**
     * Méthode qui liste les tournées comprises dans un export Geoconcept
     * @param int $iRequeteExportId L'ID de la requete d'export
     * @return array Le tableau contenant la liste des tournées de l'export
     */
    public function listerTournees($iRequeteExportId){
        $connection = $this->getEntityManager()->getConnection();
        $query = "SELECT DISTINCT code_tournee AS nb_tournees FROM export_geoconcept WHERE requete_export_id = $iRequeteExportId;";
        $stmt = $connection->executeQuery($query);
        $result = $stmt->fetchAll();
        return $result;
    }
    
    /**
     * Méthode qui compte le nombre de points par tournée
     * @param int $iRequeteExportId L'ID de la requete d'export
     * @return array Le tableau des points de livraison distincts par tournée
     */
    public function compterPointsParTournee($iRequeteExportId){
        $connection = $this->getEntityManager()->getConnection();
        $query = "SELECT code_tournee, COUNT(DISTINCT point_livraison_id) AS nb_points FROM export_geoconcept WHERE requete_export_id = $iRequeteExportId GROUP BY code_tournee;";
        $stmt = $connection->executeQuery($query);
        return $stmt->fetchAll();
    }
    
    /**
     * Méthode qui compte le nombre de points distincts dans l'export
     * @param int $iRequeteExportId L'ID de la requete d'export
     * @return int Le nombre total de points distincts dans les données de l'export
     */
    public function compterPointsDansExport($iRequeteExportId){
        $connection = $this->getEntityManager()->getConnection();
        $query = "SELECT COUNT(DISTINCT point_livraison_id) AS total FROM export_geoconcept WHERE requete_export_id = $iRequeteExportId";
        $stmt = $connection->executeQuery($query);
        $result = $stmt->fetchAll();
        
        return (int)$result[0]['total'];
    }
    
    
}
