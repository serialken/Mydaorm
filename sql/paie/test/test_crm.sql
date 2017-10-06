SELECT
                        pr.id,
                        pt.date_distrib,
                        pr.type_id,
                        pt.code as tournee_id,
                        concat_ws(' ',e.nom,e.prenom1,e.prenom2) as employe_id,
                        pr.societe_id,
                        pr.nbrec_abonne,
                        pr.nbrec_diffuseur,
                        pr.commentaire,
                        (select group_concat(if (c.imputation_paie,c.id,concat('-',c.id))separator ',')
                            from crm_detail c
                            inner join crm_demande cd on c.crm_demande_id=cd.id and cd.crm_categorie_id=1 -- seulement les reclamations
                            where c.pai_tournee_id=pt.id and c.societe_id=pr.societe_id
                         --   and c.imputation_paie=true
                            group by c.societe_id,c.pai_tournee_id
                        ) as crm,
                        if(type_id=2 and pr.date_extrait is null,true,false) as isModif -- on ne peut pas modifier les reclamations crm ou pepp
                    FROM pai_reclamation pr
                    INNER JOIN pai_tournee pt ON pr.tournee_id=pt.id
                    LEFT OUTER JOIN employe e on pt.employe_id=e.id
                    WHERE  pt.employe_id=5960
                    ORDER BY pt.date_distrib desc,pt.code
                    
                    select * from employe where nom='JAWAD'
                    
                    -27927
                    -28557
                    
                    select * from crm_detail where id in (27927,28557);
                    update crm_detail set imputation_paie=imputation_paie where date_imputation_paie>='2015-01-21' and pai_tournee_id not in (663861,670845);
                    
code	date_distrib	date_imputation_paie	nom	prenom1	crm	reclam_abo	reclam_dis
042NXK034VE	23/01/2015 00:00:00	23/01/2015 00:00:00	LEVEILLE	MIKELSON	32024	-1	0
040JTG007MA	03/02/2015 00:00:00	03/02/2015 00:00:00	KOFFI	KONAN JOACHIM	37964	-1	0
Duplicate entry '201502-1-663861-68' for key 'un_pai_reclamation_tournee'
Duplicate entry '201502-1-670845-26' for key 'un_pai_reclamation_tournee'
select * from pai_tournee where id=670845
                    
    select 
      pt.id,pt.code,pt.date_distrib,c.date_imputation_paie,e.nom,e.prenom1,c.id as crm,
      -- le total peut-être négatif, on fait éventuellement de la regul
      sum(case when not c.imputation_paie then 0 when c.client_type=0 THEN 1 ELSE 0 END)-coalesce((select sum(nbrec_abonne) from pai_reclamation pr2 where pr2.tournee_id=c.pai_tournee_id and pr2.societe_id=c.societe_id),0) as reclam_abo,
      sum(case when not c.imputation_paie then 0 when c.client_type=1 THEN 1 ELSE 0 END)-coalesce((select sum(nbrec_diffuseur) from pai_reclamation pr2 where pr2.tournee_id=c.pai_tournee_id and pr2.societe_id=c.societe_id),0) as reclam_dis
    from crm_detail c
    inner join crm_demande cd on c.crm_demande_id=cd.id and cd.crm_categorie_id=1 -- seulement les reclamations
    inner join pai_tournee pt on c.pai_tournee_id=pt.id
    left outer join employe e on pt.employe_id=e.id
    left outer join pai_mois pm on pt.date_distrib<=pm.date_fin and pt.flux_id=pm.flux_id
    inner join pai_ref_mois prm on pt.date_distrib between prm.date_debut and prm.date_fin
   /*  where  c.imputation_paie=true
    and c.id in (27927,28557)*/
       where pt.date_distrib>='2015-01-21'

    group by c.societe_id,c.pai_tournee_id,coalesce(pm.anneemois,prm.anneemois)
    having sum(case when not c.imputation_paie then 0 when c.client_type=0 THEN 1 ELSE 0 END)-coalesce((select sum(nbrec_abonne) from pai_reclamation pr2 where pr2.tournee_id=c.pai_tournee_id and pr2.societe_id=c.societe_id),0)<>0
    or     sum(case when not c.imputation_paie then 0 when c.client_type=1 THEN 1 ELSE 0 END)-coalesce((select sum(nbrec_diffuseur) from pai_reclamation pr2 where pr2.tournee_id=c.pai_tournee_id and pr2.societe_id=c.societe_id),0)<>0
    order by 3,4,2
                        