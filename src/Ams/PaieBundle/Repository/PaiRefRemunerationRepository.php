<?php
namespace Ams\PaieBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class PaiRefRemunerationRepository extends GlobalRepository {

    function select() {
        $sql = "SELECT id
                        ,societe_id
                        ,population_id
                        ,date_debut
                        ,date_fin
                        ,valeur
                        ,valeurHP
                        ,valeurHP2
                    FROM pai_ref_remuneration
                    ORDER BY societe_id,population_id,date_debut
                    ";
        return $this->_em->getConnection()->fetchAll($sql);
    }

}
