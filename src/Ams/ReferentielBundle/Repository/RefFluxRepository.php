<?php
namespace Ams\ReferentielBundle\Repository;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityRepository;

class RefFluxRepository extends EntityRepository
{

    public function getFluxs(){
        $qb = $this->createQueryBuilder('d')
            ->orderBy('d.id','ASC')
            ;
        return $qb->getQuery()->getResult();
    }
    
    function selectCombo() {
        $sql = "SELECT
                id,
                libelle
                FROM ref_flux
                ORDER BY id"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
   
}
