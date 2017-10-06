
/*
set @idtrt=null;
CALL INT_PEPP2MROAD(0,@idtrt,'20141220 000000');
select * from pai_int_log where idtrt in(select max(id) from pai_int_traitement) order by id desc;
select * from emp_cycle;
*/

/*
drop table int_mroad;
create table int_mroad(matricule varchar(10), date_debut varchar(8), date_fin varchar(8),eta varchar(3), employe_id int);
insert into int_mroad select distinct cb,to_char(cf,'YYYYMMDD'),to_char(cff,'YYYYMMDD'),eta,null from perzprpl_intdcs where eta in ('28','29','40','41','42') and cff>=to_date('20141021','YYYYMMDD');
update int_mroad i set employe_id=(select id from employe e where e.matricule=i.matricule);
select * from int_mroad where employe_id is not null;
delete int_mroad where employe_id is null;
commit;

-- alter table activite add idmroad int;
-- initialisation de la transco activit�
select * from pai_pepp_tournees;

update activite a
set idmroad=(select ma.id
            from ref_activite ma 
            where a.codeactivite=ma.code)
where a.codeactivite in (select h.codeactivite from heures h where h.extrait='N')
;
*/

-- insert into ref_typetournee(id,code, libelle, societe_id, population_id) values(4,'PEP','Pepp',1,1);
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS INT_PEPP2MROAD;
CREATE PROCEDURE INT_PEPP2MROAD(
  IN 		_utilisateur_id INT,
  INOUT _idtrt		      INT,
  IN    _date_extrait		VARCHAR(15)
) BEGIN
DECLARE CONTINUE  HANDLER FOR SQLWARNING    CALL int_logwarning(_idtrt);
DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN
  ROLLBACK;
  CALL int_logerreur(_idtrt);
END;
        
  CALL int_logdebut(_utilisateur_id,_idtrt,'PEPP2MROAD',null,null,null);
  commit;
  update pai_pepp_tournees_detail td
  inner join pai_pepp_tournees t on td.datetr=t.datetr and td.codetr=t.codetr
  set td.nbrspl=td.nbrcli, td.nbrcli=0,td.nbrex=0
  where codetitre=99 and td.nbrcli=td.nbrex and td.nbrcli<>0
  and t.dextrait=_date_extrait
  ;
  
  CALL int_pepp2mroad_erreur(_idtrt,_date_extrait);
  -- ATTENTION, pb si plusieurs transfert dans le même mois !!!!!
--  CALL int_pepp2mroad_nettoyage(_idtrt,_date_extrait);
  CALL int_pepp2mroad_exec(_idtrt,_date_extrait);
/*
  call int_logger(_idtrt,'INT_OCT2MROAD','Validation des tourn�es');
  call pai_valide(_idtrt,null,null);
*/
  call pai_valide_pepp_activite(_idtrt);
  
  call recalcul_pepp_produit(_idtrt);
  call recalcul_pepp_tournee(_idtrt);
  call pai_valide_pepp_tournee(_idtrt);
  call recalcul_pepp_majoration(_idtrt);
  CALL int_logfin(_idtrt);
END;

-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_pepp2mroad_erreur;
CREATE PROCEDURE int_pepp2mroad_erreur(
  IN    _idtrt		      INT,
  IN    _date_extrait		VARCHAR(15)
) BEGIN
declare nberreur INTEGER DEFAULT 0;
  -- transco activit� manquante
  insert into pai_int_log(idtrt,date_log,module,msg,level)
  select distinct _idtrt,sysdate(),'ERREUR',concat_ws(' ',a.codeactivite,a.libelle,': transco activité manquante'),0
  from pai_pepp_heures h
  left outer join pai_pepp_transco_activite a on a.codeactivite=h.codeactivite
  where a.activite_id is null
  and h.dextrait=_date_extrait
  ;
  set nberreur = nberreur+row_count();

  -- groupe manquant
  insert into pai_int_log(idtrt,date_log,module,msg,level)
  select distinct _idtrt,sysdate(),'ERREUR',concat_ws(' ',t.datetr,t.codetr,': groupe manquant'),0
  from pai_pepp_tournees t
  left outer join depot d on lpad(trim(t.eta),3,'0')=d.code
  left outer join groupe_tournee g on g.depot_id=d.id and g.code=concat(char(64+d.id),substr(t.codetr,3,1))
  where (g.id is null)
  and t.dextrait=_date_extrait
  ;
  set nberreur = nberreur+row_count();

