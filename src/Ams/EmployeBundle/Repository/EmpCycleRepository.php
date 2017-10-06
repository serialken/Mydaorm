<?php
namespace Ams\EmployeBundle\Repository;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityRepository;

class EmpCycleRepository extends EntityRepository {

    public function getCurrent($employe_id) {
        $sql = "SELECT
                ecy.dimanche
                ,ecy.lundi
                ,ecy.mardi
                ,ecy.mercredi
                ,ecy.jeudi
                ,ecy.vendredi
                ,ecy.samedi
                FROM emp_cycle ecy
                WHERE ecy.employe_id='".$employe_id."'
                AND curdate() between ecy.date_debut and ecy.date_fin"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function selectPlanning($depot_id, $flux_id, $employe_id, $start, $end) {
        $sql = "SELECT
                    prc.datecal as start,
                    true as allDay,
                    'background' as rendering,
                    '#F5F6CE' as backgroundColor
                FROM pai_ref_calendrier prc
                INNER JOIN emp_cycle ecy on prc.datecal between ecy.date_debut and ecy.date_fin
                WHERE ecy.employe_id = '" . $employe_id . "'
                AND prc.datecal between '" . $start . "' and '" . $end . "'
                AND (ecy.dimanche and prc.jour_id=1
                OR ecy.lundi and prc.jour_id=2
                OR ecy.mardi and prc.jour_id=3
                OR ecy.mercredi and prc.jour_id=4
                OR ecy.jeudi and prc.jour_id=5
                OR ecy.vendredi and prc.jour_id=6
                OR ecy.samedi and prc.jour_id=7)
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }
}
