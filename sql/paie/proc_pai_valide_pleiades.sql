/*
call pai_valide_pleiades(@id , null, null);

select * from pai_int_log where idtrt=1 order by id desc limit 1000;
select * from pai_journal where erreur_id>=40;
--truncate table pai_journal;
delete from pai_int_log where idtrt=1;
*/
 
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS pai_valide_pleiades;
CREATE PROCEDURE pai_valide_pleiades(INOUT _validation_id INT, IN _depot_id INT, IN _flux_id INT)
BEGIN
DECLARE _anneemois VARCHAR(6);
	IF (_validation_id IS NULL) THEN
		INSERT INTO modele_validation(utilisateur_id) VALUES(1);
		SELECT LAST_INSERT_ID() INTO _validation_id;
    call pai_valide_logger('PAI_VALIDE_PLEIADES',concat_ws('*',_validation_id,_depot_id,_flux_id,null));
	END IF;
  
  -- La validation est déja faite lors du recalcul des tournées
  -- CALL pai_valide_rh_tournee(_validation_id, _depot_id, _flux_id, NULL, NULL);
--	CALL pai_valide_rh_activite(_validation_id, _depot_id, _flux_id, NULL, NULL);
--	CALL pai_valide_rh_vehicule(_validation_id, _depot_id, _flux_id);
--	CALL pai_valide_rh_sejour(_validation_id, _depot_id, _flux_id);
	select anneemois into _anneemois from pai_ref_mois where now() between date_debut and date_fin;
	call pai_valide_rh_mensuel(_validation_id,_anneemois,null);
	call pai_valide_rh_6jour(_validation_id,_anneemois,null);
/*
  CALL pai_valide_delete_employe('PL', _depot_id, _flux_id, null);

	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id)
	SELECT 42,_validation_id,xa.depot_id,xa.flux_id,prm.anneemois,greatest(prm.date_debut,epd.date_debut),epd.employe_id ,NULL, NULL
  FROM pai_mois pm
  inner join pai_ref_mois prm on prm.anneemois>=pm.anneemois and prm.date_debut<=now()
  inner join pai_png_xrcautreactivit xa on xa.begin_date<=prm.date_fin and xa.end_date>=prm.date_debut and pm.flux_id=xa.flux_id
  INNER JOIN emp_pop_depot epd ON epd.rcoid=xa.relationcontrat and xa.begin_date between epd.date_debut and epd.date_fin
                                and  epd.date_debut<=prm.date_fin and epd.date_fin>=prm.date_debut
  inner join depot d on epd.depot_id=d.id
  INNER JOIN employe e on epd.employe_id=e.id
  inner join pai_png_xta_rcactivite xrc on xa.xta_rcactivte=xrc.oid
  inner join pai_png_xta_rcmetier xm on xa.xta_rcmetier=xm.oid
  inner join pai_png_xta_rcactivhpre xrcp on xa.xta_rcactivhpre=xrcp.oid
  inner join pai_png_relationcontrat rc on xa.relationcontrat=rc.oid
  left outer join pai_png_xhorporpol xh on xh.relationcontrat=rc.oid and xa.begin_date between xh.begin_date and xh.end_date
  inner join ref_emploi em on epd.emploi_id=em.id
  WHERE xrc.code='RC'
  and coalesce(xh.horcontractuel,0)=0;
  call pai_valide_logger('pai_valide_pleiades', 'Contrat hors-presse unique sans horaire mensuel garanti');*/
END;
