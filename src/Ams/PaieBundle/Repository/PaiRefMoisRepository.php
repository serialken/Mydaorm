<?php
namespace Ams\PaieBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class PaiRefMoisRepository extends GlobalRepository {

    function selectCombo() {
        $sql = "SELECT
                anneemois,
                libelle
                FROM pai_ref_mois prm
                ORDER BY anneemois"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
    function getMoisCourant() {
        $sql = "SELECT anneemois
                FROM pai_ref_mois prm
                WHERE curdate() between prm.date_debut and prm.date_fin
                "
                ;
        return $this->_em->getConnection()->fetchColumn($sql);
    }
    function getDateDebutCourant() {
        $sql = "SELECT date_format(prm.date_debut,'%Y-%m-%d')
                FROM pai_ref_mois prm
                WHERE curdate() between prm.date_debut and prm.date_fin
                "
                ;
        return $this->_em->getConnection()->fetchColumn($sql);
    }
    function getDateFinCourant() {
        $sql = "SELECT date_format(prm.date_fin,'%Y-%m-%d')
                FROM pai_ref_mois prm
                WHERE curdate() between prm.date_debut and prm.date_fin
                "
                ;
        return $this->_em->getConnection()->fetchColumn($sql);
    }
   public function getAnneemoisByDate($date) {
        $sql = "SELECT  anneemois,date_format(prm.date_debut,'%Y-%m-%d') date_debut, date_format(prm.date_fin,'%Y-%m-%d') date_fin
                FROM pai_ref_mois prm
                WHERE '". $date."' between prm.date_debut and prm.date_fin
                "
                ;
        return $this->_em->getConnection()->fetchAssoc($sql); 
    }
}
