 /*
 kill 5220848
 drop index un_remplacement on modele_remplacement_jour;
 select * from modele_remplacement_jour;
 delete from modele_remplacement_jour;
 delete from modele_remplacement;
 call int_png2mroad_maj_remplacement(1,null,null,null);

 call mod_remplacement_insert_jour(0,null,null,null);
 CALL mod_valide_remplacement(@id,NULL,NULL,NULL);
 select * from modele_ref_erreur
 delete from modele_journal where erreur_id between 22 and 26
 
	update modele_remplacement_jour mrj
	inner join modele_remplacement mr on mr.id=mrj.remplacement_id
	inner join pai_mois pm on pm.flux_id=mr.flux_id and mr.date_fin>=pm.date_debut
	-- On prend les pai_tournee au debut du contrat (et non de la période) pour avoir toujours les mêmes en cas de changement de taux horaire
	inner join emp_contrat_type ect on ect.id=mr.contrattype_id
	inner join pai_tournee pt ON pt.id=(select max(pt2.id) 
										  from pai_tournee pt2 
										  inner join modele_tournee_jour mtj on pt2.modele_tournee_jour_id=mtj.id
										  where mtj.tournee_id=mrj.modele_tournee_id and mtj.jour_id=mrj.jour_id
										  and pt2.date_distrib<ect.date_debut
										  and pt2.tournee_org_id is null)
  inner join ref_typetournee rtt on mr.flux_id=rtt.id
	inner join ref_population rp ON rtt.population_id=rp.id
  inner join pai_ref_remuneration r on rtt.societe_id=r.societe_id AND rtt.population_id=r.population_id AND pt.date_distrib BETWEEN r.date_debut AND r.date_fin
  set mrj.pai_tournee_id=pt.id
	,mrj.date_distrib=pt.date_distrib
	,mrj.valrem=pt.valrem_paie
	,mrj.etalon=cal_modele_etalon(sec_to_time(pt.duree_tournee),pt.nbcli)
	,mrj.duree=pai_duree_tournee(pt.tournee_org_id,pt.split_id,rtt.id,pt.valrem_paie,rp.majoration,r.valeur,pt.nbcli)
	,mrj.nbcli=pt.nbcli
	;
	
  call mod_remplacement_update_valrem(0,null,null,null);
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 /*
DROP PROCEDURE IF EXISTS mod_remplacement_delete;
CREATE PROCEDURE mod_remplacement_delete(
  IN 		_utilisateur_id INT,
  INOUT _idtrt		      INT,
  IN    _depot_id		    INT,
  IN    _flux_id		    INT
) BEGIN
declare _validation_id INT;
DECLARE	_batchactif BOOLEAN;
DECLARE CONTINUE  HANDLER FOR SQLWARNING    CALL int_logwarning(_idtrt);
DECLARE EXIT      HANDLER FOR SQLEXCEPTION  CALL int_logerreur(_idtrt);
   
  CALL int_logdebut(_utilisateur_id,_idtrt,'PNG2MROAD',null,_depot_id,_flux_id);
  select max(id) into _batchactif from pai_int_traitement where statut='C' and (typetrt in ('PNG2MROAD','ALIM_EMPLOYE') OR typetrt like 'GENERE_PLEIADES_%') and id<>_idtrt;
  if _batchactif is not null then
      call int_loglevel(_idtrt,0,'INT_PNG2MROAD','Une proc�dure d''alimentation est d�j� en cours d''�x�cution.');
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Une proc�dure d''alimentation est d�j� en cours d''�x�cution.';
  end if;

  CALL int_png2mroad_exec(_idtrt,_depot_id,_flux_id);
  
  -- Ajout des activit� hors-presse en retro
  call alim_act_insert_activite_hors_presse_retro(_utilisateur_id,_idtrt,_depot_id,_flux_id);
  
  -- Nettoyage
  delete pm 
  from pai_majoration pm
  WHERE pm.date_extrait is null
  AND not exists(select null from emp_pop_depot epd where pm.employe_id=epd.employe_id and pm.date_distrib between epd.date_debut and epd.date_fin)
  ;

END; 
*/
DROP PROCEDURE IF EXISTS mod_remplacement_insert_jour;
CREATE PROCEDURE mod_remplacement_insert_jour(
  IN 		_utilisateur_id   INT,
  IN    _depot_id		      INT,
  IN    _flux_id		      INT,
  IN    _remplacement_id  INT
) BEGIN
	-- Met en inactif un remplacement si suppression dans remplacement_jour
	UPDATE modele_remplacement mr
	INNER JOIN modele_remplacement_jour mrj on mr.id=mrj.remplacement_id
	inner join pai_mois pm on pm.flux_id=mr.flux_id and mr.date_fin>=pm.date_debut
	set actif=false
	WHERE (mr.depot_id=_depot_id OR _depot_id IS NULL)
	AND (mr.flux_id=_flux_id OR _flux_id IS NULL)
	AND (mr.id=_remplacement_id OR _remplacement_id IS NULL)
	and not exists(select null	
					from emp_pop_depot epd
					inner join ref_jour rj on (  rj.id=1 and epd.dimanche
                      					or rj.id=2 and epd.lundi
                      					or rj.id=3 and epd.mardi
                      					or rj.id=4 and epd.mercredi
                      					or rj.id=5 and epd.jeudi
                      					or rj.id=6 and epd.vendredi
                      					or rj.id=7 and epd.samedi
                      					)
					where  mr.employe_id=epd.employe_id and mr.contrattype_id=epd.contrattype_id and mr.date_debut between epd.date_debut and epd.date_fin
          )
	;
	-- Supprime les jour qui n'appartiennent plus au cycle
	delete mrj
	from modele_remplacement mr
	INNER JOIN modele_remplacement_jour mrj on mr.id=mrj.remplacement_id
	inner join pai_mois pm on pm.flux_id=mr.flux_id and mr.date_fin>=pm.date_debut
	WHERE (mr.depot_id=_depot_id OR _depot_id IS NULL)
	AND (mr.flux_id=_flux_id OR _flux_id IS NULL)
	AND (mr.id=_remplacement_id OR _remplacement_id IS NULL)
	and not exists(select null	
					from emp_pop_depot epd
					inner join ref_jour rj on(  rj.id=1 and epd.dimanche
                        					or rj.id=2 and epd.lundi
                        					or rj.id=3 and epd.mardi
                        					or rj.id=4 and epd.mercredi
                        					or rj.id=5 and epd.jeudi
                        					or rj.id=6 and epd.vendredi
                        					or rj.id=7 and epd.samedi
                        					)
					where  mr.employe_id=epd.employe_id and mr.contrattype_id=epd.contrattype_id and mr.date_debut between epd.date_debut and epd.date_fin
          )
	;
	
	-- Met en inactif un remplacement si ajout dans remplacement_jour
	UPDATE modele_remplacement mr
	inner join pai_mois pm on pm.flux_id=mr.flux_id and mr.date_fin>=pm.date_debut
	inner join emp_pop_depot epd on mr.employe_id=epd.employe_id and mr.contrattype_id=epd.contrattype_id and mr.date_debut between epd.date_debut and epd.date_fin
	inner join ref_jour rj
	set actif=false
	WHERE (mr.depot_id=_depot_id OR _depot_id IS NULL)
	AND (mr.flux_id=_flux_id OR _flux_id IS NULL)
	AND (mr.id=_remplacement_id OR _remplacement_id IS NULL)
	and (  rj.id=1 and epd.dimanche
	or rj.id=2 and epd.lundi
	or rj.id=3 and epd.mardi
	or rj.id=4 and epd.mercredi
	or rj.id=5 and epd.jeudi
	or rj.id=6 and epd.vendredi
	or rj.id=7 and epd.samedi
	)
	and not exists(select null from modele_remplacement_jour mrj where mrj.remplacement_id=mr.id and mrj.jour_id=rj.id)
	;
	-- Ajoute les nouveaux jours
	INSERT INTO modele_remplacement_jour(remplacement_id,jour_id,utilisateur_id,date_creation)
	select mr.id,rj.id,0,now()
	from modele_remplacement mr
	inner join pai_mois pm on pm.flux_id=mr.flux_id and mr.date_fin>=pm.date_debut
	inner join emp_pop_depot epd on mr.employe_id=epd.employe_id and mr.contrattype_id=epd.contrattype_id and mr.date_debut between epd.date_debut and epd.date_fin
	inner join ref_jour rj
	WHERE (mr.depot_id=_depot_id OR _depot_id IS NULL)
	AND (mr.flux_id=_flux_id OR _flux_id IS NULL)
	AND (mr.id=_remplacement_id OR _remplacement_id IS NULL)
	and (  rj.id=1 and epd.dimanche
	or rj.id=2 and epd.lundi
	or rj.id=3 and epd.mardi
	or rj.id=4 and epd.mercredi
	or rj.id=5 and epd.jeudi
	or rj.id=6 and epd.vendredi
	or rj.id=7 and epd.samedi
	)
	and not exists(select null from modele_remplacement_jour mrj where mrj.remplacement_id=mr.id and mrj.jour_id=rj.id)
	;

	-- Complète les remplacements avec les modele_tournee
	update modele_remplacement_jour mrj
	inner join modele_remplacement mr on mr.id=mrj.remplacement_id
	inner join pai_mois pm on pm.flux_id=mr.flux_id and mr.date_fin>=pm.date_debut
	set mr.actif=false
	,mrj.modele_tournee_id=( select min(mt.id)
							from emp_contrat_type ect
							inner join modele_tournee_jour mtj on ect.remplace_id=mtj.employe_id
							inner join modele_tournee mt on mtj.tournee_id=mt.id and mt.actif
							where mr.contrattype_id=ect.id
              and mr.date_debut between mtj.date_debut and mtj.date_fin and mtj.jour_id=mrj.jour_id)
	WHERE (mr.depot_id=_depot_id OR _depot_id IS NULL)
	AND (mr.flux_id=_flux_id OR _flux_id IS NULL)
	AND (mr.id=_remplacement_id OR _remplacement_id IS NULL)
	and mrj.modele_tournee_id is null
	;
	/*
  select * from modele_remplacement_jour;
  
  select *
							from modele_remplacement mr
  inner join modele_remplacement_jour mrj on mr.id=mrj.remplacement_id
              inner join emp_contrat_type ect on  mr.contrattype_id=ect.id
							inner join modele_tournee_jour mtj on ect.remplace_id=mtj.employe_id
							inner join modele_tournee mt on mtj.tournee_id=mt.id and mt.actif
              where mr.date_debut between mtj.date_debut and mtj.date_fin and mtj.jour_id=mrj.jour_id
              

  */
	-- Complète les remplacements avec les pai_tournee
	update modele_remplacement_jour mrj
	inner join modele_remplacement mr on mr.id=mrj.remplacement_id
	inner join pai_mois pm on pm.flux_id=mr.flux_id and mr.date_fin>=pm.date_debut
	-- On prend les pai_tournee au debut du contrat (et non de la période) pour avoir toujours les mêmes en cas de changement de taux horaire
	inner join emp_contrat_type ect on ect.id=mr.contrattype_id
	inner join pai_tournee pt ON pt.id=(select max(pt2.id) 
										  from pai_tournee pt2 
										  inner join modele_tournee_jour mtj on pt2.modele_tournee_jour_id=mtj.id
										  where mtj.tournee_id=mrj.modele_tournee_id and mtj.jour_id=mrj.jour_id
										  and pt2.date_distrib<ect.date_debut
										  and pt2.tournee_org_id is null)
  inner join ref_typetournee rtt on mr.flux_id=rtt.id
	inner join ref_population rp ON rtt.population_id=rp.id
  inner join pai_ref_remuneration r on rtt.societe_id=r.societe_id AND rtt.population_id=r.population_id AND pt.date_distrib BETWEEN r.date_debut AND r.date_fin
  set mr.actif=false
  ,mrj.pai_tournee_id=pt.id
	,mrj.date_distrib=pt.date_distrib
	,mrj.valrem=pt.valrem_paie
	,mrj.etalon=cal_modele_etalon(sec_to_time(pt.duree_tournee),pt.nbcli)
	,mrj.duree=pai_duree_tournee(pt.tournee_org_id,pt.split_id,rtt.id,pt.valrem_paie,rp.majoration,r.valeur,pt.nbcli)
	,mrj.nbcli=pt.nbcli
	WHERE (mr.depot_id=_depot_id OR _depot_id IS NULL)
	AND (mr.flux_id=_flux_id OR _flux_id IS NULL)
	AND (mr.id=_remplacement_id OR _remplacement_id IS NULL)
	and mrj.pai_tournee_id is null
	;
	
  call mod_remplacement_update_valrem(_utilisateur_id,_depot_id,_flux_id,_remplacement_id);
