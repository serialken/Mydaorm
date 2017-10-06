<?php
namespace Ams\ModeleBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class EtalonTourneeRepository extends GlobalRepository {

    function select($etalon_id) {
                 /*   @valrem_moyenne:=(SELECT round(AVG(mtj2.valrem_calculee),5)
                                      FROM modele_tournee_jour mtj2 
                                      WHERE mtj.date_debut between mtj2.date_debut and mtj2.date_fin  
                                      AND mtj.employe_id=mtj2.employe_id and mtj.tournee_id=mtj2.tournee_id
                                      GROUP BY mtj2.employe_id,mtj2.tournee_id
                                  ) as valrem_moyen,*/
            $sql = "SELECT 
                    et.id,
                    e.id as etalon_id,
                    date_format(et.date_distrib,'%d/%m/%Y') as date_distrib,
                    concat(mt.code,rj.code) as code,
                    et.duree,
                    subtime(subtime(et.duree,et.duree_reperage),et.duree_supplement) as duree_calculee,
                    et.nbcli,
                    et.employe_id,
                    et.nbkm,
                    et.nbkm_paye,
                    et.transport_id,
                    et.valrem_reelle,
                    et.valrem_calculee,
                    et.valrem_moyen,
                    et.etalon_calcule,
                    et.etalon_moyen,
                    round(abs(1-et.valrem_calculee/et.valrem_reelle)*100) ecart,
                    et.heure_debut,   
                    et.duree_totale,
                    et.duree_nuit,
                    et.duree_tournee,
                    et.duree_supplement,
                    et.duree_reperage,
                    et.nbtitre,
                    et.nbspl,
                    et.nbprod,
                    et.nbrep,
                    et.depart_depot, et.retour_depot,
                    case
                    when mtj.id is null then 'Création'
                    when coalesce(mtj.employe_id,0)<>coalesce(e.employe_id,0) then concat_ws(' ',e2.nom,e2.prenom1,'remplacé par',e1.nom,e1.prenom1)
                    else ''
                    end as msg,
                    true as isModif
                FROM etalon_tournee et
                inner join etalon e on et.etalon_id=e.id
                inner join modele_tournee mt on et.modele_tournee_id=mt.id
                inner join groupe_tournee gt on mt.groupe_id=gt.id
                inner join ref_jour rj on et.jour_id=rj.id
                left outer join modele_tournee_jour mtj on et.modele_tournee_id=mtj.tournee_id and et.jour_id=mtj.jour_id and e.date_application<=mtj.date_fin
                left outer join employe e1 on e.employe_id=e1.id
                left outer join employe e2 on mtj.employe_id=e2.id
                WHERE et.etalon_id='".$etalon_id."'
                    
                UNION
                
                SELECT 
                    -mtj.id,
                    e.id,
                    null as date_distrib,
                    mtj.code,
                    mtj.duree,
                    null,
                    mtj.nbcli,
                    mtj.employe_id,
                    mtj.nbkm,
                    mtj.nbkm_paye,
                    mtj.transport_id,
                    mtj.valrem,
                    null as valrem_calculee,
                    mtj.valrem_moyen,
                    mtj.etalon,
                    mtj.etalon_moyen,
                    null as ecart,
                    null as heure_debut,   
                    null as duree_totale,
                    null as duree_nuit,
                    null as duree_tournee,
                    null as duree_supplement,
                    null as duree_reperage,
                    null as nbtitre,
                    null as nbspl,
                    null as nbprod,
                    null as nbrep,
                    mtj.depart_depot, mtj.retour_depot,
                    concat_ws(' ',e2.nom,e2.prenom1,'supprimé du modèle') as msg,
                    false as isModif
                FROM etalon e
                INNER JOIN modele_tournee_jour mtj ON e.employe_id=mtj.employe_id
                INNER JOIN ref_typeetalon rte on e.type_id=rte.id and (rte.dimanche and mtj.jour_id=1 or rte.lundi and mtj.jour_id=2 or rte.mardi and mtj.jour_id=3 or rte.mercredi and mtj.jour_id=4 or rte.jeudi and mtj.jour_id=5 or rte.vendredi and mtj.jour_id=6 or rte.samedi and mtj.jour_id=7)
                left outer join employe e2 on mtj.employe_id=e2.id
                WHERE e.id='".$etalon_id."'
                AND e.date_application<=mtj.date_fin
                AND mtj.id not in (SELECT mtj.id
                                    FROM etalon_tournee et
                                    inner join modele_tournee mt on et.modele_tournee_id=mt.id
                                    inner join ref_jour rj on et.jour_id=rj.id
                                    inner join modele_tournee_jour mtj on et.modele_tournee_id=mtj.tournee_id and et.jour_id=mtj.jour_id
                                    where et.etalon_id=e.id
                                    and e.date_application<=mtj.date_fin
                                    )
                ORDER BY 3 asc,4";
            return $this->_em->getConnection()->fetchAll($sql);
    }

