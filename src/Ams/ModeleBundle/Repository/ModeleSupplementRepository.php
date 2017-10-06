<?php
namespace Ams\ModeleBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class ModeleSupplementRepository extends GlobalRepository {
    function select($depot_id, $flux_id, $sqlCondition='') {
        $sql = "SELECT
                    ms.id,
                    ms.depot_id,
                    ms.flux_id,
                    date_format(ms.date_debut,'%d/%m/%Y') as date_debut,
                    date_format(ms.date_fin,'%d/%m/%Y') as date_fin,
                    ms.jour_id,
                    ms.natureclient_id,
                    ms.supplement_id,
                    ms.produit_id,
                    ms.commentaire
                FROM modele_supplement ms
                WHERE ms.depot_id=" . $depot_id . " AND ms.flux_id=" . $flux_id." ".
                ($sqlCondition!='' ? $sqlCondition : "")."
                ORDER BY ms.date_fin desc,ms.jour_id,ms.supplement_id,ms.produit_id"
                ;
            return $this->_em->getConnection()->fetchAll($sql);
    }

    public function insert(&$msg, &$msgException, $param, $user, &$id) {
        try {
            $sql = "INSERT INTO modele_supplement SET 
                depot_id = " . $param['depot_id'] . ",
                flux_id = " . $param['flux_id'] . ",
                date_debut = " . $this->sqlField->sqlDate($param['date_debut']) . ",
                date_fin = " . $this->sqlField->sqlDateOr2999($param['date_fin']) . ",
                jour_id = " . $param['jour_id'] . ",
                natureclient_id = " . $this->sqlField->sqlTrim($param['natureclient_id']) . ",
                supplement_id = " . $this->sqlField->sqlTrim($param['supplement_id']) . ",
                produit_id = " . $this->sqlField->sqlTrim($param['produit_id']) . ",
                commentaire = " . $this->sqlField->sqlTrimQuote($param['commentaire']) . ",
                utilisateur_id = " . $user . ",
                date_creation = NOW()";
            $this->_em->getConnection()->prepare($sql)->execute();
            $id = $this->_em->getConnection()->lastInsertId();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "Le supplément doit être unique.","UNIQUE","");
        }
        return true;
    }

    public function update(&$msg, &$msgException, $param, $user, &$id) {
        try {
            $sql = "UPDATE modele_supplement SET 
                date_debut = " . $this->sqlField->sqlDate($param['date_debut']) . ",
                date_fin = " . $this->sqlField->sqlDateOr2999($param['date_fin']) . ",
                jour_id = " . $param['jour_id'] . ",
                natureclient_id = " . $this->sqlField->sqlTrim($param['natureclient_id']) . ",
                supplement_id = " . $this->sqlField->sqlTrim($param['supplement_id']) . ",
                produit_id = " . $this->sqlField->sqlTrim($param['produit_id']) . ",
                commentaire = " . $this->sqlField->sqlTrimQuote($param['commentaire']) . ",
                utilisateur_id = " . $user . ",
                date_modif = NOW()";
            $sql .= " WHERE id =" . $param['gr_id'];
            $this->_em->getConnection()->prepare($sql)->execute();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "Le supplément doit être unique.","UNIQUE","");
        }
        return true;
    }

    public function delete(&$msg, &$msgException, $param) {
        try {
            $this->_em->getConnection()->beginTransaction();
            //$this->_em->getConnection()->prepare("DELETE FROM modele_journal WHERE activite_id=" . $param['gr_id'])->execute();
            $this->_em->getConnection()->prepare("DELETE FROM modele_supplement WHERE id=" . $param['gr_id'])->execute();
            $this->_em->getConnection()->commit();
        } catch (DBALException $ex) {
            $error= $this->sqlField->sqlError($msg, $msgException, $ex, "Suppression impossible.","FOREIGN", "");
            $this->_em->getConnection()->rollBack();
            return $error;
        }
        return true;
    }

    function selectComboTitre($flux_id) {
        $sql = "SELECT
                id,
                libelle
            FROM produit p
            WHERE p.type_id in (1)
            AND p.flux_id=".$flux_id."
            ORDER BY libelle"
            ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    function selectComboSupplement($flux_id) {
        $sql = "SELECT
                id,
                libelle
            FROM produit p
            WHERE p.type_id in (3)
            AND p.flux_id=".$flux_id."
            ORDER BY libelle"
            ;
        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function selectComboAjouterOrg($flux_id, $supplement_id) {
        $sql="select distinct
                p.id
            ,   p.libelle
            from produit p
            inner join produit s on p.societe_id=s.societe_id
            where p.flux_id=".$flux_id."
            and p.type_id = 1
            and s.id=".$supplement_id."
            and sysdate() between p.date_debut and coalesce(p.date_fin,'2999-01-01')
            order by p.libelle
        ;";            
        return $this->_em->getConnection()->fetchAll ($sql);
    }

}
