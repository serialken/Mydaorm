 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 -- Vue
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP table IF EXISTS pai_ev_heure;
DROP view IF EXISTS pai_ev_heure;
CREATE VIEW pai_ev_heure(date_distrib, employe_id, depot_id, flux_id, typejour_id, heure_debut, duree, duree_nuit, nbkm_paye, transport_id,activite_id) AS 
  -- Union des temps des tournées, activités, suppléments
  SELECT  date_distrib, employe_id, depot_id, flux_id, typejour_id, heure_debut, duree, duree_nuit, nbkm_paye, transport_id, activite_id
  FROM pai_ev_activite
  WHERE duree<>'00:00:00'
  UNION ALL
  SELECT date_distrib, employe_id, depot_id, flux_id, typejour_id, heure_debut, duree, duree_nuit, nbkm_paye, transport_id, null
  FROM  pai_ev_tournee
  WHERE duree<>'00:00:00'
/*  UNION ALL
  SELECT date_distrib, employe_id, depot_id, flux_id, typejour_id, duree_supplement, '00:00:00', 0, null, null
  FROM  pai_ev_tournee pt
  INNER JOIN pai_ev_produit pp ON pp.tournee_id=pt.id
  WHERE duree_supplement<>'00:00:00'*/
  ;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP view IF EXISTS pai_ev_heure_hst;
CREATE VIEW pai_ev_heure_hst(idtrt,date_distrib, employe_id, depot_id, flux_id, typejour_id, heure_debut, duree, duree_nuit, nbkm_paye, transport_id,activite_id) AS 
  -- Union des temps des tournées, activités, suppléments
  SELECT  idtrt,date_distrib, employe_id, depot_id, flux_id, typejour_id, heure_debut, duree, duree_nuit, nbkm_paye, transport_id, activite_id
  FROM pai_ev_activite_hst
  WHERE duree<>'00:00:00'
  UNION ALL
  SELECT idtrt,date_distrib, employe_id, depot_id, flux_id, typejour_id, heure_debut, duree, duree_nuit, nbkm_paye, transport_id, null
  FROM  pai_ev_tournee_hst
  WHERE duree<>'00:00:00'
/*  UNION ALL
  SELECT pt.idtrt,date_distrib, employe_id, depot_id, flux_id, typejour_id, duree_supplement, '00:00:00', 0, null, null
  FROM  pai_ev_tournee_hst pt
  INNER JOIN pai_ev_produit_hst pp ON pp.idtrt=pt.idtrt and pp.tournee_id=pt.id
  WHERE duree_supplement<>'00:00:00'*/
  ;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP table IF EXISTS pai_int_ev_hst;
DROP view IF EXISTS pai_int_ev_hst;
CREATE VIEW pai_int_ev_hst(idtrt, ligne) AS 
  select
	e.idtrt
	,concat_ws('|'
		,'XEvFactory     '
		,'C'
		,'MROAD          '
		,rpad(trim(e.matricule),8,'Z')
		,coalesce(e.rc,'900001')
		,rpad(e.poste,10,' ')
		,date_format(e.datev,'%Y%m%d')        			-- dateEffet
		,coalesce(concat(' ',lpad(e.ordre,2,'0')),'   ')	-- noOrdre
		,'        '   			-- dateFin
		,'EVPORTAGE '  			-- TA_RgpNatEvGenW
		,'1|0|0|0|0|0'
		,concat(' ',lpad(round(e.qte ,2),11,'0'))
		,concat(' ',lpad(if(prpg.taux,round(e.taux,3),0),11,'0'))
		,concat(' ',lpad(if(prpg.montant,round(e.val ,2),0),11,'0'))
		,' '          			-- signe
		-- ||'|'||rpad(coalesce(lib,' '),30,' ') 		--lib
		,rpad(' ',30,' ') 			-- lib
		,'      ||      | |    |   |          |0|5|      |   |      |   |      | | ||'
		) as ligne
	from pai_ev_hst e
  inner join pai_ref_postepaie_general prpg on e.poste=prpg.poste
	-- and (rc<>'900001' or rc is null)
	order by e.matricule,coalesce(e.rc,'900001'),e.poste,e.datev,coalesce(e.ordre,0)
;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP table if exists pai_ev_diffv;
DROP VIEW if exists pai_ev_diffv;
CREATE VIEW pai_ev_diffv(idtrt1,diff,typev,matricule,poste,datev,ordre,libelle,rc,qte_PEPP,qte_MROAD,taux_PEPP,taux_MROAD,val_PEPP,val_MROAD) AS 
  select e1.idtrt1,e1.diff,e1.typev,e1.matricule,e1.poste,e1.datev,e1.ordre,e1.libelle,e1.rc,e1.qte2,e1.qte1,e1.taux2,e1.taux1,e1.val2,e1.val1
  from pai_ev_diff e1;


 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 -- select ligne from pai_int_ev_diff_NG where idtrt=1911 order by ordre;
