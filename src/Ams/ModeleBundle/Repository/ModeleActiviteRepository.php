<?php
namespace Ams\ModeleBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class ModeleActiviteRepository extends GlobalRepository {
    function select($depot_id, $flux_id, $est_hors_presse, $sqlCondition='') {
        $sql = "SELECT
                    ma.id,
                    ma.depot_id,
                    ma.flux_id,
                    ma.jour_id,
                    ma.activite_id,
                    ma.employe_id,
                    ma.transport_id,
                    date_format(ma.date_debut,'%d/%m/%Y') as date_debut,
                    date_format(ma.date_fin,'%d/%m/%Y') as date_fin,
                    date_format(ma.heure_debut,'%H:%i') as heure_debut,
                    date_format(ma.duree,'%H:%i') as duree,
                    ma.nbkm_paye,
                    ma.commentaire,
                    -- journal
                    coalesce(min(me.valide),true) as valide,
                    group_concat(me.msg order by me.level,me.rubrique,me.code separator '<br/>') as msg,
                    min(mj.id) as journal_id,
                    min(me.level) as level
                FROM modele_activite ma
                inner join ref_activite ra on ma.activite_id=ra.id and ra.est_hors_presse=".($est_hors_presse?"true":"false")."
                left outer join modele_journal mj on ma.id=mj.activite_id
                LEFT OUTER JOIN modele_ref_erreur me ON mj.erreur_id=me.id
                WHERE ma.depot_id=" . $depot_id . " AND ma.flux_id=" . $flux_id." ".
                ($sqlCondition!='' ? $sqlCondition : "")."
                GROUP BY ma.id
                ORDER BY ma.date_fin desc,ma.employe_id,ma.jour_id,ma.heure_debut,ma.activite_id"
                ;
            return $this->_em->getConnection()->fetchAll($sql);
    }

    public function selectPlanning($depot_id, $flux_id, $employe_id) {
        $sql = "SELECT
                    ma.id,
                    ma.depot_id,
                    ma.flux_id,
                    ma.jour_id,
                    coalesce(ma.heure_debut,'00:00') as heure_debut,
                    coalesce(addtime(ma.heure_debut,ma.duree),'24:00') as heure_fin,
                    ra.libelle,
                    ra.couleur,
                    -- journal
                    coalesce(min(me.valide),true) as valide,
                    group_concat(me.msg order by me.level,me.rubrique,me.code separator '<br/>') as msg,
                    min(mj.id) as journal_id,
                    min(me.level) as level
                FROM modele_activite ma
                INNER JOIN ref_activite ra ON ma.activite_id=ra.id
                left outer join modele_journal mj on ma.id=mj.activite_id
                LEFT OUTER JOIN modele_ref_erreur me ON mj.erreur_id=me.id
                WHERE ma.depot_id = " . $depot_id . " AND ma.flux_id = " . $flux_id."
                AND ma.employe_id = " . $this->sqlField->sqlIdOrNull($employe_id)."
                AND curdate() between ma.date_debut and ma.date_fin
                GROUP BY ma.id
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function insert(&$msg, &$msgException, $param, $user, &$id) {
        try {
            $sql = "INSERT INTO modele_activite(
                depot_id, flux_id,jour_id,
                activite_id, employe_id, transport_id,
                date_debut, date_fin,
                heure_debut, duree,
                nbkm_paye,
                commentaire,
                utilisateur_id, date_creation
                ) SELECT 
                " . $param['depot_id'] . "," . $param['flux_id'] . "," . $param['jour_id'] . ",
                " . $param['activite_id'] . "," . $this->sqlField->sqlTrim($param['employe_id']) . "," . $this->sqlField->sqlTrim($param['transport_id']) . ",
                " . $this->sqlField->sqlDate($param['date_debut']) . "," . $this->sqlField->sqlDateOr2999($param['date_fin']) . ",
                " . $this->sqlField->sqlTrimQuote($param['heure_debut']) . "," . $this->sqlField->sqlTrimQuote($param['duree']) . ",
                case when coalesce(rt.km_paye,1) and coalesce(ra.km_paye,1) then " . $this->sqlField->sqlTrim($param['nbkm_paye']) . " else 0 end,
                " . $this->sqlField->sqlTrimQuote($param['commentaire']) . ",
                " . $user . ",NOW()
                FROM ref_activite ra
                left outer join ref_transport rt on rt.id=" . $this->sqlField->sqlTrim($param['transport_id']) . "
                WHERE ra.id=" . $this->sqlField->sqlTrim($param['activite_id']);
            $this->_em->getConnection()->prepare($sql)->execute();
            $id = $this->_em->getConnection()->lastInsertId();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "L'activite doit être unique.","UNIQUE","");
        }
        return true;
    }

    public function update(&$msg, &$msgException, $param, $user, &$id) {
        try {
            $sql = "UPDATE modele_activite ma
                left outer join ref_activite ra on ra.id=" . $this->sqlField->sqlTrim($param['activite_id']) . "
                left outer join ref_transport rt on rt.id=" . $this->sqlField->sqlTrim($param['transport_id']) . "
                SET
                ma.jour_id = " . $param['jour_id'] . ",
                ma.activite_id = " . $param['activite_id'] . ",
                ma.employe_id = " . $this->sqlField->sqlTrim($param['employe_id']) . ",
                ma.transport_id = " . $this->sqlField->sqlTrim($param['transport_id']) . ",
                ma.date_debut = " . $this->sqlField->sqlDate($param['date_debut']) . ",
                ma.date_fin = " . $this->sqlField->sqlDateOr2999($param['date_fin']) . ",
                ma.heure_debut = " . $this->sqlField->sqlTrimQuote($param['heure_debut']) . ",
                ma.duree = " . $this->sqlField->sqlTrimQuote($param['duree']) . ",
                ma.nbkm_paye = case when coalesce(rt.km_paye,1) and coalesce(ra.km_paye,1) then " . $this->sqlField->sqlTrim($param['nbkm_paye']) . " else 0 end,
                ma.commentaire = " . $this->sqlField->sqlTrimQuote($param['commentaire']) . ",
                ma.utilisateur_id = " . $user . ",
                ma.date_modif = NOW()";
            $sql .= " WHERE ma.id =" . $param['gr_id'];
            $this->_em->getConnection()->prepare($sql)->execute();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "L'activite doit être unique.","UNIQUE","");
        }
        return true;
    }

    public function delete(&$msg, &$msgException, $param) {
        try {
            $this->_em->getConnection()->beginTransaction();
            $this->_em->getConnection()->prepare("DELETE FROM modele_journal WHERE activite_id=" . $param['gr_id'])->execute();
            $this->_em->getConnection()->prepare("DELETE FROM modele_activite WHERE id=" . $param['gr_id'])->execute();
            $this->_em->getConnection()->commit();
        } catch (DBALException $ex) {
            $error= $this->sqlField->sqlError($msg, $msgException, $ex, "Suppression impossible.","FOREIGN", "");
            $this->_em->getConnection()->rollBack();
            return $error;
        }
        return true;
    }

    public function validate(&$msg, &$msgException, $id, $action="", $param=null) {
        try {
            $sql = "call mod_valide_activite(@validation_id,null,null," . $this->sqlField->sqlId($id) . ")";
            return $this->executeProc($sql, "@validation_id");
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "Erreur inconnue.", "", "");
       }
    }
}
