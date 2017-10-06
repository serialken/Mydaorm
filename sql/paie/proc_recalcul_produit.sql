/*
select * from depot
call recalcul_produit_date_distrib('2017-05-29',null,1);
call recalcul_produit_date_distrib(null,null,2);
call recalcul_produit_date_distrib(null,null,null);

call recalcul_produit_date_distrib(null,null,null);
show open tables
select * from metadata_locks
commit;

*/
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_produit;
CREATE PROCEDURE recalcul_produit(
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT,
    IN 		_groupe_id      INT,
    IN 		_produit_id     INT,
    IN 		_tournee_id     INT,
    IN 		_natureclient_id INT,
    IN 		_id             INT
) BEGIN
    UPDATE pai_prd_tournee ppt
    inner join pai_tournee pt on ppt.tournee_id=pt.id
    inner join emp_pop_depot epd on pt.employe_id=epd.employe_id and pt.date_distrib between epd.date_debut and epd.date_fin
    inner join produit p on ppt.produit_id=p.id
    inner join produit_type t ON p.type_id=t.id
    inner join prd_caract pc on p.type_id=pc.produit_type_id and pc.code='POIDS'
    left outer join prd_caract_jour pcj on ppt.produit_id=pcj.produit_id and pcj.prd_caract_id=pc.id and pt.date_distrib = pcj.date_distrib 
    left outer join prd_caract_groupe pcg on ppt.produit_id=pcg.produit_id and pcg.prd_caract_id=pc.id and pt.groupe_id=pcg.groupe_id and pt.date_distrib=pcg.date_distrib
    -- rémunération au niveau du produit
    left outer join pai_ref_poids prpp on pt.date_distrib between prpp.date_debut and prpp.date_fin 
                                      and epd.typetournee_id=prpp.typetournee_id 
                                      and p.id=prpp.produit_id
                                      -- pour SDVP on ne tient compte du poids
                                      -- and ((epd.typetournee_id=1 and 0=prpp.borne_inf)
                                      -- pour Néo/Média on ne tient pas compte du poids
                                      -- or   (epd.typetournee_id=2 and coalesce(pcg.valeur_int,pcj.valeur_int) between prpp.borne_inf and prpp.borne_sup))
    -- rémunération au niveau du type
    left outer join pai_ref_poids prpt on pt.date_distrib between prpt.date_debut and prpt.date_fin
                                      and epd.typetournee_id=prpt.typetournee_id
                                      and p.type_id=prpt.produit_type_id and prpt.produit_id is null
                                      -- pour SDVP on ne tient compte du poids
                                      -- and ((epd.typetournee_id=1 and 0=prpt.borne_inf)
                                      -- pour Néo/Média on ne tient pas compte du poids
                                      -- or   (epd.typetournee_id=2 and coalesce(pcg.valeur_int,pcj.valeur_int) between prpt.borne_inf and prpt.borne_sup))
    left outer join pai_ref_remuneration prr on epd.societe_id=prr.societe_id and epd.population_id = prr.population_id
                                      and pt.date_distrib between prr.date_debut and prr.date_fin
    left outer join (select pcj.date_distrib,dp2.parent_id,prs.typetournee_id,prs.valeur
                                  from dependance_produit dp2
                                  inner join prd_caract_jour pcj on dp2.enfant_id=pcj.produit_id
                                  inner join prd_caract pc on pcj.prd_caract_id=pc.id and pc.code='POIDS'
                                  inner join pai_ref_supplement prs on pcj.date_distrib between prs.date_debut and prs.date_fin
                                  where (pcj.date_distrib=_date_distrib or _date_distrib is null)
                                  AND (dp2.parent_id=_produit_id or _produit_id is null)
                                  group by dp2.parent_id,pcj.date_distrib,prs.id,prs.typetournee_id, prs.valeur, prs.borne_inf, prs.borne_sup
                                  HAVING SUM(pcj.valeur_int) between prs.borne_inf and prs.borne_sup
                                  ) as sup on sup.date_distrib = pt.date_distrib and ppt.produit_id=sup.parent_id and epd.typetournee_id=sup.typetournee_id

    SET ppt.poids=ppt.qte*coalesce(coalesce(pcg.valeur_int,pcj.valeur_int),0)
    ,   ppt.duree_supplement= CASE 
                              WHEN p.type_id=1 THEN
                                coalesce(sec_to_time(ppt.nbcli*sup.valeur/prr.valeur*3600),'00:00')
                              WHEN prr.valeur is not null THEN
                                coalesce(sec_to_time(ppt.qte*coalesce(prpp.valeur,prpt.valeur)/prr.valeur*3600) ,'00:00')
                              ELSE
                                  '00:00'
                              END
    ,   ppt.duree_reperage= CASE 
                              WHEN t.id not in (1,2,3) and not t.hors_presse AND prr.valeur is not null THEN
                                coalesce(sec_to_time(2*ppt.nbrep*coalesce(prpp.valeur,prpt.valeur)/prr.valeur*3600) ,'00:00')
                              ELSE
                                  '00:00'
                              END
    ,   ppt.pai_qte=CASE 
                    WHEN p.type_id=1 THEN ppt.nbcli
                    ELSE ppt.qte
                    END
    ,   ppt.pai_taux=CASE 
                    WHEN p.type_id=1 THEN sup.valeur
                    ELSE coalesce(prpp.valeur,prpt.valeur)
                    END
    ,   ppt.pai_mnt=CASE 
                    WHEN p.type_id=1 THEN ppt.nbcli*sup.valeur
                    ELSE round(ppt.qte*coalesce(prpp.valeur,prpt.valeur),2)
                    END
    WHERE ppt.date_extrait is null
    and (pt.date_distrib=_date_distrib or _date_distrib is null)
    and (pt.depot_id=_depot_id or _depot_id is null) and (pt.flux_id=_flux_id or _flux_id is null)
    AND (pt.groupe_id=_groupe_id or _groupe_id is null) 
    AND (ppt.produit_id=_produit_id or _produit_id is null)
    AND (ppt.tournee_id=_tournee_id or _tournee_id is null)
    AND (ppt.natureclient_id=_natureclient_id or _natureclient_id is null)
    AND (ppt.id=_id or _id is null)
    ;
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_produit_date_distrib;
CREATE PROCEDURE recalcul_produit_date_distrib(
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT
) BEGIN
declare _validation_id  INT;
    call recalcul_produit(_date_distrib,_depot_id,_flux_id,null,null,null,null,null);
  call pai_valide_produit(_validation_id, _depot_id, _flux_id, _date_distrib, null, null); -- permet de supprimer les poids non saisi
    call recalcul_tournee_date_distrib(_date_distrib,_depot_id,_flux_id);
