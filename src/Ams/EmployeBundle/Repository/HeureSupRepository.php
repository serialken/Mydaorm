<?php
namespace Ams\EmployeBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class HeureSupRepository extends GlobalRepository {

    public function select($depot_id, $flux_id, $anneemois) {
        $sql = "select
                    round(t.nbheures_hc1,2)<>0 or round(t.nbheures_hc2,2)<>0 or round(t.nbheures_hs1,2)<>0 or round(t.nbheures_hs2,2)<>0 as hchs,
                    e.matricule,
                    t.employe_id,
                    ra.id as emploi_id,
                    ech.date_debut as date_debut_contrat,
                    ech.date_fin as date_fin_contrat,
                    t.date_debut,
                    t.date_fin,
                    t.nbheures_mensuelles,
                    t.nbjours_cycle,
                    t.nbjours_absence,
                    round(t.nbheures_a_realiser,2) as nbheures_a_realiser,
                    round(t.nbheures_realisees,2) as nbheures_realisees,
                    round(t.nbheures_hn,2) as nbheures_hn,
                    round(t.nbheures_hc1,2) as nbheures_hc1,
                    round(t.nbheures_hc2,2) as nbheures_hc2,
                    round(t.nbheures_hs1,2) as nbheures_hs1,
                    round(t.nbheures_hs2,2) as nbheures_hs2,
                    date_extrait is null as isModif
                from pai_hchs t
                inner join employe e on t.employe_id=e.id
                inner join emp_contrat_hp ech on t.xaoid=ech.xaoid
                inner join ref_activite ra on ech.activite_id=ra.id
                where t.anneemois='" . $anneemois . "'
                and t.depot_id='" . $depot_id . "' and t.flux_id='" . $flux_id . "'
                order by e.nom,e.prenom1,e.prenom2,ech.date_debut,t.date_debut;
              ";
        return $this->_em->getConnection()->fetchAll($sql);;
    }

    public function selectCombo() {
        $sql = "SELECT
                id,
                libelle
                FROM ref_activite a
                WHERE a.est_pleiades
                ORDER BY 2"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function actualisation(&$msg, &$msgException, $anneemois, $depot_id = 0, $flux_id = 0) {
        try {
            $sql = "call recalcul_hchs('" . $anneemois . "'," . $this->sqlField->sqlIdOrNull($depot_id) . "," . $this->sqlField->sqlIdOrNull($flux_id) . ");";
            $this->_em->getConnection()->prepare($sql)->execute();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "", "", "");
        }
        return true;
    }
}
