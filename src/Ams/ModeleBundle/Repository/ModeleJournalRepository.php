<?php
namespace Ams\ModeleBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class ModeleJournalRepository extends GlobalRepository {

    function select($depot_id, $flux_id) {
        $sql = "SELECT
                mj.id,
                mj.depot_id,
                mj.flux_id,
                me.level,
                me.rubrique,
                me.code,
                me.msg,
                me.couleur,
                mj.jour_id,
                mj.employe_id,
                mj.tournee_id,
                mj.tournee_jour_id,
                mj.activite_id as modele_activite_id,
                ma.activite_id,
                mj.remplacement_id,
                mj.commentaire
              from modele_journal mj
              inner join modele_ref_erreur me on mj.erreur_id=me.id
              left outer join modele_activite ma on mj.activite_id=ma.id
              where mj.depot_id=$depot_id and mj.flux_id=$flux_id
              order by me.level,mj.employe_id,me.rubrique,me.code,mj.jour_id"
        ;
        return $this->_em->getConnection()->fetchAll($sql);
    }

    function selectValidation($validation_id) {
        $sql = "SELECT 
                    coalesce(min(me.valide),true) as valide,
                    group_concat(me.msg order by me.level,me.rubrique,me.code separator '<br/>') as msg,
                    min(mj.id) as journal_id,
                    min(me.level) as level
                FROM modele_journal mj
                inner join modele_ref_erreur me on mj.erreur_id=me.id
                WHERE validation_id=$validation_id
                GROUP BY validation_id"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
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
