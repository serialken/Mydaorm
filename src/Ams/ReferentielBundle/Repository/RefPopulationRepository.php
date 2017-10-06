<?php
namespace Ams\ReferentielBundle\Repository;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityRepository;

class RefPopulationRepository extends EntityRepository {

    function select() {
        $sql = "SELECT
                id,
                code,
                libelle,
                emploi_id,
                typecontrat_id,
                typeurssaf_id,
                typetournee_id,
                majoration,
                majoration_df,
                majoration_dfq,
                majoration_nuit,
                est_badge,
                km_paye,
                ouverture
                FROM ref_population
                ORDER BY libelle"
        ;
        return $this->_em->getConnection()->fetchAll($sql);
    }

    function selectCombo() {
        $sql = "SELECT
                id,
                libelle
                FROM ref_population
                ORDER BY libelle"
        ;
        return $this->_em->getConnection()->fetchAll($sql);
    }

    function selectComboHorsCadre() {
        $sql = "SELECT
                p.id,
                p.libelle
                FROM ref_population p
                INNER JOIN ref_emploi e ON p.emploi_id=e.id
                WHERE e.code<>'AUT'
                ORDER BY p.libelle"
        ;
        return $this->_em->getConnection()->fetchAll($sql);
    }

}
