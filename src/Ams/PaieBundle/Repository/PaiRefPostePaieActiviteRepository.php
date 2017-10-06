<?php
namespace Ams\PaieBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class PaiRefPostePaieActiviteRepository extends GlobalRepository {

    function select() {
            $sql = "SELECT 
                        r.id
                        ,a.libelle as activite_id
                        ,t.libelle as typejour_id
                        ,r.poste_hj
                        ,r.poste_hn
                        ,coalesce(a.date_modif,a.date_creation) as date_modif
                    FROM pai_ref_postepaie_activite r
                    INNER JOIN ref_activite a on r.activite_id=a.id
                    INNER JOIN ref_typejour t on r.typejour_id=t.id
                    ORDER BY a.libelle,r.typejour_id
                    ";
            return $this->_em->getConnection()->fetchAll($sql);
    }
    
    public function update(&$msg, &$msgException, $param, $user, &$id) {
        try {
            // ATTENTION : le produit peut-être modifié, dans ce cas le prd_caract_id change si le type produit change
            $sql = "UPDATE pai_ref_postepaie_activite SET 
                        poste_hj=" . $this->sqlField->sqlTrimQuote($param['poste_hj']) . "
                        ,poste_hn=" . $this->sqlField->sqlTrimQuote($param['poste_hn']) . "
                        ,utilisateur_id = " . $user . "
                        ,date_modif = NOW()";
            $sql .= " WHERE id =" . $param['gr_id'];
            $this->_em->getConnection()->prepare($sql)->execute();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "Le poste de paie doit être unique pour type de jour/activite.","UNIQUE","");
        }
        return true;
    }
}
