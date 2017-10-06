/*
set @id=null;
call mt_transferer(@id,15,1573,536,'001','2017-01-21')
select * from modele_tournee where code like '%NKT010'
select * from groupe_tournee where code like 'KT'

  select gt.depot_id,gt.flux_id 
  from modele_tournee mt 
  inner join groupe_tournee gt on mt.groupe_id=gt.id 
  where mt.id=1573;
  
    call recalcul_horaire(@validation_id,24,2,null,null);
    select * from pai_majoration where date_distrib='2016-04-06' and employe_id=1758
    select * from pai_tournee where date_distrib='2016-04-06' and employe_id=1758
    select * from pai_journal where tournee_id=1229274
    select * from v_employe where employe_id=1758


    select*
    FROM pai_majoration pm
--    INNER JOIN pai_mois m on pm.date_distrib>=m.date_debut and pm.flux_id=m.flux_id
 --   INNER JOIN emp_pop_depot epd ON pm.employe_id=epd.employe_id and pm.date_distrib BETWEEN epd.date_debut AND epd.date_fin
    WHERE pm.date_extrait is null
    AND (pm.date_distrib='2016-04-06')
 --   AND (pm.depot_id=24)
    AND (pm.flux_id=2)
 --   AND (pm.employe_id=_employe_id or _employe_id is null)
    ;

*/
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS mt_transferer;
create procedure mt_transferer(
  INOUT _validation_id INT, 
  IN		_utilisateur_id     INT,
  IN		_modele_tournee_id  INT,
  IN		_groupe_id	INT,
  IN		_numero			VARCHAR(3),
  IN    _date_debut DATE
) begin
declare _new_id int;
declare _depot_id int;
declare _flux_id int;
DECLARE EXIT      HANDLER FOR SQLEXCEPTION  
  BEGIN
    ROLLBACK;
    RESIGNAL;
  END;

