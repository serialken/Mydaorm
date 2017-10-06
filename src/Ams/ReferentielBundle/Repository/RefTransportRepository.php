<?php
namespace Ams\ReferentielBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class RefTransportRepository extends GlobalRepository
{
    function select() {
        $sql = "SELECT
                id,
                code,
                libelle,
                km_paye
                FROM ref_transport
                ORDER BY libelle"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function insert(&$msg, &$msgException, $param, $user, &$id) {
        try {
            $sql = "INSERT INTO ref_transport SET 
                code = " . $this->sqlField->sqlQuote($param['code']) . ",
                libelle = " . $this->sqlField->sqlQuote($param['libelle']) . ",
                km_paye = " . $param['km_paye'];
            $this->_em->getConnection()->prepare($sql)->execute();
            $id = $this->_em->getConnection()->lastInsertId();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "Le mode de transport doit être unique.","UNIQUE","");
        }
        return true;
    }

    public function update(&$msg, &$msgException, $param, $user, &$id) {
        try {
            $sql = "UPDATE ref_transport SET 
                code = " . $this->sqlField->sqlQuote($param['code']) . ",
                libelle = " . $this->sqlField->sqlQuote($param['libelle']) . ",
                km_paye = " . $param['km_paye']."
                WHERE id=" . $param['gr_id'];
            $this->_em->getConnection()->prepare($sql)->execute();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "Le mode de transport doit être unique.","UNIQUE","");
        }
        return true;
    }

    public function delete(&$msg, &$msgException, $param) {
        try {
            $sql = "DELETE FROM ref_transport
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
                FROM ref_transport
                ORDER BY id"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
}
