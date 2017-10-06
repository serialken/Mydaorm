select matricule,pt.date_distrib,pt.code,pt.id,pr.societe_id,peq.qualite,
(select sum(ppt2.nbcli) from pai_tournee pt2 inner join pai_prd_tournee ppt2 on ppt2.tournee_id=pt2.id and ppt2.natureclient_id=0 where pt.employe_id=pt2.employe_id  AND pt2.typejour_id=1 and pt2.date_extrait is not null) as totalnbcli,
(select sum(pr2.nbrec_abonne) from pai_reclamation pr2,pai_tournee pt2 where pt.employe_id=pt2.employe_id and pt2.id=pr2.tournee_id and pr2.date_extrait is not null) as totalrec,
(select sum(pr2.nbrec_abonne) from pai_reclamation pr2,pai_tournee pt2 where pt.employe_id=pt2.employe_id and pt2.id=pr2.tournee_id and pr2.date_extrait is not null)*1000/
(select sum(ppt2.nbcli) from pai_tournee pt2 inner join pai_prd_tournee ppt2 on ppt2.tournee_id=pt2.id and ppt2.natureclient_id=0 where pt.employe_id=pt2.employe_id  AND pt2.typejour_id=1 and pt2.date_extrait is not null) as taux,
pr.date_creation,pr.date_extrait,pr.anneemois,pr.nbrec_abonne,pr.nbrec_diffuseur,
c.id,c.date_creat,c.date_saisie_modif,c.date_debut,c.date_fin,c.date_imputation_paie,c.imputation_paie,c.*
,c.*
from pai_reclamation pr
inner join pai_tournee pt on pr.tournee_id=pt.id
left outer join crm_detail c on c.pai_tournee_id=pt.id
left outer join pai_ev_qualite peq on pt.employe_id=peq.employe_id
inner join employe e on pt.employe_id=e.id
where matricule in ('7000360400','NEP0002400','NEP0005400','NEP0005700','NEP0039500','NEP0041100','NEP0041200','NEP0085800','NEP0092100','NEP0113800','NEP0114100','NEP0130700','NEP0140000','NEP0147900')
order by matricule