END;

DROP PROCEDURE IF EXISTS mod_remplacement_update_valrem;
CREATE PROCEDURE mod_remplacement_update_valrem(
  IN 		_utilisateur_id   INT,
  IN    _depot_id		      INT,
  IN    _flux_id		      INT,
  IN    _remplacement_id  INT
) BEGIN
  UPDATE modele_remplacement_jour mrj
  INNER JOIN modele_remplacement mr ON mrj.remplacement_id=mr.id
  inner join pai_mois pm on pm.flux_id=mr.flux_id and mr.date_fin>=pm.date_debut
  inner join ref_typetournee rtt on mr.flux_id=rtt.id
  inner join pai_ref_remuneration prr_new on rtt.societe_id=prr_new.societe_id AND rtt.population_id=prr_new.population_id AND mr.date_debut between prr_new.date_debut and prr_new.date_fin
  INNER JOIN (select mr.id
      ,   modele_valrem(mr.date_debut,mr.flux_id,sec_to_time(sum(time_to_sec(mrj.duree))),sum(mrj.nbcli)) as valrem_moyen
      ,   cal_modele_etalon(sec_to_time(sum(mrj.duree)),sum(mrj.nbcli)) as etalon_moyen
      from modele_remplacement_jour mrj
      INNER JOIN modele_remplacement mr ON mrj.remplacement_id=mr.id
    	WHERE (mr.depot_id=_depot_id OR _depot_id IS NULL)
    	AND (mr.flux_id=_flux_id OR _flux_id IS NULL)
      AND (mr.id=_remplacement_id OR _remplacement_id IS NULL)
      and mrj.jour_id=1
      group by mr.id) as mrm on mrm.id=mr.id
  SET mrj.tauxhoraire=prr_new.valeur
  ,   mrj.valrem_moyen=mrm.valrem_moyen
  ,   mrj.etalon_moyen=mrm.etalon_moyen
	WHERE (mr.depot_id=_depot_id OR _depot_id IS NULL)
	AND (mr.flux_id=_flux_id OR _flux_id IS NULL)
  AND (mr.id=_remplacement_id OR _remplacement_id IS NULL)
  AND mrj.jour_id=1;

  UPDATE modele_remplacement_jour mrj
  INNER JOIN modele_remplacement mr ON mrj.remplacement_id=mr.id
  inner join pai_mois pm on pm.flux_id=mr.flux_id and mr.date_fin>=pm.date_debut
  inner join ref_typetournee rtt on mr.flux_id=rtt.id
  inner join pai_ref_remuneration prr_new on rtt.societe_id=prr_new.societe_id AND rtt.population_id=prr_new.population_id AND mr.date_debut between prr_new.date_debut and prr_new.date_fin
  INNER JOIN (select mr.id
      ,   modele_valrem(mr.date_debut,mr.flux_id,sec_to_time(sum(time_to_sec(mrj.duree))),sum(mrj.nbcli)) as valrem_moyen
      ,   cal_modele_etalon(sec_to_time(sum(mrj.duree)),sum(mrj.nbcli)) as etalon_moyen
      from modele_remplacement_jour mrj
      INNER JOIN modele_remplacement mr ON mrj.remplacement_id=mr.id
    	WHERE (mr.depot_id=_depot_id OR _depot_id IS NULL)
    	AND (mr.flux_id=_flux_id OR _flux_id IS NULL)
      AND (mr.id=_remplacement_id OR _remplacement_id IS NULL)
      and mrj.jour_id<>1
      group by mr.id) as mrm on mrm.id=mr.id
  SET mrj.tauxhoraire=prr_new.valeur
  ,   mrj.valrem_moyen=mrm.valrem_moyen
  ,   mrj.etalon_moyen=mrm.etalon_moyen
	WHERE (mr.depot_id=_depot_id OR _depot_id IS NULL)
	AND (mr.flux_id=_flux_id OR _flux_id IS NULL)
  AND (mr.id=_remplacement_id OR _remplacement_id IS NULL)
  AND mrj.jour_id<>1;

  UPDATE modele_remplacement_jour mrj
  INNER JOIN modele_remplacement mr ON mrj.remplacement_id=mr.id
  inner join pai_mois pm on pm.flux_id=mr.flux_id and mr.date_fin>=pm.date_debut
  inner join modele_journal mj on mr.id=mj.remplacement_id
  inner join modele_ref_erreur mre on mj.erreur_id=mre.id and not mre.valide
  SET mrj.valrem_moyen=0
  ,   mrj.etalon_moyen=0
	WHERE (mr.depot_id=_depot_id OR _depot_id IS NULL)
	AND (mr.flux_id=_flux_id OR _flux_id IS NULL)
  AND (mr.id=_remplacement_id OR _remplacement_id IS NULL);

