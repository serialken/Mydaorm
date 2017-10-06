<?php
namespace Ams\PaieBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class PaiActiviteRepository extends GlobalRepository {

    function select($depot_id, $flux_id, $date_distrib, $est_hors_presse, $sqlCondition='') {
        $sql = "SELECT
                    pa.id,
                    pa.depot_id,
                    pa.flux_id,
                    pa.date_distrib,
                    pa.jour_id,
                    if(ra.est_pleiades,ra.libelle,pa.activite_id) as activite_id,
                    coalesce(pa.xaoid,pa.employe_id) as employe_id,
                    pa.ouverture,
                    pa.transport_id,
                    date_format(pa.heure_debut,'%H:%i') as heure_debut,
                    date_format(pa.heure_debut_calculee,'%H:%i') as heure_debut_calculee,
                    date_format(pa.duree,'%H:%i') as duree,
                    date_format(pa.duree_nuit,'%H:%i') as duree_nuit,
                    date_format(pa.duree_garantie,'%H:%i') as duree_garantie,
                    pa.nbkm_paye,
                    pa.commentaire,
                    ech.commentaire as descriptif,
                    pa.date_extrait is null isModif,
                    pa.date_extrait is null and (not ra.est_pleiades or group_concat(pe.id) like '%35%' or group_concat(pe.id) like '%36%') isDelete,
                    ra.est_pleiades,
                    -- journal
                    coalesce(min(pe.valide),true) as valide,
                    group_concat(pe.msg order by pe.level,pe.rubrique,pe.code separator '<br/>') as msg,
                    min(pj.id) as journal_id,
                    min(pe.level) as level
                FROM pai_activite pa
                inner join ref_activite ra on pa.activite_id=ra.id and ra.est_hors_presse=".($est_hors_presse?"true":"false")."
                left outer join pai_journal pj on pa.id=pj.activite_id
                LEFT OUTER JOIN pai_ref_erreur pe ON pj.erreur_id=pe.id
                left outer join emp_contrat_hp ech on ech.xaoid=pa.xaoid
                WHERE pa.depot_id=$depot_id AND pa.flux_id=$flux_id AND pa.date_distrib='$date_distrib'
                AND pa.activite_id>-1 
                ".($sqlCondition!='' ? $sqlCondition : "")."
                GROUP BY pa.id
                ORDER BY pa.employe_id,pa.heure_debut_calculee"
        ;
        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function selectPlanning($employe_id, $start, $end) {
        $sql = "SELECT
                    pa.id,
                    ra.libelle as title,
                    concat(pa.date_distrib,'T',coalesce(pa.heure_debut_calculee,'00:00')) as start,
                    concat(pa.date_distrib,'T',coalesce(addtime(pa.heure_debut_calculee,pa.duree),'24:00')) as end,
                    concat('liste-pai-activite?depot_id=',pa.depot_id, '&flux_id=',pa.flux_id, '&date_distrib=',pa.date_distrib, '&activite_id=',pa.id) as url,
                    '#FF8000' as color,
                    if(coalesce(min(pe.valide),true),ra.couleur,'#FAD9DD') as backgroundColor
                FROM pai_activite pa
                INNER JOIN ref_activite ra ON pa.activite_id=ra.id
                left outer join pai_journal pj on pa.id=pj.activite_id
                LEFT OUTER JOIN pai_ref_erreur pe ON pj.erreur_id=pe.id
                WHERE pa.employe_id = '$employe_id'
                AND pa.date_distrib between '$start' and '$end'
                AND pa.duree<>0
                GROUP BY pa.id,ra.id
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    public function insert(&$msg, &$msgException, $param, $user, &$id) {
        try {
            $sql = "INSERT INTO pai_activite(
                depot_id, flux_id,date_distrib,
                activite_id, employe_id, transport_id, xaoid,
                heure_debut, duree,
                nbkm_paye,
                commentaire,
                utilisateur_id, date_creation";
            if (isset($param['ouverture_centre'])) {
                $sql .= ",ouverture";
            }
                $sql .= ") SELECT 
                " . $param['depot_id'] . "," . $param['flux_id'] . ",'" . $param['date_distrib'] . "',
                ra.id,
                coalesce(ech.employe_id," . $this->sqlField->sqlTrimQuote($param['employe_id']) . "),
                rt.id,
                ech.xaoid,
                " . $this->sqlField->sqlHeureOrNull($param['heure_debut']) . "," . $this->sqlField->sqlDuree($param['duree']) . ",
                case when coalesce(epd.km_paye,1) and coalesce(rt.km_paye,1) and coalesce(ra.km_paye,1) then " . $this->sqlField->sqlTrim($param['nbkm_paye']) . " else 0 end,
                " . $this->sqlField->sqlTrimQuote($param['commentaire']) . ",
                " . $user . ",NOW()";
            if (isset($param['ouverture_centre'])) {
                $sql .= "," . $param['ouverture_centre'];
                if ($param['ouverture_centre']==1) {
                    $this->initOuverture($param['depot_id'], $param['flux_id'], $param['date_distrib']);
                }
            }
                $sql .= " FROM ref_activite ra
                left outer join emp_contrat_hp ech on ech.xaoid=" . $this->sqlField->sqlTrimQuote($param['employe_id']) . "
                left outer join emp_pop_depot epd on epd.employe_id=coalesce(ech.employe_id," . $this->sqlField->sqlTrimQuote($param['employe_id']) . ") and '" . $param['date_distrib'] . "' between epd.date_debut and epd.date_fin
                left outer join ref_transport rt on rt.id=" . $this->sqlField->sqlTrim($param['transport_id']) . "
                WHERE ra.id=" . $this->sqlField->sqlTrim($param['activite_id']);
            $this->_em->getConnection()->prepare($sql)->execute();
            $id = $this->_em->getConnection()->lastInsertId();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "L'activite doit être unique.", "UNIQUE", "");
        }
        return true;
    }

    public function update(&$msg, &$msgException, $param, $user, &$id) {
        try {
            $sql = "UPDATE pai_activite pa
                left outer join emp_contrat_hp ech on ech.xaoid=" . $this->sqlField->sqlTrimQuote($param['employe_id']) . "
                left outer join emp_pop_depot epd on epd.employe_id=coalesce(ech.employe_id," . $this->sqlField->sqlTrimQuote($param['employe_id']) . ") and pa.date_distrib between epd.date_debut and epd.date_fin";
            if ($param['est_pleiades']==0){
            $sql .= " left outer join ref_activite ra on ra.id=" . $this->sqlField->sqlIdOrNull($param['activite_id']);
            } else {
            $sql .= " left outer join ref_activite ra on ra.id=pa.activite_id";
            }
            $sql .= " left outer join ref_transport rt on rt.id=" . $this->sqlField->sqlIdOrNull($param['transport_id']);
            $sql .= " SET ";
            if ($param['est_pleiades']==0){
            $sql .= "pa.activite_id = " . $param['activite_id'] . ",
                    pa.employe_id = coalesce(ech.employe_id," . $this->sqlField->sqlTrimQuote($param['employe_id']) . "),";
            }
            $sql .= "pa.transport_id = " . $this->sqlField->sqlIdOrNull($param['transport_id']) . ",
                pa.xaoid=ech.xaoid,
                pa.heure_debut = " . $this->sqlField->sqlHeureOrNull($param['heure_debut']) . ",
                pa.duree = " . $this->sqlField->sqlDuree($param['duree']) . ",
                pa.nbkm_paye = case when coalesce(epd.km_paye,1) and coalesce(rt.km_paye,1) and coalesce(ra.km_paye,1) then " . $this->sqlField->sqlTrim($param['nbkm_paye']) . " else 0 end,
                pa.commentaire = " . $this->sqlField->sqlTrimQuote($param['commentaire']) . ",
                pa.utilisateur_id = " . $user . ",
                pa.date_modif = NOW()";
            if (isset($param['ouverture_centre'])) {
                $sql .= ",pa.ouverture = " . $param['ouverture_centre'];
                if ($param['ouverture_centre']==1) {
                    $this->initOuverture($param['depot_id'], $param['flux_id'], $param['date_distrib']);
                }
            }
            $sql .= " WHERE pa.id =" . $param['gr_id'];
            $this->_em->getConnection()->prepare($sql)->execute();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "L'activite doit être unique.", "UNIQUE", "");
        }
        return true;
    }