START TRANSACTION;
	INSERT INTO modele_validation(utilisateur_id) VALUES(1);
  SELECT LAST_INSERT_ID() INTO _validation_id;

  select gt.depot_id,gt.flux_id into _depot_id,_flux_id 
  from modele_tournee mt 
  inner join groupe_tournee gt on mt.groupe_id=gt.id 
  where mt.id=_modele_tournee_id;
  -- recherche si le modele de tournée existe déjà
  select mt.id into _new_id 
  from modele_tournee mt
  where mt.groupe_id=_groupe_id and mt.numero=_numero;
  
  if _new_id is not null then
    -- Le modele de tournée existe déjà, on le met à jour
    update modele_tournee mt
    inner join modele_tournee old_mt on old_mt.id=_modele_tournee_id
    set mt.utilisateur_id=_utilisateur_id
    , mt.date_modif=now()
    , mt.actif=true
    , mt.employe_id=old_mt.employe_id
    , mt.codeDCS=old_mt.codeDCS
    , mt.libelle=old_mt.libelle
    where mt.id=_new_id;
    -- on supprime les modeles de tournee jour qui ne sont/seront pas utilisés
    delete mtj
    from modele_tournee_jour mtj
    where mtj.tournee_id=_new_id
    and mtj.date_debut>_date_debut
    and mtj.date_debut>now();
    -- on met à jour la date de fin
    update modele_tournee_jour mtj
    inner join modele_tournee mt on mtj.tournee_id=mt.id
    set mtj.utilisateur_id=_utilisateur_id
    , mtj.date_modif=now()
    , mtj.date_fin=adddate(_date_debut,interval -1 day)
    where mt.id=_new_id
    and mtj.date_fin>=_date_debut;
  else
    -- Le modele de tournée n'existe pas, on le crée
    insert into modele_tournee(groupe_id,utilisateur_id,date_creation,actif,employe_id,numero,codeDCS,libelle)
    select _groupe_id,_utilisateur_id,now(),true,old_mt.employe_id,_numero,old_mt.codeDCS,old_mt.libelle
    from modele_tournee old_mt
    where old_mt.id=_modele_tournee_id;

    set _new_id:=LAST_INSERT_ID();
  end if;
  
  -- on recopie les modèles tournée jour de l'ancienne tournée vers la nouvelle
  insert into modele_tournee_jour
  ( tournee_id, jour_id
  , date_debut, date_fin
  , employe_id, remplacant_id
  , transport_id, nbkm, nbkm_paye
  , depart_depot, retour_depot
  , tauxhoraire
  , valrem, valrem_moyen
  , etalon, etalon_moyen
  , duree, nbcli
  , date_creation, utilisateur_id
  ) 
  select 
    _new_id, mtj.jour_id
  , _date_debut, '2999-01-01'
  , mtj.employe_id, mtj.remplacant_id
  , mtj.transport_id, mtj.nbkm, mtj.nbkm_paye
  , mtj.depart_depot, mtj.retour_depot
  , prr_new.valeur
  , prr_new.valeur/prr_old.valeur*mtj.valrem ,prr_new.valeur/prr_old.valeur*mtj.valrem_moyen
  , mtj.etalon, mtj.etalon_moyen
  , mtj.duree, mtj.nbcli
  , now(), _utilisateur_id
 from modele_tournee_jour mtj
  inner join modele_tournee mt on mtj.tournee_id=mt.id
  inner join groupe_tournee gt_old on mt.groupe_id=gt_old.id
  inner join ref_typetournee rtt_old on gt_old.flux_id=rtt_old.id
  inner join groupe_tournee gt_new on gt_new.id=_groupe_id
  inner join ref_typetournee rtt_new on gt_new.flux_id=rtt_new.id
  -- pour mettre à jour la valeur de rem si la rémunération change !!!!!
  inner join pai_ref_remuneration prr_new on rtt_new.societe_id=prr_new.societe_id AND rtt_new.population_id=prr_new.population_id AND _date_debut between prr_new.date_debut and prr_new.date_fin
  inner join pai_ref_remuneration prr_old on rtt_old.societe_id=prr_old.societe_id AND rtt_old.population_id=prr_old.population_id AND date_add(_date_debut,interval -1 day) between prr_old.date_debut and prr_old.date_fin
  where mtj.tournee_id=_modele_tournee_id
  and mtj.date_fin='2999-01-01';

  call mod_valide_tournee(_validation_id,null,null,_new_id);
  call mod_valide_tournee_jour(_validation_id,null,null,_new_id,null);
  /*
  select * from modele_tournee_jour where tournee_id=_new_id and date_debut=_date_debut;
  rollback;
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'test';
  */
  update pai_tournee pt
  inner join modele_tournee_jour mtj on pt.modele_tournee_jour_id=mtj.id
  inner join modele_tournee_jour mtj2 on mtj.jour_id=mtj2.jour_id and pt.date_distrib between mtj2.date_debut and mtj2.date_fin
  set pt.modele_tournee_jour_id=mtj2.id
  , pt.code=mtj2.code
  where pt.date_extrait is null
  and pt.tournee_org_id is null
  and mtj.tournee_id=_modele_tournee_id
  and mtj2.tournee_id=_new_id and mtj2.date_debut=_date_debut;

  -- on ferme les modèles tournée jour de l'ancienne tournée
  update modele_tournee_jour mtj
  inner join modele_tournee mt on mtj.tournee_id=mt.id
  set mtj.utilisateur_id=_utilisateur_id
    , mtj.date_modif=now()
    , mtj.date_fin=adddate(_date_debut,interval -1 day)
  where mt.id=_modele_tournee_id
  and mtj.date_fin='2999-01-01'
  and mtj.date_debut<>_date_debut;

  call mod_valide_tournee_jour(_validation_id,null,null,_modele_tournee_id,null);

  call pai_valide_tournee(_validation_id,_depot_id,_flux_id,null,null);
  call recalcul_horaire(_validation_id,_depot_id,_flux_id,null,null);
  
  -- On met à jour la table modele_tournee_transfert
  insert into modele_tournee_transfert(tournee_id_init, tournee_id_future, utilisateur_id, date_application, date_modif)
  value(_modele_tournee_id, _new_id, _utilisateur_id, _date_debut, now());
COMMIT;
end;
/*
CREATE UNIQUE INDEX UNIQ_EE7B87ACE07FDBA1 ON modele_tournee (codeDCS);
select * from modele_tournee where codeDCS='H25'
select * from modele_tournee where codeDCS='43X'
select codeDCS from modele_tournee where actif=1 group by codeDCS having count(*)>1
*/
