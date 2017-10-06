select * from employe where nom='BRIK' 
select * from pai_int_traitement where (typetrt like 'MROAD2PNG%' or typetrt like 'GENERE_PLEIADES%') order by id desc;
update pai_int_traitement set statut='E' where(typetrt like 'MROAD2PNG%' or typetrt like 'GENERE_PLEIADES%')and statut='C';
select * from pai_int_traitement  order by id desc;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 set @idtrt=null;
call INT_MROAD2EV_historique(@idtrt,2062);
 set @idtrt=null;
call INT_MROAD2EV_historique(@idtrt,1665);
    select * from pai_int_log where idtrt in(select max(id) from pai_int_traitement) order by id desc;
    select * from pai_int_log where idtrt=1823;
    select * from pai_stc where date_extrait is null
    select * from pai_ev_emp_pop_depot
    select * from pai_ev_hst where idtrt=1675 and matricule='7000012320'
    
  ;
select * from pai_activite where activite_id=-10 order by employe_id,date_distrib;
  CALL INT_MROAD2HEURESGARANTIES(0,1993);
    SELECT epd.depot_id, -10, epd.employe_id, 1, pit.utilisateur_id, pih.date_distrib, '00:00:00', sec_to_time(abs(time_to_sec(pih.hgaranties))), 0, pit.date_debut, epd.flux_id, null, '00:00:00', null, null
    from pai_int_oct_hgaranties pih
    inner join emp_pop_depot epd on pih.employe_id=epd.employe_id and pih.date_distrib between epd.date_debut and epd.date_fin
    inner join pai_int_traitement pit on pit.id=1993
    where pih.hgaranties<0
    and pih.idtrt=pit.id

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
  call int_mroad2ev_diff(1940); -- jour
  select d.* FROM pai_ev_diff d WHERE d.diff<>'=' and idtrt1=1939 and idtrt2=1939 ORDER BY d.matricule,d.datev,d.poste;
  select d.* FROM pai_ev_diff d WHERE d.diff<>'=' and poste='CPHG' and idtrt1=1939 and idtrt2=1939 ORDER BY d.matricule,d.datev,d.poste;
  
  call int_mroad2ev_history_diff(1675, 1940); -- jour
  select d.* FROM pai_ev_diff d WHERE d.diff<>'=' and idtrt1=1675 and idtrt2=1940 ORDER BY d.matricule,d.datev,d.poste;
  select d.* FROM pai_ev_diff d WHERE d.diff<>'=' and idtrt1=1675 and idtrt2=1940 and typev<>'PRIME' and poste<>'CPHG' ORDER BY d.matricule,d.datev,d.poste;
  select d.* FROM pai_ev_diff d WHERE d.diff<>'=' and idtrt1=1675 and idtrt2=1940 and poste='CPHG' ORDER BY d.matricule,d.datev,d.poste;
  select d.* FROM pai_ev_diff d WHERE d.diff<>'=' and idtrt1=1675 and idtrt2=1940 and typev='PRIME' ORDER BY d.matricule,d.datev,d.poste;
  call int_mroad2ev_history_diff(1665, 1947); -- nuit
  select d.* FROM pai_ev_diff d WHERE d.diff<>'=' and idtrt1=1665 and idtrt2=1892  ORDER BY d.matricule,d.datev,d.poste;

  select * from pai_incident pi inner join pai_tournee pt on pi.tournee_id=pt.id inner join employe e on pt.employe_id=e.id where matricule='NEP0137720';
  1675	1829	+	BLOQUAGE	NEP0137720	000001	DPPY	21/01/2015 00:00:00		Prime qualité polyvalent (0=blocage)		1,00		0,000		0,00
  
1665	1892	M	PRIME	7000309900	900007	0105	07/02/2015 00:00:00		Prime Qualité porteur	1,00	0,00	0,000	0,000	0,00	0,00
1665	1892	C	PRIME	7000377000	900001	0105	21/01/2015 00:00:00		Prime Qualité porteur		1,00		0,000		0,00

  select * from pai_ev_qualite q inner join employe e on q.employe_id=e.id where e.matricule='7000377000' and idtrt in (1665,1892) order by idtrt desc;
  select * from pai_ev_emp_pop_hst q inner join employe e on q.employe_id=e.id where e.matricule='7000377000' and idtrt in (1665,1892);
  select * from pai_reclamation r inner join pai_tournee pt on r.tournee_id=pt.id inner join employe e on pt.employe_id=e.id where e.matricule='7000350000' order by date_distrib desc;
  select r.* from pai_ev_reclamation_hst r  inner join pai_tournee pt on r.tournee_id=pt.id inner join employe e on pt.employe_id=e.id where e.matricule='7000309900' and idtrt in (1665,1892) order by idtrt desc;


  call int_mroad2ev_diff(1991); -- jour
