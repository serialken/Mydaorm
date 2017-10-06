<?php

namespace Ams\DistributionBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\DBALException;

class ClientAServirHistoRepository extends EntityRepository {

    /**
     * Historisation des tables "client_a_servir_logist" et "client_a_servir_src"
     * 
     * @param \DateTime $dateDistrib
     * @throws \Doctrine\DBAL\DBALException
     */
    public function historise(\DateTime $dateDistrib) {
        try {
            // On ne lance le transfert de donnees pour la date $date que si la table "client_a_servir_logist" est renseigne pour cette date
            $sSlctVerif = " SELECT COUNT(*) AS nb FROM client_a_servir_logist WHERE date_distrib = '".$dateDistrib->format('Y-m-d')."' ";
            $aRes   = $this->_em->getConnection()->fetchAll($sSlctVerif);
            foreach($aRes as $aArr)
            {
                if($aArr['nb'] > 3) // On fait le transfert
                {
                    // Suppression des donnees de la table "client_a_servir_histo" pour la date concernee
                    $sDelete    = " DELETE FROM client_a_servir_histo WHERE date_distrib = '".$dateDistrib->format('Y-m-d')."' ";
                    $this->_em->getConnection()->executeQuery($sDelete);
                    $this->_em->clear();
                    
                    // Historisation
                    $sInsertHisto    = " INSERT INTO client_a_servir_histo
                                        (date_distrib, date_parution, num_parution, numabo_ext, vol1, vol2, vol3, vol4, vol5, cp, ville 
                                        , type_portage, qte, abonne_soc_id, client_type, divers1, info_comp1, info_comp2, divers2
                                        , nouv_appar, date_absence, abonne_unique_id, fic_recap_id, adresse_id, rnvp_id 
                                        , tournee_jour_id, pai_tournee_id, point_livraison_id, point_livraison_ordre, ordre_dans_arret, commune_id, depot_id, flux_id
                                        , societe_id, produit_id, soc_code_ext, prd_code_ext, spr_code_ext, employe_id)
                                    SELECT
                                        csl.date_distrib, csl.date_parution, src.num_parution, src.numabo_ext, src.vol1, src.vol2, src.vol3, src.vol4, src.vol5, src.cp, src.ville 
                                        , src.type_portage, csl.qte, csl.abonne_soc_id, csl.client_type, src.divers1, src.info_comp1, src.info_comp2, src.divers2
                                        , csl.nouv_appar, csl.date_absence, csl.abonne_unique_id, csl.fic_recap_id, csl.adresse_id, csl.rnvp_id 
                                        , csl.tournee_jour_id, csl.pai_tournee_id, csl.point_livraison_id, csl.point_livraison_ordre, csl.ordre_dans_arret, csl.commune_id, csl.depot_id, csl.flux_id
                                        , csl.societe_id, csl.produit_id, p.soc_code_ext, p.prd_code_ext, p.spr_code_ext, csl.employe_id
                                    FROM
                                        client_a_servir_logist csl
                                        LEFT JOIN client_a_servir_src src ON csl.client_a_servir_src_id = src.id
                                        LEFT JOIN produit p ON csl.produit_id = p.id
                                    WHERE
                                        csl.date_distrib = '".$dateDistrib->format('Y-m-d')."' ";
                    $this->_em->getConnection()->executeQuery($sInsertHisto);
                    $this->_em->clear();
                    
                    // Suppression des donnees historisee de la table "client_a_servir_logist" pour la date concernee
                    $sDeleteLogist    = " DELETE FROM client_a_servir_logist WHERE date_distrib = '".$dateDistrib->format('Y-m-d')."' ";
                    $this->_em->getConnection()->executeQuery($sDeleteLogist);
                    $this->_em->clear();
                    
                    // Suppression des donnees historisee de la table "client_a_servir_src" pour la date concernee
                    $sDeleteSrc    = " DELETE FROM client_a_servir_src WHERE date_distrib = '".$dateDistrib->format('Y-m-d')."' ";
                    $this->_em->getConnection()->executeQuery($sDeleteSrc);
                    $this->_em->clear();
                }
            }
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    

    /**
     * Suppression des historiques plus de 365 jours
     * @param type $iNbJourMaxAGarder
     * @throws \Doctrine\DBAL\DBALException
     */
    public function suppr_historique($iNbJourMaxAGarder=365) {
        try {
            // Suppression des donnees de la table "client_a_servir_histo" pour la date concernee
            $sDelete    = " DELETE FROM client_a_servir_histo WHERE date_distrib < DATE_SUB(CURDATE(), INTERVAL ".$iNbJourMaxAGarder." DAY) ";
            $this->_em->getConnection()->executeQuery($sDelete);
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    

    /**
     * Suppression des historiques dont la date est $dateDistrib
     * @param \DateTime $dateDistrib
     * @throws \Doctrine\DBAL\DBALException
     */
    public function suppr_historique_date(\DateTime $dateDistrib) {
        try {
            // Suppression des donnees de la table "client_a_servir_histo" pour la date concernee
            $sDelete    = " DELETE FROM client_a_servir_histo WHERE date_distrib = '".$dateDistrib->format('Y-m-d')."' ";
            $this->_em->getConnection()->executeQuery($sDelete);
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
}