END;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_produit_tournee_id;
CREATE PROCEDURE recalcul_produit_tournee_id(
    IN 		_tournee_id     INT
) BEGIN
declare _validation_id  INT;
    call recalcul_produit(null,null,null,null,null,_tournee_id,null,null);
  call pai_valide_produit(_validation_id, null, null, null, _tournee_id, null); -- permet de supprimer les poids non saisi
    call recalcul_tournee_id(_tournee_id);
END;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_produit_nature_id;
CREATE PROCEDURE recalcul_produit_nature_id(
    IN 		_tournee_id       INT,
    IN 		_produit_id       INT,
    IN 		_natureclient_id  INT
) BEGIN
declare _validation_id  INT;
  call recalcul_produit(null,null,null,null,_produit_id,_tournee_id,_natureclient_id,null);
  call pai_valide_produit(_validation_id, null, null, null, _tournee_id, null); -- permet de supprimer les poids non saisi
  call recalcul_tournee_id(_tournee_id);
END;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_produit_id;
CREATE PROCEDURE recalcul_produit_id(
    IN 		_tournee_id       INT,
    IN 		_id               INT
) BEGIN
declare _validation_id  INT;
  call recalcul_produit(null,null,null,null,null,_tournee_id,null,_id);
  call pai_valide_produit(_validation_id, null, null, null, _tournee_id, null); -- permet de supprimer les poids non saisi
  call recalcul_tournee_id(_tournee_id);
END;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
-- Appelé lors de la saisie des poids groupe
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_produit_groupe_id;
DROP PROCEDURE IF EXISTS recalcul_produit_poids_groupe;
CREATE PROCEDURE recalcul_produit_poids_groupe(
    IN 		_date_distrib   DATE,
    IN 		_groupe_id      INT,
    IN 		_produit_id     INT
) BEGIN
declare _validation_id  INT;
  call recalcul_produit(_date_distrib,null,null,_groupe_id,_produit_id,null,null,null);
  call pai_valide_produit(_validation_id, null, null, _date_distrib, null, null); -- permet de supprimer les poids non saisi
  call recalcul_tournee_poids_groupe(_date_distrib,_groupe_id);
END;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
-- Appelé lors de la saisie des poids PCO
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_produit_produit_id;
DROP PROCEDURE IF EXISTS recalcul_produit_poids_PCO;
CREATE PROCEDURE recalcul_produit_poids_PCO(
    IN 		_date_distrib   DATE,
    IN 		_produit_id     INT
) BEGIN
declare _validation_id  INT;
  call recalcul_produit(_date_distrib,null,null,null,_produit_id,null,null,null);
  call pai_valide_produit(_validation_id, null, null, _date_distrib, null, null); -- permet de supprimer les poids non saisi
  -- Utilisé dans poids PCO, on recalcule pour toute la date 
  call recalcul_tournee_poids_PCO(_date_distrib);
END;