END;

DROP PROCEDURE IF EXISTS mod_remplacement_update_planning;
/*
CREATE PROCEDURE mod_remplacement_update_planning(
  IN 		_utilisateur_id   INT,
  IN    _depot_id		      INT,
  IN    _flux_id		      INT,
  IN    _remplacement_id  INT
) BEGIN
declare _validation_id int;

  update pai_tournee pt
  inner join modele_tournee_jour mtj on pt.modele_tournee_jour_id=mtj.id
  left outer join modele_remplacement_jour mrj on mrj.modele_tournee_id=mtj.tournee_id and mrj.jour_id=mtj.jour_id
  left outer join modele_remplacement mr on mrj.remplacement_id=mr.id and pt.date_distrib between mr.date_debut and mr.date_fin
  set   pt.valrem_org=pai_valrem_org(pt.flux_id,pt.employe_id,mr.actif,mr.employe_id,mrj.valrem_moyen,mrj.valrem,mtj.employe_id,mtj.remplacant_id,mtj.valrem_moyen,mtj.valrem)
    ,   pt.valrem=pai_valrem(pt.flux_id,pt.employe_id,mr.actif,mr.employe_id,mrj.valrem_moyen,mrj.valrem,mtj.employe_id,mtj.remplacant_id,mtj.valrem_moyen,mtj.valrem)
    ,   pt.valrem_paie=pai_valrem(pt.flux_id,pt.employe_id,mr.actif,mr.employe_id,mrj.valrem_moyen,mrj.valrem,mtj.employe_id,mtj.remplacant_id,mtj.valrem_moyen,mtj.valrem)
    ,   pt.valrem_logistique=mtj.valrem
  where pt.date_extrait is null
  AND (mr.depot_id=_depot_id OR _depot_id IS NULL)
  AND (mr.flux_id=_flux_id OR _flux_id IS NULL)
  AND (mr.id=_remplacement_id OR _remplacement_id IS NULL)
  ;
  -- ATTENTION il faut recalculer les tournées
  call recalcul_horaire_remplacement(_validation_id,_depot_id,_flux_id,_remplacement_id);
END;
*/
  /*
  select mr.id,1,0,now(),
        mtj.id,
        pt.id,
        pt.date_distrib,
        pt.valrem,
        cal_modele_etalon(sec_to_time(pt.duree_tournee),pt.nbcli),
        pt.duree_tournee,
        pt.nbcli
  from modele_remplacement mr
  inner join emp_pop_depot epd on mr.employe_id=epd.employe_id and mr.date_debut between epd.date_debut and epd.date_fin
--  inner join ref_jour rj
  left outer join modele_tournee_jour mtj on mr.employe_id=mtj.employe_id and mr.date_debut between mtj.date_debut and mtj.date_fin
  LEFT OUTER JOIN pai_tournee pt ON pt.id=(select max(pt2.id) 
                                          from pai_tournee pt2 
                                          where pt2.modele_tournee_jour_id=mtj.id
                                          and pt2.date_distrib<=mr.date_debut)
	WHERE not exists(select null from modele_remplacement_jour mrj where mrj.remplacement_id=mr.id and mrj.jour_id=mtj.jour_id)
  and (  mtj.jour_id=1 and epd.dimanche
  or mtj.jour_id=2 and epd.lundi
  or mtj.jour_id=3 and epd.mardi
  or mtj.jour_id=4 and epd.mercredi
  or mtj.jour_id=5 and epd.jeudi
  or mtj.jour_id=6 and epd.vendredi
  or mtj.jour_id=7 and epd.samedi
  )
  and mtj.id not in (select id from modele_tournee_jour)
  ;
*/

