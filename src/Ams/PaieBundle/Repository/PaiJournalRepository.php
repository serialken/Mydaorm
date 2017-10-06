<?php
namespace Ams\PaieBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class PaiJournalRepository extends GlobalRepository
{
    function select($depot_id, $flux_id, $anneemois_id){  
            $sql = "SELECT
                pj.id,
                pj.depot_id,
                pj.flux_id,
                pj.date_distrib,
                pe.level,
                pe.rubrique,
                pe.code,
                pe.msg,
                pe.couleur,
                pj.employe_id,
                pj.tournee_id,
                pj.produit_id,
                ppt.produit_id as ref_produit_id,
                pj.activite_id,
                pa.activite_id as ref_activite_id,
                pj.commentaire
            from pai_journal pj
            inner join pai_ref_erreur pe on pj.erreur_id=pe.id
            left outer join pai_prd_tournee ppt on pj.produit_id=ppt.id
            left outer join pai_activite pa on pj.activite_id=pa.id
            where pj.anneemois='$anneemois_id'
            and pj.depot_id=$depot_id and pj.flux_id=$flux_id
            order by pe.level,pe.rubrique,pe.code,pj.date_distrib
            ";
            return $this->_em->getConnection()->fetchAll ($sql);
    }


    function selectValidation($validation_id) {
        $sql = "SELECT 
                    coalesce(min(pe.valide),true) as valide,
                    group_concat(pe.msg order by pe.level,pe.rubrique,pe.code separator '<br/>') as msg,
                    min(pj.id) as journal_id,
                    min(pe.level) as level
                FROM pai_journal pj
                inner join pai_ref_erreur pe on pj.erreur_id=pe.id
                WHERE validation_id=$validation_id
                GROUP BY pj.validation_id"
            ;
        return $this->_em->getConnection()->fetchAll($sql);
    }


    public function actualisation(&$msg, &$msgException, $anneemois_id, $depot_id, $flux_id) {
        try {
            $validation_id=null;
            $sql = "call pai_valide(@validation_id,'$anneemois_id',".$this->sqlField->sqlIdOrNull($depot_id).",".$this->sqlField->sqlIdOrNull($flux_id).")";
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
            $msg = '';
            $level = '';
            $journal_id = '';
            if ($validation_id != 0) {
                $result = $this->selectValidation($validation_id);
                if (isset($result[0])) {
                    $valide = $result[0]["valide"];
                    $msg = $result[0]["msg"];
                    $journal_id = $result[0]["journal_id"];
                    $level = $result[0]["level"];
                }
            }
        } catch (DBALException $ex) {
            throw $ex;
        }
    }

}
