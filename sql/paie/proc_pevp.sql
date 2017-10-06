/*



call INT_MROAD2PEVP_INSERT('20161015');
call INT_MROAD2PEVP_CLOTURE('20161015');

select * from pai_pevp_tournee;
select * from pai_tournee where depot_id=22;
    select anneemois from pai_ref_mois pm where str_to_date('20161015','%Y%m%d') between pm.date_debut and pm.date_fin;
    select * from pai_int_traitement order by id desc
    update pai_int_traitement set statut='E' where id in (7658,7657)

commit;


*/
-- ------------------------------------------------------------------------------------------------------------------------------------------------
-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS INT_MROAD2PEVP_INSERT;
CREATE PROCEDURE INT_MROAD2PEVP_INSERT(
    IN 		_date_paie    varchar(8)
) BEGIN
  DECLARE _anneemois varchar(6);
  DECLARE _idtrt int;
    select anneemois into _anneemois from pai_ref_mois pm where str_to_date(_date_paie,'%Y%m%d') between pm.date_debut and pm.date_fin;
    
    CALL int_logdebutanneemois(0,_idtrt,'INT_MROAD2PEVP_INSERT',null,null, null, _anneemois);

    call int_mroad2pevp_insert_tournee(_idtrt, _anneemois);
    call int_mroad2pevp_insert_produit(_idtrt, _anneemois);
    call int_mroad2pevp_insert_reclamation(_idtrt, _anneemois);
    call int_mroad2pevp_insert_incident(_idtrt, _anneemois);

    CALL int_logfin2(_idtrt,'INT_MROAD2PEVP_INSERT');
end;

-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2pevp_insert_tournee;
CREATE PROCEDURE int_mroad2pevp_insert_tournee(
    IN 		_idtrt		    INT,
    IN 		_anneemois varchar(6)
) BEGIN
    delete from pai_pevp_tournee;
    insert into pai_pevp_tournee(tournee_id,date_distrib,employe_id,produit_id,type_id,qte)
    Select	  
      pt.id as tournee_id,
      pt.date_distrib,
      pt.employe_id,
      ppt.produit_id,
      p.type_id,
      sum(ppt.qte) as qte
    FROM pai_ref_mois pm 
    inner join pai_tournee pt on  pt.date_distrib between pm.date_debut and pm.date_fin
    inner join emp_pop_depot epd on pt.employe_id=epd.employe_id and epd.population_id=-1
    inner join pai_prd_tournee ppt on  pt.id=ppt.tournee_id
    inner join produit p on ppt.produit_id=p.id and p.type_id in (1,2,3)
    where pm.anneemois=_anneemois
    and pt.date_extrait is null
    and (pt.tournee_org_id is null or pt.split_id is not null)
    and not exists(SELECT NULL FROM pai_journal pj,pai_ref_erreur pe where pt.id=pj.tournee_id and pj.erreur_id=pe.id and pe.valide=0)
    group by pt.id,pt.date_distrib, pt.employe_id, ppt.produit_id;

  call int_logrowcount_C(_idtrt,4,'int_mroad2pevp_insert_tournee','Tournées / produits insérés');
end;


-- ------------------------------------------------------------------------------------------------------------------------------------------------
-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2pevp_insert_produit;
CREATE PROCEDURE int_mroad2pevp_insert_produit(
    IN 		_idtrt		    INT,
    IN 		_anneemois varchar(6)
) BEGIN
    delete from pai_pevp_produit;
    insert into pai_pevp_produit(produit_id,libelle,taux)
     SELECT DISTINCT
           p.id
          ,p.libelle
          ,coalesce(prpp.valeur,prpt.valeur)*1000
        FROM pai_pevp_tournee pt
        INNER JOIN produit p ON p.id=pt.produit_id
        left outer join pai_ref_poids prpp on pt.date_distrib between prpp.date_debut and prpp.date_fin
                                          and 0=prpp.typetournee_id
                                          and p.id=prpp.produit_id
        left outer join pai_ref_poids prpt on pt.date_distrib between prpt.date_debut and prpt.date_fin
                                          and 0=prpt.typetournee_id
                                          and p.type_id=prpt.produit_type_id and prpt.produit_id is null;
  call int_logrowcount_C(_idtrt,4,'int_mroad2pevp_insert_produit','Produits insérés');
end;
-- ------------------------------------------------------------------------------------------------------------------------------------------------
-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2pevp_insert_reclamation;
CREATE PROCEDURE int_mroad2pevp_insert_reclamation(
    IN 		_idtrt		    INT,
    IN 		_anneemois varchar(6)
) BEGIN
    delete from pai_pevp_reclamation;
    insert into pai_pevp_reclamation(reclamation_id,employe_id,nbrec_abonne,nbrec_diffuseur)
    SELECT DISTINCT
      pr.id,
      pt.employe_id,
      pr.nbrec_abonne,
      pr.nbrec_diffuseur
    FROM pai_reclamation pr
    INNER JOIN pai_tournee pt on pr.tournee_id=pt.id
    where pr.anneemois=_anneemois
    and pt.employe_id in (select distinct ppt.employe_id from pai_pevp_tournee ppt)
    and pr.date_extrait is null
    ;
  call int_logrowcount_C(_idtrt,4,'int_mroad2pevp_insert_reclamation','Réclamationss insérés');
end;

-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS INT_MROAD2PEVP_INSERT_INCIDENT;
CREATE PROCEDURE INT_MROAD2PEVP_INSERT_INCIDENT(
    IN 		_idtrt		    INT,
    IN 		_anneemois varchar(6)
) BEGIN
    delete from pai_pevp_incident;
    insert into pai_pevp_incident(incident_id,employe_id,date_distrib)
    SELECT DISTINCT
      pi.id,
      pi.employe_id,
      pi.date_distrib
    FROM pai_ref_mois pm 
    inner join pai_incident pi on  pi.date_distrib between pm.date_debut and pm.date_fin
    where pi.employe_id in (select distinct ppt.employe_id from pai_pevp_tournee ppt)
    and pi.date_extrait is null
    ;
  call int_logrowcount_C(_idtrt,4,'int_mroad2pevp_insert_incident','Incidents insérés');
