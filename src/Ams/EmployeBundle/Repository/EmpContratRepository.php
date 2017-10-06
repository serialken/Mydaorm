<?php

namespace Ams\EmployeBundle\Repository;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityRepository;

class EmpContratRepository extends EntityRepository {

    public function getAnnexeEmployes($depot_id, $flux_id, $date) {
        $sql = "SELECT DISTINCT eco.employe_id
                FROM emp_contrat eco
                INNER JOIN employe e on eco.employe_id=e.id
                INNER JOIN emp_pop_depot epd ON eco.employe_id=epd.employe_id and '$date' between epd.date_debut and epd.date_fin
                INNER JOIN ref_population rp ON epd.population_id=rp.id and rp.emploi_id=1 -- que les porteurs
                INNER JOIN modele_tournee_jour mtj ON eco.employe_id=mtj.employe_id and '$date' between mtj.date_debut and mtj.date_fin
                INNER JOIN modele_tournee mt on mtj.tournee_id=mt.id
                INNER JOIN groupe_tournee gt on mt.groupe_id=gt.id and gt.depot_id=$depot_id and gt.flux_id=$flux_id
                WHERE '$date' between eco.date_debut and eco.date_fin AND mt.actif
                ORDER BY e.nom, e.prenom1
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function getAnnexeEntete($employe_id, $date) {
        $sql = "SELECT 
                    concat_ws(' ',e.nom,e.prenom1,e.prenom2) as employe,
                    d.libelle as depot,
                    group_concat(distinct mt.code order by mt.code separator ' ') as tournees,
                    count(distinct mt.code) as nb_tournees,
                    ecy.cycle,
                    group_concat(distinct time_format(gt.heure_debut,'%kh%i') separator ' ') as heure_debut,
                    date_format(mtj.date_debut,'%d/%m/%Y') as date_application,
                    concat(concat_ws('_','Parametres_Tournee',$date,e.nom,e.prenom1,e.prenom2),'.pdf') as fichier
                FROM emp_contrat eco
                INNER JOIN employe e on eco.employe_id=e.id
                INNER JOIN emp_pop_depot epd ON eco.employe_id=epd.employe_id and '$date' between epd.date_debut and epd.date_fin
                INNER JOIN depot d on epd.depot_id=d.id
                LEFT OUTER JOIN modele_tournee_jour mtj ON eco.employe_id=mtj.employe_id and '$date' between mtj.date_debut and mtj.date_fin
                LEFT OUTER JOIN modele_tournee mt on mtj.tournee_id=mt.id
                LEFT OUTER JOIN groupe_tournee gt on mt.groupe_id=gt.id
                LEFT OUTER JOIN emp_cycle ecy on ecy.employe_id=eco.employe_id and '$date' between ecy.date_debut and ecy.date_fin
                WHERE mt.actif
                AND '$date' between eco.date_debut and eco.date_fin
                AND eco.employe_id=$employe_id
                GROUP BY eco.id,e.id,ecy.id
                ORDER BY e.id
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function getAnnexeTournees($employe_id, $date) {
        $sql = "  SELECT 
                    rj.libelle as jour,
                    mt.code,
                    coalesce(time_format(mtj.duree,'%kh%i'),'N/A') as duree,
                    coalesce(mtj.nbcli,'N/A') as nbcli,
                    coalesce(mtj.nbkm_paye,'N/A') as nbkm
                FROM ref_jour rj
                LEFT OUTER JOIN modele_tournee_jour mtj ON rj.id=mtj.jour_id AND '$date' between mtj.date_debut and mtj.date_fin AND mtj.employe_id=$employe_id
                LEFT OUTER JOIN modele_tournee mt on mtj.tournee_id=mt.id
                WHERE mt.actif
                order by (rj.id+5)%7,mt.code
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function getAnnexeReference($employe_id, $date) {
        $sql = "SELECT 
                    rtj.libelle as jour,
                    format(sum(mtj.nbcli)/count(*),1,'fr_FR') as nbcli,
                    time_format(sec_to_time(sum(time_to_sec(mtj.duree))/count(*)),'%Hh%i') as duree,
                    group_concat(distinct format(mtj.valrem_moyen,5,'fr_FR') separator ' ') as valrem
                FROM modele_tournee_jour mtj
                LEFT OUTER JOIN modele_tournee mt on mtj.tournee_id=mt.id
                LEFT OUTER JOIN ref_jour rj on mtj.jour_id=rj.id
                LEFT OUTER JOIN ref_typejour rtj on rj.typejour_id=rtj.id
                WHERE mt.actif
                AND '$date' between mtj.date_debut and mtj.date_fin AND mtj.employe_id=$employe_id
                group by rtj.id
                order by rtj.id
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }

}
