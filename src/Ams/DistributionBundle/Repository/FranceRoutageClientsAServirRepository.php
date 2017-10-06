<?php 

namespace Ams\DistributionBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\DBALException;

class FranceRoutageClientsAServirRepository extends EntityRepository
{
    
    /**
     * Recuperation des donnees a destination de France Routage
     * @throws \Doctrine\DBAL\DBALException
     */
    public function donneesPourFranceRoutage($aSocDatesATraiter)
    {
        try {
            
            $sSlct  = " SELECT DISTINCT 
                            DATE_FORMAT(fr.date_distrib, '%Y-%m-%d') AS date_distrib, fr.numabo_ext, 
                            d.code AS depot, IFNULL(fr.modele_tournee_jour_code, '') AS modele_tournee_jour_code, 
                            IFNULL(fr.ordre, 0) AS ordre, IFNULL(CONCAT_WS(' - ', fr.divers1, fr.info_comp1, fr.info_comp2, fr.divers2), '') AS infos_portage,
                            fr.soc_code_ext, fr.prd_code_ext, fr.spr_code_ext
                        FROM
                            france_routage_c_a_s fr
                            LEFT JOIN depot d ON fr.depot_id = d.id
                        WHERE
                            fr.date_distrib = '".$aSocDatesATraiter['date_distrib']."' AND fr.soc_code_ext = '".$aSocDatesATraiter['soc_code_ext']."'
                        ORDER BY
                            d.code, fr.numabo_ext, fr.soc_code_ext, fr.prd_code_ext, fr.spr_code_ext
                        ";
            return $this->_em->getConnection()->fetchAll($sSlct);
        } catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Recuperation des donnees a destination de Jade
     * @throws \Doctrine\DBAL\DBALException
     */
    public function donneesPourJade($aSocDatesATraiter)
    {
        try {
            
            $sSlct  = " SELECT
                            IFNULL(fr.num_parution, DATE_FORMAT(fr.date_distrib, '%Y%m%d')) AS num_parution,
                            DATE_FORMAT(fr.date_distrib, '%Y/%m/%d') AS date_distrib, 
                            fr.numabo_ext, 
                            IFNULL(fr.rnvp_vol1, '') AS vol1,
                            IFNULL(fr.rnvp_vol2, '') AS vol2,
                            IFNULL(fr.rnvp_vol3, '') AS vol3,
                            IFNULL(fr.rnvp_vol4, '') AS vol4,
                            IFNULL(fr.rnvp_vol5, '') AS vol5,
                            IFNULL(fr.rnvp_cp, '') AS cp,
                            IFNULL(fr.rnvp_ville, '') AS ville,
                            'R' AS type_portage,
                            fr.soc_code_ext AS code_societe,
                            fr.prd_code_ext AS code_titre,
                            fr.spr_code_ext AS code_edition,
                            fr.qte,
                            IFNULL(fr.divers1, '') AS divers1,
                            IFNULL(fr.info_comp1, '') AS info_comp1,
                            IFNULL(fr.info_comp2, '') AS info_comp2,
                            IFNULL(fr.divers2, '') AS divers2,
                            d.code AS depot 
                        FROM
                            france_routage_c_a_s fr
                            LEFT JOIN depot d ON fr.depot_id = d.id
                        WHERE
                            date_distrib = '".$aSocDatesATraiter['date_distrib']."' AND soc_code_ext = '".$aSocDatesATraiter['soc_code_ext']."'
                        ORDER BY
                            d.code, fr.numabo_ext, fr.soc_code_ext, fr.prd_code_ext, fr.spr_code_ext
                        ";
            return $this->_em->getConnection()->fetchAll($sSlct);
        } catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    
    
}