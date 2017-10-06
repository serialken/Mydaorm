<?php 
namespace Ams\EmployeBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class EmployeRepository extends GlobalRepository
{
// JOURNAL -----------------------------------------------------------------
    public function selectComboModeleJournal($depot_id, $flux_id) {
        // On selectionne 
        // - les employés sous contrat à la date du jour
        // - les employé hors contrat mais présent dans les modèles
        $sql = "SELECT e.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2) libelle 
                FROM employe e
                INNER JOIN modele_journal mj ON e.id = mj.employe_id AND mj.depot_id=$depot_id AND mj.flux_id=$flux_id
                ORDER BY 2
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    public function selectComboEmpJournal($depot_id, $flux_id) {
        // On selectionne 
        // - les employés sous contrat à la date du jour
        // - les employé hors contrat mais présent dans les modèles
        $sql = "SELECT e.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2) libelle 
                FROM employe e
                INNER JOIN emp_journal ej ON e.id = ej.employe_id AND ej.depot_id=$depot_id AND ej.flux_id=$flux_id
                ORDER BY 2
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }
    public function selectComboPaiJournal($anneemois,$depot_id=0, $flux_id=0) {
        // On selectionne 
        // - les employés sous contrat dans le mois
        // - les employé hors contrat mais présent dans les tournées ou les activités
        $sql = "SELECT e.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2, CASE WHEN epd.id is null and eaf.id is null then '*' else '' end) libelle 
                FROM employe e
                INNER JOIN pai_journal pj ON e.id = pj.employe_id AND pj.anneemois='$anneemois'
                ".($depot_id<>0?"AND pj.depot_id=".$depot_id:"")."
                ".($flux_id<>0?"AND pj.flux_id=" . $flux_id:"")."
                INNER JOIN pai_ref_mois prm ON prm.anneemois='$anneemois'
                LEFT OUTER JOIN emp_pop_depot epd ON e.id = epd.employe_id AND pj.date_distrib between epd.date_debut and epd.date_fin AND epd.depot_id=pj.depot_id AND epd.flux_id=pj.flux_id
                LEFT OUTER JOIN emp_pop_depot epd2 ON e.id = epd2.employe_id AND pj.date_distrib between epd2.date_debut and epd2.date_fin
                LEFT OUTER JOIN emp_affectation eaf ON epd2.contrat_id = eaf.contrat_id AND pj.date_distrib between eaf.date_debut and eaf.date_fin AND eaf.depot_dst_id=pj.depot_id AND eaf.flux_id=pj.flux_id                    
                ORDER BY 2
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
 
// MODELE -----------------------------------------------------------------
    public function selectComboModeleTournee($depot_id, $flux_id) {
        // On selectionne 
        // - les employés sous contrat à la date du jour
        // - les employé hors contrat mais présent dans les modèles
        $sql = "SELECT e.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2) libelle 
                FROM employe e
                INNER JOIN emp_pop_depot epd ON e.id = epd.employe_id AND NOW()<=epd.date_fin AND epd.depot_id=$depot_id AND epd.flux_id=$flux_id
                INNER JOIN ref_emploi re ON epd.emploi_id=re.id AND re.affichage_modele_tournee
                
                UNION
                
                SELECT e.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2) libelle 
                FROM employe e
                INNER JOIN emp_pop_depot epd ON e.id = epd.employe_id AND NOW()<=epd.date_fin
                INNER JOIN emp_affectation eaf ON eaf.contrat_id = epd.contrat_id AND NOW()<=eaf.date_fin AND eaf.depot_dst_id=$depot_id AND eaf.flux_id=$flux_id
                INNER JOIN ref_emploi re ON epd.emploi_id=re.id AND re.affichage_modele_tournee
                
                UNION
                
                SELECT e.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2, '*') libelle 
                FROM employe e
                INNER JOIN modele_tournee mt ON e.id = mt.employe_id
                INNER JOIN groupe_tournee gt ON gt.id = mt.groupe_id AND gt.depot_id=$depot_id AND gt.flux_id=$flux_id
                WHERE not exists (SELECT null
                                FROM emp_pop_depot epd2
                                INNER JOIN ref_emploi re ON epd2.emploi_id=re.id AND re.affichage_modele_tournee
                                WHERE epd2.depot_id=$depot_id AND epd2.flux_id=$flux_id
                                AND NOW()<=epd2.date_fin
                                AND e.id=epd2.employe_id
                                )
                AND not exists (SELECT null
                                FROM emp_affectation eaf2
                                INNER JOIN emp_pop_depot epd2 ON eaf2.contrat_id = epd2.contrat_id AND NOW()<=epd2.date_fin
                                INNER JOIN ref_emploi re ON epd2.emploi_id=re.id AND re.affichage_modele_tournee
                                WHERE eaf2.depot_dst_id=$depot_id AND eaf2.flux_id=$flux_id
                                AND NOW()<=eaf2.date_fin
                                AND e.id=epd2.employe_id
                                )
                ORDER BY 2
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function selectComboModeleTourneeJour($depot_id, $flux_id) {
        // On selectionne 
        // - les employés sous contrat à la date du jour
        // - les employé hors contrat mais présent dans les modèles
        $sql = "SELECT e.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2) libelle 
                FROM employe e
                INNER JOIN emp_pop_depot epd ON e.id = epd.employe_id AND NOW()<=epd.date_fin AND epd.depot_id=$depot_id AND epd.flux_id=$flux_id
                INNER JOIN ref_emploi re ON epd.emploi_id=re.id AND re.affichage_modele_tournee
                
                UNION
                
                SELECT e.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2) libelle 
                FROM employe e
                INNER JOIN emp_pop_depot epd ON e.id = epd.employe_id AND NOW()<=epd.date_fin
                INNER JOIN emp_affectation eaf ON eaf.contrat_id = epd.contrat_id AND NOW()<=eaf.date_fin AND eaf.depot_dst_id=$depot_id AND eaf.flux_id=$flux_id
                INNER JOIN ref_emploi re ON epd.emploi_id=re.id AND re.affichage_modele_tournee
                
                UNION
                
                SELECT e.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2, '*') libelle 
                FROM employe e
                INNER JOIN modele_tournee_jour mtj ON e.id = mtj.employe_id
                INNER JOIN modele_tournee mt ON mtj.tournee_id = mt.id
                INNER JOIN groupe_tournee gt ON gt.id = mt.groupe_id AND gt.depot_id=$depot_id AND gt.flux_id=$flux_id
                WHERE not exists (SELECT null
                                FROM emp_pop_depot epd2
                                INNER JOIN ref_emploi re ON epd2.emploi_id=re.id AND re.affichage_modele_tournee
                                WHERE epd2.depot_id=$depot_id AND epd2.flux_id=$flux_id
                                AND NOW()<=epd2.date_fin
                                AND e.id=epd2.employe_id
                                )
                AND not exists (SELECT null
                                FROM emp_affectation eaf2
                                INNER JOIN emp_pop_depot epd2 ON eaf2.contrat_id = epd2.contrat_id AND NOW()<=epd2.date_fin
                                INNER JOIN ref_emploi re ON epd2.emploi_id=re.id AND re.affichage_modele_tournee
                                WHERE eaf2.depot_dst_id=$depot_id AND eaf2.flux_id=$flux_id
                                AND NOW()<=eaf2.date_fin
                                AND e.id=epd2.employe_id
                                )
                ORDER BY 2
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function selectComboModeleActivite($depot_id, $flux_id) {
        // On selectionne 
        // - les employés sous contrat à la date du jour
        // - les employé hors contrat mais présent dans les modèles
        $sql = "SELECT e.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2) libelle 
                FROM employe e
                INNER JOIN emp_pop_depot epd ON e.id = epd.employe_id AND epd.population_id>0 AND NOW()<=epd.date_fin AND epd.depot_id=$depot_id AND epd.flux_id=$flux_id
                INNER JOIN ref_emploi re ON epd.emploi_id=re.id AND re.affichage_modele_activite
                
                UNION
                
                SELECT e.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2) libelle 
                FROM employe e
                INNER JOIN emp_pop_depot epd ON e.id = epd.employe_id AND NOW()<=epd.date_fin AND epd.population_id>0
                INNER JOIN emp_affectation eaf ON eaf.contrat_id = epd.contrat_id AND NOW()<=eaf.date_fin AND eaf.depot_dst_id=$depot_id AND eaf.flux_id=$flux_id
                INNER JOIN ref_emploi re ON epd.emploi_id=re.id AND re.affichage_modele_activite
                