/*
UPDATE modele_remplacement_jour mrj
                    INNER JOIN modele_remplacement mr ON mrj.remplacement_id=mr.id
--                    inner join modele_tournee_jour mtj on mtj.id=mtj.tournee_id=" . $this->sqlField->sqlIdOrNull($tournee_id) . " and mrj.jour_id=mtj.jour_id
                    LEFT OUTER JOIN pai_tournee pt ON pt.id=(select max(pt2.id) 
                                                            from pai_tournee pt2 
                                                            inner join modele_tournee_jour mtj on pt2.modele_tournee_jour_id=mtj.id
                                                            where mtj.tournee_id=" . $this->sqlField->sqlIdOrNull($tournee_id) . " and mrj.jour_id=mtj.jour_id
                                                            and pt2.date_distrib<=mr.date_debut)
                    SET
                    mrj.modele_tournee_id = " . $this->sqlField->sqlIdOrNull($tournee_id) . ",
                    mrj.pai_tournee_id = pt.id,
                    mrj.date_distrib = pt.date_distrib,
                    mrj.valrem = pt.valrem,
                    mrj.etalon=cal_modele_etalon(sec_to_time(pt.duree_tournee),pt.nbcli),
                    mrj.duree = pt.duree_tournee,
                    mrj.nbcli = pt.nbcli,
                    mrj.utilisateur_id = $user,
                    mrj.date_modif = NOW()
                    WHERE mrj.remplacement_id=$remplacement_id
                    AND mrj.jour_id=$jour_id
                    */