<?php

namespace Ams\DistributionBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\DBALException;

/**
 * ImprimanteRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ImprimanteRepository extends EntityRepository
{
    
     /**
     * @param string $etat 0si pas actif, 1 si actif
     * @param type $allowdepId liste des id des depots 
     * @return array liste des imprimantes actives accessible à l'utilisateur connecté
     */
    public function getActiveAllowImp($etat, $allowdepId = array()) {
        $sql = "SELECT id ,libelle_imprimante as LibelleImprimante, ip_imprimante as IpImprimante, depotId_id as DepotId, etat as Etat FROM imprimante imp WHERE imp.etat = " . $etat . " AND imp.depotId_id IN ( " . implode(',', $allowdepId) . " );";
        
         return $this->_em->getConnection()->fetchAll($sql);
    }
}