-- modele manquant
  insert into pai_int_log(idtrt,date_log,module,msg,level)
  select distinct _idtrt,sysdate(),'ERREUR',concat_ws(' ',t.datetr,t.codetr,g.id,': modele manquant'),0
  from pai_pepp_tournees t
  inner join depot d on lpad(trim(t.eta),3,'0')=d.code
  inner join groupe_tournee g on g.depot_id=d.id and g.code=concat(char(64+d.id),substr(t.codetr,3,1))
  left outer join modele_tournee mt on mt.code=concat(d.code,'N',lpad(g.code,2,' '),'0',substr(t.codetr,4,2))
  where (mt.id is null)
  and t.dextrait=_date_extrait
  ;
  set nberreur = nberreur+row_count();

-- modele jour manquant
  insert into pai_int_log(idtrt,date_log,module,msg,level)
  select distinct _idtrt,sysdate(),'WARNING',concat_ws(' ',t.datetr,t.codetr,g.id,mt.id,': modele jour manquant'),0
  from pai_pepp_tournees t
  inner join depot d on lpad(trim(t.eta),3,'0')=d.code
  inner join groupe_tournee g on g.depot_id=d.id and g.code=concat(char(64+d.id),substr(t.codetr,3,1))
  inner join modele_tournee mt on mt.code=concat(d.code,'N',lpad(g.code,2,' '),'0',substr(t.codetr,4,2))
  left outer join modele_tournee_jour mtj on mt.id=mtj.tournee_id and mtj.jour_id=DAYOFWEEK(STR_TO_DATE(datetr,'%Y%m%d'))
                  and STR_TO_DATE(datetr,'%Y%m%d') between mtj.date_debut and mtj.date_fin
  where (mtj.id is null)
  and t.dextrait=_date_extrait
  ;
  set nberreur = nberreur+row_count();

  -- produits
  insert into pai_int_log(idtrt,date_log,module,msg,level)
  select distinct _idtrt,sysdate(),'ERREUR',concat_ws(' ',td.codetitre,ti.libelle,': transco titre manquante'),0
  from pai_pepp_tournees_detail td
  inner join pai_pepp_tournees t on td.datetr=t.datetr and td.codetr=t.codetr
  left outer join pai_pepp_transco_titre ti on td.codetitre=ti.codetitre
  left outer join produit p on ti.produit_id=p.id
  where (td.nbrcli<>0 or td.nbrex<>0)
  and p.id is null
  and t.dextrait=_date_extrait
  group by ti.codetitre,ti.libelle
  ;
  set nberreur = nberreur+row_count();

  -- suppl�ment
  insert into pai_int_log(idtrt,date_log,module,msg,level)
  select distinct _idtrt,sysdate(),'ERREUR',concat_ws(' ',td.codetitre,ti.libelle,': transco supplement manquante'),0
  from pai_pepp_tournees_detail td
  inner join pai_pepp_tournees t on td.datetr=t.datetr and td.codetr=t.codetr
  left outer join pai_pepp_transco_titre ti on td.codetitre=ti.codetitre
  left outer join produit s on ti.supplement_id=s.id
  where (td.nbrspl<>0)
  and s.id is null
  and t.dextrait=_date_extrait
  group by ti.codetitre,ti.libelle
  having sum(td.nbrspl)<>0
  ;
  set nberreur = nberreur+row_count();

  -- suppl�ment
  insert into pai_int_log(idtrt,date_log,module,msg,level)
  select distinct _idtrt,sysdate(),'ERREUR',concat_ws(' ',pph.datetr,pph.eta,substr(pph.codetr,1,3),': plusieurs temps d''attente'),0
  from pai_pepp_heures pph
  where pph.codeactivite='AT' 
  and pph.dextrait=_date_extrait
  group by pph.datetr,pph.eta,substr(pph.codetr,1,3),pph.dextrait
  having count(distinct pph.temps1)>1
  ;
  set nberreur = nberreur+row_count();
  
  
  if (nberreur>0) then
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Transco manquante, consultez la log';
  end if;
  /*
  select distinct td.codetitre,ti.libelle,if(sum(td.nbrcli)=0,'---',p.id) idtitre,if(sum(td.nbrcli)=0,'---',p.libelle) titre,if(sum(td.nbrspl)=0,'---',s.id) idsupplement,if(sum(td.nbrspl)=0,'---',s.libelle) supplement
  from pai_pepp_tournees_detail td
  inner join pai_pepp_tournees t on td.datetr=t.datetr and td.codetr=t.codetr
  left outer join pai_pepp_transco_titre ti on td.codetitre=ti.codetitre
  left outer join produit p on ti.produit_id=p.id
  left outer join produit s on ti.supplement_id=s.id
  where (td.nbrcli<>0 or td.nbrex<>0 or nbrspl<>0)
--  and t.dextrait=_date_extrait
  group by ti.codetitre,ti.libelle,p.id,p.libelle,s.id,s.libelle
  ;
  */
