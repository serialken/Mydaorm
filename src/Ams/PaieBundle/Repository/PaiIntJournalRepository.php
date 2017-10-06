<?php
namespace Ams\PaieBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class PaiIntJournalRepository extends GlobalRepository
{
    function select($anneemois_id,$flux_id){  
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
            where pj.anneemois='$anneemois_id' and pj.flux_id=$flux_id
            and pj.depot_id<>21 -- PCO
            order by pe.level,pe.rubrique,pe.code,pj.date_distrib
            ";
            return $this->_em->getConnection()->fetchAll ($sql);
    }

    public function getMsg($validation_id) {
        try {
            if ($validation_id==0) { return ''; }
            $sql = "SELECT
                        pe.msg 
                    FROM pai_journal pj
                    inner join pai_ref_erreur pe on pj.erreur_id=pe.id
                    WHERE validation_id=$validation_id
                    ORDER BY pe.level,pe.rubrique,pe.code
                    ";
            $msg='';
            $messages = $this->_em->getConnection()->fetchAll($sql);
            foreach ($messages as $message) {
                $msg .= $message["msg"] . "<br/>";
            }
            return $msg;
        } catch (DBALException $ex) {
            throw $ex;
        }
    }

}
