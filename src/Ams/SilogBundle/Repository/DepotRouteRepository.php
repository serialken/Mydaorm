<?php

namespace Ams\SilogBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\DBALException;

class DepotRouteRepository extends EntityRepository
{
    public function insertRoute($utilisateur,$codeRoute, $libelleRoute, $codeCentre, $libelleCentre, $createdAt,$actif = 0){
        try {
        
            $aInsertVar = array();
            $aInsertVar['utilisateur_id']     = $utilisateur;
            $aInsertVar['code_route']     = $codeRoute;
            $aInsertVar['libelle_route']     = $libelleRoute;
            $aInsertVar['code_centre']     = $codeCentre;
            $aInsertVar['libelle_centre']     = $libelleCentre;
            $aInsertVar['created_at']     = $createdAt;
            $aInsertVar['actif']     = $actif;
            $this->_em->getConnection()->insert('depot_route', $aInsertVar);
            return $this->_em->getConnection()->lastInsertId();
        } 
        catch (DBALException $ex) {
            throw $ex;
        }
        
        
        
    }
}