select ligne from pai_int_ev_diff_NG where idtrt=1991 and ligne like '%Z0050459%'
  select d.* FROM pai_ev_diff d WHERE d.diff<>'=' and idtrt1=1991 and idtrt2=1991  and matricule='NEP00530' ORDER BY d.matricule,d.datev,d.poste;
  select * from pai_ev_hst  where idtrt=1991
    
  select * FROM pai_ev_hst where idtrt=1675 and typev='PRIME' and matricule='7000213020';
  select * from pai_ev_emp_pop_depot order by matricule,d;
select * from employe where matricule='7000309900'
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
    -- Mise en correspondance des logs
    select l1.module,l1.msg,l1.level,l1.date_log,l1.count,l2.date_log,l2.count
    from pai_int_log l1
    left outer join pai_int_log l2 on l1.module=l2.module and l1.msg=l2.msg
    where l1.idtrt=1675 and l2.idtrt=1823
    order by l2.id desc



select * from pai_ev_activite_hst where idtrt in (1675,1839) and employe_id in (7221)
select * from pai_tournee where id in (654767,655419,663424,663559,672349)
select * from pai_reclamation where tournee_id in (654767,655419,663424,663559,672349)
select * from pai_ev_tournee where id in (654767,655419)
select * from employe where id=6861
select * from pai_journal where tournee_id in (654767,655419)

select * from pai_activite where id in (566888,561750)
select * from pai_journal where activite_id=566888
select * from emp_pop_depot where employe_id=7221

  SELECT a.id,a.date_distrib,a.employe_id,a.depot_id,a.flux_id,a.jour_id,a.typejour_id,a.activite_id,a.nbkm_paye,a.transport_id,a.duree,a.duree_nuit 
  FROM pai_activite a 
  INNER JOIN pai_ev_emp_pop_depot e ON e.employe_id=a.employe_id and e.depot_id=a.depot_id and e.flux_id=a.flux_id and a.date_distrib between e.d and e.f
  inner join employe e2 on a.employe_id=e2.id and e2.matricule='7000373920'
  WHERE (a.date_extrait='2015-02-21 12:48:49')
  AND e.typetournee_id in (1,2) -- on exclu les encadrants
--  AND (not _is1M and  a.date_distrib NOT LIKE '%-05-01'or _is1M and a.date_distrib LIKE '%-05-01')
  AND NOT exists(SELECT NULL FROM pai_journal pj INNER JOIN pai_ref_erreur pe on pj.erreur_id=pe.id WHERE a.id=pj.activite_id AND NOT pe.valide)
  and a.tournee_id is null -- on ne prend pas les activités liées à une tournées car elles peuvent être invalide


  SELECT t.id,t.date_distrib,t.employe_id,t.depot_id,t.flux_id,t.jour_id,t.typejour_id,t.valrem,t.valrem_majoree,t.code,t.nbkm_paye,t.transport_id,t.duree,t.duree_nuit,t.nbcli,t.majoration
  FROM pai_tournee t
  INNER JOIN pai_ev_emp_pop_depot e ON e.employe_id=t.employe_id and e.depot_id=t.depot_id and e.flux_id=t.flux_id and t.date_distrib between e.d and e.f
  --   inner join employe e2 on t.employe_id=e2.id and e2.matricule='7000350000'
  WHERE t.date_extrait='2015-01-20 18:27:39'
  AND e.typetournee_id in (1,2) -- on exclu les encadrants
  AND (t.tournee_org_id is null or split_id is not null)
  AND NOT exists(SELECT NULL FROM pai_journal pj INNER JOIN pai_ref_erreur pe on pj.erreur_id=pe.id WHERE t.id=pj.tournee_id AND NOT pe.valide)
  ;
