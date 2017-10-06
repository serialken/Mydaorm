<?php 
namespace Ams\PaieBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class PaiIncidentRepository extends GlobalRepository
{
    function select($depot_id,$flux_id, $anneemois_id, $sqlCondition=''){  
        // ATTENTION, rajouter un bloquage avec isModif !!!
        $sql = "SELECT
                    pi.id,
                    pi.date_distrib,
                    concat_ws(' ',e.nom,e.prenom1,e.prenom2) as employe_id,
                    pi.incident_id,
                    pi.commentaire,
                    case 
                        when epd.societe_id=1 and pai_typejour_societe(pi.date_distrib,epd.societe_id)=1 or epd.societe_id=2 then 'Suppression prime qualité'
                        when epd.societe_id=1 and pai_typejour_societe(pi.date_distrib,epd.societe_id) in (2,3) then 'Suppression majoration Dimanche/Ferié'
                        when epd.societe_id=2 then 'Suppression prime qualité'
                        when epd.population_id=-1 then 'On ne sait pas encore !!!'
                        else '????'
                    end as incidence,
                    pi.date_extrait is null isModif
                FROM pai_incident pi
                INNER JOIN pai_ref_mois pm ON pi.date_distrib between pm.date_debut and pm.date_fin
                INNER JOIN employe e on pi.employe_id=e.id
                INNER JOIN emp_pop_depot epd on pi.employe_id=epd.employe_id and pi.date_distrib between epd.date_debut and epd.date_fin
                WHERE  epd.depot_id=$depot_id  AND  epd.flux_id=$flux_id
                AND pm.anneemois='$anneemois_id'".
                 ($sqlCondition!='' ? $sqlCondition : "")."
                 ORDER BY pi.date_distrib desc,e.nom
                ";
        return $this->_em->getConnection()->fetchAll ($sql);
    }
    
    public function insert(&$msg, &$msgException, $param, $user, &$id) {
        try {
            // ATTENTION : empécher la saisie sur un mois passé
            $sql = "INSERT INTO pai_incident SET
                    date_distrib = '" . $param['date_distrib'] . "',
                    employe_id = " . $param['employe_id'] . ",
                    incident_id = " . $param['incident_id'] . ",
                    commentaire = " . $this->sqlField->sqlTrimQuote($param['commentaire']) . ",
                    utilisateur_id = " . $user . ",
                    date_creation = NOW()";
            $this->_em->getConnection()->prepare($sql)->execute();
            $id = $this->_em->getConnection()->lastInsertId();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "L'incident doit être unique.","UNIQUE","");
        }
        return true;
    }
    
    public function update(&$msg, &$msgException, $param, $user, &$id) {
          try {
            $sql = "UPDATE pai_incident SET
                     incident_id = " . $param['incident_id'] . ",
                    commentaire = " . $this->sqlField->sqlTrimQuote($param['commentaire']) . ",
                    utilisateur_id = " . $user . ",
                    date_modif = NOW()";
            $sql .= " WHERE id = " . $param['gr_id']." AND date_extrait is null";
            $this->_em->getConnection()->prepare($sql)->execute();
            $id = $this->_em->getConnection()->lastInsertId();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "","","");
        }
        return true;
    }
    
    public function delete(&$msg, &$msgException, $param) {
        try {
            $sql = "DELETE FROM pai_incident
                    WHERE id = " . $param['gr_id']." AND date_extrait is null";
            $this->_em->getConnection()->prepare($sql)->execute();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "","","");
        }
        return true;
    }

    function selectComboTourneeDate($depot_id, $flux_id, $date_distrib) {
        $sql = "SELECT
                    pt.id,
                    CONCAT_WS(' ', pt.code,e.nom, e.prenom1, e.prenom2) as libelle
                    FROM pai_tournee pt
                    LEFT OUTER JOIN employe e ON pt.employe_id=e.id
                    WHERE pt.depot_id=" . $depot_id . " AND pt.flux_id=" . $flux_id . "
                    AND pt.date_distrib='" . $date_distrib . "'
                    AND pt.date_extrait is null
                    ORDER BY pt.code"
        ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    function selectComboTourneeMois($depot_id, $flux_id, $anneemois) {
        $sql = "SELECT
                    pt.id,
                    pt.code as libelle
                    FROM pai_incident pi
                    INNER JOIN pai_tournee pt on pt.id=pi.tournee_id
                    INNER JOIN pai_ref_mois pm on pm.anneemois='" . $anneemois . "'
                    LEFT OUTER JOIN employe e ON pt.employe_id=e.id
                    WHERE pt.depot_id=" . $depot_id . " AND pt.flux_id=" . $flux_id . "
                    AND pt.date_distrib between pm.date_debut and pm.date_fin
                    ORDER BY pt.code"
        ;
        return $this->_em->getConnection()->fetchAll($sql);
    }

    
    function selectComboEmployeMois($depot_id, $flux_id, $anneemois) {
        $sql = "SELECT
                    e.id,
                    CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2) as libelle
                    FROM pai_tournee pt
                    INNER JOIN pai_ref_mois pm on pm.anneemois='" . $anneemois . "'
                    LEFT OUTER JOIN employe e ON pt.employe_id=e.id
                    WHERE pt.depot_id=" . $depot_id . " AND pt.flux_id=" . $flux_id . "
                    AND pt.date_distrib between pm.date_debut and pm.date_fin
                    ORDER BY pt.code"
        ;
        return $this->_em->getConnection()->fetchAll($sql);
    }

}