END;


-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_pepp2mroad_exec;
CREATE PROCEDURE int_pepp2mroad_exec(
  IN    _idtrt		      INT,
  IN    _date_extrait		VARCHAR(15)
) BEGIN
  call int_pepp2mroad_exec_activite(_idtrt,_date_extrait);
  call int_pepp2mroad_exec_heure(_idtrt,_date_extrait);
  call int_pepp2mroad_exec_tournee(_idtrt,_date_extrait);
  call int_pepp2mroad_exec_produit(_idtrt,_date_extrait);
  call int_pepp2mroad_exec_supplement(_idtrt,_date_extrait);
  call int_pepp2mroad_exec_attente(_idtrt,_date_extrait);
  call int_pepp2mroad_exec_reclamation(_idtrt,_date_extrait);
  call int_pepp2mroad_exec_incident(_idtrt,_date_extrait);
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_pepp2mroad_nettoyage;
CREATE PROCEDURE int_pepp2mroad_nettoyage(
  IN    _idtrt		      INT,
  IN    _date_extrait		VARCHAR(15)
) BEGIN
  delete from pai_journal where activite_id in (select id from pai_activite where date_extrait is null and commentaire like 'PEPP%');
  call int_logrowcount_C(_idtrt,5,'int_pepp2mroad_nettoyage','pai_journal pai_activite');
  delete from pai_journal where tournee_id in (select id from pai_tournee where date_extrait is null and typetournee_id=4);
  call int_logrowcount_C(_idtrt,5,'int_pepp2mroad_nettoyage','pai_journal pai_tournee');
  delete from pai_journal where produit_id in (select id from pai_prd_tournee where date_extrait is null and tournee_id in (select id from pai_tournee where date_extrait is null and typetournee_id=4));
  call int_logrowcount_C(_idtrt,5,'int_pepp2mroad_nettoyage','pai_journal pai_prd_tournee');
--  truncate table prd_caract_tournee;
  delete from pai_activite where date_extrait is null and commentaire like 'PEPP%';
  call int_logrowcount_C(_idtrt,5,'int_pepp2mroad_nettoyage','pai_activite');
  delete from pai_prd_tournee where date_extrait is null and tournee_id in (select id from pai_tournee where date_extrait is null and typetournee_id=4);
  call int_logrowcount_C(_idtrt,5,'int_pepp2mroad_nettoyage','pai_prd_tournee');
  delete from pai_reclamation where date_extrait is null and tournee_id in (select id from pai_tournee where date_extrait is null and typetournee_id=4);
  call int_logrowcount_C(_idtrt,5,'int_pepp2mroad_nettoyage','pai_reclamation');
  delete from pai_incident where date_extrait is null and tournee_id in (select id from pai_tournee where date_extrait is null and typetournee_id=4);
  call int_logrowcount_C(_idtrt,5,'int_pepp2mroad_nettoyage','pai_incident');
  delete from pai_tournee where date_extrait is null and typetournee_id=4;
  call int_logrowcount_C(_idtrt,5,'int_pepp2mroad_nettoyage','pai_tournee');
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_pepp2mroad_exec_activite;
CREATE PROCEDURE int_pepp2mroad_exec_activite(
  IN    _idtrt		      INT,
  IN    _date_extrait		VARCHAR(15)
) BEGIN
-- transfert des activites sauf celles li�es aux tourn�es
  insert into pai_activite(depot_id,activite_id,employe_id,transport_id,utilisateur_id,date_distrib,heure_debut,duree,nbkm_paye,flux_id,commentaire,tournee_id)
  select 
    d.id,-- depot_id,
    a.activite_id,-- activite_id,
    e.id,-- employe_id,
    3,-- transport_id,
    0,-- utilisateur_id,
    STR_TO_DATE(datetr,'%Y%m%d'),-- date_distrib,
    sec_to_time((6-temps2)*3600),-- heure_debut,
    sec_to_time(h.temps1*3600),-- duree,
    nbkm,-- nbkm_paye,
    1,-- flux_id,
    concat_ws(' ','PEPP :',commentaire),-- commentaire,
    null-- tournee_id
  from pai_pepp_heures h
  left outer join employe e on e.matricule=h.mat
  left outer join depot d on trim(h.eta)=substr(d.code,2,2)
  inner join pai_pepp_transco_activite a on a.codeactivite=h.codeactivite
  where h.codetr=''
  and h.dextrait=_date_extrait
  ;
  call int_logrowcount_C(_idtrt,5,'int_pepp2mroad_exec_activite','')
  ;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_pepp2mroad_exec_heure;
