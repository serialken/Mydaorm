<?php
namespace Ams\PaieBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class PaiRefPostePaieSupplementRepository extends GlobalRepository {

    function select() {
        $sql = "SELECT 
                        r.id
                        ,p.libelle as produit_id
                        ,r.poste_bf
                        ,r.poste_bdc
                    FROM pai_ref_postepaie_supplement r
                    INNEr JOIN produit p ON r.produit_id=p.id
                    ORDER BY p.libelle
                    ";
        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function update(&$msg, &$msgException, $param, $user, &$id) {
        try {
            // ATTENTION : le produit peut-être modifié, dans ce cas le prd_caract_id change si le type produit change
            $sql = "UPDATE pai_ref_postepaie_supplement SET 
                        poste_bf=" . $this->sqlField->sqlTrimQuote($param['poste_bf']) . "
                        ,poste_bdc=" . $this->sqlField->sqlTrimQuote($param['poste_bdc']) . "
                        ,utilisateur_id = " . $user . ",
                        ,date_modif = NOW()";
            $sql .= " WHERE id =" . $param['gr_id'];
            $this->_em->getConnection()->prepare($sql)->execute();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "Le poste de paie doit être unique pour type de produit/type Urssaf.", "UNIQUE", "");
        }
        return true;
    }

}
