<?php
namespace Ams\ReferentielBundle\Repository;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityRepository;

class RefJourRepository extends EntityRepository
{
    function selectCombo() {
        $sql = "SELECT
                id,
                libelle
                FROM ref_jour
                ORDER BY id"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    public function getAllId()
    {
        $sql = "SELECT id FROM ref_jour ORDER BY id ";
        return $this->_em->getConnection()->fetchAll($sql);
    }
}
