<?php

namespace Ams\DistributionBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * FranceRoutageProspectionAdrNormRefTmpRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FranceRoutageProspectionAdrNormRefTmpRepository extends EntityRepository
{
    /**
     * Mise en table temporaire des adresses livrees le jour de la date de reference
     * @param \DateTime $date_ref
     * @param \AmsReferentielBundle\RefFlux $flux
     * @throws \Ams\DistributionBundle\Repository\DBALException
     */
    public function init(\DateTime $date_ref, $flux) {
        try {
            
            $sTruncate  = " TRUNCATE TABLE france_routage_prospection_adr_norm_ref_tmp ";
            $this->_em->getConnection()->executeQuery($sTruncate);
            /*
            $sInsert    = " INSERT INTO france_routage_prospection_adr_norm_ref_tmp (adresse, insee, flux_id, tournee_jour_id, date_ref)
                            SELECT adresse, insee, flux_id, tournee_jour_id, '".$date_ref->format('Y-m-d')."'
                            FROM
                            (
                                SELECT  
                                    ar.adresse, ar.insee, csl.flux_id, csl.tournee_jour_id
                                FROM 
                                    client_a_servir_logist csl
                                    INNER JOIN adresse_rnvp ar ON csl.rnvp_id = ar.id 
                                WHERE
                                    csl.date_distrib = '".$date_ref->format('Y-m-d')."' 
                                    AND csl.tournee_jour_id IS NOT NULL 
                                    ".( is_null($flux) ? "" : " AND csl.flux_id = ".$flux->getId() )."
                                    AND ar.adresse <> ''
                                ORDER BY ar.adresse, ar.insee, csl.flux_id, csl.tournee_jour_id
                            ) t 
                            GROUP BY adresse, insee
                            ";
            */
            $sInsert    = " INSERT INTO france_routage_prospection_adr_norm_ref_tmp (adresse, insee, flux_id, tournee_jour_id, date_ref)                            
                            SELECT  
                                al.adresse, c.insee, al.flux_id, al.tournee_jour_id, '".$date_ref->format('Y-m-d')."'
                            FROM 
                                adresse_livree al
                                INNER JOIN commune c ON al.commune_id = c.id
                            WHERE
                                al.date_distrib = '".$date_ref->format('Y-m-d')."' 
                                ".( is_null($flux) ? "" : " AND al.flux_id = ".$flux->getId() )." 
                            ";
            $this->_em->getConnection()->executeQuery($sInsert);
        }
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    } 
    
    
    
    
}