CREATE PROCEDURE int_pepp2mroad_exec_heure(
  IN    _idtrt		      INT,
  IN    _date_extrait		VARCHAR(15)
) BEGIN
  insert into pai_heure(groupe_id,utilisateur_id,date_distrib,heure_debut,heure_debut_theo,duree_attente)
  select distinct 
  g.id,-- groupe_id,
  0,-- utilisateur_id,
  STR_TO_DATE(t.datetr,'%Y%m%d'),-- date_distrib,
  g.heure_debut,-- heure_debut,
  g.heure_debut,-- heure_debut_theo,
  '00:00:00'-- duree_attente
  from pai_pepp_tournees t
  left outer join depot d on lpad(trim(t.eta),3,'0')=d.code
  left outer join groupe_tournee g on g.depot_id=d.id and g.code=concat(char(64+d.id),substr(t.codetr,3,1))
  where not exists(select null from pai_heure h where h.groupe_id=g.id and h.date_distrib=STR_TO_DATE(t.datetr,'%Y%m%d'))
  and t.dextrait=_date_extrait
  ;
  call int_logrowcount_C(_idtrt,5,'int_pepp2mroad_exec_heure','')
  ;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_pepp2mroad_exec_tournee;
CREATE PROCEDURE int_pepp2mroad_exec_tournee(
  IN    _idtrt		      INT,
  IN    _date_extrait		VARCHAR(15)
) BEGIN
  insert into pai_tournee(depot_id,groupe_id,employe_id,transport_id,utilisateur_id,date_distrib,valrem,duree_nuit,nbkm,nbkm_paye,flux_id,typetournee_id,code,majoration,modele_tournee_jour_id,duree_retard,heure_debut,heure_id,ordre) 
  select distinct 
  d.id,-- depot_id,
  g.id,-- groupe_id,
  e.id,-- employe_id,
  3,-- transport_id,
  0,-- utilisateur_id,
  STR_TO_DATE(t.datetr,'%Y%m%d'),-- date_distrib,
  t.valrem,-- valrem,
--  t.temps,-- duree,
--  concat_ws(':',lpad(truncate(h.tnuit,0),2,'0'),lpad(round((h.tnuit-truncate(h.tnuit,0))*60),2,'0')),-- duree,
  sec_to_time(t.tnuit*3600),-- duree_nuit,
  t.nbkm2,-- nbkm,
  t.nbkm,-- nbkm_paye,
  1,-- flux_id,
  4,-- typetournee_id,
  t.codetr,-- code,
  0,-- majoration,
  mtj.id,-- modele_tournee_jour_id,
  '00:00:00',-- duree_retard,
  g.heure_debut,-- heure_debut,
  ph.id,-- heure_id,
  1-- ordre
  from pai_pepp_tournees t
  left outer join employe e on e.matricule=t.mat
  left outer join depot d on lpad(trim(t.eta),3,'0')=d.code
  left outer join groupe_tournee g on g.depot_id=d.id and g.code=concat(char(64+d.id),substr(t.codetr,3,1))
  left outer join modele_tournee mt on mt.code=concat(d.code,'N',lpad(g.code,2,' '),'0',substr(t.codetr,4,2))
  left outer join modele_tournee_jour mtj on mt.id=mtj.tournee_id and mtj.jour_id=DAYOFWEEK(STR_TO_DATE(datetr,'%Y%m%d')) and STR_TO_DATE(t.datetr,'%Y%m%d') between mtj.date_debut and mtj.date_fin
  left outer join pai_heure ph on ph.groupe_id=g.id and STR_TO_DATE(t.datetr,'%Y%m%d')=ph.date_distrib
  where t.dextrait=_date_extrait
  ;
  call int_logrowcount_C(_idtrt,5,'int_pepp2mroad_exec_tournee','')
  ;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_pepp2mroad_exec_attente;
