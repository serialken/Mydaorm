<?php
namespace Ams\ReferentielBundle\Repository;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityRepository;

class RefCiviliteRepository extends EntityRepository
{
    function selectCombo() {
        $sql = "SELECT
                id,
                libelle
                FROM ref_civilite
                ORDER BY id"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
}
