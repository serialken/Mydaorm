<?php
namespace Ams\PaieBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class PaiProduitRepository extends GlobalRepository {

    function select($depot_id, $flux_id, $date_distrib, $sqlCondition='') {
        $sql = "SELECT
                    ppt.id,
                    pt.groupe_id,
                    ppt.tournee_id,
                    ppt.produit_id,
                    ppt.natureclient_id,
                    ppt.qte,
                    ppt.nbcli,
                    ppt.nbrep,
                    ppt.pai_qte,
                    ppt.pai_taux,
                    ppt.pai_mnt,
                    ppt.duree_supplement,
                    (pt.tournee_org_id is null or pt.split_id is not null) and pt.date_extrait is null isModif,
                    IF(pt.tournee_org_id = pt.id,'O', 'N') tournee_mere,
                    pt.tournee_org_id,
                    -- journal
                    coalesce(min(pe.valide),true) as valide,
                    group_concat(pe.msg order by pe.level,pe.rubrique,pe.code separator '<br/>') as msg,
                    min(pj.id) as journal_id,
                    min(pe.level) as level
                FROM pai_prd_tournee ppt
                INNER JOIN pai_tournee pt ON ppt.tournee_id=pt.id
                LEFT OUTER JOIN pai_journal pj ON ppt.id=pj.produit_id
                LEFT OUTER JOIN pai_ref_erreur pe ON pj.erreur_id=pe.id
                WHERE pt.depot_id=" . $depot_id . " AND pt.flux_id=" . $flux_id."
                AND pt.date_distrib='".$date_distrib."'
                ".($sqlCondition!='' ? $sqlCondition : "")."
                GROUP BY pt.id,ppt.id
                ORDER BY pt.id,ppt.produit_id"
                ;
            return $this->_em->getConnection()->fetchAll($sql);
    }

    public function insert(&$msg, &$msgException, $param, $user, &$id) {
        try {
            $sql = "INSERT INTO pai_prd_tournee SET 
                tournee_id = " . $param['tournee_id'] . ",
                produit_id = " . $param['produit_id'] . ",
                natureclient_id = " . $param['natureclient_id'] . ",
                qte = " . $this->sqlField->sqlTrim($param['qte']) . ",
                nbcli = " . $this->sqlField->sqlTrim($param['nbcli']) . ",
                nbrep = " . $this->sqlField->sqlTrim($param['nbrep']) . ",
                utilisateur_id = " . $user . ",
                date_creation = NOW()";
            $this->_em->getConnection()->prepare($sql)->execute();
            $id = $this->_em->getConnection()->lastInsertId();
            $this->_em->getRepository("AmsPaieBundle:PaiPrdTournee")->recalcul_id($param['tournee_id'],$id);
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "Le produit/nature client doit être unique dans la tournée.","UNIQUE","");
        }
        return true;
    }

    public function update(&$msg, &$msgException, $param, $user, &$id) {
        try {
            if ($this->sqlField->sqlTrim($param['qte'])==0 && $this->sqlField->sqlTrim($param['nbcli'])==0 && $this->sqlField->sqlTrim($param['nbrep'])==0) {
                $this->delete($msg, $msgException, $param);
            } else {
//                    tournee_id = " . $param['tournee_id'] . ",
                $sql = "UPDATE pai_prd_tournee SET 
                    produit_id = " . $param['produit_id'] . ",
                    natureclient_id = " . $param['natureclient_id'] . ",
                    qte = " . $this->sqlField->sqlTrim($param['qte']) . ",
                    nbcli = " . $this->sqlField->sqlTrim($param['nbcli']) . ",
                    nbrep = " . $this->sqlField->sqlTrim($param['nbrep']) . ",
                    utilisateur_id = " . $user . ",
                    date_modif = NOW()";
                $sql .= " WHERE id =" . $param['gr_id'];
                $this->_em->getConnection()->prepare($sql)->execute();
            }
            $this->_em->getRepository("AmsPaieBundle:PaiPrdTournee")->recalcul_id($param['tournee_id'],$param['gr_id']);
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "Le produit/nature client doit être unique dans la tournée.","UNIQUE","");
        }
        return true;
    }

    public function delete(&$msg, &$msgException, $param) {
        try {
            $sql = "DELETE FROM pai_prd_tournee
                    WHERE id=" . $param['gr_id'];
            $this->_em->getConnection()->prepare($sql)->execute();
            $this->_em->getRepository("AmsPaieBundle:PaiTournee")->recalcul_id($param['tournee_id']);
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "","","");
        }
        return true;
    }

    public function validate(&$msg, &$msgException, $id, $action="", $param=null) {
        return $this->_em->getRepository("AmsPaieBundle:PaiPrdTournee")->validate($msg, $msgException, $id, $action, $param);
    }
}