CREATE PROCEDURE int_pepp2mroad_exec_attente(
  IN    _idtrt		      INT,
  IN    _date_extrait		VARCHAR(15)
) BEGIN
-- transfert des activites sauf celles li�es aux tourn�es
/*  insert into pai_activite(depot_id,activite_id,employe_id,transport_id,utilisateur_id,date_distrib,heure_debut,duree,nbkm_paye,flux_id,commentaire,tournee_id)
  select 
    d.id,-- depot_id,
    -2,-- activite_id,
    e.id,-- employe_id,
    3,-- transport_id,
    0,-- utilisateur_id,
    STR_TO_DATE(datetr,'%Y%m%d'),-- date_distrib,
    g.heure_debut,-- heure_debut,
    sec_to_time(t.tatt*3600),-- duree,
    0,-- nbkm_paye,
    1,-- flux_id,
    'PEPP Attente',-- commentaire,
    pt.id-- tournee_id
  from pai_pepp_tournees t
  inner join pai_tournee pt on pt.date_distrib=STR_TO_DATE(datetr,'%Y%m%d') and pt.code=t.codetr and pt.typetournee_id=4
  left outer join employe e on e.matricule=t.mat
  left outer join depot d on trim(t.eta)=substr(d.code,2,2)
  left outer join groupe_tournee g on g.depot_id=d.id and g.code=concat(char(64+d.id),substr(t.codetr,3,1))
  where t.tatt<>0
  and t.dextrait=_date_extrait
  ;*/
  update pai_pepp_heures pph
  inner join depot d on lpad(trim(pph.eta),3,'0')=d.code
  inner join groupe_tournee g on g.depot_id=d.id and g.code=concat(char(64+d.id),substr(pph.codetr,3,1))
  inner join pai_heure ph on ph.groupe_id=g.id and ph.date_distrib=STR_TO_DATE(datetr,'%Y%m%d')
  set ph.duree_attente=sec_to_time(pph.temps1*3600)
  where pph.codeactivite='AT' 
  and  pph.dextrait=_date_extrait
  ;
  call int_logrowcount_C(_idtrt,5,'int_pepp2mroad_exec_attente','')
  ;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_pepp2mroad_exec_produit;
CREATE PROCEDURE int_pepp2mroad_exec_produit(
  IN    _idtrt		      INT,
  IN    _date_extrait		VARCHAR(15)
) BEGIN
  insert into pai_prd_tournee(tournee_id,produit_id,natureclient_id,utilisateur_id,qte,nbcli,nbadr,nbcli_unique,nbrep)
  select distinct 
  pt.id,-- tournee_id,
  ti.produit_id,-- produit_id,
  if(td.codenatcli='A',0,1),-- natureclient_id,
  0,-- utilisateur_id,
  sum(td.nbrex),-- qte,
  sum(td.nbrcli),-- nbcli,
  0,-- nbadr,
  0,-- nbcli_unique,
  0-- nbrep
  from pai_pepp_tournees t
  inner join pai_pepp_tournees_detail td on t.datetr=td.datetr and t.codetr=td.codetr
  inner join pai_pepp_transco_titre ti on td.codetitre=ti.codetitre
  left outer join pai_tournee pt on STR_TO_DATE(t.datetr,'%Y%m%d')=pt.date_distrib and t.codetr=pt.code and pt.typetournee_id=4
  where (td.nbrcli<>0 or td.nbrex<>0)
  and t.dextrait=_date_extrait
  group by pt.id,ti.produit_id,if(td.codenatcli='A',0,1)
  ;
  call int_logrowcount_C(_idtrt,5,'int_pepp2mroad_exec_produit','')
  ;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_pepp2mroad_exec_supplement;
CREATE PROCEDURE int_pepp2mroad_exec_supplement(
  IN    _idtrt		      INT,
  IN    _date_extrait		VARCHAR(15)
) BEGIN
  insert into pai_prd_tournee(tournee_id,produit_id,natureclient_id,utilisateur_id,qte,nbcli,nbadr,nbcli_unique,nbrep) 
  select distinct 
  pt.id,-- tournee_id,
  ti.supplement_id,-- produit_id,
  if(td.codenatcli='A',0,1),-- natureclient_id,
  0,-- utilisateur_id,
  sum(td.nbrspl),-- qte,
  0,-- nbcli,
  0,-- nbadr,
  0,-- nbcli_unique,
  0-- nbrep
  from pai_pepp_tournees t
  inner join pai_pepp_tournees_detail td on t.datetr=td.datetr and t.codetr=td.codetr
  inner join pai_pepp_transco_titre ti on td.codetitre=ti.codetitre
  left outer join pai_tournee pt on STR_TO_DATE(t.datetr,'%Y%m%d')=pt.date_distrib and t.codetr=pt.code and pt.typetournee_id=4
  where (td.nbrspl<>0)
  and t.dextrait=_date_extrait
  and ti.supplement_id is not null
  group by pt.id,ti.produit_id,if(td.codenatcli='A',0,1)
  ;
  call int_logrowcount_C(_idtrt,5,'int_pepp2mroad_exec_supplement','')
  ;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_pepp2mroad_exec_reclamation;
