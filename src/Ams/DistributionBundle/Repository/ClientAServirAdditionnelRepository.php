<?php

namespace Ams\DistributionBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ClientAServirAdditionnelRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ClientAServirAdditionnelRepository extends EntityRepository
{
    
    public function insertProduitAdditionnel($dateDistrib,$produitRef,$produitAdd) {
        $q =    "
                CREATE TEMPORARY TABLE temp_casl
                SELECT 
                    casl.*
                from
                    client_a_servir_logist casl
                        JOIN
                    abonne_soc asoc ON asoc.id = casl.abonne_soc_id
                        JOIN
                    produit_additionnel pa ON pa.produit_reference = casl.produit_id AND casl.produit_id = $produitRef
                        AND pa.date_distrib = '$dateDistrib'
                    JOIN client_a_servir_additionnel casa ON casa.numabo_ext = ABS(asoc.numabo_ext) 
                WHERE
                    casl.date_distrib = '$dateDistrib';
                ";
        
        $q.= "UPDATE temp_casl SET produit_id = $produitAdd,client_a_servir_src_id = null,id = null;";
        $q.= "INSERT INTO client_a_servir_logist SELECT * FROM temp_casl;";
        $q.= "DROP TABLE temp_casl;";
        $this->_em->getConnection()->prepare($q)->execute();
    }
}
