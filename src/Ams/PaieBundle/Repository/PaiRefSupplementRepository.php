<?php
namespace Ams\PaieBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class PaiRefSupplementRepository extends GlobalRepository {

    function select() {
            $sql = "SELECT r.id
                        ,r.typetournee_id
                        ,r.date_debut
                        ,r.date_fin
                        ,r.borne_inf
                        ,r.borne_sup
                        ,r.valeur
                    FROM pai_ref_supplement r
                    ORDER BY r.typetournee_id,r.date_debut
                    ";
            return $this->_em->getConnection()->fetchAll($sql);
    }

    public function insert(&$msg, &$msgException, $param, $user, &$id) {
        try {
            $sql = "INSERT INTO pai_ref_supplement(typetournee_id,date_debut,date_fin,borne_inf,borne_sup,valeur,utilisateur_id,date_creation)
                    VALUES( 
                        " . $param['typetournee_id'] . "
                        ," . $this->sqlField->sqlDate($param['date_debut']) . "
                        ," . $this->sqlField->sqlDateOr2999($param['date_fin']) . "
                        ," . $this->sqlField->sqlInt($param['borne_inf']) . "
                        ," . $this->sqlField->sqlInt($param['borne_sup']) . "
                        ," . $this->sqlField->sqlInt($param['valeur']) . "
                        ," . $user . "
                        ,now()
                    )";
            $this->_em->getConnection()->prepare($sql)->execute();
            $id = $this->_em->getConnection()->lastInsertId();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "L'enregistrement doit être unique.","UNIQUE","");
        }
        return true;
    }
        
    public function update(&$msg, &$msgException, $param, $user, &$id) {
        try {
            $sql = "UPDATE pai_ref_supplement SET 
                        typetournee_id=" . $param['typetournee_id'] . "
                        ,date_debut=" . $this->sqlField->sqlDate($param['date_debut']) . "
                        ,date_fin=" . $this->sqlField->sqlDateOr2999($param['date_fin']) . "
                        ,borne_inf=" . $this->sqlField->sqlInt($param['borne_inf']) . "
                        ,borne_sup=" . $this->sqlField->sqlInt($param['borne_sup']) . "
                        ,valeur=" . $this->sqlField->sqlInt($param['valeur']) . "
                        ,utilisateur_id = " . $user . "
                        ,date_modif = NOW()";
            $sql .= " WHERE id =" . $param['gr_id'];
            $this->_em->getConnection()->prepare($sql)->execute();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "L'enregistrement doit être unique.","UNIQUE","");
        }
        return true;
    }

    public function delete(&$msg, &$msgException, $param) {
        try {
            $sql = "DELETE FROM pai_ref_supplement
                    WHERE id=" . $param['gr_id'];
            $this->_em->getConnection()->prepare($sql)->execute();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "","","");
        }
        return true;
    }
}
