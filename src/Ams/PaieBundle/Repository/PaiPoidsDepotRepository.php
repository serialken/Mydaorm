<?php
namespace Ams\PaieBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class PaiPoidsDepotRepository extends GlobalRepository {

    function select($depot_id, $flux_id, $date_distrib) {
            $sql = "SELECT 
                        coalesce(pcg.id,concat(gt.id,'-',pcj.id)) id
                        ,gt.id groupe_id
                        ,pcj.date_distrib
                        ,pcj.produit_id
                        ,pcj.valeur_int poidsPCO
                        ,pcg.valeur_int poids
                        ,gt.code
                        ,coalesce(p2.libelle,p.libelle) as libelle
                    FROM groupe_tournee gt
                    INNER JOIN prd_caract_jour pcj
                    INNER JOIN produit p on pcj.produit_id=p.id
                    INNER JOIN prd_caract pc ON pc.id = pcj.prd_caract_id
                    LEFT OUTER JOIN prd_caract_groupe pcg ON gt.id=pcg.groupe_id and pcg.date_distrib=pcj.date_distrib AND pcg.prd_caract_id=pcj.prd_caract_id AND pcg.produit_id=pcj.produit_id
                    LEFT OUTER JOIN produit p2 on pcg.produit_id=p2.id
                    WHERE pc.code='POIDS'
                    AND pcj.date_distrib='".$date_distrib."'
                    AND gt.depot_id=".$depot_id." AND gt.flux_id=".$flux_id."
                    UNION
                    SELECT 
                        pcg.id id
                        ,gt.id groupe_id
                        ,pcg.date_distrib
                        ,pcg.produit_id
                        ,NULL
                        ,pcg.valeur_int poids
                        ,gt.code
                        ,p.libelle
                    FROM prd_caract_groupe pcg
                    INNER JOIN groupe_tournee gt ON pcg.groupe_id=gt.id
                    INNER JOIN prd_caract pc ON pc.id = pcg.prd_caract_id
                    INNER JOIN produit p on pcg.produit_id=p.id
                    WHERE pc.code='POIDS'
                    AND pcg.date_distrib='".$date_distrib."'
                    AND gt.depot_id=".$depot_id." AND gt.flux_id=".$flux_id."
                    AND NOT EXISTS(SELECT NULL FROM prd_caract_jour pcj WHERE pcg.date_distrib=pcj.date_distrib AND pcg.prd_caract_id=pcj.prd_caract_id AND pcg.produit_id=pcj.produit_id)
                    ORDER BY 7,8
                    ";
            return $this->_em->getConnection()->fetchAll($sql);
    }

    public function insert(&$msg, &$msgException, $param, $user, &$id) {
        try {
            $sql = "INSERT INTO prd_caract_groupe(date_distrib,prd_caract_id,produit_id,groupe_id,valeur_int,utilisateur_id,date_creation)
                    SELECT " . $this->sqlField->sqlQuote($param['date_distrib']) . "
                        ,pc.id
                        ," . $param['produit_id'] . "
                        ," . $param['groupe_id'] . "
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
            $this->_em->getRepository("AmsPaieBundle:PaiPrdTournee")->recalcul_poids_groupe($param['date_distrib'], $param['groupe_id'], $param['produit_id']);
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "Le poids d'un produit doit être unique par groupe/jour.","UNIQUE","");
        }
        return true;
    }

    public function _update(&$msg, &$msgException, $param, $user, &$id) {
        try {
            // ATTENTION : le produit peut-être modifié, dans ce cas le prd_caract_id change si le type produit change
            $sql = "UPDATE prd_caract_groupe SET 
                valeur_int = " . $this->sqlField->sqlInt($param['poids']) . ",
                utilisateur_id = " . $user . ",
                date_modif = NOW()";
            $sql .= " WHERE id =" . $param['gr_id'];
            $this->_em->getConnection()->prepare($sql)->execute();
            $this->_em->getRepository("AmsPaieBundle:PaiPrdTournee")->recalcul_poids_groupe($param['date_distrib'], $param['groupe_id'], $param['produit_id']);
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "Le poids d'un produit doit être unique par groupe/jour.","UNIQUE","");
        }
        return true;
    }

    public function update(&$msg, &$msgException, $param, $user, &$id) {
            if (strpos($param['gr_id'],"-")!==FALSE){
                $id=0;
                return $this->insert($msg, $msgException, $param, $user, $id);
            } elseif (!$param['poids']) {
                return $this->delete($msg, $msgException, $param, $user, $id);
            }else {
                return $this->_update($msg, $msgException, $param, $user, $id);
            }
    }

    public function delete(&$msg, &$msgException, $param, &$id) {
        try {
            if (strpos($param['gr_id'],"-")!==FALSE){
                $msg='Suppression impossible.<br/>Le poids a été renseigné seulement par le PCO.';
                return false;
            } else {
                $sql = "DELETE FROM prd_caract_groupe
                        WHERE id=" . $param['gr_id'];
                $this->_em->getConnection()->prepare($sql)->execute();
                $id='56-67'; //???
                $this->_em->getRepository("AmsPaieBundle:PaiPrdTournee")->recalcul_poids_groupe($param['date_distrib'], $param['groupe_id'], $param['produit_id']);
            }
            } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "","","");
        }
        return true;
    }

}