                UNION
                
                SELECT e.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2, '*') libelle 
                FROM employe e
                INNER JOIN modele_activite ma ON e.id = ma.employe_id AND ma.depot_id=$depot_id AND ma.flux_id=$flux_id
                WHERE not exists (SELECT null
                                FROM emp_pop_depot epd2
                                INNER JOIN ref_emploi re ON epd2.emploi_id=re.id AND re.affichage_modele_activite
                                WHERE epd2.depot_id=$depot_id AND epd2.flux_id=$flux_id
                                AND NOW()<=epd2.date_fin
                                AND epd2.population_id>0
                                AND e.id=epd2.employe_id
                                )
                AND not exists (SELECT null
                                FROM emp_affectation eaf2
                                INNER JOIN emp_pop_depot epd2 ON eaf2.contrat_id = epd2.contrat_id AND NOW()<=epd2.date_fin
                                INNER JOIN ref_emploi re ON epd2.emploi_id=re.id AND re.affichage_modele_activite AND epd2.population_id>0
                                WHERE eaf2.depot_dst_id=$depot_id AND eaf2.flux_id=$flux_id
                                AND NOW()<=eaf2.date_fin
                                AND e.id=epd2.employe_id
                                )
                ORDER BY 2
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }    
    
    public function selectComboModele($depot_id, $flux_id) {
        // Utilisé pour alimenter le planning
        // On selectionne 
        // - les employés sous contrat à la date du jour
        // - les employé hors contrat mais présent dans les modèles
        $sql = "SELECT e.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2) libelle 
                FROM employe e
                INNER JOIN emp_pop_depot epd ON e.id = epd.employe_id AND NOW()<=epd.date_fin AND epd.depot_id=$depot_id AND epd.flux_id=$flux_id
                INNER JOIN ref_emploi re ON epd.emploi_id=re.id AND (re.affichage_modele_tournee or re.affichage_modele_activite)
                
                UNION
                
                SELECT e.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2) libelle 
                FROM employe e
                INNER JOIN emp_pop_depot epd ON e.id = epd.employe_id AND NOW()<=epd.date_fin AND epd.population_id>0
                INNER JOIN emp_affectation eaf ON eaf.contrat_id = epd.contrat_id AND NOW()<=eaf.date_fin AND eaf.depot_dst_id=$depot_id AND eaf.flux_id=$flux_id
                INNER JOIN ref_emploi re ON epd.emploi_id=re.id AND (re.affichage_modele_tournee or re.affichage_modele_activite)

                UNION
                
                SELECT e.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2, '*') libelle 
                FROM employe e
                INNER JOIN modele_tournee mt ON e.id = mt.employe_id
                INNER JOIN groupe_tournee gt ON gt.id = mt.groupe_id AND gt.depot_id=$depot_id AND gt.flux_id=$flux_id
                WHERE not exists (SELECT null
                                FROM emp_pop_depot epd2
                                INNER JOIN ref_emploi re ON epd2.emploi_id=re.id AND re.affichage_modele_tournee
                                WHERE epd2.depot_id=$depot_id AND epd2.flux_id=$flux_id
                                AND NOW()<=epd2.date_fin
                                AND epd2.population_id>0
                                AND e.id=epd2.employe_id
                                )
                AND not exists (SELECT null
                                FROM emp_affectation eaf2
                                INNER JOIN emp_pop_depot epd2 ON eaf2.contrat_id = epd2.contrat_id AND NOW()<=epd2.date_fin
                                INNER JOIN ref_emploi re ON epd2.emploi_id=re.id AND re.affichage_modele_tournee
                                WHERE eaf2.depot_dst_id=$depot_id AND eaf2.flux_id=$flux_id
                                AND NOW()<=eaf2.date_fin
                                AND e.id=epd2.employe_id
                                )
                UNION
                
                SELECT e.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2, '*') libelle 
                FROM employe e
                INNER JOIN modele_tournee_jour mtj ON e.id = mtj.employe_id
                INNER JOIN modele_tournee mt ON mtj.tournee_id = mt.id
                INNER JOIN groupe_tournee gt ON gt.id = mt.groupe_id AND gt.depot_id=$depot_id AND gt.flux_id=$flux_id
                WHERE not exists (SELECT null
                                FROM emp_pop_depot epd2
                                INNER JOIN ref_emploi re ON epd2.emploi_id=re.id AND re.affichage_modele_tournee
                                WHERE epd2.depot_id=$depot_id AND epd2.flux_id=$flux_id
                                AND NOW()<=epd2.date_fin
                                AND e.id=epd2.employe_id
                                )
                AND not exists (SELECT null
                                FROM emp_affectation eaf2
                                INNER JOIN emp_pop_depot epd2 ON eaf2.contrat_id = epd2.contrat_id AND NOW()<=epd2.date_fin
                                INNER JOIN ref_emploi re ON epd2.emploi_id=re.id AND re.affichage_modele_tournee
                                WHERE eaf2.depot_dst_id=$depot_id AND eaf2.flux_id=$flux_id
                                AND NOW()<=eaf2.date_fin
                                AND e.id=epd2.employe_id
                                )
                UNION
                
                SELECT e.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2, '*') libelle 
                FROM employe e
                INNER JOIN modele_activite ma ON e.id = ma.employe_id AND ma.depot_id=$depot_id AND ma.flux_id=$flux_id
                WHERE not exists (SELECT null
                                FROM emp_pop_depot epd2
                                INNER JOIN ref_emploi re ON epd2.emploi_id=re.id AND re.affichage_modele_activite
                                WHERE epd2.depot_id=$depot_id AND epd2.flux_id=$flux_id
                                AND NOW()<=epd2.date_fin
                                AND e.id=epd2.employe_id
                                )
                AND not exists (SELECT null
                                FROM emp_affectation eaf2
                                INNER JOIN emp_pop_depot epd2 ON eaf2.contrat_id = epd2.contrat_id AND NOW()<=epd2.date_fin AND epd2.population_id>0
                                INNER JOIN ref_emploi re ON epd2.emploi_id=re.id AND re.affichage_modele_activite
                                WHERE eaf2.depot_dst_id=$depot_id AND eaf2.flux_id=$flux_id
                                AND NOW()<=eaf2.date_fin
                                AND e.id=epd2.employe_id
                                )
                ORDER BY 2
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
// PAIE -----------------------------------------------------------------
    public function selectComboPaiTournee($depot_id, $flux_id, $date_distrib, $withVCP=false) {
        // On selectionne 
        // - les employés sous contrat à la date du jour
        // - les employé hors contrat mais présent dans les tournées
        $sql = "SELECT e.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2) libelle 
                FROM employe e
                INNER JOIN emp_pop_depot epd ON e.id = epd.employe_id AND '$date_distrib' between  epd.date_debut AND epd.date_fin AND epd.depot_id=$depot_id AND epd.flux_id=$flux_id
                ".(!$withVCP?" WHERE epd.population_id>0":"")."
                
                UNION
                
                SELECT e.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2) libelle 
                FROM employe e
                INNER JOIN emp_pop_depot epd ON e.id = epd.employe_id AND '$date_distrib' between  epd.date_debut AND epd.date_fin
                INNER JOIN emp_affectation eaf ON eaf.contrat_id = epd.contrat_id AND '$date_distrib' between  eaf.date_debut AND eaf.date_fin AND eaf.depot_dst_id=$depot_id AND eaf.flux_id=$flux_id
                ".(!$withVCP?" WHERE epd.population_id>0":"")."
                    
                UNION
                
                SELECT e.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2, '*') libelle 
                FROM employe e
                INNER JOIN pai_tournee pt ON e.id = pt.employe_id AND '$date_distrib'=pt.date_distrib AND pt.depot_id=$depot_id AND pt.flux_id=$flux_id
                WHERE not exists(SELECT null
                                FROM emp_pop_depot epd2
                                WHERE epd2.depot_id=$depot_id AND epd2.flux_id=$flux_id
                                AND '$date_distrib' between  epd2.date_debut AND epd2.date_fin
                                ".(!$withVCP?" AND epd2.population_id>0":"")."
                                AND epd2.employe_id=e.id
                                )
                AND not exists (SELECT null
                                FROM emp_affectation eaf2
                                INNER JOIN emp_pop_depot epd2 ON eaf2.contrat_id = epd2.contrat_id AND '$date_distrib' between  epd2.date_debut AND epd2.date_fin
                                WHERE eaf2.depot_dst_id=$depot_id AND eaf2.flux_id=$flux_id
                                AND '$date_distrib' between  eaf2.date_debut AND eaf2.date_fin
                                ".(!$withVCP?" AND epd2.population_id>0":"")."
                                AND e.id=epd2.employe_id
                                )
                ORDER BY 2
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    public function selectComboPaiActivite($depot_id, $flux_id,$date_distrib,$est_horspresse) {
        if ($est_horspresse)
            return $this->selectComboPaiActiviteHorsPresse($depot_id, $flux_id,$date_distrib);
        else
            return $this->selectComboPaiActivitePresse($depot_id, $flux_id,$date_distrib);
    }    

    public function selectComboPaiActivitePresse($depot_id, $flux_id,$date_distrib) {
        $sql = "SELECT e.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2) libelle 
                FROM employe e
                INNER JOIN emp_pop_depot epd ON e.id = epd.employe_id AND epd.population_id>0 AND '$date_distrib' between  epd.date_debut AND epd.date_fin AND epd.depot_id=$depot_id AND epd.flux_id=$flux_id
                
                UNION
                
                SELECT e.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2) libelle 
                FROM employe e
                INNER JOIN emp_pop_depot epd ON e.id = epd.employe_id AND epd.population_id>0 AND '$date_distrib' between  epd.date_debut AND epd.date_fin
                INNER JOIN emp_affectation eaf ON eaf.contrat_id = epd.contrat_id AND '$date_distrib' between  eaf.date_debut AND eaf.date_fin AND eaf.depot_dst_id=$depot_id AND eaf.flux_id=$flux_id
                
                UNION
                
                SELECT e.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2, '*') libelle 
                FROM employe e
                INNER JOIN pai_activite pa ON e.id = pa.employe_id AND '$date_distrib'=pa.date_distrib AND pa.depot_id=$depot_id AND pa.flux_id=$flux_id
                INNER JOIN ref_activite ra on pa.activite_id=ra.id and not ra.est_hors_presse
                WHERE not exists(SELECT null
                                FROM emp_pop_depot epd2
                                WHERE epd2.depot_id=$depot_id AND epd2.flux_id=$flux_id
                                AND '$date_distrib' between  epd2.date_debut AND epd2.date_fin
                                AND epd2.population_id>0
                                AND epd2.employe_id=e.id
                                )
                AND not exists (SELECT null
                                FROM emp_affectation eaf2
                                INNER JOIN emp_pop_depot epd2 ON eaf2.contrat_id = epd2.contrat_id AND '$date_distrib' between  epd2.date_debut AND epd2.date_fin
                                WHERE eaf2.depot_dst_id=$depot_id AND eaf2.flux_id=$flux_id
                                AND '$date_distrib' between  eaf2.date_debut AND eaf2.date_fin
                                AND epd2.population_id>0
                                AND e.id=epd2.employe_id
                                )
                ORDER BY 2
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }    

    public function selectComboPaiActiviteHorsPresse($depot_id, $flux_id,$date_distrib) {
        $sql = "SELECT ech.xaoid as id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2,'-',ra.libelle) libelle 
                FROM employe e
                INNER JOIN emp_contrat_hp ech ON e.id = ech.employe_id AND '$date_distrib' between  ech.date_debut AND ech.date_fin AND ech.depot_id=$depot_id AND ech.flux_id=$flux_id
                INNER JOIN ref_activite ra on ech.activite_id=ra.id and ra.est_hors_presse
                
                UNION
                
                SELECT e.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2) libelle 
                FROM employe e
                INNER JOIN emp_pop_depot epd ON e.id = epd.employe_id AND epd.population_id>0 AND '$date_distrib' between  epd.date_debut AND epd.date_fin AND epd.depot_id=$depot_id AND epd.flux_id=$flux_id
                WHERE not exists (SELECT null
                                FROM emp_contrat_hp ech
                                WHERE epd.depot_id=ech.depot_id AND epd.flux_id=ech.flux_id
                                AND '$date_distrib' between  ech.date_debut AND ech.date_fin
                                AND epd.employe_id=ech.employe_id
                                )
                UNION
                
                SELECT pa.xaoid, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2,'-',ra.libelle, '*') libelle 
                FROM employe e
                INNER JOIN pai_activite pa ON e.id = pa.employe_id AND '$date_distrib'=pa.date_distrib AND pa.depot_id=$depot_id AND pa.flux_id=$flux_id
                INNER JOIN ref_activite ra on pa.activite_id=ra.id and ra.est_hors_presse
                WHERE not exists (SELECT null
                                FROM emp_contrat_hp ech2 
                                WHERE ech2.depot_id=pa.depot_id AND ech2.flux_id=pa.flux_id
                                AND pa.date_distrib between  ech2.date_debut AND ech2.date_fin
                                AND e.id=ech2.employe_id AND pa.xaoid=ech2.xaoid
                                )
                                
                UNION
                
                SELECT pa.employe_id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2, '*') libelle 
                FROM employe e
                INNER JOIN pai_activite pa ON e.id = pa.employe_id AND '$date_distrib'=pa.date_distrib AND pa.depot_id=$depot_id AND pa.flux_id=$flux_id
                INNER JOIN ref_activite ra on pa.activite_id=ra.id and ra.est_hors_presse
                WHERE not exists (SELECT epd2.employe_id
                                FROM emp_pop_depot epd2 
                                WHERE epd2.depot_id=pa.depot_id AND epd2.flux_id=pa.flux_id
                                AND pa.date_distrib between  epd2.date_debut AND epd2.date_fin
                                AND e.id=epd2.employe_id
                                )
                ORDER BY 2
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }    
    
    public function selectComboPaiIncident($depot_id, $flux_id, $date_distrib) {
        $sql = "SELECT e.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2) libelle 
                FROM employe e
                INNER JOIN emp_pop_depot epd on e.id=epd.employe_id AND '$date_distrib' between  epd.date_debut AND epd.date_fin AND epd.depot_id=$depot_id AND epd.flux_id=$flux_id
                ORDER BY 2
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    public function selectComboPaiStc($anneemois, $flux_id) {
        // On selectionne 
        // - les employés sous contrat dans le mois
        // - les employé hors contrat mais présent dans les tournées ou les activités
        $sql = "SELECT e.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2) libelle 
                FROM employe e
                INNER JOIN pai_ref_mois prm ON prm.anneemois='$anneemois'
                INNER JOIN emp_pop_depot epd ON e.id = epd.employe_id AND epd.fRC between prm.date_debut and prm.date_fin and epd.flux_id=$flux_id
                    
                UNION
                                
                SELECT e.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2, '*') libelle 
                FROM employe e
                INNER JOIN pai_stc ps ON e.id = ps.employe_id and ps.anneemois='$anneemois'
                WHERE NOT exists (SELECT null
                                FROM emp_pop_depot epd
                                WHERE epd.employe_id=ps.employe_id and epd.fRC=ps.date_stc
                                )
                ORDER BY 2
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }
 
    public function selectComboPaiMois($anneemois,$depot_id, $flux_id) {
        // On selectionne 
        // - les employés sous contrat dans le mois
        // - les employé hors contrat mais présent dans les tournées ou les activités
        $sql = "SELECT e.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2) libelle 
                FROM employe e
                INNER JOIN pai_ref_mois prm ON prm.anneemois='$anneemois'
                INNER JOIN emp_pop_depot epd ON e.id = epd.employe_id AND epd.depot_id=$depot_id AND epd.flux_id=$flux_id AND epd.date_fin>=prm.date_debut and epd.date_debut<=prm.date_fin
                    
                UNION
                
                SELECT e.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2) libelle 
                FROM employe e
                INNER JOIN pai_ref_mois prm ON prm.anneemois='$anneemois'
                INNER JOIN emp_pop_depot epd ON e.id = epd.employe_id AND epd.date_fin>=prm.date_debut and epd.date_debut<=prm.date_fin
                INNER JOIN emp_affectation eaf ON eaf.contrat_id = epd.contrat_id AND eaf.depot_dst_id=$depot_id AND eaf.flux_id=$flux_id AND eaf.date_fin>=prm.date_debut and eaf.date_debut<=prm.date_fin
                
                UNION

                SELECT e.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2, '*') libelle 
                FROM employe e
                INNER JOIN pai_ref_mois prm ON prm.anneemois='$anneemois'
                INNER JOIN pai_tournee t ON e.id = t.employe_id AND t.depot_id=$depot_id AND t.flux_id=$flux_id AND t.date_distrib between prm.date_debut and prm.date_fin
                WHERE not exists (SELECT null
                                FROM emp_pop_depot epd
                                WHERE epd.date_fin>=prm.date_debut and epd.date_debut<=prm.date_fin
                                AND epd.depot_id=$depot_id AND epd.flux_id=$flux_id
                                AND epd.employe_id=e.id
                                )
                AND not exists (SELECT null
                                FROM emp_affectation eaf2
                                INNER JOIN emp_pop_depot epd2 ON eaf2.contrat_id = epd2.contrat_id
                                WHERE eaf2.depot_dst_id=$depot_id AND eaf2.flux_id=$flux_id
                                AND eaf2.date_fin>=prm.date_debut and eaf2.date_debut<=prm.date_fin
                                AND epd2.date_fin>=prm.date_debut and epd2.date_debut<=prm.date_fin
                                AND e.id=epd2.employe_id
                                )
                                
                UNION
                
                SELECT e.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2, '*') libelle 
                FROM employe e
                INNER JOIN pai_ref_mois prm ON prm.anneemois='$anneemois'
                INNER JOIN pai_activite a ON e.id = a.employe_id AND a.depot_id=$depot_id AND a.flux_id=$flux_id AND a.date_distrib between prm.date_debut and prm.date_fin
                WHERE not exists (SELECT null
                                FROM emp_pop_depot epd
                                WHERE epd.date_fin>=prm.date_debut and epd.date_debut<=prm.date_fin
                                AND epd.depot_id=$depot_id AND epd.flux_id=$flux_id
                                AND epd.employe_id=e.id
                                )
                AND not exists (SELECT null
                                FROM emp_affectation eaf2
                                INNER JOIN emp_pop_depot epd2 ON eaf2.contrat_id = epd2.contrat_id
                                WHERE eaf2.depot_dst_id=$depot_id AND eaf2.flux_id=$flux_id
                                AND eaf2.date_fin>=prm.date_debut and eaf2.date_debut<=prm.date_fin
                                AND epd2.date_fin>=prm.date_debut and epd2.date_debut<=prm.date_fin
                                AND e.id=epd2.employe_id
                                )
                ORDER BY 2
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }
 
