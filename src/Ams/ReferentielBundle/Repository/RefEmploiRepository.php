<?php
namespace Ams\ReferentielBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class RefEmploiRepository extends GlobalRepository
{
   function select() {
        $sql = "SELECT
                id,
                code,
                libelle,
                codeNG,
                paie,
                prime,
                affichage_modele_tournee,
                affichage_modele_activite
                FROM ref_emploi
                ORDER BY libelle"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function update(&$msg, &$msgException, $param, $user, &$id) {
        try {
            $sql = "UPDATE ref_emploi SET 
                code = " . $this->sqlField->sqlQuote($param['code']) . ",
                paie = " . $param['paie'] . ",
                prime = " . $param['prime'] . ",
                affichage_modele_tournee = " . $param['affichage_modele_tournee'] . ",
                affichage_modele_activite = " . $param['affichage_modele_activite'];
            $sql .= " WHERE id =" . $param['gr_id'];
            $this->_em->getConnection()->prepare($sql)->execute();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "L'emploi doit être unique.","UNIQUE","");
        }
        return true;
    }

    function selectCombo() {
        $sql = "SELECT
                id,
                libelle
                FROM ref_emploi
                ORDER BY libelle"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
   function selectComboCode() {
        $sql = "SELECT 'POR' as id,'Porteur' as libelle
                UNION
                SELECT 'POL','Polyvalent'"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
}
