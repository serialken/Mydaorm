<?php
namespace Ams\EmployeBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class ContratRepository extends GlobalRepository {

    function select($depot_id, $flux_id, $anneemois_id) {
        $sql = "SELECT
                epd.id,
                e.matricule,
                epd.employe_id,
                epd.date_debut,
                epd.date_fin,
                ps.date_extrait as date_stc,
                epd.depot_id,
                epd.flux_id,
                epd.rc,
                epd.emploi_id,
                epd.societe_id,
                epd.typetournee_id,
                epd.typecontrat_id,
                date_format(epd.heure_debut,'%H:%i') as heure_debut,
                epd.nbheures_garanties,
                now() <= date_add(epd.date_fin,interval +1 day) as actif
                FROM emp_pop_depot epd
                INNER JOIN employe e on epd.employe_id=e.id
                LEFT OUTER JOIN pai_stc ps on epd.rcoid=ps.rcoid
                WHERE epd.employe_id in (SELECT epd2.employe_id
                                         FROM emp_pop_depot epd2
                                         INNER JOIN pai_ref_mois rm ON epd2.date_debut<=rm.date_fin and epd2.date_fin>=rm.date_debut
                                         WHERE epd2.depot_id=" . $depot_id . " 
                                         AND epd2.flux_id=" . $flux_id . "
                                         AND rm.anneemois='" . $anneemois_id . "')
                ORDER BY e.nom,e.prenom1,e.prenom2,epd.date_debut
            ";
        return $this->_em->getConnection()->fetchAll($sql);
    }

}
