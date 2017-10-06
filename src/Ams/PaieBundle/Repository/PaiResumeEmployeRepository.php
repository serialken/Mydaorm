<?php
namespace Ams\PaieBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class PaiResumeEmployeRepository extends GlobalRepository
{
    function select($depot_id, $flux_id, $anneemois, $employe_id, $mois_calendaire=false, $resume_valide=false){
        if (isset($employe_id)){
        $sql = "
            SELECT prc.datecal as date_distrib, prc.jour_id,R.*
            FROM  pai_ref_calendrier prc
            INNER JOIN pai_ref_mois prm ON prm.anneemois='$anneemois'
            ".($mois_calendaire  ?" and prc.datecal between str_to_date(concat(prm.anneemois,'01'),'%Y%m%d') and last_day(str_to_date(concat(prm.anneemois,'01'),'%Y%m%d'))"
                                :" and prc.datecal between prm.date_debut and prm.date_fin")."
            LEFT JOIN (
               SELECT
                    concat(pt.id,'-') as id,
                    pt.id as tournee_id,
                    null as activite_id,
                     pt.typejour_id,
                    pt.date_distrib r_date_distrib,
                    concat('Tournée ',pt.code) as libelle,
                    pt.transport_id,
                    pt.nbkm,
                    pt.nbkm_paye,
                    time_format(pt.heure_debut_calculee,'%H:%i') as heure_debut,
                    format(time_to_sec(pt.duree)/3600,2) as duree,
                    format(time_to_sec(addtime(pt.duree_nuit,coalesce(pa.duree_nuit,'00:00')))/3600,2) as duree_nuit,
                    format(time_to_sec(pt.duree_tournee)/3600,2) as duree_tournee,
                    null as duree_activite,
                    format(time_to_sec(pt.duree_reperage)/3600,2) as duree_reperage,
                    format(time_to_sec(pt.duree_supplement)/3600,2) as duree_supplement,
                    format(time_to_sec(pt.duree)/3600,2)  as duree_totale,
                    pt.nbcli,
                    pt.nbtitre,
                    pt.nbspl,
                    pt.nbprod,
                    pt.nbrep,
                    pt.nbadr,
                    '' as commentaire,
                    -- journal
                    coalesce(min(pe.valide),true) as valide,
                    group_concat(pe.msg order by pe.level,pe.rubrique,pe.code separator '<br/>') as msg,
                    min(pj.id) as journal_id,
                    min(pe.level) as level
                FROM pai_tournee pt
                LEFT OUTER JOIN pai_activite pa on pa.tournee_id=pt.id and pa.activite_id=-2
                INNER JOIN pai_ref_mois prm ON prm.anneemois='$anneemois'"
                .($mois_calendaire  ?" and pt.date_distrib between str_to_date(concat(prm.anneemois,'01'),'%Y%m%d') and last_day(str_to_date(concat(prm.anneemois,'01'),'%Y%m%d'))"
                                    :" and pt.date_distrib between prm.date_debut and prm.date_fin")."
                LEFT OUTER JOIN pai_journal pj ON pt.id=pj.tournee_id
                LEFT OUTER JOIN pai_ref_erreur pe ON pj.erreur_id=pe.id ".($resume_valide?"and pe.valide":"")."
                WHERE pt.employe_id=$employe_id
                AND (pt.tournee_org_id is null or pt.split_id is not null)
                GROUP BY pt.id
            UNION
                SELECT
                    concat('-',pa.id),
                    null as tournee_id,
                    pa.id as activite_id,
                    pa.typejour_id,
                    pa.date_distrib r_date_distrib,
                    ra.libelle,
                    pa.transport_id,
                    null nbkm,
                    pa.nbkm_paye,
                    time_format(pa.heure_debut_calculee,'%H:%i') as heure_debut,
                    format(time_to_sec(pa.duree)/3600,2) as duree,
                    format(time_to_sec(pa.duree_nuit)/3600,2) as duree_nuit,
                    null duree_tournee,
                    format(time_to_sec(pa.duree)/3600,2) as duree_activite,
                    null duree_reperage,
                    null duree_supplement,
                    format(time_to_sec(pa.duree)/3600,2) as duree_totale,
                    null nbcli,
                    null nbtitre,
                    null nbspl,
                    null nbprod,
                    null nbrep,
                    null nbadr,
                    pa.commentaire,
                    -- journal
                    coalesce(min(pe.valide),true) as valide,
                    group_concat(pe.msg order by pe.level,pe.rubrique,pe.code separator '<br/>') as msg,
                    min(pj.id) as journal_id,
                    min(pe.level) as level
                FROM pai_activite pa
                INNER JOIN ref_activite ra ON pa.activite_id=ra.id and ra.id<>-1
                INNER JOIN pai_ref_mois prm ON prm.anneemois='$anneemois'"
                .($mois_calendaire  ?" and pa.date_distrib between str_to_date(concat(prm.anneemois,'01'),'%Y%m%d') and last_day(str_to_date(concat(prm.anneemois,'01'),'%Y%m%d'))"
                                    :" and pa.date_distrib between prm.date_debut and prm.date_fin")."
                LEFT OUTER JOIN pai_journal pj on pa.id=pj.activite_id
                LEFT OUTER JOIN pai_ref_erreur pe ON pj.erreur_id=pe.id ".($resume_valide?"and pe.valide":"")."
                WHERE /*pa.depot_id=$depot_id AND pa.flux_id $flux_id
                AND*/ pa.employe_id=$employe_id
                GROUP BY pa.id
            ) R ON  prc.datecal = R.r_date_distrib 
            
            union -- total
            
               SELECT 'Total' as date_distrib,
                    null as jour_id,
                    null as id,
                    null as tournee_id,
                    null as activite_id,
                    null as typejour_id,
                    null as r_date_distrib,
                    null as libelle,
                    null as transport_id,
                    sum(R.nbkm),
                    sum(R.nbkm_paye),
                    null  heure_debut,
                    format(sum(time_to_sec(R.duree))/3600,2) as duree,
                    format(sum(time_to_sec(R.duree_nuit))/3600,2) as duree_nuit,
                    format(sum(time_to_sec(R.duree_tournee))/3600,2) as duree_tournee,
                    format(sum(time_to_sec(R.duree_activite))/3600,2) as duree_activite,
                    format(sum(time_to_sec(R.duree_reperage))/3600,2) as duree_reperage,
                    format(sum(time_to_sec(R.duree_supplement))/3600,2) as duree_supplement,
                    format(sum(time_to_sec(R.duree))/3600,2) as duree_totale,
                    sum(R.nbcli) nbcli,
                    sum(R.nbtitre) nbtitre,
                    sum(R.nbspl) nbspl,
                    sum(R.nbprod) nbprod,
                    sum(R.nbrep) nbrep,
                    sum(R.nbadr) nbrep,
                    null as commentaire,
                    -- journal
                    true as valide,
                    null as msg,
                    null as journal_id,
                    null as level
                FROM 
            (
               SELECT
                    pt.id,
                    pt.duree,
                    pt.duree_nuit,
                    pt.duree_tournee,
                    null duree_activite,
                    pt.duree_reperage,
                    pt.duree_supplement,
                    pt.nbcli,
                    pt.nbtitre,
                    pt.nbspl,
                    pt.nbprod,
                    pt.nbrep,
                    pt.nbadr,
                    pt.nbkm,
                    pt.nbkm_paye
                FROM pai_tournee pt
                INNER JOIN pai_ref_mois prm ON prm.anneemois='".$anneemois."'"
                .($mois_calendaire  ?" and pt.date_distrib between str_to_date(concat(prm.anneemois,'01'),'%Y%m%d') and last_day(str_to_date(concat(prm.anneemois,'01'),'%Y%m%d'))"
                                    :" and pt.date_distrib between prm.date_debut and prm.date_fin")."
                LEFT OUTER JOIN pai_journal pj ON pt.id=pj.tournee_id
                LEFT OUTER JOIN pai_ref_erreur pe ON pj.erreur_id=pe.id ".($resume_valide?"and pe.valide":"")."
                WHERE pt.employe_id=$employe_id
                AND (pt.tournee_org_id is null or pt.split_id is not null)
                GROUP BY pt.id
            UNION
                SELECT
                    pa.id,
                    pa.duree,
                    pa.duree_nuit,
                    null duree_tournee,
                    pa.duree duree_activite,
                    null duree_reperage,
                    null duree_supplement,
                    null nbcli,
                    null nbtitre,
                    null nbspl,
                    null nbprod,
                    null nbrep,
                    null nbadr,
                    null nbkm,
                    pa.nbkm_paye
                FROM pai_activite pa
                INNER JOIN ref_activite ra ON pa.activite_id=ra.id and ra.id<>-1
                INNER JOIN pai_ref_mois prm ON prm.anneemois='$anneemois'"
                .($mois_calendaire  ?" and pa.date_distrib between str_to_date(concat(prm.anneemois,'01'),'%Y%m%d') and last_day(str_to_date(concat(prm.anneemois,'01'),'%Y%m%d'))"
                                    :" and pa.date_distrib between prm.date_debut and prm.date_fin")."
                LEFT OUTER JOIN pai_journal pj on pa.id=pj.activite_id
                LEFT OUTER JOIN pai_ref_erreur pe ON pj.erreur_id=pe.id ".($resume_valide?"and pe.valide":"")."
                WHERE pa.employe_id=$employe_id
                GROUP BY pa.id
            ) R
            order by 1,8
        ";
                
        return $this->_em->getConnection()->fetchAll ($sql);
        }
    }
}
