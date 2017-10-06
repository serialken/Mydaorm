<?php
namespace Ams\ReferentielBundle\Repository;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityRepository;

class RefNatureClientRepository extends EntityRepository
{
   function select() {
        $sql = "SELECT
                id,
                code,
                libelle,
                typeurssaf_id,
                duree_livraison
                FROM ref_natureclient
                ORDER BY libelle"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
   function selectCombo() {
        $sql = "SELECT
                id,
                libelle
                FROM ref_natureclient
                ORDER BY libelle"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
}
