<?php
namespace Ams\EmployeBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class EmpAffectationRepository extends GlobalRepository {

    function select($depot_id, $flux_id, $anneemois_id, $sqlCondition='') {
        $sql = "SELECT
                    eaf.id,
                    eaf.flux_id,
                    eaf.depot_org_id,
                    eaf.depot_dst_id,
                    eaf.contrat_id,
                    eaf.date_debut,
                    eaf.date_fin,
                    eaf.commentaire,
                    eaf.depot_org_id=$depot_id as isModif,
                    eaf.depot_org_id=$depot_id as isDelete
--                    eaf.date_fin between date_add(now(),interval -30 day) and '2999-01-01' as isModif
                FROM emp_affectation eaf
                INNER JOIN emp_contrat eco on eaf.contrat_id=eco.id
                INNER JOIN employe e on eco.employe_id=e.id
                INNER JOIN pai_ref_mois prm ON eaf.date_debut<=prm.date_fin and eaf.date_fin>=prm.date_debut
                WHERE (eaf.depot_org_id=$depot_id or eaf.depot_dst_id=$depot_id)
                AND eaf.flux_id=$flux_id
                AND prm.anneemois='$anneemois_id'
                $sqlCondition
                ORDER BY e.nom,e.prenom1,e.prenom2,eaf.date_debut
            ";
        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function insert(&$msg, &$msgException, $param, $user, &$id) {
        try {
            $sql = "INSERT INTO emp_affectation SET
                    depot_org_id = " . $param['depot_org_id'] . ",
                    depot_dst_id = " . $param['depot_dst_id'] . ",
                    flux_id = " . $param['flux_id'] . ",
                    contrat_id = " . $this->sqlField->sqlTrim($param['contrat_id']) . ",
                    commentaire = " . $this->sqlField->sqlTrimQuote($param['commentaire']) . ",
                    date_debut = " . $this->sqlField->sqlDate($param['date_debut']) . ",
                    date_fin = " . $this->sqlField->sqlDateOr2999($param['date_fin']) . ",
                    utilisateur_id = $user,
                    date_creation = NOW()";
            $this->_em->getConnection()->prepare($sql)->execute();
            $id = $this->_em->getConnection()->lastInsertId();
//            return $this->updateDateFin($msg, $msgException, $param);
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "", "", "");
        }
        return true;
    }
    
    public function update(&$msg, &$msgException, $param, $user, &$id) {
        try {
            $sql = "UPDATE emp_affectation eaf SET
                    depot_org_id = " . $param['depot_org_id'] . ",
                    depot_dst_id = " . $param['depot_dst_id'] . ",
                    flux_id = " . $param['flux_id'] . ",
                    contrat_id = " . $this->sqlField->sqlTrim($param['contrat_id']) . ",
                    commentaire = " . $this->sqlField->sqlTrimQuote($param['commentaire']) . ",
                    date_debut = " . $this->sqlField->sqlDate($param['date_debut']) . ",
                    date_fin = " . $this->sqlField->sqlDateOr2999($param['date_fin']) . ",
                    utilisateur_id = $user,
                    date_creation = NOW()
                WHERE eaf.id =" . $param['gr_id'];
            $this->_em->getConnection()->prepare($sql)->execute();
//            return $this->updateDateFin($msg, $msgException, $param);
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "Le transfert doit être unique.", "UNIQUE", "");
        }
        return true;
    }

    public function delete(&$msg, &$msgException, $param) {
        try {
            $this->_em->getConnection()->beginTransaction();
//            $this->_em->getConnection()->prepare("DELETE FROM emp_journal WHERE transfert_id = " . $param['gr_id'])->execute();
            $this->_em->getConnection()->prepare("DELETE FROM emp_affectation WHERE id=" . $param['gr_id'])->execute();
//            $this->updateDateFin($msg, $msgException, $param);
            $this->_em->getConnection()->commit();
        } catch (DBALException $ex) {
            $error = $this->sqlField->sqlError($msg, $msgException, $ex, "Suppression impossible.", "FOREIGN", "");
            $this->_em->getConnection()->rollBack();
            return $error;
        }
        return true;
    }
 
/* Rammene la date de transfert à la date de début
 * Ne marche pas si il y a plusieurs transfert anterieur à la date de début de contrat
 * 
update emp_transfert et
inner join emp_contrat eco on et.contrat_id=eco.id
left outer join emp_transfert et2 on et.contrat_id=et2.contrat_id and et2.date_debut=eco.date_debut
set et.date_debut=eco.date_debut
where et.date_debut<eco.date_debut
and et2.id is null
 */    
    public function actualisation(&$msg, &$msgException, $user, $depot_id, $flux_id) {
        try {
            ini_set('max_execution_time', 999000);
            $validation_id=null;
            $sql = "call mod_valide_rh(@validation_id,null,".$this->sqlField->sqlIdOrNull($flux_id).")";
            $validation_id = $this->executeProc($sql, "@validation_id");

            $sql = "CALL pai_valide_rh_activite(@validation_id,null,".$this->sqlField->sqlIdOrNull($flux_id).", NULL, NULL)";
            $validation_id = $this->executeProc($sql, "@validation_id");
            
            $sql = "call recalcul_tournee_date_distrib(null, null,".$this->sqlField->sqlIdOrNull($flux_id).")";
            $this->executeProc($sql);

            $sql = "call pai_valide_pleiades(@validation_id,null,".$this->sqlField->sqlIdOrNull($flux_id).")";
            $validation_id = $this->executeProc($sql, "@validation_id");
            return true;
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "", "", "");
        }
        return true;
     }
}
