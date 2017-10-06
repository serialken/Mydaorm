<?php

namespace Ams\AdresseBundle\Repository;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityRepository;

class AdresseVol123LivreeRepository extends EntityRepository 
{    
    /**
     * Sauvegarde des adresses livrees
     * 
     * @param Integer $iJourMin Jour de distribution MIN a traiter. Si -10, on prend J-10
     * @param Integer $iJourMax Jour de distribution MAX a traiter. Si 1, on prend J+1
     * @throws DBALException
     */
    public function insert($iJourMin=-7, $iJourMax=0) {
        try {
            $sTruncate    = " TRUNCATE TABLE adresse_vol123_livree ";
            $this->_em->getConnection()->executeQuery($sTruncate);
            $this->_em->clear();
            
            $sInsert    = " INSERT INTO adresse_vol123_livree
                                (jour_id, flux_id, mtj_code, ordre, cadrs, adresse, lieudit, cp, ville, insee, liste_soc_code_ext)
                            SELECT
                                CAST(DATE_FORMAT(date_distrib, '%w') AS SIGNED)+1 jour_id, flux_id, mtj_code, point_livraison_ordre, cadrs, adresse, lieudit, cp, ville, insee, GROUP_CONCAT(DISTINCT soc_code_ext ORDER BY soc_code_ext DESC SEPARATOR '|') AS liste_soc_code_ext
                            FROM
                                (
                                SELECT
                                        csl.date_distrib, csl.flux_id, mtj.code AS mtj_code, ar.cadrs, ar.adresse, ar.lieudit, ar.cp, ar.ville, ar.insee, p.soc_code_ext, csl.point_livraison_ordre
                                FROM
                                        client_a_servir_logist csl
                                        INNER JOIN adresse_rnvp ar ON csl.rnvp_id = ar.id 
                                        INNER JOIN modele_tournee_jour mtj ON csl.tournee_jour_id = mtj.id
                                        INNER JOIN produit p ON csl.produit_id = p.id AND p.periodicite_id IN (1) -- quotidien
                                WHERE
                                        csl.date_distrib BETWEEN DATE_ADD(CURDATE(), INTERVAL ".$iJourMin." DAY) AND DATE_ADD(CURDATE(), INTERVAL ".$iJourMax." DAY)
                                ORDER BY 
                                        csl.date_distrib DESC
                                ) t
                            GROUP BY
                                CAST(DATE_FORMAT(date_distrib, '%w') AS SIGNED)+1, flux_id, cadrs, adresse, lieudit, insee
                            ORDER BY
                                jour_id 
                        ";
            $this->_em->getConnection()->executeQuery($sInsert);
            $this->_em->clear();
            
            $sDelete    = " DELETE FROM adresse_vol123_livree WHERE adresse = '' ";
            $this->_em->getConnection()->executeQuery($sDelete);
            $this->_em->clear();
        }
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    } 

}
