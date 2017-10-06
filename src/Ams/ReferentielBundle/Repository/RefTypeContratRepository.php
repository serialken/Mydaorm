<?php
namespace Ams\ReferentielBundle\Repository;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityRepository;

class RefTypeContratRepository extends EntityRepository
{
    function selectCombo() {
        $sql = "SELECT
                id,
                code as libelle
                FROM ref_typecontrat
                ORDER BY id"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
}