DROP table IF EXISTS pai_int_ev_diff_NG;
DROP view IF EXISTS pai_int_ev_diff_NG;
CREATE VIEW pai_int_ev_diff_NG(idtrt, ordre, ligne) AS 
    select distinct 
      d.idtrt1,
      concat_ws('|',rpad(trim(d.matricule),8,'Z'),d.rc,'1'),
      '#DEBUT_TRS' 
    from pai_ev_diff d
    where d.diff<>'='
    and d.idtrt1=d.idtrt2
  union
    -- On place les suppressions avant les ajouts/modifications
    select 
      d.idtrt1,
      concat_ws('|',rpad(trim(d.matricule),8,'Z'),d.rc,'2',if(d.diff='S','1','2'),date_format(d.datev,'%Y%m%d')),
    	concat_ws('|'
        ,'XEvFactory     '
        ,d.diff
        ,'MROAD          '
        ,rpad(trim(d.matricule),8,'Z')
        ,d.rc
        ,rpad(d.poste,10,' ')
        ,date_format(d.datev,'%Y%m%d')
        ,coalesce(concat(' ',lpad(d.ordre,2,'0')),'   ')	-- noOrdre
        ,'        |          |0|0|0|0|0|0'
        ,concat(' ',lpad(round(if(d.diff='S',d.qte1,d.qte2) ,2),11,'0'))
        ,concat(' ',lpad(if(prpg.taux,round(if(d.diff='S',coalesce(d.taux1,0),d.taux2) ,3),0),11,'0'))
        ,concat(' ',lpad(if(prpg.montant,round(if(d.diff='S',coalesce(d.val1,0),d.val2) ,2),0),11,'0'))
        ,' '          			-- signe
    		,rpad(' ',30,' ') 			-- lib
    		,'      ||      | |    |   |          |0|5|      |   |      |   |      | | ||'
  		) as ligne
    from pai_ev_diff d
    inner join pai_ref_postepaie_general prpg on d.poste=prpg.poste
    where d.diff<>'='
    and d.idtrt1=d.idtrt2
  union
    select distinct 
      d.idtrt1,
      concat_ws('|',rpad(trim(d.matricule),8,'Z'),d.rc,'3'),
      '#FIN_TRS' 
    from pai_ev_diff d
    where d.diff<>'='
    and d.idtrt1=d.idtrt2
    order by 1,2
  ;
  
   -- ------------------------------------------------------------------------------------------------------------------------------------------------
 -- select ligne from pai_int_ev_diff_history where idtrt1=5047 and idtrt2=5281 order by ordre;
DROP table IF EXISTS pai_int_ev_diff_history;
DROP view IF EXISTS pai_int_ev_diff_history;
CREATE VIEW pai_int_ev_diff_history(idtrt1, idtrt2, ordre, ligne) AS 
    select distinct 
      d.idtrt1,
      d.idtrt2,
      concat_ws('|',rpad(trim(d.matricule),8,'Z'),d.rc,'1'),
      '#DEBUT_TRS' 
    from pai_ev_diff d
    where d.diff<>'='
  union
    -- On place les suppressions avant les ajouts/modifications
    select 
      d.idtrt1,
      d.idtrt2,
      concat_ws('|',rpad(trim(d.matricule),8,'Z'),d.rc,'2',if(d.diff='S','1','2'),date_format(d.datev,'%Y%m%d')),
    	concat_ws('|'
        ,'XEvFactory     '
        ,d.diff
        ,'MROAD          '
        ,rpad(trim(d.matricule),8,'Z')
        ,d.rc
        ,rpad(d.poste,10,' ')
        ,date_format(d.datev,'%Y%m%d')
        ,coalesce(concat(' ',lpad(d.ordre,2,'0')),'   ')	-- noOrdre
        ,'        |          |0|0|0|0|0|0'
        ,concat(' ',lpad(round(if(d.diff='S',d.qte1,d.qte2) ,2),11,'0'))
        ,concat(' ',lpad(round(if(d.diff='S',d.taux1,d.taux2) ,3),11,'0'))
        ,concat(' ',lpad(round(if(d.diff='S',d.val1,d.val2) ,2),11,'0'))
        ,' '          			-- signe
    		,rpad(' ',30,' ') 			-- lib
    		,'      ||      | |    |   |          |0|5|      |   |      |   |      | | ||'
  		) as ligne
    from pai_ev_diff d
    where d.diff<>'='
  union
    select distinct 
      d.idtrt1,
      d.idtrt2,
      concat_ws('|',rpad(trim(d.matricule),8,'Z'),d.rc,'3'),
      '#FIN_TRS' 
    from pai_ev_diff d
    where d.diff<>'='
    order by 1,2
  ;