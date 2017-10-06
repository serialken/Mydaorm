<?php

namespace Ams\AdresseBundle\Repository;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityRepository;

class AdresseLivreeRepository extends EntityRepository 
{
    /**
     * Sauvegarde des adresses livrees
     * Si $bForce=1; // -> On initialise (supprime, puis remplace) les adresses livrees deja stockees auparavant
     * 
     * @param \DateTime $dateATraiter
     * @param Boolean $bForce
     * @throws DBALException
     */
    public function sauvegarde(\DateTime $dateATraiter, $bForce=0) {
        try {
            
            $bDejaSauvegarde    = false;
            $sSlctDejaSauvegarde    = " SELECT COUNT(*) nb FROM adresse_livree WHERE date_distrib = '".$dateATraiter->format("Y-m-d")."' ";
            $resDejaSauvegarde    = $this->_em->getConnection()->fetchAll($sSlctDejaSauvegarde);
            foreach($resDejaSauvegarde as $aArr) 
            {
                if($aArr['nb']>0)
                {
                    $bDejaSauvegarde    = true;
                }
            }
            
            if($bDejaSauvegarde==true && $bForce==0)
            {
                //echo "\r\nOn ne fait rien\r\n";
            }
            else
            {
                if($bDejaSauvegarde==true)
                {
                    $sDelete    = " DELETE FROM adresse_livree WHERE date_distrib = '".$dateATraiter->format("Y-m-d")."' ";
                    //echo "\r\n$sDelete\r\n";
                    $this->_em->getConnection()->executeQuery($sDelete);
                    $this->_em->clear();
                }
                
                $sInsert    = " INSERT INTO adresse_livree (date_distrib, flux_id, adresse, commune_id, tournee_jour_id)
                                SELECT date_distrib, flux_id, adresse, commune_id, tournee_jour_id
                                FROM
                                    (
                                    SELECT
                                        csl.date_distrib, csl.flux_id, ar.adresse, csl.commune_id, csl.tournee_jour_id
                                    FROM
                                        client_a_servir_logist csl
                                        INNER JOIN adresse a ON csl.adresse_id = a.id
                                        INNER JOIN adresse_rnvp ar ON csl.rnvp_id = ar.id 
                                    WHERE
                                        date_distrib = '".$dateATraiter->format("Y-m-d")."'
                                        AND csl.tournee_jour_id IS NOT NULL
                                        AND csl.flux_id IS NOT NULL
                                        AND csl.commune_id IS NOT NULL
                                        AND ar.adresse <> ''
                                        AND a.adresse_rnvp_etat_id IN (1,2) AND ar.geo_etat <> 0 /*Adresses non rejetees*/
                                    ORDER BY csl.date_distrib, csl.flux_id, csl.commune_id, ar.adresse, csl.tournee_jour_id
                                    ) t
                                GROUP BY date_distrib, flux_id, adresse, commune_id
                                ";
                //echo "\r\n$sInsert\r\n";
                $this->_em->getConnection()->executeQuery($sInsert);
                $this->_em->clear();
            }
        }
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    } 

}
