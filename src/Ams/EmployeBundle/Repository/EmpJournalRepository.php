<?php
namespace Ams\EmployeBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class EmpJournalRepository extends GlobalRepository {

    function select($depot_id, $flux_id) {
        $sql = "SELECT
                ej.id,
                ej.depot_id,
                ej.flux_id,
                ere.level,
                ere.rubrique,
                ere.code,
                ere.msg,
                ere.couleur,
                ej.employe_id,
                ej.commentaire
              from emp_journal ej
              inner join emp_ref_erreur ere on ej.erreur_id=ere.id
              where ej.depot_id=$depot_id and ej.flux_id=$flux_id
              order by ere.level,ej.employe_id,ere.rubrique,ere.code"
        ;
        return $this->_em->getConnection()->fetchAll($sql);
    }

    function selectValidation($validation_id) {
        $sql = "SELECT 
                    coalesce(min(ere.valide),true) as valide,
                    group_concat(ere.msg order by ere.level,ere.rubrique,ere.code separator '<br/>') as msg,
                    min(ej.id) as journal_id,
                    min(ere.level) as level
                FROM emp_journal ej
              inner join emp_ref_erreur ere on ej.erreur_id=ere.id
                WHERE validation_id=$validation_id
                GROUP BY validation_id"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function actualisation(&$msg, &$msgException, $depot_id, $flux_id) {
        try {
            $validation_id=null;
            $sql = "call emp_valide_rh(@validation_id,".$this->sqlField->sqlIdOrNull($depot_id).",".$this->sqlField->sqlIdOrNull($flux_id).")";
            $validation_id = $this->executeProc($sql, "@validation_id");
            return $validation_id;
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "", "", "");
        }
        return true;
     }
     
    public function getMsg($validation_id, &$valide, &$msg, &$level, &$journal_id) {
        try {
            $valide = true;
//            $msg = '';
            $level = '';
            $journal_id = '';
            if ($validation_id != 0) {
                $result = $this->selectValidation($validation_id);
                if (isset($result[0])) {
                    $valide = $result[0]["valide"];
                    $msg .= ($msg!=''?'<br/>':'').$result[0]["msg"];
                    $journal_id = $result[0]["journal_id"];
                    $level = $result[0]["level"];
                }
            }
        } catch (DBALException $ex) {
            throw $ex;
        }
    }

}
