<?php

namespace Ams\InvenduBundle\Repository;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityRepository;

class InvenduRepository extends EntityRepository {

    function selectInvendu($depotCode, $lv, $dateParution) {
        $sql = "SELECT
                inv.id,
                inv.qte_livree,
                inv.prix,
                inv.qte_invendue,
                inv.code_societe,
                inv.code_titre,
                lv.libelle,
                lv.numero,
                lv.adresse_1,
                lv.adresse_2,
                lv.adresse_3,
                lv.telephone,
                inv.libelle_abrege,
                inv.date_export_dcs,
                inv.qte_livree,
                inv.qte_invendue
                FROM invendu inv
                INNER JOIN lieu_vente lv ON lv.numero=inv.num_lieu_vente
                WHERE inv.num_lieu_vente=" . $lv . "
                AND inv.date_parution='" . $dateParution . "'
                AND lv.code_depot='" . $depotCode . "'
                ORDER BY cast(inv.code_societe as unsigned ), inv.code_societe, inv.code_titre, inv.code_produit"
                
        ;
        return $this->_em->getConnection()->fetchAll($sql);
    }

    function selectLieuVente($codeDepot) {
        $sql = "SELECT
                lv.libelle,
                lv.numero
                FROM lieu_vente lv
                WHERE lv.code_depot='" . $codeDepot . "' ORDER BY lv.libelle"
        ;
        return $this->_em->getConnection()->fetchAll($sql);
    }

    function updateInvendu($nbInvendu,$idInvendu) {
        $sql = "UPDATE invendu SET
                qte_invendue = ?
                WHERE id = ?";
        return $this->_em->getConnection()->executeUpdate($sql, array($nbInvendu, $idInvendu));
    }
    
    function selectInvenduDouchette($lv, $dateParution) {
        $sql = "SELECT
                inv.id,
                inv.qte_livree,
                inv.prix,
                inv.qte_invendue,
                inv.code_societe,
                inv.code_titre,
                lv.libelle,
                lv.numero,
                lv.adresse_1,
                lv.adresse_2,
                lv.adresse_3,
                lv.telephone,
                inv.libelle_abrege,
                inv.date_export_dcs,
                inv.qte_livree,
                inv.qte_invendue
                FROM invendu inv
                INNER JOIN lieu_vente lv ON lv.numero=inv.num_lieu_vente
                WHERE inv.num_lieu_vente=" . $lv . "
                AND inv.date_parution='" . $dateParution . "'
                ORDER BY cast(inv.code_societe as unsigned ), inv.code_societe, inv.code_titre, inv.code_produit"
                
        ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    function selectLV($lv) {
        $sql = "SELECT
                lv.libelle,
                lv.numero,
                lv.adresse_1,
                lv.adresse_2,
                lv.adresse_3,
                lv.telephone,
                lv.code_depot
                FROM lieu_vente lv
                WHERE lv.numero='" . $lv . "'"
        ;
        return $this->_em->getConnection()->fetchAll($sql);
    }

    function getTotalLivreInvendu($depotCode, $lv, $dateParution) {
        $sql = "SELECT
                sum(inv.qte_livree) as nblivree,
                sum(inv.qte_invendue) as nbInvendu
                FROM invendu inv
                INNER JOIN lieu_vente lv ON lv.numero=inv.num_lieu_vente
                WHERE inv.num_lieu_vente=" . $lv . "
                AND inv.date_parution='" . $dateParution . "'";
        if($depotCode){
            $sql.= " AND lv.code_depot='" . $depotCode . "'";
        }
         $sql.= " ORDER BY cast(inv.code_societe as unsigned ), inv.code_societe, inv.code_titre, inv.code_produit";
        return $this->_em->getConnection()->fetchAll($sql);
    }
}
