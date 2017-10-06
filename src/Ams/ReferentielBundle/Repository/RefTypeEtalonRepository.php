<?php
namespace Ams\ReferentielBundle\Repository;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityRepository;

class RefTypeEtalonRepository extends EntityRepository
{
   function selectCombo() {
        $sql = "SELECT
                id,
                libelle
                FROM ref_typeetalon
                ORDER BY libelle"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
}
