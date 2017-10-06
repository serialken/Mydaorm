<?php
namespace Ams\PaieBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class PaiStcRepository extends GlobalRepository
{
    function select($anneemois, $flux_id, $sqlCondition=''){
        $sql = "SELECT DISTINCT
                    ps.rcoid as id,
                    true as extrait,
                    epd.depot_id,
                    epd.flux_id,
                    ps.employe_id,
                    epd.rc,
                    ps.date_stc,
                    ps.date_extrait,
                    IF(ps.date_extrait is not null,false,true) as isModif,
                    IF(ps.date_extrait is null,false,true) as isDelete,
                    e.matricule,
                    substr(e.matricule,1,8) as matriculeNG
                FROM pai_stc ps
                INNER JOIN employe e ON ps.employe_id = e.id
                LEFT OUTER JOIN emp_pop_depot epd ON epd.rcoid=ps.rcoid -- and epd.fRC=ps.date_stc
                -- On prend seulement la derniere situation
                AND epd.date_fin in (select max(date_fin) from emp_pop_depot epd2 where epd.rcoid=epd2.rcoid)
                WHERE ps.anneemois='$anneemois' and epd.flux_id=$flux_id
                ".($sqlCondition!='' ? $sqlCondition : "")."
                    
                UNION
                
                SELECT DISTINCT
                    epd.rcoid as id,
                    false as extrait,
                    epd.depot_id,
                    epd.flux_id,
                    epd.employe_id,
                    epd.rc,
                    epd.fRC as date_stc,
                    null as date_extrait,
                    true as isModif,
                    false as isDelete,
                    e.matricule,
                    substr(e.matricule,1,8) as matriculeNG
                FROM employe e
                INNER JOIN pai_ref_mois prm ON prm.anneemois='$anneemois'
                INNER JOIN emp_pop_depot epd ON epd.employe_id = e.id AND epd.fRC between prm.date_debut and prm.date_fin and epd.flux_id=$flux_id
                -- On prend seulement la derniere situation
                WHERE epd.date_fin in (select max(date_fin) from emp_pop_depot epd2 where epd.rcoid=epd2.rcoid)
                AND epd.population_id>0
                /*AND (exists (SELECT NULL from pai_tournee pt WHERE pt.employe_id=e.id and pt.date_distrib between prm.date_debut and prm.date_fin)
                OR   exists (SELECT NULL from pai_activite pa WHERE pa.employe_id=e.id and pa.date_distrib between prm.date_debut and prm.date_fin)
                )*/ AND NOT exists(SELECT NULL
                                FROM pai_stc ps
                                WHERE ps.rcoid=epd.rcoid
                               )
                ".($sqlCondition!='' ? $sqlCondition : "")."
                ORDER BY 2 desc,7
                ";
        return $this->_em->getConnection()->fetchAll ($sql);
    }

    public function update(&$msg, &$msgException, $param, $user, &$id) {
        try {
            if ($param['extrait']==1) {
                if ($param['date_extrait']=='') {
                    $sql = "INSERT INTO pai_stc(rcoid,employe_id,date_stc,anneemois,utilisateur_id,date_modif)
                        SELECT " . $this->sqlField->sqlQuote($param['gr_id']) . ",
                            " . $this->sqlField->sqlIdOrNull($param['employe_id']) . ",
                            " . $this->sqlField->sqlDate($param['date_stc']) . ",
                            rm.anneemois,
                            " . $user . ",
                            NOW()
                        FROM pai_ref_mois rm where " . $this->sqlField->sqlDate($param['date_stc']) . " between  rm.date_debut and rm.date_fin
                        ";
                    $this->_em->getConnection()->prepare($sql)->execute();
                } else {
                    //$sql="set @idtrt=null;";
                    // Le fait que la première instruction sql (set @idtrt=null) soit correct, empeche de lever l'exception sur la deuxième instruction
                    // ATTENTION, apperement le @idtrt n'est pas réutilisé et remis à null à chaque excecution
                    // Pourquoi le set @idtrt=null a-t-il était positionné dans les autres appels de procédures ????
                    $sql="call INT_MROAD2EV_ANNULE_STC(@idtrt," . $user ."," . $this->sqlField->sqlQuote($param['gr_id']) .")";
                    $idtrt = $this->executeProc($sql, "@idtrt");
                }
            } else {
                $sql = "DELETE FROM pai_stc
                        WHERE rcoid=" . $this->sqlField->sqlQuote($param['gr_id']);
                $this->_em->getConnection()->prepare($sql)->execute();
            }
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "Erreur inconnue.", "", "");
        }
        return true;
    }
/*
    public function delete(&$msg, &$msgException, $param, $user) {
        try {
            //$sql="set @idtrt=null;";
            // Le fait que la première instruction sql (set @idtrt=null) soit correct, empeche de lever l'exception sur la deuxième instruction
            // ATTENTION, apperement le @idtrt n'est pas réutilisé et remis à null à chaque excecution
            // Pourquoi le set @idtrt=null a-t-il était positionné dans les autres appels de procédures ????
            $sql="call INT_MROAD2EV_ANNULE_STC(@idtrt," . $user ."," . $this->sqlField->sqlQuote($param['gr_id']) .")";
            $idtrt = $this->executeProc($sql, "@idtrt");
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "Suppression impossible.", "", "");
        }
        return true;
    }*/
}