    public function nettoyage(&$msg, &$msgException, $param) {
        try {
            $sql = "DELETE FROM etalon_tournee WHERE etalon_id=".$param["etalon_id"];
            $this->_em->getConnection()->prepare($sql)->execute();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "","","");
        }
        return true;
    }

    public function insert(&$msg, &$msgException, $param, $user, &$id) {
        try {
            if ($param["ajout"]=="false") {
                $this->nettoyage($msg, $msgException, $param);
            }
            $sql = "INSERT INTO etalon_tournee
                    (etalon_id
                    , date_distrib
                    , modele_tournee_id,jour_id
                    , employe_id
                    , valrem_reelle
                    , transport_id, nbkm, nbkm_paye
                    , heure_debut
                    , duree, duree_totale, duree_tournee, duree_reperage, duree_supplement, duree_nuit
                    , nbtitre, nbspl, nbprod, nbrep, nbcli
                    , depart_depot, retour_depot
                    , utilisateur_id, date_creation) 
                    SELECT 
                    e.id,
--                    pt.id,
                    pt.date_distrib,
                    mt.id,pt.jour_id,
                    pt.employe_id,
                    pt.valrem,
                    pt.transport_id, pt.nbkm, case when coalesce(rt.km_paye,1) then pt.nbkm_paye else 0 end,
                    gt.heure_debut,
                    if(pt.tournee_org_id,ptf.duree,pt.duree), if(pt.tournee_org_id,ptf.duree,pt.duree), if(pt.tournee_org_id,ptf.duree_tournee,pt.duree_tournee), if(pt.tournee_org_id,ptf.duree_reperage,pt.duree_reperage), if(pt.tournee_org_id,ptf.duree_supplement,pt.duree_supplement), if(pt.tournee_org_id,ptf.duree_nuit,pt.duree_nuit),
                    pt.nbtitre, pt.nbspl, pt.nbprod, pt.nbrep, pt.nbcli,
                    mtj.depart_depot, mtj.retour_depot,
                    " . $user . ", now()
                FROM etalon e
                INNER JOIN pai_tournee pt ON pt.depot_id=e.depot_id AND pt.flux_id=e.flux_id
                    AND pt.date_distrib between ".$this->sqlField->sqlDate($param["date_debut"])." AND ".$this->sqlField->sqlDate($param["date_fin"])."
                    ".($param["employe_id"]!=""?"AND pt.employe_id=".$param["employe_id"]:"")."
                    AND pt.split_id is null
                INNER JOIN ref_typeetalon rte on e.type_id=rte.id and (rte.dimanche and pt.jour_id=1 or rte.lundi and pt.jour_id=2 or rte.mardi and pt.jour_id=3 or rte.mercredi and pt.jour_id=4 or rte.jeudi and pt.jour_id=5 or rte.vendredi and pt.jour_id=6 or rte.samedi and pt.jour_id=7)
                LEFT OUTER JOIN (SELECT ptf.tournee_org_id, sec_to_time(sum(time_to_sec(ptf.duree))) as duree, sec_to_time(sum(time_to_sec(ptf.duree_tournee))) as duree_tournee, sec_to_time(sum(time_to_sec(ptf.duree_reperage))) as duree_reperage, sec_to_time(sum(time_to_sec(ptf.duree_supplement))) as duree_supplement, sec_to_time(sum(time_to_sec(ptf.duree_nuit))) as duree_nuit
                                FROM pai_tournee ptf WHERE ptf.split_id is not null
                                GROUP BY ptf.tournee_org_id
                                ) ptf ON  pt.id=ptf.tournee_org_id
                INNER JOIN modele_tournee_jour mtj ON pt.modele_tournee_jour_id=mtj.id
                INNER JOIN modele_tournee mt ON mtj.tournee_id=mt.id
                    ".($param["tournee_id"]!=""?"AND mt.id=".$param["tournee_id"]:"")."
                INNER JOIN groupe_tournee gt on mt.groupe_id=gt.id
                left outer join ref_transport rt on rt.id=pt.transport_id
                WHERE e.id=".$param["etalon_id"];
            $this->_em->getConnection()->prepare($sql)->execute();
            
            if ($param["tournee_id"]<>"") {            
                $sql = "INSERT INTO etalon_tournee
                        (etalon_id
                        , date_distrib
                        , modele_tournee_id,jour_id
                        , employe_id
                        , valrem_reelle
                        , transport_id, nbkm, nbkm_paye
                        , heure_debut
                        , duree, duree_totale, duree_tournee, duree_reperage, duree_supplement, duree_nuit
                        , nbtitre, nbspl, nbprod, nbrep, nbcli
                        , depart_depot, retour_depot
                        , utilisateur_id, date_creation) 
                        SELECT 
                        e.id,
                        null,
                        mt.id, rj.id,
                        null,
                        null,
                        null, 0, 0,
                        gt.heure_debut,
                        '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00',
                        0, 0, 0, 0, 0,
                        0, 0,
                        " . $user . ", now()
                    FROM etalon e
                    INNER JOIN modele_tournee mt ON mt.id=".$param["tournee_id"]."
                    INNER JOIN ref_typeetalon rte on e.type_id=rte.id
                    LEFT OUTER JOIN emp_cycle ecy ON ecy.employe_id=e.employe_id and e.date_application between ecy.date_debut and ecy.date_fin
                    INNER JOIN ref_jour rj ON   rte.dimanche AND rj.id=1 and (ecy.dimanche or e.employe_id is null)
                                            OR  rte.lundi AND rj.id=2 and (ecy.lundi or e.employe_id is null)
                                            OR  rte.mardi AND rj.id=3 and (ecy.mardi or e.employe_id is null)
                                            OR  rte.mercredi AND rj.id=4 and (ecy.mercredi or e.employe_id is null)
                                            OR  rte.jeudi AND rj.id=5 and (ecy.jeudi or e.employe_id is null)
                                            OR  rte.vendredi AND rj.id=6 and (ecy.vendredi or e.employe_id is null)
                                            OR  rte.samedi AND rj.id=7 and (ecy.samedi or e.employe_id is null)
                    INNER JOIN groupe_tournee gt on mt.groupe_id=gt.id
                    WHERE e.id=".$param["etalon_id"]."
                    AND NOT exists(select null from etalon_tournee et where et.jour_id=rj.id and et.etalon_id=".$param["etalon_id"].")";
                $this->_em->getConnection()->prepare($sql)->execute();
                }
            
            $this->update_valrem($msg, $msgException,$this->sqlField->sqlTrim($param['etalon_id']));
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "","","");
        }
        return true;
    }

    public function update(&$msg, &$msgException, $param, $user, &$id) {
        try {
            $sql = "UPDATE etalon_tournee et
                    left outer join ref_transport rt on rt.id=" . $this->sqlField->sqlTrim($param['transport_id']) . "
                    SET
                    et.duree = " . $this->sqlField->sqlDureeNotNull($param['duree']) . ",
                    et.nbcli = " . $this->sqlField->sqlTrim($param['nbcli']) . ",
                    et.transport_id = " . $this->sqlField->sqlTrim($param['transport_id']) . ",
                    et.nbkm = " . $this->sqlField->sqlTrim($param['nbkm']) . ",
                    et.nbkm_paye = case when coalesce(rt.km_paye,1) then " . $this->sqlField->sqlTrim($param['nbkm_paye']) . " else 0 end,
                    et.depart_depot = " . $param['depart_depot'] . ",
                    et.retour_depot = " . $param['retour_depot'] . ",
                    et.utilisateur_id = " . $user . ",
                    et.date_modif = NOW()";
            $sql .= " WHERE et.id=" . $param['gr_id'];
            $this->_em->getConnection()->prepare($sql)->execute();
            
            return $this->update_valrem($msg, $msgException,$this->sqlField->sqlTrim($param['etalon_id']));
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "","","");
        }
        return true;
    }

    public function delete(&$msg, &$msgException, $param) {
        try {
            $this->_em->getConnection()->prepare("DELETE FROM etalon_tournee WHERE id=" . $param['gr_id'])->execute();
            return $this->update_valrem($msg, $msgException,$this->sqlField->sqlTrim($param['etalon_id']));
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex,"","","");
            //$this->_em->getConnection()->rollBack();
            //return $error;
        }
        return true;
    }
    
    public function update_valrem(&$msg, &$msgException, $etalon_id) {
        try {
            $sql = "UPDATE etalon_tournee et
                    INNER JOIN etalon e on et.etalon_id=e.id
                    inner join ref_typetournee rtt on e.flux_id=rtt.id
                    inner join pai_ref_remuneration prr_new on rtt.societe_id=prr_new.societe_id AND rtt.population_id=prr_new.population_id AND e.date_application between prr_new.date_debut and prr_new.date_fin
                    INNER JOIN (select e.id
                        ,   modele_valrem(e.date_application,e.flux_id,sec_to_time(sum(time_to_sec(subtime(subtime(et.duree,et.duree_reperage),et.duree_supplement)))),sum(et.nbcli)) as valrem_moyen
                        ,   cal_modele_etalon(sec_to_time(sum(time_to_sec(subtime(subtime(et.duree,et.duree_reperage),et.duree_supplement)))),sum(et.nbcli)) as etalon_moyen
                        from etalon e
                        inner join etalon_tournee et on et.etalon_id=e.id
                        WHERE e.id=$etalon_id and et.jour_id=1
                        group by e.id) as em on em.id=e.id
                    SET et.tauxhoraire=prr_new.valeur
                    ,   et.valrem_calculee=modele_valrem(e.date_application,e.flux_id,subtime(subtime(et.duree,et.duree_reperage),et.duree_supplement),et.nbcli)
                    ,   et.etalon_calcule=cal_modele_etalon(subtime(subtime(et.duree,et.duree_reperage),et.duree_supplement),et.nbcli)
                    ,   et.valrem_moyen=em.valrem_moyen
                    ,   et.etalon_moyen=em.etalon_moyen
                    WHERE et.etalon_id=$etalon_id
                    AND et.jour_id=1";
            $this->_em->getConnection()->prepare($sql)->execute();

            $sql = "UPDATE etalon_tournee et
                    INNER JOIN etalon e on et.etalon_id=e.id
                    inner join ref_typetournee rtt on e.flux_id=rtt.id
                    inner join pai_ref_remuneration prr_new on rtt.societe_id=prr_new.societe_id AND rtt.population_id=prr_new.population_id AND e.date_application between prr_new.date_debut and prr_new.date_fin
                    INNER JOIN (select e.id
                        ,   modele_valrem(e.date_application,e.flux_id,sec_to_time(sum(time_to_sec(subtime(subtime(et.duree,et.duree_reperage),et.duree_supplement)))),sum(et.nbcli)) as valrem_moyen
                        ,   cal_modele_etalon(sec_to_time(sum(time_to_sec(subtime(subtime(et.duree,et.duree_reperage),et.duree_supplement)))),sum(et.nbcli)) as etalon_moyen
                        from etalon e
                        inner join etalon_tournee et on et.etalon_id=e.id
                        WHERE e.id=$etalon_id and et.jour_id<>1
                        group by e.id) as em on em.id=e.id
                    SET et.tauxhoraire=prr_new.valeur
                    ,   et.valrem_calculee=modele_valrem(e.date_application,e.flux_id,subtime(subtime(et.duree,et.duree_reperage),et.duree_supplement),et.nbcli)
                    ,   et.etalon_calcule=cal_modele_etalon(subtime(subtime(et.duree,et.duree_reperage),et.duree_supplement),et.nbcli)
                    ,   et.valrem_moyen=em.valrem_moyen
                    ,   et.etalon_moyen=em.etalon_moyen
                    WHERE et.etalon_id=$etalon_id
                    AND et.jour_id<>1";
            $this->_em->getConnection()->prepare($sql)->execute();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "","","");
        }
        return true;
    }    

    public function validation($etalon_id) {
            $msg='';
            if ($etalon_id!="") {
                $sql = "SELECT DISTINCT concat('Tournées ',mt.code, ' multiples pour la journée du ',rj.libelle) as libelle
                        FROM etalon e
                        INNER JOIN etalon_tournee et ON et.etalon_id=e.id
                        INNER JOIN modele_tournee mt on et.modele_tournee_id=mt.id
                        INNER JOIN ref_jour rj on et.jour_id=rj.id
                        WHERE e.id=$etalon_id
                        group by mt.id,rj.id
                        having count(distinct et.date_distrib)>1

                        union

                        SELECT DISTINCT concat('Données non renseignées pour la tournée ',mt.code,rj.code) as libelle
                        FROM etalon e
                        INNER JOIN etalon_tournee et ON et.etalon_id=e.id
                        INNER JOIN modele_tournee mt on et.modele_tournee_id=mt.id
                        INNER JOIN ref_jour rj on et.jour_id=rj.id
                        WHERE e.id=$etalon_id
                        and (et.transport_id is null
                        or et.nbkm is null
                        or et.nbkm_paye is null
                        or coalesce(et.valrem_calculee,0)=0
                        or coalesce(et.valrem_moyen,0)=0
                        or coalesce(time_to_sec(et.duree),0)=0
                        or coalesce(et.nbcli,0)=0
                        )

                        union

                        SELECT DISTINCT concat('Pas de cycle au ',date_format(e.date_application,'%d/%m/%Y')) as libelle
                        FROM etalon e
                        WHERE e.id=$etalon_id
                        AND e.employe_id is not null
                        AND NOT exists(select null from emp_cycle ecy where ecy.employe_id=e.employe_id and e.date_application between ecy.date_debut and ecy.date_fin)

                        union

                        SELECT DISTINCT concat('Tournée ',mt.code,' hors etalonnage.') as libelle
                        FROM etalon e
                        INNER JOIN ref_typeetalon rte on e.type_id=rte.id
                        INNER JOIN etalon_tournee et ON et.etalon_id=e.id
                        INNER JOIN modele_tournee mt on et.modele_tournee_id=mt.id
                        WHERE e.id=$etalon_id
                        AND (not rte.dimanche AND et.jour_id=1
                        OR  not rte.lundi AND et.jour_id=2
                        OR  not rte.mardi AND et.jour_id=3
                        OR  not rte.mercredi AND et.jour_id=4
                        OR  not rte.jeudi AND et.jour_id=5
                        OR  not rte.vendredi AND et.jour_id=6
                        OR  not rte.samedi AND et.jour_id=7
                        )

                        union

                        SELECT DISTINCT concat('Tournée ',mt.code,' hors cycle.') as libelle
                        FROM etalon e
                        INNER JOIN etalon_tournee et ON et.etalon_id=e.id
                        INNER JOIN modele_tournee mt on et.modele_tournee_id=mt.id
                        INNER JOIN emp_cycle ecy ON ecy.employe_id=e.employe_id and e.date_application between ecy.date_debut and ecy.date_fin
                        WHERE e.id=$etalon_id
                        AND (not ecy.dimanche AND et.jour_id=1
                        OR  not ecy.lundi AND et.jour_id=2
                        OR  not ecy.mardi AND et.jour_id=3
                        OR  not ecy.mercredi AND et.jour_id=4
                        OR  not ecy.jeudi AND et.jour_id=5
                        OR  not ecy.vendredi AND et.jour_id=6
                        OR  not ecy.samedi AND et.jour_id=7
                        )

                        union

                        SELECT DISTINCT concat('Tournée manquante pour la journée du Dimanche.') as libelle
                        FROM etalon e
                        INNER JOIN ref_typeetalon rte on e.type_id=rte.id
                        INNER JOIN emp_cycle ecy ON ecy.employe_id=e.employe_id and e.date_application between ecy.date_debut and ecy.date_fin
                        WHERE e.id=$etalon_id
                        AND rte.dimanche AND ecy.dimanche AND not exists(select null from etalon_tournee et where et.etalon_id=e.id AND et.jour_id=1)

                        union

                        SELECT DISTINCT concat('Tournée manquante pour la journée du Lundi.') as libelle
                        FROM etalon e
                        INNER JOIN ref_typeetalon rte on e.type_id=rte.id
                        INNER JOIN emp_cycle ecy ON ecy.employe_id=e.employe_id and e.date_application between ecy.date_debut and ecy.date_fin
                        WHERE e.id=$etalon_id
                        AND rte.lundi AND ecy.lundi AND not exists(select null from etalon_tournee et where et.etalon_id=e.id AND et.jour_id=2)

                        union

                        SELECT DISTINCT concat('Tournée manquante pour la journée du Mardi.') as libelle
                        FROM etalon e
                        INNER JOIN ref_typeetalon rte on e.type_id=rte.id
                        INNER JOIN emp_cycle ecy ON ecy.employe_id=e.employe_id and e.date_application between ecy.date_debut and ecy.date_fin
                        WHERE e.id=$etalon_id
                        AND rte.mardi AND ecy.mardi AND not exists(select null from etalon_tournee et where et.etalon_id=e.id AND et.jour_id=3)

                        union

                        SELECT DISTINCT concat('Tournée manquante pour la journée du Mercredi.') as libelle
                        FROM etalon e
                        INNER JOIN ref_typeetalon rte on e.type_id=rte.id
                        INNER JOIN emp_cycle ecy ON ecy.employe_id=e.employe_id and e.date_application between ecy.date_debut and ecy.date_fin
                        WHERE e.id=$etalon_id
                        AND rte.mercredi AND ecy.mercredi AND not exists(select null from etalon_tournee et where et.etalon_id=e.id AND et.jour_id=4)

                        union

                        SELECT DISTINCT concat('Tournée manquante pour la journée du Jeudi.') as libelle
                        FROM etalon e
                        INNER JOIN ref_typeetalon rte on e.type_id=rte.id
                        INNER JOIN emp_cycle ecy ON ecy.employe_id=e.employe_id and e.date_application between ecy.date_debut and ecy.date_fin
                        WHERE e.id=$etalon_id
                        AND rte.jeudi AND ecy.jeudi AND not exists(select null from etalon_tournee et where et.etalon_id=e.id AND et.jour_id=5)

                        union

                        SELECT DISTINCT concat('Tournée manquante pour la journée du Vendredi.') as libelle
                        FROM etalon e
                        INNER JOIN ref_typeetalon rte on e.type_id=rte.id
                        INNER JOIN emp_cycle ecy ON ecy.employe_id=e.employe_id and e.date_application between ecy.date_debut and ecy.date_fin
                        WHERE e.id=$etalon_id
                        AND rte.vendredi AND ecy.vendredi AND not exists(select null from etalon_tournee et where et.etalon_id=e.id AND et.jour_id=6)

                        union

                        SELECT DISTINCT concat('Tournée manquante pour la journée du Samedi.') as libelle
                        FROM etalon e
                        INNER JOIN ref_typeetalon rte on e.type_id=rte.id
                        INNER JOIN emp_cycle ecy ON ecy.employe_id=e.employe_id and e.date_application between ecy.date_debut and ecy.date_fin
                        WHERE e.id=$etalon_id
                        AND rte.samedi AND ecy.samedi AND not exists(select null from etalon_tournee et where et.etalon_id=e.id AND et.jour_id=7)
                        ";
                $curseur = $this->_em->getConnection()->fetchAll($sql);
                foreach ($curseur as $row) {
                    $msg = $msg . $row["libelle"] . '<br/>';
                }
            }
            return $msg;
    }    

}