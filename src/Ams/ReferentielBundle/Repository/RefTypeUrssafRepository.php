<?php
namespace Ams\ReferentielBundle\Repository;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityRepository;

class RefTypeUrssafRepository extends EntityRepository
{
   function select() {
        $sql = "SELECT
                id,
                code,
                libelle
                FROM ref_typeurssaf
                ORDER BY libelle"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
   function selectCombo() {
        $sql = "SELECT
                id,
                libelle
                FROM ref_typeurssaf
                ORDER BY libelle"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
   function selectComboCode() {
        $sql = "SELECT
                code as id,
                libelle
                FROM ref_typeurssaf
                ORDER BY libelle"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
}
