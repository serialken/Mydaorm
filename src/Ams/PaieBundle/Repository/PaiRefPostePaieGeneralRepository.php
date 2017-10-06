<?php
namespace Ams\PaieBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class PaiRefPostePaieGeneralRepository extends GlobalRepository {

    function select() {
            $sql = "SELECT 
                        r.id
                        ,r.code
                        ,r.annexe
                        ,r.libelle
                        ,r.poste
                        ,r.typeurssaf_id
                        ,r.semaine
                        ,r.dimanche
                        ,r.ferie
                        ,r.taux
                        ,r.montant
                        ,r.majoration
                    FROM pai_ref_postepaie_general r
                    ORDER BY r.code
                    ";
            return $this->_em->getConnection()->fetchAll($sql);
    }

    public function insert(&$msg, &$msgException, $param, $user, &$id) {
        try {
            $sql = "INSERT INTO pai_ref_postepaie_general(code,annexe,libelle,poste,typeurssaf_id,semaine,dimanche,ferie,taux,montant,majoration,utilisateur_id,date_creation)
                    VALUES(
                        " . $this->sqlField->sqlQuote($param['code']) . "
                        ," . $this->sqlField->sqlQuote($param['annexe']) . "
                        ," . $this->sqlField->sqlQuote($param['libelle']) . "
                        ," . $this->sqlField->sqlQuote($param['poste']) . "
                        ," . $this->sqlField->sqlTrim($param['typeurssaf_id']) . "
                        ," . $param['semaine'] . "
                        ," . $param['dimanche'] . "
                        ," . $param['ferie'] . "
                        ," . $param['taux'] . "
                        ," . $param['montant'] . "
                        ," . $param['majoration'] . "
                        ," . $user . "
                        ,now()
                    )";
            $this->_em->getConnection()->prepare($sql)->execute();
            $id = $this->_em->getConnection()->lastInsertId();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "Le code du poste de paie doit être unique.","UNIQUE","");
        }
        return true;
    }
        
    public function update(&$msg, &$msgException, $param, $user, &$id) {
        try {
            // ATTENTION : le produit peut-être modifié, dans ce cas le prd_caract_id change si le type produit change
            $sql = "UPDATE pai_ref_postepaie_general SET
                        code = " . $this->sqlField->sqlQuote($param['code']) . "
                        ,annexe = " . $this->sqlField->sqlQuote($param['annexe']) . "
                        ,libelle = " . $this->sqlField->sqlQuote($param['libelle']) . "
                        ,poste = " . $this->sqlField->sqlQuote($param['poste']) . "
                        ,typeurssaf_id = " . $this->sqlField->sqlTrim($param['typeurssaf_id']) . "
                        ,semaine = " . $param['semaine'] . "
                        ,dimanche = " . $param['dimanche'] . "
                        ,ferie = " . $param['ferie'] . "
                        ,taux = " . $param['taux'] . "
                        ,montant = " . $param['montant'] . "
                        ,majoration = " . $param['majoration'] . "
                        ,utilisateur_id = " . $user . "
                        ,date_modif = NOW()";
            $sql .= " WHERE id =" . $param['gr_id'];
            $this->_em->getConnection()->prepare($sql)->execute();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "Le code du poste de paie doit être unique.","UNIQUE","");
        }
        return true;
    }

    public function delete(&$msg, &$msgException, $param) {
        try {
            $sql = "DELETE FROM pai_ref_postepaie_general
                    WHERE id=" . $param['gr_id'];
            $this->_em->getConnection()->prepare($sql)->execute();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "","","");
        }
        return true;
    }
}
