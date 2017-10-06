<?php
namespace Ams\PaieBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class PaiRefSemaineRepository extends GlobalRepository {

    function getDateDebutPrecedente() {
        $sql = "SELECT date_format(prs.date_debut,'%d/%m/%Y')
                FROM pai_ref_semaine prs
                WHERE anneesem=(select max(anneesem) from pai_ref_semaine where date_fin<curdate())
                "
                ;
        return $this->_em->getConnection()->fetchColumn($sql);
    }
    function getDateFinPrecedente() {
        $sql = "SELECT date_format(prs.date_fin,'%d/%m/%Y')
                FROM pai_ref_semaine prs
                WHERE anneesem=(select max(anneesem) from pai_ref_semaine where date_fin<curdate())
                "
                ;
        return $this->_em->getConnection()->fetchColumn($sql);
    }
}
