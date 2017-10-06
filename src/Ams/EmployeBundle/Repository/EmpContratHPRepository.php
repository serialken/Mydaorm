<?php
namespace Ams\EmployeBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class EmpContratHPRepository extends GlobalRepository {

    function select($depot_id, $flux_id, $anneemois_id) {
        $sql = "SELECT
                    ech.id,
                    e.matricule,
                    ech.employe_id,
                    epd.rc,
                    ech.date_debut,
                    ech.date_fin,
                    ech.dimanche,
                    ech.lundi,
                    ech.mardi,
                    ech.mercredi,
                    ech.jeudi,
                    ech.vendredi,
                    ech.samedi,
                    time_format(ech.heure_debut_dimanche,'%H:%i') as heure_debut_dimanche,
                    time_format(ech.heure_debut_lundi,'%H:%i') as heure_debut_lundi,
                    time_format(ech.heure_debut_mardi,'%H:%i') as heure_debut_mardi,
                    time_format(ech.heure_debut_mercredi,'%H:%i') as heure_debut_mercredi,
                    time_format(ech.heure_debut_jeudi,'%H:%i') as heure_debut_jeudi,
                    time_format(ech.heure_debut_vendredi,'%H:%i') as heure_debut_vendredi,
                    time_format(ech.heure_debut_samedi,'%H:%i') as heure_debut_samedi,
                    time_format(sec_to_time(ech.nbheures_dimanche*3600),'%H:%i') as nbheures_dimanche,
                    time_format(sec_to_time(ech.nbheures_lundi*3600),'%H:%i') as nbheures_lundi,
                    time_format(sec_to_time(ech.nbheures_mardi*3600),'%H:%i') as nbheures_mardi,
                    time_format(sec_to_time(ech.nbheures_mercredi*3600),'%H:%i') as nbheures_mercredi,
                    time_format(sec_to_time(ech.nbheures_jeudi*3600),'%H:%i') as nbheures_jeudi,
                    time_format(sec_to_time(ech.nbheures_vendredi*3600),'%H:%i') as nbheures_vendredi,
                    time_format(sec_to_time(ech.nbheures_samedi*3600),'%H:%i') as nbheures_samedi,
--                    time_format(ech.heure_debut,'%H:%i') as heure_debut,
--                    time_format(sec_to_time(ech.nbheures_jour*3600),'%H:%i') as nbheurejr,
                    time_format(sec_to_time(ech.nbheures_mensuel*3600),'%H:%i') as nbheures_mensuel,
                    ech.xta_rcactivte,
                    ech.xta_rcmetier,
                    ech.xta_rcactivhpre,
                    ech.travhorspresse,
                    ech.commentaire,
                    now() between ech.date_debut and date_add(ech.date_fin,interval +1 day) as actif,
                    ech.date_fin between date_add(now(),interval -30 day) and '2999-01-01' as isModif
                FROM emp_contrat_hp ech
                INNER JOIN emp_pop_depot epd ON epd.employe_id=ech.employe_id and ech.date_debut between epd.date_debut and epd.date_fin
                INNER JOIN employe e on epd.employe_id=e.id
                INNER JOIN pai_ref_mois rm ON ech.date_debut<=rm.date_fin and ech.date_fin>=rm.date_debut
                WHERE ech.depot_id=" . $depot_id . " 
                AND ech.flux_id=" . $flux_id . "
                AND rm.anneemois='" . $anneemois_id . "'
                ORDER BY e.nom,e.prenom1,e.prenom2,ech.date_debut
            ";
        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function update(&$msg, &$msgException, $param, $user, &$id) {
        try {
            $sql = "UPDATE emp_contrat_hp ech
                SET ech.commentaire= " . $this->sqlField->sqlTrimQuote($param['commentaire']) . ",
                ech.utilisateur_id = " . $user . ",
                ech.date_modif = NOW()
                WHERE ech.id =" . $param['gr_id'];
            $this->_em->getConnection()->prepare($sql)->execute();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "L'activite doit être unique.", "UNIQUE", "");
        }
        return true;
    }
    
    function selectComboContrat() {
        $sql = "SELECT
                    x.oid as id,
                    x.libelle
                FROM pai_png_xta_rcactivite x
              ORDER BY x.libelle
            ";
        return $this->_em->getConnection()->fetchAll($sql);
    }

    function selectComboMetier() {
        $sql = "SELECT
                    x.oid as id,
                    x.libelle
                FROM pai_png_xta_rcmetier x
              ORDER BY x.libelle
            ";
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    function selectComboActivite() {
        $sql = "SELECT
                    x.oid as id,
                    x.libelle
                FROM pai_png_xta_rcactivhpre x
              ORDER BY x.libelle
            ";
        return $this->_em->getConnection()->fetchAll($sql);
    }
}
