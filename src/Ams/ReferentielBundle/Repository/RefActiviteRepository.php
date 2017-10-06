<?php
namespace Ams\ReferentielBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class RefActiviteRepository extends GlobalRepository
{
   function select() {
        $sql = "SELECT
                id,
                code,
                libelle,
                affichage_modele,
                km_paye,
                est_hors_presse,
                est_hors_travail,
                est_1mai,
                est_pleiades,
                est_JTPX,
                est_badge,
                est_garantie,
                couleur,
                actif,
                not est_pleiades as isDelete
                FROM ref_activite a
                ORDER BY actif desc,libelle"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function insert(&$msg, &$msgException, $param, $user, &$id) {
        try {
            $sql = "INSERT INTO ref_activite SET 
                code = " . $this->sqlField->sqlQuote($param['code']) . ",
                libelle = " . $this->sqlField->sqlQuote($param['libelle']) . ",
                affichage_modele = " . $param['affichage_modele'] . ",
                km_paye = " . $param['km_paye'] . ",
                est_hors_presse = " . $param['est_hors_presse'] . ",
                est_hors_travail = " . $param['est_hors_travail'] . ",
                est_1mai = " . $param['est_1mai'] . ",
                est_pleiades = false,
                est_JTPX = " . $param['est_JTPX'] . ",
                est_badge = " . $param['est_badge'] . ",
                est_garantie = " . $param['est_garantie'] . ",
                couleur = " . $this->sqlField->sqlTrimQuote($param['couleur']) . ",
                actif = " . $param['actif'] . ",
                utilisateur_id = " . $user . ",
                date_creation = NOW()";
            $this->_em->getConnection()->prepare($sql)->execute();
            $id = $this->_em->getConnection()->lastInsertId();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "L'activite doit être unique.","UNIQUE","");
        }
        return true;
    }

    public function update(&$msg, &$msgException, $param, $user, &$id) {
        try {
            $sql = "UPDATE ref_activite SET 
                code = " . $this->sqlField->sqlQuote($param['code']) . ",
                libelle = " . $this->sqlField->sqlQuote($param['libelle']) . ",
                affichage_modele = " . $param['affichage_modele'] . ",
                km_paye = " . $param['km_paye'] . ",
                est_hors_presse = " . $param['est_hors_presse'] . ",
                est_hors_travail = " . $param['est_hors_travail'] . ",
                est_1mai = " . $param['est_1mai'] . ",
                est_pleiades = " . $param['est_pleiades'] . ",
                est_JTPX = " . $param['est_JTPX'] . ",
                est_badge = " . $param['est_badge'] . ",
                est_garantie = " . $param['est_garantie'] . ",
                couleur = " . $this->sqlField->sqlTrimQuote($param['couleur']) . ",
                actif = " . $param['actif'] . ",
                utilisateur_id = " . $user . ",
                date_modif = NOW()";
            $sql .= " WHERE id =" . $param['gr_id'];
            $this->_em->getConnection()->prepare($sql)->execute();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "L'activite doit être unique.","UNIQUE","");
        }
        return true;
    }

    public function delete(&$msg, &$msgException, $param) {
        try {
            $sql = "DELETE FROM ref_activite
                    WHERE est_hors_presse=false
                    AND id=" . $param['gr_id'];
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

    function selectComboModele($est_hors_presse) {
        $sql = "SELECT
                id,
                libelle
                FROM ref_activite a
                WHERE a.affichage_modele=true
                AND a.id>0
                AND a.actif
                and a.est_hors_presse=".($est_hors_presse?"true":"false")."
                ORDER BY a.libelle"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
    }

    function selectComboPai($est_hors_presse) {
        $sql = "SELECT
                id,
                libelle
                FROM ref_activite a
                WHERE a.id>0
                AND a.actif
                and a.est_hors_presse=".($est_hors_presse?"true":"false")." and a.est_pleiades=false
                ORDER BY a.libelle"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
    }

    function selectComboJournal() {
        $sql = "SELECT
                id,
                libelle
                FROM ref_activite a
                ORDER BY libelle"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
    }

    function selectCombo() {
        $sql = "SELECT
                id,
                libelle
                FROM ref_activite a
                ORDER BY libelle"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
}