NEP0005400	05/11/2014 00:00:00	042NXK016ME	626894	68	0,633	3161	3	0,9491	19/11/2014 18:19:52	20/11/2014 16:19:36	201411	1	0	4039	05/11/2014 00:00:00		05/11/2014 00:00:00	05/11/2014 00:00:00	05/11/2014 00:00:00	1	4039	704	127		110	132	24	68	11462	11462	6603	498497	05/11/2014 00:00:00	05/11/2014 00:00:00	05/11/2014 00:00:00	00009113239	M    RICHARD PEREZ			13 RUE DE L ANCIENNE COMEDIE		75006	PARIS	LI	27				10/11/2014 05:19:19	Erreur porteur.	0	0									1			429	487001	626894	05/11/2014 00:00:00	1			11534	4039	704	127		110	132	24	68	11462	11462	6603	498497	05/11/2014 00:00:00	05/11/2014 00:00:00	05/11/2014 00:00:00	00009113239	M    RICHARD PEREZ			13 RUE DE L ANCIENNE COMEDIE		75006	PARIS	LI	27				10/11/2014 05:19:19	Erreur porteur.	0	0									1			429	487001	626894	05/11/2014 00:00:00	1			11534
NEP0114100	01/11/2014 00:00:00	028NJT061SA	626367	63	0,000	1446	1	0,6916	19/11/2014 18:19:52	20/11/2014 16:19:36	201411	1	0	5046	14/11/2014 00:00:00		01/11/2014 00:00:00	08/11/2014 00:00:00	01/11/2014 00:00:00	1	5046	714	131		111	21	10	63	32981	33109	6309	502763	14/11/2014 00:00:00	01/11/2014 00:00:00	08/11/2014 00:00:00	5305529		MME MICHELE MIMOUN	43 RUE DE L ASSOMPTION			75016	PARIS	TV	31	NR 2841/42 (bal nom et interne / besoin d'une cl			19/11/2014 04:22:38	relivree par le porteur avec du retard	0	0				21	8	1		19/11/2014 04:23:07	1			567	7037	626367	01/11/2014 00:00:00	1	livree en retard		14214	5046	714	131		111	21	10	63	32981	33109	6309	502763	14/11/2014 00:00:00	01/11/2014 00:00:00	08/11/2014 00:00:00	5305529		MME MICHELE MIMOUN	43 RUE DE L ASSOMPTION			75016	PARIS	TV	31	NR 2841/42 (bal nom et interne / besoin d'une cl			19/11/2014 04:22:38	relivree par le porteur avec du retard	0	0				21	8	1		19/11/2014 04:23:07	1			567	7037	626367	01/11/2014 00:00:00	1	livree en retard		14214

select * 
from pai_ev_qualite
select distinct idtrt from pai_ev_hst
select * from pai_int_traitement order by id desc
select * from crm_detail where imputation_paie and pai_tournee_id not in (select tournee_id from pai_reclamation);

select * from pai_reclamation where tournee_id=623991
201412-1-626513-63
201412-1-627125-39

    update crm_detail set imputation_paie=imputation_paie where imputation_paie=true and pai_tournee_id is not null;
    update crm_detail set imputation_paie=imputation_paie where imputation_paie=true and pai_tournee_id=627125;
    update crm_detail set imputation_paie=imputation_paie where imputation_paie=true and id=598;

    select 
      now(),0,
      1, -- crm
      c.societe_id,
      c.pai_tournee_id,
      coalesce(pm.anneemois,prm.anneemois),
      -- le total peut-être négatif, on fait éventuellement de la regul
      sum(case when c.client_type=0 THEN 1 ELSE 0 END)-coalesce((select sum(nbrec_abonne) from pai_reclamation pr2 where pr2.tournee_id=c.pai_tournee_id and pr2.societe_id=c.societe_id),0),
      sum(case when c.client_type=1 THEN 1 ELSE 0 END)-coalesce((select sum(nbrec_diffuseur) from pai_reclamation pr2 where pr2.tournee_id=c.pai_tournee_id and pr2.societe_id=c.societe_id),0)
    from crm_detail c
    inner join crm_demande cd on c.crm_demande_id=cd.id and cd.crm_categorie_id=1 -- seulement les reclamations
    inner join pai_tournee pt on c.pai_tournee_id=pt.id
    left outer join pai_mois pm on pt.date_distrib<=pm.date_fin
    inner join pai_ref_mois prm on pt.date_distrib between prm.date_debut and prm.date_fin
    where c.pai_tournee_id=623991
    and c.imputation_paie=true
    group by c.societe_id,c.pai_tournee_id,coalesce(pm.anneemois,prm.anneemois)
    having sum(case when c.client_type=0 THEN 1 ELSE 0 END)-coalesce((select sum(nbrec_abonne) from pai_reclamation pr2 where pr2.tournee_id=c.pai_tournee_id and pr2.societe_id=c.societe_id),0)<>0
    or     sum(case when c.client_type=1 THEN 1 ELSE 0 END)-coalesce((select sum(nbrec_diffuseur) from pai_reclamation pr2 where pr2.tournee_id=c.pai_tournee_id and pr2.societe_id=c.societe_id),0)<>0;

select 24*3600,time_to_sec(duree),pt.* from pai_tournee pt where duree='38:54:08'
select * from pai_tournee where time_to_sec(duree)>=24*3600

update pai_tournee set duree='24:00:00' where time_to_sec(duree)>=24*3600 and date_extrait is null
update pai_tournee set duree_nuit='24:00:00' where time_to_sec(duree_nuit)>=24*3600 and date_extrait is null
update pai_tournee set duree_nuit='00:00:00' where time_to_sec(duree_nuit)<0 and date_extrait is null