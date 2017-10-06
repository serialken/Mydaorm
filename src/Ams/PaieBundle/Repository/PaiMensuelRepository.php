<?php
namespace Ams\PaieBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class PaiMensuelRepository extends GlobalRepository
{
    function select($anneemois, $sqlCondition=''){
        $sql = "SELECT DISTINCT
                    ps.rcoid as id,
                    true as extrait,
                    epd.depot_id,
                    epd.flux_id,
                    ps.employe_id,
                    epd.rc,
                    ps.date_stc,
                    ps.date_extrait,
                    IF(ps.date_extrait is not null,false,true) as isModif
                FROM pai_stc ps
                LEFT OUTER JOIN emp_pop_depot epd ON epd.rcoid=ps.rcoid and epd.fRC=ps.date_stc
                WHERE ps.anneemois='".$anneemois."'".
                ($sqlCondition!='' ? $sqlCondition : "")."
                    
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
                    true as isModif
                FROM emp_pop_depot epd
                INNER JOIN pai_ref_mois rm ON rm.anneemois='".$anneemois."'
                WHERE epd.fRC between rm.date_debut and rm.date_fin
                AND NOT exists(SELECT NULL
                                FROM pai_stc ps
                                WHERE ps.rcoid=epd.rcoid
                               )".
                ($sqlCondition!='' ? $sqlCondition : "")."
                ORDER BY 2 desc,7
                ";
        return $this->_em->getConnection()->fetchAll ($sql);
    }

    public function update(&$msg, &$msgException, $param, $user, &$id) {
        try {
            if ($param['extrait']==1) {
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
            $sql = "DELETE FROM pai_stc
                    WHERE rcoid=" . $this->sqlField->sqlQuote($param['gr_id']);
            $this->_em->getConnection()->prepare($sql)->execute();
            }
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "Erreur inconnue.", "", "");
        }
        return true;
    }
}
