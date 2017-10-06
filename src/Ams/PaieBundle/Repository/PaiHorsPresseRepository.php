<?php
namespace Ams\PaieBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class PaiHorsPresseRepository extends GlobalRepository {

    function selectTournee($depot_id,$flux_id, $date_distrib, $sqlCondition=''){  
        $sql = "SELECT DISTINCT
                    pt.id,
                    pt.groupe_id,
                    pt.employe_id,
                    (pt.tournee_org_id is null or pt.split_id is not null) and pt.date_extrait is null isModif,
                    IF(pt.tournee_org_id = pt.id,'O', 'N') tournee_mere,
                    pt.tournee_org_id,
                    -- journal
                    coalesce(min(pe.valide),true) as valide,
                    group_concat(pe.msg order by pe.level,pe.rubrique,pe.code separator '<br/>') as msg,
                    min(pj.id) as journal_id,
                    min(pe.level) as level
                FROM pai_tournee pt
                LEFT OUTER JOIN pai_prd_tournee ppt ON ppt.tournee_id=pt.id
                LEFT OUTER JOIN produit p ON ppt.produit_id=p.id
                LEFT OUTER JOIN produit_type t ON p.type_id=t.id AND t.hors_presse
                LEFT OUTER JOIN pai_journal pj ON pt.id=pj.tournee_id
                LEFT OUTER JOIN pai_ref_erreur pe ON pj.erreur_id=pe.id
                LEFT OUTER JOIN emp_pop_depot epd on pt.employe_id=epd.employe_id and pt.date_distrib between epd.date_debut and epd.date_fin
                WHERE  pt.depot_id=".$depot_id."  AND  pt.flux_id=".$flux_id." AND pt.date_distrib = '".$date_distrib."'
                AND coalesce(epd.typetournee_id,1)>0 -- Pas de tournées VCP
                ".($sqlCondition!='' ? $sqlCondition : "")."
                GROUP BY pt.id,ppt.id,p.id
                ORDER BY pt.code
                ";
        return $this->_em->getConnection()->fetchAll ($sql);
    }

    function selectProduit($depot_id,$flux_id, $date_distrib) {
        $sql = "SELECT DISTINCT
                p.id,
                p.libelle
                FROM pai_tournee pt
                INNER JOIN pai_prd_tournee ppt ON ppt.tournee_id=pt.id
                INNER JOIN produit p ON ppt.produit_id=p.id
                INNER JOIN produit_type t ON p.type_id=t.id AND t.hors_presse
                LEFT OUTER JOIN emp_pop_depot epd on pt.employe_id=epd.employe_id and pt.date_distrib between epd.date_debut and epd.date_fin
                WHERE pt.depot_id=" . $depot_id . " AND pt.flux_id=" . $flux_id." AND pt.date_distrib='".$date_distrib."'
                AND coalesce(epd.typetournee_id,1)>0 -- Pas de tournées VCP
                ORDER BY p.soc_code_ext,p.type_id,p.prd_code_ext,p.spr_code_ext"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
    }

    function selectQte($depot_id, $flux_id, $date_distrib, $sqlCondition='') {
        $sql = "SELECT
                ppt.id,
                ppt.tournee_id,
                ppt.produit_id,
                ppt.natureclient_id,
                ppt.qte,
                ppt.nbcli
                FROM pai_tournee pt
                INNER JOIN pai_prd_tournee ppt ON ppt.tournee_id=pt.id
                INNER JOIN produit p ON ppt.produit_id=p.id
                INNER JOIN produit_type t ON p.type_id=t.id AND t.hors_presse
                LEFT OUTER JOIN emp_pop_depot epd on pt.employe_id=epd.employe_id and pt.date_distrib between epd.date_debut and epd.date_fin
                WHERE pt.depot_id=" . $depot_id . " AND pt.flux_id=" . $flux_id." AND pt.date_distrib='".$date_distrib."'
                AND coalesce(epd.typetournee_id,1)>0 -- Pas de tournées VCP
                ".($sqlCondition!='' ? $sqlCondition : "")."
                ORDER BY ppt.tournee_id,ppt.produit_id"
                ;
            return $this->_em->getConnection()->fetchAll($sql);
    }
    // ATTENTION : On met les produits hors-presse en abonné
    public function update(&$msg, &$msgException, $param, $user, &$id) {
        try {
            foreach($param as $key => $value){
                if (substr($key,0,1)=="Q"){
                    $produit_id=substr($key,1);
                    $qte=$param["Q".$produit_id];
                    if (isset($param["C".$produit_id])) {
                        $nbcli = $param["C".$produit_id];
                    } else {
                        $nbcli = 0;
                    }
                    $nbRows=$this->_em->getRepository("AmsPaieBundle:PaiPrdTournee")->update($user, $param["gr_id"], $produit_id, 0, $qte, $nbcli);
                    if ($nbRows==0) {
                        $this->_em->getRepository("AmsPaieBundle:PaiPrdTournee")->insert($user, $param["gr_id"], $produit_id, 0, $qte, $nbcli);
                    } else {
                        $this->_em->getRepository("AmsPaieBundle:PaiPrdTournee")->nettoyage($param["gr_id"], $produit_id,0);
                    }
                    $this->_em->getRepository("AmsPaieBundle:PaiPrdTournee")->recalcul_tournee_produit_nature($param["gr_id"],$produit_id,0);
                }
            }
/*            if ($param["tournee_org_id"]!='') {
                $this->_em->getRepository("AmsPaieBundle:PaiTournee")->validate($msg, $msgException, $param["tournee_org_id"]);
            }
            $this->_em->getRepository("AmsPaieBundle:PaiTournee")->validate($msg, $msgException, $param["tournee_id"]);*/
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "","","");
        }
        return true;
    }

}
