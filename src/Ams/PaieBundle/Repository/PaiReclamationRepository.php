<?php
namespace Ams\PaieBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class PaiReclamationRepository extends GlobalRepository
{
    function select($depot_id,$flux_id, $anneemois_id, $sqlCondition=''){  
        try {
            $sql = "SELECT
                        pr.id,
                        pt.date_distrib,
                        pr.type_id,
                        pt.code as tournee_id,
                        concat_ws(' ',e.nom,e.prenom1,e.prenom2) as employe_id,
                        pr.societe_id,
                        pr.nbrec_abonne,
                        pr.nbrec_diffuseur,
                        pr.commentaire,
                        (select group_concat(if (c.imputation_paie,c.id,concat('-',c.id))separator ',')
                            from crm_detail c
                            inner join crm_demande cd on c.crm_demande_id=cd.id and cd.crm_categorie_id=1 -- seulement les reclamations
                            where c.pai_tournee_id=pt.id and c.societe_id=pr.societe_id
                            -- and c.imputation_paie=true
                            group by c.societe_id,c.pai_tournee_id
                        ) as crm,
                        if(type_id=2 and pr.date_extrait is null,true,false) as isModif -- on ne peut pas modifier les reclamations crm ou pepp
                    FROM pai_reclamation pr
                    INNER JOIN pai_tournee pt ON pr.tournee_id=pt.id
                    LEFT OUTER JOIN employe e on pt.employe_id=e.id
                    WHERE  pt.depot_id=".$depot_id."  AND  pt.flux_id=".$flux_id."
                    AND pr.anneemois = '".$anneemois_id."'".
                    ($sqlCondition!='' ? $sqlCondition : "")."
                    ORDER BY pt.date_distrib desc,pt.code
                    ";
            return $this->_em->getConnection()->fetchAll ($sql);
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "","","");
        }
    }
    
    public function insert(&$msg, &$msgException, $param, $user, &$id) {
        try {
            if ($this->sqlField->sqlQuantite($param['nbrec_abonne'])==0 and $this->sqlField->sqlQuantite($param['nbrec_diffuseur'])==0){
                $msg="Le nombre de réclamations doit être différent de 0.";
                return false;
            }
            $sql = "INSERT INTO pai_reclamation(anneemois,type_id,tournee_id,societe_id,nbrec_abonne,nbrec_abonne_brut,nbrec_diffuseur,nbrec_diffuseur_brut,commentaire,utilisateur_id,date_creation) 
                    SELECT
                    coalesce(pm.anneemois,prm.anneemois),
                    2,
                    " . $this->sqlField->sqlId($param['tournee_id']) . ",
                    " . $this->sqlField->sqlId($param['societe_id']) . ",
                    " . $this->sqlField->sqlQuantite($param['nbrec_abonne']) . ",
                    " . $this->sqlField->sqlQuantite($param['nbrec_abonne']) . ",
                    " . $this->sqlField->sqlQuantite($param['nbrec_diffuseur']) . ",
                    " . $this->sqlField->sqlQuantite($param['nbrec_diffuseur']) . ",
                    " . $this->sqlField->sqlTrimQuote($param['commentaire']) . ",
                    " . $user . ",
                    NOW()
                    FROM pai_tournee pt
                    left outer join pai_mois pm on pt.date_distrib<=pm.date_fin and pt.flux_id=pm.flux_id
                    inner join pai_ref_mois prm on pt.date_distrib between prm.date_debut and prm.date_fin
                    WHERE pt.id=".$param['tournee_id'];
            $this->_em->getConnection()->prepare($sql)->execute();
            $id = $this->_em->getConnection()->lastInsertId();
            $this->_em->getRepository("AmsPaieBundle:PaiMajoration")->recalcul_id($param['tournee_id']);
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "La réclamation doit être unique.","UNIQUE","");
        }
        return true;
    }
    
    public function update(&$msg, &$msgException, $param, $user, &$id) {
          try {
            if ($this->sqlField->sqlQuantite($param['nbrec_abonne'])==0 and $this->sqlField->sqlQuantite($param['nbrec_diffuseur'])==0){
                $msg="Le nombre de réclamations doit être différent de 0.";
                return false;
            }
            $sql = "UPDATE pai_reclamation SET
                    tournee_id = " . $this->sqlField->sqlId($param['tournee_id']) . ",
                    societe_id = " . $this->sqlField->sqlId($param['societe_id']) . ",
                    nbrec_abonne = " . $this->sqlField->sqlQuantite($param['nbrec_abonne']) . ",
                    nbrec_abonne_brut = " . $this->sqlField->sqlQuantite($param['nbrec_abonne']) . ",
                    nbrec_diffuseur = " . $this->sqlField->sqlQuantite($param['nbrec_diffuseur']) . ",
                    nbrec_diffuseur_brut = " . $this->sqlField->sqlQuantite($param['nbrec_diffuseur']) . ",
                    commentaire = " . $this->sqlField->sqlTrimQuote($param['commentaire']) . ",
                    utilisateur_id = " . $user . ",
                    date_modif = NOW()";
            $sql .= " WHERE id = " . $param['gr_id']." AND type_id=2 AND date_extrait is null";
            $this->_em->getConnection()->prepare($sql)->execute();
            $this->_em->getRepository("AmsPaieBundle:PaiMajoration")->recalcul_id($param['tournee_id']);
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "","","");
        }
        return true;
    }
    
    public function delete(&$msg, &$msgException, $param) {
        try {
            $sql = "DELETE FROM pai_reclamation
                    WHERE id = " . $param['gr_id']."
                    AND type_id=2
                    AND date_extrait is null
                    ";
            $this->_em->getConnection()->prepare($sql)->execute();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "","","");
        }
        return true;
    }

    function selectComboTourneeDate($depot_id, $flux_id, $date_distrib) {
        $sql = "SELECT distinct
                    pt.id,
                    CONCAT_WS(' ', pt.code,e.nom, e.prenom1, e.prenom2) as libelle
                    FROM pai_tournee pt
                    LEFT OUTER JOIN employe e ON pt.employe_id=e.id
                    WHERE pt.depot_id=" . $depot_id . " AND pt.flux_id=" . $flux_id . "
                    AND pt.date_distrib='" . $date_distrib . "'
                    ORDER BY pt.code"
        ;
        return $this->_em->getConnection()->fetchAll($sql);
    }

    function selectComboTourneeMois($depot_id, $flux_id, $anneemois) {
        $sql = "SELECT distinct
                    pt.id,
                    pt.code as libelle
                    FROM pai_reclamation pr
                    INNER JOIN pai_tournee pt on pt.id=pr.tournee_id
                    LEFT OUTER JOIN employe e ON pt.employe_id=e.id
                    WHERE pt.depot_id=" . $depot_id . " AND pt.flux_id=" . $flux_id . "
                    AND pr.anneemois='" . $anneemois . "'
                    ORDER BY pt.code"
        ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
/*    
    function selectComboEmployeMois($depot_id, $flux_id, $anneemois) {
        // ATTENTION, il peut manquer des employés si retroactivité
        $sql = "SELECT distinct
                    e.id,
                    CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2) as libelle
                    FROM pai_reclamation pr
                    INNER JOIN pai_tournee pt on pt.id=pr.tournee_id
                    LEFT OUTER JOIN employe e ON pt.employe_id=e.id
                    WHERE pt.depot_id=" . $depot_id . " AND pt.flux_id=" . $flux_id . "
                    AND pr.anneemois='" . $anneemois . "'
                    ORDER BY pt.code"
        ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
*/    
    }