CREATE PROCEDURE int_pepp2mroad_exec_reclamation(
  IN    _idtrt		      INT,
  IN    _date_extrait		VARCHAR(15)
) BEGIN
  insert into pai_reclamation(tournee_id, anneemois, nbrec_abonne, nbrec_diffuseur, date_extrait, date_creation, type_id) 
  select distinct 
  pt.id,-- tournee_id,
  pm.anneemois,-- 'anneemois'
  sum(r.nbrecab-r.nbannab), -- nbrec_abonne
  sum(r.nbrecdif-r.nbanndif), -- nbrec_diffuseur
  null,-- 'date_extrait'
  sysdate()-- 'date_creation'
  ,3
  from pai_pepp_tournees t
  inner join pai_pepp_reclamations r on t.datetr=r.datetr and t.codetr=r.codetr
  inner join pai_mois pm on pm.flux_id=1
  left outer join pai_tournee pt on STR_TO_DATE(t.datetr,'%Y%m%d')=pt.date_distrib and t.codetr=pt.code and pt.typetournee_id=4
  where t.dextrait=_date_extrait
  group by pt.id
  having sum(r.nbrecab-r.nbannab)<>0 or sum(r.nbrecdif-r.nbanndif)<>0
  ;
  call int_logrowcount_C(_idtrt,5,'int_pepp2mroad_exec_reclamation','')
  ;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_pepp2mroad_exec_incident;
CREATE PROCEDURE int_pepp2mroad_exec_incident(
  IN    _idtrt		      INT,
  IN    _date_extrait		VARCHAR(15)
) BEGIN
  insert into pai_incident(date_distrib,employe_id, incident_id, commentaire, utilisateur_id, date_creation, date_modif, date_extrait) 
  select distinct 
  STR_TO_DATE(t.datetr,'%Y%m%d'),
  e.id,-- tournee_id,
  0,-- incident_id
  'PEPP',-- 'commentaire'
  0,-- utilisateur_id
  sysdate(),-- 'date_creation'
  null,-- 'date_modif'
  null-- 'date_extrait'
  from pai_pepp_tournees t
  left outer join employe e on e.matricule=t.mat
  inner join pai_pepp_reclamations r on t.datetr=r.datetr and t.codetr=r.codetr
  where t.dextrait=_date_extrait
  group by t.datetr,e.id
  having sum(r.indicinc-r.anninc)<>0
  ;
  call int_logrowcount_C(_idtrt,5,'int_pepp2mroad_exec_incident','')
  ;
END;
/*
-- suppl�ment
select id from pai_tournee@QMROAD where id=498937;
select date_distrib from pai_tournee@QMROAD where id=498937;
select date_creation from pai_tournee@QMROAD where id=498937;
select t2s@QMROAD(date_distrib) from pai_heure@QMROAD;
rollback;
alter session set HS_FDS_DATE_MAPPING=DATE;
*/


