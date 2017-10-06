<?php
namespace Ams\PaieBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class PaiHeureRepository extends GlobalRepository {

    function select($depot_id, $flux_id, $date_distrib) {
        // ATTENTION, rajouter un bloquage avec isModif !!!
        $sql = "SELECT
                    ph.id,
                    ph.groupe_id,
                    gt.depot_id,
                    gt.flux_id,
                    ph.date_distrib,
                    date_format(ph.heure_debut_theo,'%H:%i') as heure_debut_theo,
                    date_format(ph.duree_attente,'%H:%i') as duree_attente,
                    date_format(ph.heure_debut,'%H:%i') as heure_debut
                FROM pai_heure ph
                INNER JOIN groupe_tournee gt ON gt.id = ph.groupe_id
                WHERE  gt.depot_id=" . $depot_id . "  AND  gt.flux_id=" . $flux_id . "
                AND ph.date_distrib = '" . $date_distrib . "'
                ORDER BY gt.code
                ";

        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function ajouter(&$msg, &$msgException, $user, $date_distrib, $modele_tournee_jour_id) {
        try {
                $sql = "INSERT INTO pai_heure(groupe_id,date_distrib,heure_debut,heure_debut_theo,duree_attente,utilisateur_id,date_creation)
                    SELECT gt.id,'" . $date_distrib . "',gt.heure_debut,gt.heure_debut,'00:00'," . $user . ",now()
                    FROM modele_tournee_jour mtj
                    INNER JOIN modele_tournee mt on mtj.tournee_id=mt.id
                    INNER JOIN groupe_tournee gt on mt.groupe_id=gt.id
                    WHERE mtj.id=".$modele_tournee_jour_id."
                    AND not exists(select null from pai_heure where date_distrib='" . $date_distrib . "' and groupe_id=gt.id)";
                $this->_em->getConnection()->prepare($sql)->execute();
                $id = $this->_em->getConnection()->lastInsertId();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "L'heure doit être unique.","UNIQUE","");
        }
        return true;
    }

    public function update(&$msg, &$msgException, $param, $user, &$id) {
        try {
                $sql = "UPDATE pai_heure SET
                    duree_attente = " . $this->sqlField->sqlDuree($param['duree_attente']) . ",
                    heure_debut = " . $this->sqlField->sqlHeureOrNull($param['heure_debut']) . ",
                    utilisateur_id = " . $user . ",
                    date_modif = NOW()";
                $sql .= " WHERE id = " . $param['gr_id'];
                $this->_em->getConnection()->prepare($sql)->execute();
/*                
                $sql = "UPDATE pai_tournee SET
                    heure_debut = " . $this->sqlField->sqlHeure($param['heure_debut']) . ",
                    utilisateur_id = " . $user . ",
                    date_modif = NOW()";
                $sql .= " WHERE heure_id = " . $param['gr_id'];
                $this->_em->getConnection()->prepare($sql)->execute();
                */
                // Attention : on recalcul sur tout le depot/flux au lieu de groupe seulement (mais comment faire autrement avec les activités !!!)
                $this->recalcul_horaire($validation_id, $param['depot_id'], $param['flux_id'], $param['date_distrib'], null);
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "L'heure du groupe tournée doit être unique.", "UNIQUE", "");
        }
        return true;
    }

    public function recalcul_horaire(&$validation_id, $depot_id, $flux_id, $date_distrib, $employe_id) {
        try {
            $sql = "call recalcul_horaire(@validation_id," . $this->sqlField->sqlIdOrNull($depot_id) . "," . $this->sqlField->sqlIdOrNull($flux_id). "," . $this->sqlField->sqlTrimQuote($date_distrib) . "," . $this->sqlField->sqlIdOrNull($employe_id) . ")";
            if (isset($validation_id)){
                $sql="set @validation=".$this->sqlField->sqlId($validation_id).";".$sql;
            }
            $validation_id = $this->executeProc($sql, "@validation_id");
        } catch (DBALException $ex) {
            throw $ex;
        }
    }

    public function recalcul_horaire_tournee(&$validation_id, $tournee_id) {
        try {
            $sql = "call recalcul_horaire_tournee(" . $this->sqlField->sqlId($validation_id)  . "," . $this->sqlField->sqlIdOrNull($tournee_id) . ")";
            if (isset($validation_id)){
                $sql="set @validation=".$this->sqlField->sqlId($validation_id).";".$sql;
            }
            $validation_id = $this->executeProc($sql, "@validation_id");
        } catch (DBALException $ex) {
            throw $ex;
        }
   }
    public function recalcul_horaire_contrathp(&$validation_id, $date_distrib, $xaoid) {
        try {
            $sql = "call recalcul_horaire_contrathp(@validation_id," . $this->sqlField->sqlTrimQuote($date_distrib) . "," . $this->sqlField->sqlTrimQuote($xaoid) . ")";
            if (isset($validation_id)){
                $sql="set @validation=".$this->sqlField->sqlId($validation_id).";".$sql;
            }
            $validation_id = $this->executeProc($sql, "@validation_id");
        } catch (DBALException $ex) {
            throw $ex;
        }
    }


    function getEmargement($depot_id, $flux_id, $date_distrib) {
        $sql = "SELECT 
                    ph.groupe_id,
                    gt.code,
                    TIME_FORMAT(ph.heure_debut_theo, '%Hh%i') as heure_debut,
                    TIME_FORMAT(ph.duree_attente, '%Hh%i')  as duree_attente
                FROM  pai_heure ph 
                INNER JOIN groupe_tournee gt on ph.groupe_id=gt.id
                WHERE gt.depot_id=" . $depot_id . " AND gt.flux_id=" . $flux_id . " AND  ph.date_distrib='" . $date_distrib . "'
                order by code
        ";
        return $this->_em->getConnection()->fetchAll($sql);
    }
}
