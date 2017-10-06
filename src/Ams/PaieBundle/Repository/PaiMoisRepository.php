<?php
namespace Ams\PaieBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class PaiMoisRepository extends GlobalRepository {
    
    public function bloquer(&$msg, &$msgException, $user, $flux_id) {
        try {
            $sql = "UPDATE pai_mois SET 
                date_blocage = NOW()";
            $sql .= " WHERE flux_id=".$flux_id;
            $this->_em->getConnection()->prepare($sql)->execute();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "Erreur inconnue.", "", "");
        }
        return true;
    }
    
    public function debloquer(&$msg, &$msgException, $user, $flux_id) {
        try {
            $sql = "UPDATE pai_mois SET 
                date_blocage = null";
            $sql .= " WHERE flux_id=".$flux_id;
            $this->_em->getConnection()->prepare($sql)->execute();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "Erreur inconnue.", "", "");
        }
        return true;
    }
    
    public function bloquerReclamation(&$msg, &$msgException, $user, $flux_id) {
        try {
            $sql = "UPDATE pai_mois pm SET 
                    pm.anneemois_reclamation = (select min(anneemois) from pai_ref_mois prm where prm.anneemois>pm.anneemois)
                    WHERE pm.flux_id=".$flux_id;
            $this->_em->getConnection()->prepare($sql)->execute();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "Erreur inconnue.", "", "");
        }
        return true;
    }
    
    
    function getAnneeMois($flux_id){
        $sql = "SELECT pm.anneemois
                FROM pai_mois pm
                WHERE pm.flux_id=".$flux_id;
        return $this->_em->getConnection()->fetchColumn($sql);
    }
    
    function getLibelle($flux_id){
        $sql = "SELECT pm.libelle
                FROM pai_mois pm
                WHERE pm.flux_id=".$flux_id;
        return $this->_em->getConnection()->fetchColumn($sql);
    }
    
    function getDateDebut($flux_id){
        $sql = "SELECT date_format(pm.date_debut,'%d/%m/%Y') as date_debut
                FROM pai_mois pm
                WHERE pm.flux_id=".$flux_id;
        return $this->_em->getConnection()->fetchColumn($sql);
    }
    
    function getDateFin($flux_id){
        $sql = "SELECT date_format(pm.date_fin,'%d/%m/%Y') as date_debut
                FROM pai_mois pm
                WHERE pm.flux_id=".$flux_id;
        return $this->_em->getConnection()->fetchColumn($sql);
    }
    
    function getBlocage($flux_id){
        $sql = "SELECT pm.date_blocage
                FROM pai_mois pm
                WHERE pm.flux_id=".$flux_id;
        return $this->_em->getConnection()->fetchColumn($sql);
    }
    
    function getBlocageReclamation($flux_id){
        $sql = "SELECT pm.anneemois_reclamation
                FROM pai_mois pm
                WHERE pm.flux_id=".$flux_id."
                AND pm.anneemois<>pm.anneemois_reclamation";
        return $this->_em->getConnection()->fetchColumn($sql);
    }
    
    function isMoisCourant($anneemois_id, $flux_id) {
        $sql = "SELECT pm.anneemois
                FROM pai_mois pm
                WHERE pm.anneemois='".$anneemois_id."' AND pm.flux_id=".$flux_id;
        $anneemois = $this->_em->getConnection()->fetchColumn($sql);
        return ($anneemois);
    }

    function isMoisCourantFutur($anneemois_id, $flux_id) {
        $sql = "SELECT prm.anneemois
                FROM pai_mois pm,pai_ref_mois prm
                WHERE prm.anneemois>=pm.anneemois and prm.anneemois='".$anneemois_id."' AND pm.flux_id=".$flux_id;
        $anneemois = $this->_em->getConnection()->fetchColumn($sql);
        return ($anneemois);
    }

    function isDateInMoisCourant($date_distrib, $flux_id) {
        $sql = "SELECT case
                        when '".$date_distrib."'<pm.date_debut then -1
                        when '".$date_distrib."' between pm.date_debut and pm.date_fin then 0
                        when '".$date_distrib."'>=pm.date_fin then 1
                       end
                FROM pai_mois pm
                WHERE pm.flux_id=".$flux_id;
        return $this->_em->getConnection()->fetchColumn($sql);
    }

    function isAnnemoisInMoisCourant($anneemois, $flux_id) {
        $sql = "SELECT case
                        when '".$anneemois."'<pm.anneemois then -1
                        when '".$anneemois."'=pm.anneemois and pm.date_blocage is not null then -1
                        when '".$anneemois."'=pm.anneemois and pm.date_blocage is null then 0
                        when '".$anneemois."'>pm.anneemois then 1
                       end
                FROM pai_mois pm
                WHERE pm.flux_id=".$flux_id;
        return $this->_em->getConnection()->fetchColumn($sql);
    }

    public function getAnneemoisByFlux($flux_id) {
        $sql = "SELECT  anneemois,date_format(pm.date_debut,'%Y-%m-%d') date_debut, date_format(pm.date_fin,'%Y-%m-%d') date_fin
                FROM pai_mois pm
                WHERE pm.flux_id=".$flux_id;
                ;
        return $this->_em->getConnection()->fetchAssoc($sql); 
    }
    
}