DROP PROCEDURE IF EXISTS recalcul_pepp_produit;
CREATE PROCEDURE recalcul_pepp_produit(
  IN    _idtrt		      INT
) BEGIN
    UPDATE pai_prd_tournee ppt
    inner join pai_tournee pt on ppt.tournee_id=pt.id
    inner join emp_pop_depot epd on pt.employe_id=epd.employe_id and pt.date_distrib between epd.date_debut and epd.date_fin
    inner join produit p on ppt.produit_id=p.id
    inner join prd_caract pc on p.type_id=pc.produit_type_id and pc.code='POIDS'
    left outer join prd_caract_jour pcj on ppt.produit_id=pcj.produit_id and pcj.prd_caract_id=pc.id and pt.date_distrib = pcj.date_distrib 
    left outer join prd_caract_groupe pcg on ppt.produit_id=pcg.produit_id and pcg.prd_caract_id=pc.id and pt.groupe_id=pcg.groupe_id and pt.date_distrib=pcg.date_distrib
    -- r�mun�ration au niveau du produit
    left outer join pai_ref_poids prpp on pt.date_distrib between prpp.date_debut and prpp.date_fin 
                                      and epd.typetournee_id=prpp.typetournee_id 
                                      and p.id=prpp.produit_id
                                      -- pour SDVP on ne tient compte du poids
                                      -- and ((epd.typetournee_id=1 and 0=prpp.borne_inf)
                                      -- pour N�o/M�dia on ne tient pas compte du poids
                                      -- or   (epd.typetournee_id=2 and coalesce(pcg.valeur_int,pcj.valeur_int) between prpp.borne_inf and prpp.borne_sup))
    -- r�mun�ration au niveau du type
    left outer join pai_ref_poids prpt on pt.date_distrib between prpt.date_debut and prpt.date_fin
                                      and epd.typetournee_id=prpt.typetournee_id
                                      and p.type_id=prpt.produit_type_id
                                      -- pour SDVP on ne tient compte du poids
                                      -- and ((epd.typetournee_id=1 and 0=prpt.borne_inf)
                                      -- pour N�o/M�dia on ne tient pas compte du poids
                                      -- or   (epd.typetournee_id=2 and coalesce(pcg.valeur_int,pcj.valeur_int) between prpt.borne_inf and prpt.borne_sup))
    left outer join pai_ref_remuneration prr on epd.societe_id=prr.societe_id and epd.population_id = prr.population_id
                                      and pt.date_distrib between prr.date_debut and prr.date_fin
    SET ppt.poids=ppt.qte*coalesce(coalesce(pcg.valeur_int,pcj.valeur_int),0)
    ,   ppt.duree_supplement= CASE WHEN p.type_id<>1 AND prr.valeur is not null THEN
                                coalesce(
                                sec_to_time(ppt.qte*coalesce(prpp.valeur,prpt.valeur)/prr.valeur*3600)
                                -- +prpp.quantite*prpp.valeur_unite
                                --  floor(ppt.qte/prpp.quantite)*prpp.valeur+prpp.quantite*prpp.valeur_unite
                                ,'00:00')
                            ELSE
                                '00:00'
                            END
    ,   ppt.pai_qte=ppt.qte
    ,   ppt.pai_taux=coalesce(prpp.valeur,prpt.valeur)
    ,   ppt.pai_mnt=round(ppt.qte*coalesce(prpp.valeur,prpt.valeur),2)
    WHERE ppt.date_extrait is null
    and pt.typetournee_id=4
    ;
  call int_logrowcount_C(_idtrt,5,'recalcul_pepp_produit','')
  ;
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_pepp_tournee;
CREATE PROCEDURE recalcul_pepp_tournee(
  IN    _idtrt		      INT
) BEGIN
    UPDATE pai_tournee pt
    SET pt.nbcli	=COALESCE((SELECT SUM(ppt.nbcli) FROM pai_prd_tournee ppt INNER JOIN produit p ON ppt.produit_id=p.id WHERE ppt.tournee_id=pt.id AND p.type_id not in (2,3)),0)
    , pt.nbcli_unique=COALESCE((SELECT SUM(ppt.nbcli_unique) FROM pai_prd_tournee ppt INNER JOIN produit p ON ppt.produit_id=p.id WHERE ppt.tournee_id=pt.id AND p.type_id not in (2,3)),0)
    , pt.nbadr	=COALESCE((SELECT SUM(ppt.nbadr) FROM pai_prd_tournee ppt INNER JOIN produit p ON ppt.produit_id=p.id WHERE ppt.tournee_id=pt.id AND p.type_id not in (2,3)),0)
    , pt.nbrep	=COALESCE((SELECT SUM(ppt.nbrep) FROM pai_prd_tournee ppt INNER JOIN produit p ON ppt.produit_id=p.id WHERE ppt.tournee_id=pt.id AND p.type_id in (1)),0)
    , pt.nbtitre	=COALESCE((SELECT SUM(ppt.qte) FROM pai_prd_tournee ppt INNER JOIN produit p ON ppt.produit_id=p.id WHERE ppt.tournee_id=pt.id AND p.type_id IN (1)),0)
    , pt.nbspl	=COALESCE((SELECT SUM(ppt.qte) FROM pai_prd_tournee ppt INNER JOIN produit p ON ppt.produit_id=p.id WHERE ppt.tournee_id=pt.id AND p.type_id IN (2,3)),0)
    , pt.nbprod	=COALESCE((SELECT SUM(ppt.qte) FROM pai_prd_tournee ppt INNER JOIN produit p ON ppt.produit_id=p.id WHERE ppt.tournee_id=pt.id AND p.type_id NOT IN (1,2,3)),0)
    , pt.duree_supplement=COALESCE((select sec_to_time(sum(time_to_sec(ppt.duree_supplement))) from pai_prd_tournee ppt INNER JOIN produit p ON ppt.produit_id=p.id WHERE ppt.tournee_id=pt.id AND p.type_id<>1),0)
    , pt.poids	=COALESCE((SELECT SUM(ppt.poids) FROM pai_prd_tournee ppt WHERE ppt.tournee_id=pt.id),0)
    WHERE pt.date_extrait is null
    and pt.typetournee_id=4
    ;
  call int_logrowcount_C(_idtrt,5,'recalcul_pepp_tournee','')
  ;
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_pepp_majoration;
CREATE PROCEDURE `recalcul_pepp_majoration`(
  IN    _idtrt		      INT
) BEGIN
  DECLARE v_finished INTEGER DEFAULT 0;
  DECLARE _date_distrib DATE;
  DECLARE _employe_id INT;
  DECLARE _validation_id INT;
 
  -- declare cursor for employee email
  DEClARE _cursor CURSOR FOR
  SELECT pt.employe_id,pt.date_distrib
  FROM pai_tournee pt
  WHERE pt.typetournee_id=4
  AND pt.date_extrait is null
  AND employe_id is not null;
   
  -- declare NOT FOUND handler
  DECLARE CONTINUE HANDLER
  FOR NOT FOUND SET v_finished = 1;

  IF (_validation_id IS NULL) THEN
		INSERT INTO pai_validation(utilisateur_id) VALUES(1);
		SELECT LAST_INSERT_ID() INTO _validation_id;
  END IF;
     
  OPEN _cursor;
  _loop: LOOP
    FETCH _cursor INTO _employe_id,_date_distrib;
    IF v_finished = 1 THEN
      LEAVE _loop;
    END IF;
    call int_logrowcount_C(_idtrt,5,'recalcul_pepp_majoration',_employe_id)
    ;
    call recalcul_majoration(_date_distrib, null, null, _employe_id);