// RH -----------------------------------------------------------------
    public function selectComboRH($depot_id, $flux_id,$anneemois) {
        // On selectionne 
        // - les employés sous contrat dans le mois
        $sql = "SELECT e.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2) libelle 
                FROM employe e
                INNER JOIN pai_ref_mois prm ON prm.anneemois='$anneemois'
                INNER JOIN emp_pop_depot epd ON e.id = epd.employe_id AND epd.depot_id=$depot_id AND epd.flux_id=$flux_id AND epd.date_fin>=prm.date_debut and epd.date_debut<=prm.date_fin
                ORDER BY 2
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    public function selectComboTransfert($depot_id, $flux_id,$anneemois) {
        // On selectionne 
        // - les employés sous contrat dans le mois
        $sql = "SELECT DISTINCT epd.contrat_id as id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2, '(', epd.rc, date_format(epd.dRC,'%d/%m/%Y'),'-',date_format(epd.fRC,'%d/%m/%Y'), ')') libelle 
                FROM employe e
                INNER JOIN pai_ref_mois prm ON prm.anneemois='$anneemois'
                INNER JOIN emp_pop_depot epd ON e.id = epd.employe_id AND epd.depot_id=$depot_id AND epd.flux_id=$flux_id AND epd.date_fin>=prm.date_debut and epd.date_debut<=prm.date_fin
                
                UNION
                
                SELECT eco.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2, '(', eco.rc, date_format(eco.date_debut,'%d/%m/%Y'), '-', date_format(eco.date_fin,'%d/%m/%Y'), ')') libelle 
                FROM emp_transfert et
                INNER JOIN pai_ref_mois prm ON prm.anneemois='$anneemois'
                LEFT OUTER JOIN emp_contrat eco ON et.contrat_id=eco.id
                LEFT OUTER JOIN employe e ON eco.employe_id=e.id
                WHERE (et.depot_org_id =$depot_id OR et.depot_dst_id =$depot_id) AND et.flux_id=$flux_id
                AND et.date_fin>=prm.date_debut and et.date_debut<=prm.date_fin
                
                ORDER BY 2
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }
        
    public function selectComboAffectation($depot_id, $flux_id,$anneemois) {
        // On selectionne 
        // - les employés sous contrat dans le mois
        $sql = "SELECT DISTINCT epd.contrat_id as id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2, '(', epd.rc, date_format(epd.dRC,'%d/%m/%Y'),'-',date_format(epd.fRC,'%d/%m/%Y'), ')') libelle 
                FROM employe e
                INNER JOIN pai_ref_mois prm ON prm.anneemois='$anneemois'
                INNER JOIN emp_pop_depot epd ON e.id = epd.employe_id AND epd.depot_id=$depot_id AND epd.flux_id=$flux_id AND epd.date_fin>=prm.date_debut and epd.date_debut<=prm.date_fin
                INNER JOIN ref_population rp on epd.population_id=rp.id and rp.emploi_id=2
                
                UNION
                
                SELECT eco.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2, '(', eco.rc, date_format(eco.date_debut,'%d/%m/%Y'), '-', date_format(eco.date_fin,'%d/%m/%Y'), ')') libelle 
                FROM emp_affectation eaf
                INNER JOIN pai_ref_mois prm ON prm.anneemois='$anneemois'
                LEFT OUTER JOIN emp_contrat eco ON eaf.contrat_id=eco.id
                LEFT OUTER JOIN employe e ON eco.employe_id=e.id
                WHERE (eaf.depot_org_id =$depot_id OR eaf.depot_dst_id =$depot_id) AND eaf.flux_id=$flux_id
                AND eaf.date_fin>=prm.date_debut and eaf.date_debut<=prm.date_fin
                
                ORDER BY 2
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }
    

    public function selectComboRemplacement($depot_id, $flux_id,$anneemois) {
        // On selectionne 
        // - les employés sous contrat dans le mois
        $sql = "SELECT DISTINCT epd.contrattype_id as id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2) libelle 
                FROM employe e
                INNER JOIN emp_pop_depot epd ON e.id = epd.employe_id and epd.typecontrat_id=1 -- CDD
                INNER JOIN pai_ref_mois prm ON prm.anneemois='$anneemois'
                WHERE epd.depot_id=$depot_id AND epd.flux_id=$flux_id
                AND epd.date_fin>=prm.date_debut and epd.date_debut<=prm.date_fin
                
                UNION
                
                SELECT mr.contrattype_id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2,'*') libelle 
                FROM modele_remplacement mr
                INNER JOIN employe e on e.id=mr.employe_id
                INNER JOIN pai_ref_mois prm ON prm.anneemois='$anneemois'
                WHERE mr.depot_id=$depot_id AND mr.flux_id=$flux_id
                AND mr.date_fin>=prm.date_debut and mr.date_debut<=prm.date_fin
                and e.id not in (select e.id
                                FROM employe e
                                INNER JOIN emp_pop_depot epd ON e.id = epd.employe_id and epd.typecontrat_id=1 -- CDD
                                INNER JOIN pai_ref_mois prm ON prm.anneemois='$anneemois'
                                WHERE epd.depot_id=$depot_id AND epd.flux_id=$flux_id
                                AND epd.date_fin>=prm.date_debut and epd.date_debut<=prm.date_fin
                                )
                ORDER BY 2
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    public function selectComboActiviteHP($depot_id, $flux_id,$anneemois) {
        // On selectionne 
        // - les employés sous contrat dans le mois
        $sql = "SELECT DISTINCT e.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2) libelle 
                FROM emp_contrat_hp ech
--                INNER JOIN emp_pop_depot epd on epd.rcoid=ech.rcoid AND epd.population_id>0
                INNER JOIN employe e on ech.employe_id=e.id
                INNER JOIN pai_ref_mois prm ON prm.anneemois='$anneemois'
                WHERE ech.depot_id=$depot_id AND ech.flux_id=$flux_id
                AND ech.date_fin>=prm.date_debut and ech.date_debut<=prm.date_fin
                ORDER BY 2
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    public function selectComboSuiviHoraire($depot_id, $flux_id,$anneemois) {
        // On selectionne 
        // - les employés sous contrat dans le mois
        $sql = "SELECT e.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2) libelle 
                FROM employe e
                INNER JOIN pai_ref_mois prm ON prm.anneemois='$anneemois'
                INNER JOIN emp_pop_depot epd ON e.id = epd.employe_id AND epd.population_id>0 AND epd.depot_id=$depot_id AND epd.flux_id=$flux_id AND epd.date_fin>=prm.date_debut and epd.date_debut<=prm.date_fin
                    
                UNION
                
                SELECT e.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2) libelle 
                FROM employe e
                INNER JOIN pai_ref_mois prm ON prm.anneemois='$anneemois'
                INNER JOIN emp_pop_depot epd ON e.id = epd.employe_id AND epd.population_id>0 and epd.date_debut<=prm.date_fin
                INNER JOIN emp_affectation eaf ON eaf.contrat_id = epd.contrat_id AND eaf.depot_dst_id=$depot_id AND eaf.flux_id=$flux_id AND eaf.date_fin>=prm.date_debut and eaf.date_debut<=prm.date_fin
                
                UNION
                
                SELECT DISTINCT e.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2) libelle 
                FROM employe e
                INNER JOIN pai_ref_mois prm ON prm.anneemois='$anneemois'
                INNER JOIN emp_contrat_hp ech ON ech.employe_id=e.id AND ech.depot_id=$depot_id AND ech.flux_id=$flux_id AND ech.date_fin>=prm.date_debut and ech.date_debut<=prm.date_fin
                ORDER BY 2
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function selectComboEtalon($depot_id, $flux_id) {
        $sql = "SELECT e.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2) libelle , CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2) ordre
                FROM employe e
                WHERE e.id IN (SELECT DISTINCT me.employe_id
                                FROM etalon me
                                WHERE me.depot_id=$depot_id AND me.flux_id=$flux_id)
                                    
                UNION
                
                SELECT 0, 'Nouvelle tournée', ''
                ORDER BY 3
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    public function selectComboEtalonEmploye($depot_id, $flux_id, $date_application) {
        $sql = "SELECT e.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2) libelle , CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2) ordre
                FROM employe e
                WHERE e.id IN (SELECT epd2.employe_id
                                FROM emp_pop_depot epd2
                                WHERE epd2.depot_id=$depot_id AND epd2.flux_id=$flux_id
                                AND ".$this->sqlField->sqlDate($date_application)." between epd2.date_debut and epd2.date_fin)
                                    
                UNION
                
                SELECT null, 'Nouvelle tournée', 'ZZZ'
                ORDER BY 3
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    public function selectComboEtalonTournee($depot_id, $flux_id, $date_debut, $date_fin) {
        $sql = "SELECT e.id, CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2) libelle
                FROM employe e
                WHERE e.id IN (SELECT DISTINCT epd2.employe_id
                                FROM emp_pop_depot epd2
                                WHERE epd2.depot_id=$depot_id AND epd2.flux_id=$flux_id
                                AND epd2.date_debut<=".$this->sqlField->sqlDate($date_fin)." and epd2.date_fin>=".$this->sqlField->sqlDate($date_debut)."
                                AND epd2.typecontrat_id=2)
                ORDER BY 2
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    public function selectComboAnnexe($depot_id, $flux_id,$anneemois,$datetrt='') {
        // On met un identifiant négatif pour les annexes provisoires
        $sql = "SELECT DISTINCT
                if(pit.typetrt='GENERE_PLEIADES_CLOTURE',ped.id,-ped.id) as id,
                CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2,'(',ped.rc, date_format(ped.d,'%d/%m/%Y'),' - ',date_format(least(ped.f,coalesce(pit.date_distrib,'2999-01-01')),'%d/%m/%Y'),')') libelle,
                ped.rc,
                ped.d as date_debut,
                least(ped.f,coalesce(pit.date_distrib,'2999-01-01')) as date_fin
                from pai_int_traitement pit
                inner join pai_ev_emp_depot_hst ped on pit.id=ped.idtrt and (SUBSTR(ped.d,5,6)<>'-05-01' or SUBSTR(ped.f,5,6)<>'-05-01')
                inner join pai_ev_annexe_hst a on pit.id=a.idtrt and ped.id=a.employe_depot_hst_id and a.date_distrib between ped.d and ped.f
                inner join employe e on ped.employe_id=e.id
                where pit.id = (select max(pit2.id) 
                                from pai_int_traitement pit2 
                                where pit2.anneemois='$anneemois' and pit2.flux_id=$flux_id
                                and pit2.typetrt in ('MROAD2PNG_EV_QUOTIDIEN','MROAD2PNG_EV_MENSUEL','GENERE_PLEIADES_MENSUEL','CALCUL_PLEIADES_MENSUEL','GENERE_PLEIADES_CLOTURE') 
                                and pit2.statut='T'
                                and ('$datetrt'='' or date_format(pit2.date_distrib,'%Y-%m-%d')='$datetrt')
                                )
                and (ped.depot_id=$depot_id
                or $depot_id=18 and ped.depot_id between -121 and -101) -- Paris géré par St-Ouen
                    
                union
                
                SELECT DISTINCT
                CONCAT(' ',ped.id) as id,
                CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2,'(',ped.rc, date_format(ped.d,'%d/%m/%Y'),' - ',date_format(ped.f,'%d/%m/%Y'),')') libelle,
                ped.rc,
                ped.d as date_debut,
                ped.f as date_fin
                from pai_int_traitement pit
                inner join pai_ev_emp_depot_hst ped on pit.id=ped.idtrt and (SUBSTR(ped.d,5,6)<>'-05-01' or SUBSTR(ped.f,5,6)<>'-05-01')
                inner join pai_ev_annexe_hst a on pit.id=a.idtrt and ped.id=a.employe_depot_hst_id and a.date_distrib between ped.d and ped.f
                inner join employe e on ped.employe_id=e.id
                inner join pai_stc ps on ps.employe_id=ped.employe_id and ps.date_extrait=pit.date_debut
                where pit.anneemois='$anneemois' and pit.flux_id=$flux_id
                and pit.typetrt in ('GENERE_PLEIADES_STC') 
                and pit.statut='T'
                and (ped.depot_id=$depot_id
                or $depot_id=18 and ped.depot_id between -121 and -101) -- Paris géré par St-Ouen

                ORDER BY 2
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }
   
    public function getById($employe_id) {
        $sql = " SELECT  
                    emp.nom, 
                    emp.prenom1,
                    emp.prenom2,
                    emp.matricule
                 FROM employe emp 
                 WHERE emp.id  = ".$employe_id;
   
        $result = $this->_em->getConnection()->fetchAssoc($sql);
        return $result;
    }    
    
    function alimentation() {
        $sql = "SELECT * FROM salarie LIMIT 10";
        $results = $this->_em->getConnection()->fetchAll($sql);

        print_r($results);
    }

    public function selectAbsence($employe_id, $start, $end) {
        $sql = "SELECT
                    concat(e.,prc.datecal,pos.abs_dh),
                    concat('Absence : ',coalesce(poa.abs_lib,pos.abs_cod)) as title,
                    concat(prc.datecal,'T',if(pos.abs_typ='J','00:00',concat(substr(pos.abs_dh,1,2),':',substr(pos.abs_dh,3,2)))) as start,
                    concat(prc.datecal,'T',if(pos.abs_typ='J','24:00',concat(substr(pos.abs_fh,1,2),':',substr(pos.abs_fh,3,2)))) as end,
                    if(pos.abs_typ='J','true','false') as allDay,
                    '' as url,
                    '#FF8000' as color,
                    '#FAD9DD' as backgroundColor
                FROM employe e
                INNEr JOIN pai_oct_saiabs pos on e.matricule=pos.pers_mat
                INNER JOIN pai_ref_calendrier prc on prc.datecal between pos.abs_dat and pos.abs_fin
                left outer join pai_oct_absence poa on poa.abs_cod=if(RIGHT(pos.abs_cod,2)  REGEXP '^[0-9]+$',substr(pos.abs_cod,1,2),pos.abs_cod)
                WHERE e.id = '$employe_id'
                AND prc.datecal between '$start' and '$end'
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }}