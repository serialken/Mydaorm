<?php
namespace Ams\EmployeBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class CddRepository extends GlobalRepository {

    function select($depot_id, $flux_id, $anneemois_id) {
        $sql = "SELECT DISTINCT
                rc.oid as id,
                e.matricule,
                epd.employe_id,
                rc.begin_date as date_debut,
                rc.end_date as date_fin,
                rc.cdddatefinprevu,
                epd.depot_id,
                epd.flux_id,
                epd.rc,
                rc.relatmotifdeb,
                rc.motifremplw,
                rc.xautremotrempl,
                rc.termecddw,
                concat_ws(' ',s.nom_usuel,s.prenom1,s.prenom2) as salremplaw,
                rc.infosalrempw,
                now() between rc.begin_date and date_add(rc.end_date,interval +1 day) as actif
                FROM emp_pop_depot epd
                INNER JOIN employe e on epd.employe_id=e.id
                INNER JOIN pai_png_relationc rc on e.saloid=rc.relatmatricule and epd.rc=rc.relatnum
                LEFT OUTER JOIN pai_png_relationcontrat rc2 on rc.salremplaw=rc2.oid
                LEFT OUTER JOIN pai_png_salarie s on rc2.relatmatricule=s.oid
                WHERE e.id in (SELECT epd2.employe_id
                                        FROM emp_pop_depot epd2
                                        INNER JOIN employe e2 on epd2.employe_id=e2.id
                                        INNER JOIN pai_png_relationc rc2 on e2.saloid=rc2.relatmatricule and epd2.rc=rc2.relatnum
                                        INNER JOIN pai_ref_mois rm ON rc2.begin_date<=rm.date_fin and rc2.end_date>=rm.date_debut
                                        WHERE epd2.depot_id=$depot_id
                                        AND epd2.flux_id=$flux_id
                                        AND rm.anneemois='$anneemois_id')
                ORDER BY e.nom,e.prenom1,e.prenom2,rc.begin_date
            ";
        return $this->_em->getConnection()->fetchAll($sql);
    }
    function selectComboMotifDebut() {
        $sql = "SELECT
                oid as id,
                relatlibcdeb as libelle
                FROM pai_png_ta_relamotifdeb
                ORDER BY oid"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
    function selectComboMotifRempl() {
        $sql = "SELECT
                oid as id,
                libelle
                FROM pai_png_tp_motifremplw
                ORDER BY oid"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
    function selectComboTermeCdd() {
        $sql = "SELECT
                oid as id,
                libelle
                FROM pai_png_tp_termecddw
                ORDER BY oid"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
}
