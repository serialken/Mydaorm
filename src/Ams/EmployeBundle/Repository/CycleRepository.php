<?php
namespace Ams\EmployeBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class CycleRepository extends GlobalRepository {

    function select($depot_id, $flux_id, $anneemois_id){  
            $sql = "SELECT
                ecy.id,
                e.matricule,
                ecy.employe_id,
                ecy.date_debut,
                ecy.date_fin,
                ecy.dimanche,
                ecy.lundi,
                ecy.mardi,
                ecy.mercredi,
                ecy.jeudi,
                ecy.vendredi,
                ecy.samedi,
                now() between ecy.date_debut and date_add(ecy.date_fin,interval +1 day) as actif
                FROM emp_cycle ecy
                INNER JOIN employe e on ecy.employe_id=e.id
                WHERE ecy.employe_id in (SELECT epd2.employe_id
                                         FROM emp_pop_depot epd2
                                         INNER JOIN pai_ref_mois rm ON epd2.date_debut<=rm.date_fin and epd2.date_fin>=rm.date_debut
                                         WHERE epd2.depot_id=" . $depot_id . " 
                                         AND epd2.flux_id=" . $flux_id . "
                                         AND rm.anneemois='" . $anneemois_id . "')
                ORDER BY e.nom,e.prenom1,e.prenom2,ecy.date_debut
            ";
            return $this->_em->getConnection()->fetchAll ($sql);
    }
    
    function getText($employe_id, $date_distrib){  
            $sql = "SELECT cycle_to_string(ecy.lundi,ecy.mardi,ecy.mercredi,ecy.jeudi,ecy.vendredi,ecy.samedi,ecy.dimanche) as cycle
                FROM emp_cycle ecy
                WHERE ecy.employe_id=" . $employe_id . " 
                AND  ".$this->sqlField->sqlDate($date_distrib)." between ecy.date_debut and ecy.date_fin
            ";
            $_cycle = $this->_em->getConnection()->fetchAssoc($sql);
            if (isset($_cycle)) {
                return $_cycle['cycle'];
            } else {
                return "*******";
            }
    }
}