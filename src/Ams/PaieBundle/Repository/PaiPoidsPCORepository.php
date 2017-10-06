<?php
namespace Ams\PaieBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class PaiPoidsPCORepository extends GlobalRepository {

    function select($date_distrib) {
        $sql = "select 
                        pcj.id
                        ,pcj.date_distrib
                        ,pcj.produit_id
                        ,pcj.valeur_int poids
                    FROM prd_caract_jour pcj
                    INNER JOIN prd_caract pc ON pc.id = pcj.prd_caract_id
                    INNER JOIN produit p on pcj.produit_id=p.id
                    WHERE pc.code='POIDS'
                    AND pcj.date_distrib='" . $date_distrib . "'
                    ORDER BY pc.produit_type_id,p.libelle
                    ";
        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function insert(&$msg, &$msgException, $param, $user, &$id) {
        try {
            $sql = "INSERT INTO prd_caract_jour(date_distrib,prd_caract_id,produit_id,valeur_int,utilisateur_id,date_creation)
                    SELECT " . $this->sqlField->sqlQuote($param['date_distrib']) . "
                        ,pc.id
                        ," . $param['produit_id'] . "
                        ," . $this->sqlField->sqlInt($param['poids']) . "
                        ," . $user . "
                        ,NOW()
                    FROM produit p
                    INNER JOIN prd_caract pc ON p.type_id=pc.produit_type_id
                    WHERE pc.code='POIDS' and pc.actif=true
                    AND p.id=" . $param['produit_id'] . "
                    ";
            $this->_em->getConnection()->prepare($sql)->execute();
            $id = $this->_em->getConnection()->lastInsertId();
            $this->_em->getRepository("AmsPaieBundle:PaiPrdTournee")->recalcul_poids_PCO($param['date_distrib'], $param['produit_id']);
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "Le poids d'un produit doit être unique par jour.", "UNIQUE", "");
        }
        return true;
    }

    public function update(&$msg, &$msgException, $param, $user, &$id) {
        try {
            // ATTENTION : le produit peut-être modifié, dans ce cas le prd_caract_id change si le type produit change
            $sql = "UPDATE prd_caract_jour SET 
                valeur_int = " . $this->sqlField->sqlInt($param['poids']) . ",
                utilisateur_id = " . $user . ",
                date_modif = NOW()";
            $sql .= " WHERE id =" . $param['gr_id'];
            $this->_em->getConnection()->prepare($sql)->execute();
            $this->_em->getRepository("AmsPaieBundle:PaiPrdTournee")->recalcul_poids_PCO($param['date_distrib'], $param['produit_id']);
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "Le poids d'un produit doit être unique par jour.", "UNIQUE", "");
        }
        return true;
    }

    public function delete(&$msg, &$msgException, $param) {
        try {
            $sql = "DELETE FROM prd_caract_jour
                    WHERE id=" . $param['gr_id'];
            $this->_em->getConnection()->prepare($sql)->execute();
            $this->_em->getRepository("AmsPaieBundle:PaiPrdTournee")->recalcul_poids_PCO($param['date_distrib'], $param['produit_id']);
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "", "", "");
        }
        return true;
    }

    public function alimentation(&$msg, &$msgException, &$idtrt, $user, $date_distrib) {
        try {
            $sql="set @idtrt=".$this->sqlField->sqlIdOrNull($idtrt).";";
            $sql.="call alim_poids(" . $user . ",@idtrt," . $this->sqlField->sqlTrimQuote($date_distrib) . ")";
            $idtrt = $this->executeProc($sql, "@idtrt");
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "", "", "");
        }
        return true;
    }

}