    public function delete(&$msg, &$msgException, $param) {
        try {
            $this->_em->getConnection()->executeQuery("delete from pai_journal where activite_id=" . $param['gr_id']);
            $sql = "DELETE FROM pai_activite
                    WHERE id=" . $param['gr_id'];
            $this->_em->getConnection()->prepare($sql)->execute();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "Suppression impossible.", "FOREIGN", "");
        }
        return true;
    }

    public function validate(&$msg, &$msgException, $id, $action = "", $param = null) {
        try {
            if ($action != "delete"){
                $sql = "call pai_valide_activite(@validation_id,".$param['depot_id'].",". $param['flux_id'].",'". $param['date_distrib']."'," . $this->sqlField->sqlId($id) . ")";
                $validation_id = $this->executeProc($sql, "@validation_id");
            }
            if ($param['old_employe_id'] != '' && ($action == "delete" || $param['old_employe_id'] != $param['employe_id'])) {
                if (is_numeric($param['old_employe_id'])){
                    $this->_em->getRepository("AmsPaieBundle:PaiHeure")->recalcul_horaire($validation_id,null,null,$param['date_distrib'],$param['old_employe_id']);
                } else {
                    $this->_em->getRepository("AmsPaieBundle:PaiHeure")->recalcul_horaire_contrathp($validation_id,$param['date_distrib'],$param['old_employe_id']);
                }
            }
            if ($param['employe_id'] != '') {
                if (is_numeric($param['employe_id'])){
                    $this->_em->getRepository("AmsPaieBundle:PaiHeure")->recalcul_horaire($validation_id,null,null,$param['date_distrib'],$param['employe_id']);
                } else {
                    $this->_em->getRepository("AmsPaieBundle:PaiHeure")->recalcul_horaire_contrathp($validation_id,$param['date_distrib'],$param['employe_id']);
                }
            }
            return $validation_id;
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "Erreur inconnue.", "", "");
        }
    }

    public function alimentation(&$msg, &$msgException, &$idtrt, $user, $date_distrib, $depot_id = 0, $flux_id = 0, $est_hors_presse='') {
        try {
            $sql="set @idtrt=".$this->sqlField->sqlIdOrNull($idtrt).";";
            $sql.="call alim_activite(" . $user . ",@idtrt," . $this->sqlField->sqlTrimQuote($date_distrib) . "," . $this->sqlField->sqlIdOrNull($depot_id) . "," . $this->sqlField->sqlIdOrNull($flux_id) . "," . $this->sqlField->sqlTrim($est_hors_presse) . ")";
            $idtrt = $this->executeProc($sql, "@idtrt");
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "", "", "");
        }
        return true;
    }

    public function suppression(&$msg, &$msgException, &$idtrt, $user, $date_distrib, $depot_id = 0, $flux_id = 0, $est_hors_presse='') {
        try {
            $sql="set @idtrt=".$this->sqlField->sqlIdOrNull($idtrt).";";
            $sql.="call SUPPRIM_ACTIVITE(" . $user . ",@idtrt," . $this->sqlField->sqlTrimQuote($date_distrib) . "," . $this->sqlField->sqlIdOrNull($depot_id) . "," . $this->sqlField->sqlIdOrNull($flux_id) . "," . $this->sqlField->sqlTrim($est_hors_presse) . ")";
            $idtrt = $this->executeProc($sql, "@idtrt");
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "", "", "");
        }
        return true;
    }

    function initOuverture($depot_id, $flux_id, $date_distrib) {
        $sql = "update pai_activite pa
                set pa.ouverture=false
                where pa.depot_id=" . $depot_id . "  and pa.flux_id=" . $flux_id . " and pa.date_distrib='" . $date_distrib . "'
                and  pa.ouverture=true
                ";
        $this->_em->getConnection()->prepare($sql)->execute();
    }
    
    function getEmargement($depot_id, $flux_id, $date_distrib, $emploi, $est_hors_presse) {
        $sql = "SELECT 
                    TIME_FORMAT(pa.heure_debut_calculee, '%H:%i') as heure_debut,
                    TIME_FORMAT(pa.duree, '%H:%i') as duree,
                    pa.nbkm_paye,
                    CONCAT_WS(' ', e.nom , e.prenom1, e.prenom2) as nom_prenom,
                    ra.libelle
                FROM  pai_activite pa
                LEFT OUTER JOIN employe e ON e.id = pa.employe_id
                INNER JOIN ref_activite ra ON pa.activite_id = ra.id and ra.est_hors_presse=".($est_hors_presse?"true":"false")."
                INNER JOIN emp_pop_depot epd ON pa.employe_id = epd.employe_id AND '".$date_distrib."' between epd.date_debut AND epd.date_fin
                INNER JOIN ref_emploi re on epd.emploi_id=re.id
                WHERE  re.code ='".$emploi."'
                AND pa.depot_id=" . $depot_id . " AND pa.flux_id=" . $flux_id . " AND  pa.date_distrib='" . $date_distrib . "'
                ORDER BY nom_prenom, heure_debut"
        ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    /**
     * Méthode qui génère l'extraction envoyée quotidiennement pour le suivi d'activité
     * @param Datetime $oDateDebut L'objet contenant la date de début de la période
     * @param Datetime $oDateFin L'objet contenant la date de fin de la période
     * @return array depot,nom, prenom1, libelle, code, date_distrib, duree, nbkm_paye, libelle_flux, commentaire, description
     */
    public function getSuiviActiviteInfo($oDateDebut, $oDateFin){   
        $sql = " 
            SELECT 
                d.libelle AS depot,
                nom,
                prenom1,
                ra.libelle,
                ra.code,
                a.date_distrib,
                a.duree as duree,
                nbkm_paye,
                IF(a.flux_id = 1, 'Nuit', 'Jour'),
                a.commentaire,
                ech.commentaire as description,
                REPLACE(FORMAT((hour(a.duree)*60 + minute(a.duree) + second(a.duree)/60 )/60, 2), '.', ',') as dureeDecimal
            FROM
                pai_activite a
                inner join ref_activite ra on a.activite_id = ra.id
                left outer join emp_contrat_hp ech on ech.xaoid =  a.xaoid
                inner join employe e ON a.employe_id = e.id
                inner join depot d on d.id = a.depot_id
                WHERE date_distrib BETWEEN  '".$oDateDebut->format("Y-m-d")."' AND  '" . $oDateFin->format("Y-m-d")."' ORDER BY d.id, a.date_distrib";
         return $this->_em->getConnection()->executeQuery($sql)->fetchAll();
    }
}
