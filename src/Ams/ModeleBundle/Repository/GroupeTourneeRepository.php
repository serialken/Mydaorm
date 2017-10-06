<?php
namespace Ams\ModeleBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class GroupeTourneeRepository extends GlobalRepository {

    public function select($depot_id, $flux_id) {
            $sql = "SELECT
                    gt.id,
                    gt.depot_id,
                    gt.flux_id,
                    gt.code,
                    gt.libelle,
                    date_format(gt.heure_debut,'%H:%i') as heure_debut,
                    date_format(gt.heure_fin,'%H:%i') as heure_fin
                FROM groupe_tournee gt
                WHERE gt.depot_id = " . $depot_id . " AND gt.flux_id = " . $flux_id . " 
                ORDER BY  gt.heure_debut
                ";
            return $this->_em->getConnection()->fetchAll($sql);
    }

    public function insert(&$msg, &$msgException, $param, $user, &$id) {
        try {
            $sql = "INSERT INTO groupe_tournee SET
                    depot_id = " . $param['depot_id'] . ",
                    flux_id = " . $param['flux_id'] . ",
                    code = " . $this->sqlField->sqlQuote($param['code']) . ",
                    libelle = " . $this->sqlField->sqlQuote($param['libelle']) . ",
                    heure_debut = " . $this->sqlField->sqlQuote($param['heure_debut']) . ",
                    heure_fin = " . $this->sqlField->sqlTrimQuote($param['heure_fin']) . ",
                    utilisateur_id = " . $user . ",
                    date_creation = NOW()";
            $this->_em->getConnection()->prepare($sql)->execute();
            $id = $this->_em->getConnection()->lastInsertId();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex,"Le code groupe doit être unique.","UNIQUE","un_groupe_tournee");
        }
        return true;
    }

    public function update(&$msg, &$msgException, $param, $user, &$id) {
        try {
            $sql = "UPDATE groupe_tournee SET
                    code = " . $this->sqlField->sqlQuote($param['code']) . ",
                    libelle = " . $this->sqlField->sqlQuote($param['libelle']) . ",
                    heure_debut = " . $this->sqlField->sqlQuote($param['heure_debut']) . ",
                    heure_fin = " . $this->sqlField->sqlTrimQuote($param['heure_fin']) . ",
                    utilisateur_id = " . $user . ",
                    date_modif = NOW()";
            $sql .= " WHERE id=" . $param['gr_id'];
            // ATTENTION : Il faut revalider les tournees appartenant au groupe
            $this->_em->getConnection()->prepare($sql)->execute();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex,"Le code groupe doit être unique.","UNIQUE","un_groupe_tournee");
        }
        return true;
    }

    public function delete(&$msg, &$msgException, $param) {
        try {
            $this->_em->getConnection()->prepare("DELETE FROM groupe_tournee WHERE id=" . $param['gr_id'])->execute();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex,"Le groupe tournée est utilisé par une tournée.<br/>Suppression impossible.","FOREIGN","");
        }
        return true;
    }

    public function selectCombo($depot_id, $flux_id) {
            $sql = "SELECT
                    gt.id,
                    gt.code libelle
                FROM groupe_tournee gt
                WHERE gt.depot_id = " . $depot_id . " AND gt.flux_id = " . $flux_id ."
                ORDER BY gt.code";
            return $this->_em->getConnection()->fetchAll($sql);
    }
}
