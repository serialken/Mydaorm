<?php
namespace Ams\PaieBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class PaiRefFerieRepository extends GlobalRepository {

    function select() {
            $sql = "SELECT prf.id
                        ,prf.societe_id
                        ,prf.jfdate
                        ,prf.jfdate>=pm.date_debut as isModif
                    FROM pai_ref_ferie prf
                    INNER JOIN pai_mois pm ON prf.societe_id=pm.flux_id
                    ORDER BY prf.societe_id,isModif desc,prf.jfdate
                    ";
            return $this->_em->getConnection()->fetchAll($sql);
    }

    public function insert(&$msg, &$msgException, $param, $user, &$id) {
        try {
            $sql = "INSERT INTO pai_ref_ferie(societe_id,jfdate)
                    VALUES( 
                        " . $param['societe_id'] . "
                        ," . $this->sqlField->sqlDate($param['jfdate']) . "
                    )";
            $this->_em->getConnection()->prepare($sql)->execute();
            $id = $this->_em->getConnection()->lastInsertId();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "L'enregistrement doit être unique.","UNIQUE","");
        }
        return true;
    }
        
    public function delete(&$msg, &$msgException, $param) {
        try {
            $sql = "DELETE FROM pai_ref_ferie
                    WHERE id=" . $param['gr_id'];
            $this->_em->getConnection()->prepare($sql)->execute();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "","","");
        }
        return true;
    }

    public function selectPlanning($employe_id, $start, $end) {
        $sql = "SELECT
                    prf.jfdate as start,
                    true as allDay,
                    'background' as rendering,
                    '#FF6962' as backgroundColor
                FROM pai_ref_ferie prf
                inner join emp_pop_depot epd on prf.jfdate between epd.date_debut and epd.date_fin and epd.societe_id=prf.societe_id
                WHERE prf.jfdate between '$start' and '$end'
                AND epd.employe_id=$employe_id
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }
}
