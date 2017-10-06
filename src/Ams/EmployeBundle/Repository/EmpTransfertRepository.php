<?php
namespace Ams\EmployeBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class EmpTransfertRepository extends GlobalRepository {

    function select($depot_id, $flux_id, $anneemois_id, $sqlCondition='') {
        $sql = "SELECT
                    et.id,
                    et.flux_id,
                    et.depot_org_id,
                    et.depot_dst_id,
                    et.contrat_id,
                    et.date_debut,
                    et.date_fin,
                    et.commentaire,
                    et.depot_org_id=$depot_id as isModif,
                    et.depot_org_id=$depot_id as isDelete
--                    et.date_fin between date_add(now(),interval -30 day) and '2999-01-01' as isModif
                FROM emp_transfert et
                INNER JOIN emp_contrat eco on et.contrat_id=eco.id
                INNER JOIN employe e on eco.employe_id=e.id
                INNER JOIN pai_ref_mois prm ON et.date_debut<=prm.date_fin and et.date_fin>=prm.date_debut
                WHERE (et.depot_org_id=$depot_id or et.depot_dst_id=$depot_id)
                AND et.flux_id=$flux_id
                AND prm.anneemois='$anneemois_id'
                $sqlCondition
                ORDER BY e.nom,e.prenom1,e.prenom2,et.date_debut
            ";
        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function insert(&$msg, &$msgException, $param, $user, &$id) {
        try {
            $sql = "INSERT INTO emp_transfert SET
                    depot_org_id = " . $param['depot_org_id'] . ",
                    depot_dst_id = " . $param['depot_dst_id'] . ",
                    flux_id = " . $param['flux_id'] . ",
                    contrat_id = " . $this->sqlField->sqlTrim($param['contrat_id']) . ",
                    commentaire = " . $this->sqlField->sqlTrimQuote($param['commentaire']) . ",
                    date_debut = " . $this->sqlField->sqlDate($param['date_debut']) . ",
                    utilisateur_id = $user,
                    date_creation = NOW()";
            $this->_em->getConnection()->prepare($sql)->execute();
            $id = $this->_em->getConnection()->lastInsertId();
            return $this->updateDateFin($msg, $msgException, $param);
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "", "", "");
        }
        return true;
    }
    
    public function update(&$msg, &$msgException, $param, $user, &$id) {
        try {
            $sql = "UPDATE emp_transfert et SET
                    depot_org_id = " . $param['depot_org_id'] . ",
                    depot_dst_id = " . $param['depot_dst_id'] . ",
                    flux_id = " . $param['flux_id'] . ",
                    contrat_id = " . $this->sqlField->sqlTrim($param['contrat_id']) . ",
                    commentaire = " . $this->sqlField->sqlTrimQuote($param['commentaire']) . ",
                    date_debut = " . $this->sqlField->sqlDate($param['date_debut']) . ",
                    utilisateur_id = $user,
                    date_creation = NOW()
                WHERE et.id =" . $param['gr_id'];
            $this->_em->getConnection()->prepare($sql)->execute();
            return $this->updateDateFin($msg, $msgException, $param);
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "Le transfert doit être unique.", "UNIQUE", "");
        }
        return true;
    }

    public function delete(&$msg, &$msgException, $param) {
        try {
            $this->_em->getConnection()->beginTransaction();
//            $this->_em->getConnection()->prepare("DELETE FROM emp_journal WHERE transfert_id = " . $param['gr_id'])->execute();
            $this->_em->getConnection()->prepare("DELETE FROM emp_transfert WHERE id=" . $param['gr_id'])->execute();
            $this->updateDateFin($msg, $msgException, $param);
            $this->_em->getConnection()->commit();
        } catch (DBALException $ex) {
            $error = $this->sqlField->sqlError($msg, $msgException, $ex, "Suppression impossible.", "FOREIGN", "");
            $this->_em->getConnection()->rollBack();
            return $error;
        }
        return true;
    }
    
    public function updateDateFin(&$msg, &$msgException, $param) {
        try {
            $sql = "UPDATE emp_transfert et 
                    INNER JOIN (select min_et.id,coalesce((select date_add(min(min_et2.date_debut),interval -1 day) 
                                                    from emp_transfert min_et2
                                                    where min_et.contrat_id=min_et2.contrat_id 
                                                    and min_et2.date_debut>min_et.date_debut),eco.date_fin
                                                    ) as date_fin
                                from emp_transfert min_et
                                inner join emp_contrat eco on min_et.contrat_id=eco.id
                                where min_et.contrat_id=" . $this->sqlField->sqlTrim($param['contrat_id']) . "
                                ) as et2 on et.id=et2.id
                    SET et.date_fin=et2.date_fin
                    WHERE et.contrat_id=" . $this->sqlField->sqlTrim($param['contrat_id']);
            $this->_em->getConnection()->prepare($sql)->execute();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "", "", "");
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
            $idtrt=null;
            $sql = "call INT_PNG2MROAD($user,@idtrt,null,".$this->sqlField->sqlIdOrNull($flux_id).")";
            $idtrt = $this->executeProc($sql, "@idtrt");
            return true;
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "", "", "");
        }
        return true;
     }
}
