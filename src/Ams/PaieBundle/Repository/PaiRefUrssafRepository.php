<?php
namespace Ams\PaieBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class PaiRefUrssafRepository extends GlobalRepository {

    public function select() {
        $sql = "select 
                    coalesce(pcc.id,concat(p.id,'-',pc.id)) as id,
                    p.id as produit_id,
                    p.type_id,
                    pc.id as prd_caract_id,
                    pcc.valeur_string as type_urssaf,
                    p.date_modif
                from produit p
                inner join produit_type pt ON p.type_id=pt.id
                left outer join prd_caract pc on p.type_id=pc.produit_type_id and pc.code='URSSAF'
                left outer join prd_caract_constante pcc on pcc.produit_id=p.id and pcc.prd_caract_id=pc.id
                where not pt.hors_presse;
              ";

        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function _insert(&$msg, &$msgException, $param, $user, &$id) {
        try {
            $sql = "INSERT INTO prd_caract_constante(produit_id,prd_caract_id,valeur_string)
                    VALUES (" . $this->sqlField->sqlId($param['produit_id']) . "," . $this->sqlField->sqlId($param['prd_caract_id']) . "," . $this->sqlField->sqlQuote($param['type_urssaf']) . ")
                ";
            $this->_em->getConnection()->prepare($sql)->execute();
            $id = $this->_em->getConnection()->lastInsertId();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "Erreur inconnue.", "", "");
        }
        return true;
    }

    public function _update(&$msg, &$msgException, $param, $user, &$id) {
        try {
            $sql = "UPDATE prd_caract_constante pcc
                SET pcc.valeur_string=" . $this->sqlField->sqlQuote($param['type_urssaf']) . "
                WHERE pcc.id=" . $this->sqlField->sqlId($param['gr_id']) . "
                ";
            $this->_em->getConnection()->prepare($sql)->execute();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "Erreur inconnue.", "", "");
        }
        return true;
    }

    public function update(&$msg, &$msgException, $param, $user, &$id) {
            if (strpos($param['gr_id'],"-")!==FALSE){
                $id=0;
                return $this->_insert($msg, $msgException, $param, $user, $id);
            } elseif ($param['prd_caract_id']) {
                return $this->_update($msg, $msgException, $param, $user, $id);
            } else {
                $msg= "Caracteristique Urssaf non déclaré pour ce produit, merci de prévenir l'administrateur.";
            }
    }
}
