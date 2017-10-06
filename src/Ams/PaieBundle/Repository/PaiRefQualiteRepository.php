<?php
namespace Ams\PaieBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class PaiRefQualiteRepository extends GlobalRepository {

    function select() {
        $sql = "SELECT r.id
                        ,r.societe_id
                        ,r.emploi_code
                        ,r.qualite
                        ,r.libelle
                        ,r.borne_inf
                        ,r.borne_sup
                        ,r.valeur
                        ,r.prime
                        ,r.envoiNG
                    FROM pai_ref_qualite r
                    ORDER BY r.societe_id,r.emploi_code,r.valeur,r.qualite,r.borne_inf
                    ";
        return $this->_em->getConnection()->fetchAll($sql);
    }

    /*
SELECT prq.id
                        ,prq.societe_id
                        ,prq.emploi_code
                        ,prq.qualite
                        ,prq.libelle
                        ,prrq.libelle
                        ,prq.borne_inf
                        ,prq.borne_sup
                        ,prq.valeur
                        ,prq.prime
                        ,prq.envoiNG
                    FROM pai_ref_qualite prq
                    inner join pai_ref_ref_qualite prrq on prq.qualite=prrq.qualite
                    ORDER BY prq.societe_id,prq.emploi_code,prrq.ordre,prq.borne_inf     
     */
}
