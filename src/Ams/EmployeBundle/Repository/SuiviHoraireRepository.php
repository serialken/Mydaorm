<?php
namespace Ams\EmployeBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class SuiviHoraireRepository extends GlobalRepository {

    public function select($depot_id, $flux_id, $anneemois) {
        //projection mensuelle : (Heures réalisées / Nombre de jours travaillés depuis le 21) * (Nombre de jours à travailler du 21 au 20).
        $sql = "select 
                    e.matricule,
                    phg.employe_id,
                    phg.emploi_id as emploi_id,
                    phg.date_debut,
                    phg.date_fin,
                    phg.nbheures_garanties,
                    phg.nbjours_cycle,
                    round(phg.horaire_moyen,2) as horaire_moyen,
                    phg.nbjours_travailles,
                    round(phg.nbheures_a_realiser,2) as nbheures_a_realiser,
                    round(phg.nbheures_realisees,2) as nbheures_realisees,
                    round(phg.nbheures_delegation,2) as nbheures_delegation,
                    round(phg.nbheures_hors_presse,2) as nbheures_hors_presse,
                    round(phg.nbheures_realisees/phg.nbjours_travailles*phg.nbjours_cycle,2) as projection_mensuelle,
                    round(phg.suivi_horaire,2) as suivi_horaire,
                    round(phg.nbheures_garanties_majorees,2) as nbheures_garanties_majorees,
                    round(phg.nbheures_garanties_apayer,2) as nbheures_garanties_apayer
                from pai_hg phg
                inner join employe e on phg.employe_id=e.id
                where phg.anneemois='" . $anneemois . "'
                and phg.depot_id='" . $depot_id . "' and phg.flux_id='" . $flux_id . "'
                order by e.nom,e.prenom1,e.prenom2;
              ";
        return $this->_em->getConnection()->fetchAll($sql);;
    }


    public function selectCombo() {
        $sql = "SELECT
                id,
                libelle
                FROM ref_emploi
                UNION
                SELECT
                -id,
                libelle
                FROM ref_activite a
                WHERE a.est_pleiades
                ORDER BY 2"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function actualisation(&$msg, &$msgException, $anneemois, $depot_id = 0, $flux_id = 0) {
        try {
            $sql = "call recalcul_hg_exec('" . $anneemois . "'," . $this->sqlField->sqlIdOrNull($depot_id) . "," . $this->sqlField->sqlIdOrNull($flux_id) . ");";
            $this->_em->getConnection()->prepare($sql)->execute();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "", "", "");
        }
        return true;
    }
}