-- On ne recalcule pas les heures de nuit, on garde celle du Pepp
    call recalcul_horaire(_validation_id, null, null,_date_distrib, _employe_id);
  END LOOP _loop;
  CLOSE _cursor;
END;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS pai_valide_pepp_tournee;
CREATE PROCEDURE `pai_valide_pepp_tournee`(
  IN    _idtrt		      INT
) BEGIN
  DECLARE v_finished INTEGER DEFAULT 0;
  DECLARE _tournee_id INT;
  DECLARE _validation_id INT;
 
  -- declare cursor for employee email
  DEClARE _cursor CURSOR FOR
  SELECT pt.id
  FROM pai_tournee pt
  WHERE pt.typetournee_id=4
  AND pt.date_extrait is null;
   
  -- declare NOT FOUND handler
  DECLARE CONTINUE HANDLER
  FOR NOT FOUND SET v_finished = 1;

  IF (_validation_id IS NULL) THEN
		INSERT INTO pai_validation(utilisateur_id) VALUES(1);
		SELECT LAST_INSERT_ID() INTO _validation_id;
  END IF;
     
  OPEN _cursor;
  _loop: LOOP
    FETCH _cursor INTO _tournee_id;
    IF v_finished = 1 THEN
      LEAVE _loop;
    END IF;
    call int_logrowcount_C(_idtrt,5,'pai_valide_pepp_tournee',_tournee_id)
    ;
    call pai_valide_tournee(_validation_id, null, null, null, _tournee_id);
  END LOOP _loop;
  CLOSE _cursor;
END;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS pai_valide_pepp_activite;
CREATE PROCEDURE `pai_valide_pepp_activite`(
  IN    _idtrt		      INT
) BEGIN
  DECLARE v_finished INTEGER DEFAULT 0;
  DECLARE _activite_id INT;
  DECLARE _validation_id INT;
 
  -- declare cursor for employee email
  DEClARE _cursor CURSOR FOR
  SELECT pa.id
  FROM pai_activite pa
  WHERE pa.commentaire like 'PEPP%'
  AND pa.date_extrait is null;
   
  -- declare NOT FOUND handler
  DECLARE CONTINUE HANDLER
  FOR NOT FOUND SET v_finished = 1;

  IF (_validation_id IS NULL) THEN
		INSERT INTO pai_validation(utilisateur_id) VALUES(1);
		SELECT LAST_INSERT_ID() INTO _validation_id;
  END IF;
     
  OPEN _cursor;
  _loop: LOOP
    FETCH _cursor INTO _activite_id;
    IF v_finished = 1 THEN
      LEAVE _loop;
    END IF;
    call int_logrowcount_C(_idtrt,5,'pai_valide_pepp_activite',_activite_id)
    ;
    call pai_valide_activite(_validation_id, null, null, null, _activite_id);
  END LOOP _loop;
  CLOSE _cursor;
END;
