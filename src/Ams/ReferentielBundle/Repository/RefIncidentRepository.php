<?php
namespace Ams\ReferentielBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class RefIncidentRepository extends GlobalRepository
{
    function select() {
        $sql = "SELECT
                id,
                code,
                libelle
                FROM ref_incident
                ORDER BY id"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function insert(&$msg, &$msgException, $param, $user, &$id) {
        try {
            $sql = "INSERT INTO ref_incident SET 
                code = " . $this->sqlField->sqlQuote($param['code']) . ",
                libelle = " . $this->sqlField->sqlQuote($param['libelle']) . ",
                utilisateur_id = " . $user . ",
                date_creation = NOW()";
            $this->_em->getConnection()->prepare($sql)->execute();
            $id = $this->_em->getConnection()->lastInsertId();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "L'incident doit être unique.","UNIQUE","");
        }
        return true;
    }

    public function update(&$msg, &$msgException, $param, $user, &$id) {
        try {
            $sql = "UPDATE ref_incident SET 
                code = " . $this->sqlField->sqlQuote($param['code']) . ",
                libelle = " . $this->sqlField->sqlQuote($param['libelle']) . ",
                utilisateur_id = " . $user . ",
                date_modif = NOW()
                WHERE id=" . $param['gr_id'];
            $this->_em->getConnection()->prepare($sql)->execute();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "L'incident doit être unique.","UNIQUE","");
        }
        return true;
    }

    public function delete(&$msg, &$msgException, $param) {
        try {
            $sql = "DELETE FROM ref_incident
                    WHERE id=" . $param['gr_id'];
            $this->_em->getConnection()->prepare($sql)->execute();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "","","");
        }
        return true;
    }

    public function validation($id) {
        try {
            return 0;
        } catch (DBALException $ex) {
            throw $ex;
        }
    }
    function selectCombo() {
        $sql = "SELECT
                id,
                libelle
                FROM ref_incident
                ORDER BY id"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
}