end;

-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS INT_MROAD2PEVP_CLOTURE;
CREATE PROCEDURE INT_MROAD2PEVP_CLOTURE(
    IN 		_date_paie    varchar(8)
) BEGIN
  DECLARE _date_extrait datetime;
  DECLARE _anneemois varchar(6);
  DECLARE _idtrt int;
    select anneemois into _anneemois from pai_ref_mois pm where str_to_date(_date_paie,'%Y%m%d') between pm.date_debut and pm.date_fin;
    
    CALL int_logdebutanneemois(0,_idtrt,'INT_MROAD2PEVP_CLOTURE',null,null, null, _anneemois);
    SELECT date_debut INTO _date_extrait FROM pai_int_traitement WHERE id=_idtrt;

    call int_mroad2pevp_extrait_tournee(_idtrt, _date_extrait);
    call int_mroad2pevp_extrait_produit(_idtrt, _date_extrait);
    call int_mroad2pevp_extrait_reclamation(_idtrt, _date_extrait);
    call int_mroad2pevp_extrait_incident(_idtrt, _date_extrait);
    
    insert into pai_pevp_tournee_hst select _idtrt,ppt.* from pai_pevp_tournee ppt;
    insert into pai_pevp_produit_hst select _idtrt,ppp.* from pai_pevp_produit ppp;
    insert into pai_pevp_reclamation_hst select _idtrt,ppr.* from pai_pevp_reclamation ppr;
    insert into pai_pevp_incident_hst select _idtrt,ppi.* from pai_pevp_incident ppi;
   
  CALL int_logfin2(_idtrt,'INT_MROAD2PEVP_CLOTURE');
END;

-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2pevp_extrait_tournee;
CREATE PROCEDURE int_mroad2pevp_extrait_tournee(
    IN 		_idtrt		    INT,
    IN 		_date_extrait	datetime
) BEGIN
-- ATTENTION, les tournees splitées ne sont pas extraite, ni les invalides
  UPDATE pai_tournee pt
  INNER JOIN pai_pevp_tournee ppt on pt.id=ppt.tournee_id
  SET pt.date_extrait=_date_extrait 
  WHERE pt.date_extrait is null
  ;
  call int_logrowcount_C(_idtrt,4,'int_mroad2pevp_extrait_tournee','Tournées extraites');
  
  UPDATE pai_journal pj
  INNER JOIN pai_pevp_tournee ppt ON pj.tournee_id=ppt.tournee_id
  SET  pj.date_extrait=_date_extrait
  WHERE pj.date_extrait is null
  ;
  call int_logrowcount_C(_idtrt,4,'int_mroad2pevp_extrait_tournee','Tournées journal');
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2pevp_extrait_produit;
CREATE PROCEDURE int_mroad2pevp_extrait_produit(
    IN 		_idtrt		    INT,
    IN 		_date_extrait	datetime
) BEGIN
  UPDATE pai_prd_tournee ppt 
  INNER JOIN pai_pevp_tournee pt on pt.tournee_id=ppt.tournee_id
  inner join produit p on ppt.produit_id=p.id and p.type_id in (1,2,3)
  SET ppt.date_extrait=_date_extrait
  WHERE ppt.date_extrait is null
  ;
  call int_logrowcount_C(_idtrt,4,'int_mroad2pevp_extrait_produit','Produits extraits');
  
  UPDATE pai_journal pj
  INNER JOIN pai_prd_tournee ppt  on pj.produit_id=ppt.tournee_id
  SET  pj.date_extrait=_date_extrait
  WHERE ppt.date_extrait=_date_extrait
  and pj.date_extrait is null
  ;
  call int_logrowcount_C(_idtrt,4,'int_mroad2pevp_extrait_produit','Produits journal');
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2pevp_extrait_reclamation;
CREATE PROCEDURE int_mroad2pevp_extrait_reclamation(
    IN 		_idtrt		    INT,
    IN 		_date_extrait	datetime
) BEGIN
-- 29/11/2016 On extrait toutes les réclamations qui ont été sélectionnées dans int_mroad2pevp_insert_reclamation
  UPDATE pai_reclamation pr
  INNER JOIN pai_pevp_reclamation ppr on pr.id=ppr.reclamation_id
  SET pr.date_extrait=_date_extrait 
  WHERE pr.date_extrait is null
  ;
  call int_logrowcount_C(_idtrt,4,'int_mroad2pevp_extrait_reclamation','Réclamations extraites');
END;

-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2pevp_extrait_incident;
CREATE PROCEDURE int_mroad2pevp_extrait_incident(
    IN 		_idtrt		    INT,
    IN 		_date_extrait	datetime
) BEGIN
  UPDATE pai_incident pi
  INNER JOIN pai_pevp_incident ppi on pi.id=ppi.incident_id
  SET pi.date_extrait=_date_extrait 
  WHERE pi.date_extrait is null
  ;
  call int_logrowcount_C(_idtrt,4,'int_mroad2pevp_extrait_incident','Incidents extraits');
END;
/*
update pai_tournee pt
inner join employe e on pt.employe_id=e.id and e.matricule like 'MR%'
and pt.date_distrib>='2016-10-02'
set date_extrait=null

select * from pai_prd_tournee where tournee_id=1589602
select * from pai_int_traitement order by id desc
update pai_int_traitement set statut='E' where statut='C'
*/