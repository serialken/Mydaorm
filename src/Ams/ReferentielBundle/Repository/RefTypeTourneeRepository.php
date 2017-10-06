<?php
namespace Ams\ReferentielBundle\Repository;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityRepository;

class RefTypeTourneeRepository extends EntityRepository
{
    function selectCombo() {
        $sql = "SELECT
                id,
                libelle
                FROM ref_typetournee
                WHERE id in (0,1,2)
                ORDER BY id"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
    function selectComboAll() {
        $sql = "SELECT
                id,
                libelle
                FROM ref_typetournee
                ORDER BY id"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
}
