select date_distrib,depot_id,count(*) 
from client_a_servir_logist 
where tournee_jour_id is null  and depot_id in (14,24) and date_distrib>'2014-08-21' 
group by date_distrib,depot_id
order by depot_id,date_distrib;

-- remise à 0 des suppléments
update pai_prd_tournee ppt
inner join produit p on ppt.produit_id=p.id 
set ppt.nbcli=0
where p.type_id in (2,3) and ppt.nbcli<>0;
/*
UPDATE pai_tournee pt
                SET pt.nbcli	=COALESCE((SELECT SUM(ppt.nbcli) FROM pai_prd_tournee ppt INNER JOIN produit p ON ppt.produit_id=p.id WHERE ppt.tournee_id=pt.id AND p.type_id not in (2,3)),0)
                , pt.nbcli_unique=COALESCE((SELECT SUM(ppt.nbcli_unique) FROM pai_prd_tournee ppt INNER JOIN produit p ON ppt.produit_id=p.id WHERE ppt.tournee_id=pt.id AND p.type_id not in (2,3)),0)
                , pt.nbadr	=COALESCE((SELECT SUM(ppt.nbadr) FROM pai_prd_tournee ppt INNER JOIN produit p ON ppt.produit_id=p.id WHERE ppt.tournee_id=pt.id AND p.type_id not in (2,3)),0)
                , pt.nbtitre	=COALESCE((SELECT SUM(ppt.qte) FROM pai_prd_tournee ppt INNER JOIN produit p ON ppt.produit_id=p.id WHERE ppt.tournee_id=pt.id AND p.type_id IN (1)),0)
                , pt.nbspl	=COALESCE((SELECT SUM(ppt.qte) FROM pai_prd_tournee ppt INNER JOIN produit p ON ppt.produit_id=p.id WHERE ppt.tournee_id=pt.id AND p.type_id IN (2,3)),0)
                , pt.nbprod	=COALESCE((SELECT SUM(ppt.qte) FROM pai_prd_tournee ppt INNER JOIN produit p ON ppt.produit_id=p.id WHERE ppt.tournee_id=pt.id AND p.type_id NOT IN (1,2,3)),0)
                , pt.duree_supplement=COALESCE((select sec_to_time(sum(time_to_sec(ppt.duree_supplement))) from pai_prd_tournee ppt INNER JOIN produit p ON ppt.produit_id=p.id WHERE ppt.tournee_id=pt.id AND p.type_id IN (2,3)),0)
                , pt.poids	=COALESCE((SELECT SUM(ppt.poids) FROM pai_prd_tournee ppt WHERE ppt.tournee_id=pt.id),0);
               */ 
/*
UPDATE pai_prd_tournee ppt
                inner join pai_tournee pt on ppt.tournee_id=pt.id
                inner join emp_pop_depot epd on pt.employe_id=epd.employe_id and pt.date_distrib between epd.date_debut and epd.date_fin
                inner join produit p on ppt.produit_id=p.id
                inner join prd_caract pc on p.type_id=pc.produit_type_id and pc.code='POIDS'
                left outer join prd_caract_jour pcj on ppt.produit_id=pcj.produit_id and pcj.prd_caract_id=pc.id and pt.date_distrib = pcj.date_distrib 
                left outer join prd_caract_groupe pcg on ppt.produit_id=pcg.produit_id and pcg.prd_caract_id=pc.id and pt.groupe_id=pcg.groupe_id and pt.date_distrib=pcg.date_distrib
                -- rémunération au niveau du produit
                left outer join pai_ref_poids prpp on pt.date_distrib between prpp.date_debut and prpp.date_fin 
                                                  and pt.typetournee_id=prpp.typetournee_id 
                                                  and p.id=prpp.produit_id
                                                  -- pour SDVP on ne tient compte du poids
                                                  and ((epd.typetournee_id=1 and 0=prpp.borne_inf)
                                                  -- pour Néo/Média on ne tient pas compte du poids
                                                  or   (epd.typetournee_id<>1 and coalesce(pcg.valeur_int,pcj.valeur_int)/1000 between prpp.borne_inf and prpp.borne_sup))
                -- rémunération au niveau du type
                left outer join pai_ref_poids prpt on pt.date_distrib between prpt.date_debut and prpt.date_fin
                                                  and pt.typetournee_id=prpt.typetournee_id
                                                  and p.type_id=prpt.produit_type_id
                                                  -- pour SDVP on ne tient compte du poids
                                                  and ((epd.typetournee_id=1 and 0=prpt.borne_inf)
                                                  -- pour Néo/Média on ne tient pas compte du poids
                                                  or   (epd.typetournee_id<>1 and coalesce(pcg.valeur_int,pcj.valeur_int)/1000 between prpt.borne_inf and prpt.borne_sup))
                left outer join pai_ref_remuneration prr on epd.societe_id=prr.societe_id and epd.population_id = prr.population_id
                                                  and pt.date_distrib between prr.date_debut and prr.date_fin
                SET ppt.poids=ppt.qte*coalesce(coalesce(pcg.valeur_int,pcj.valeur_int),0)
                ,   ppt.duree_supplement= CASE WHEN p.type_id in (2,3) AND prr.valeur is not null THEN
                                            coalesce(
                                            sec_to_time(ceil(ppt.qte/coalesce(prpp.quantite,prpt.quantite))*coalesce(prpp.valeur,prpt.valeur)/prr.valeur*3600)
                                            -- +prpp.quantite*prpp.valeur_unite
                                            --  floor(ppt.qte/prpp.quantite)*prpp.valeur+prpp.quantite*prpp.valeur_unite
                                            ,'00:00')
                                        ELSE
                                            '00:00'
                                        END
                ,   pai_qte=ceil(ppt.qte/coalesce(prpp.quantite,prpt.quantite))
                ,   pai_taux=coalesce(prpp.valeur,prpt.valeur)
                ,   pai_mnt=round(ceil(ppt.qte/coalesce(prpp.quantite,prpt.quantite))*coalesce(prpp.valeur,prpt.valeur),2)
                ;
*/                                          
select * from pai_prd_tournee where duree_supplement<>'00:00:00';
ALTER TABLE pai_ref_postepaie_general CHANGE typeurssaf_id typeurssaf_id INT DEFAULT NULL;
update pai_ref_postepaie_general set typeurssaf_id=null where typeurssaf_id=0;
insert into page_element (`id`, `pag_id`, `desc_court`, `libelle`, `oblig`) values(62501, 6250, 'MODIF', 'Modification', 1);
insert into page_element (`id`, `pag_id`, `desc_court`, `libelle`, `oblig`) values(62301, 6230, 'MODIF', 'Modification', 1);

select * from pai_ref_poids;
update pai_ref_poids set borne_inf=borne_inf*1000,borne_sup=borne_sup*1000;
ALTER TABLE pai_ref_poids CHANGE borne_inf borne_inf INT NOT NULL, CHANGE borne_sup borne_sup INT NOT NULL;
delete from pai_ref_poids where date_fin<>'2999-01-01';

alter table pai_ev add column datev2 char(8);

truncate table pai_ref_postepaie_supplement;
ALTER TABLE pai_ref_postepaie_supplement DROP INDEX IDX_DE0D137EF347EFB, ADD UNIQUE INDEX UNIQ_DE0D137EF347EFB (produit_id);
ALTER TABLE pai_ref_postepaie_supplement DROP FOREIGN KEY FK_B28C4F16FCF77506;
ALTER TABLE pai_ref_postepaie_supplement DROP FOREIGN KEY FK_DE0D137EC955D1E1;
DROP INDEX un_pai_ref_postepaie_supplement ON pai_ref_postepaie_supplement;
DROP INDEX IDX_DE0D137EC955D1E1 ON pai_ref_postepaie_supplement;
DROP INDEX FK_B28C4F16FCF77506 ON pai_ref_postepaie_supplement;
ALTER TABLE pai_ref_postepaie_supplement ADD poste_bdc VARCHAR(10) NOT NULL, DROP typeurssaf_id, DROP population_id, CHANGE poste poste_bf VARCHAR(10) NOT NULL;

ALTER TABLE pai_ref_postepaie_activite DROP FOREIGN KEY FK_EF3A09FEC955D1E1;
DROP INDEX IDX_EF3A09FEC955D1E1 ON pai_ref_postepaie_activite;
DROP INDEX un_ref_postepaie_activite ON pai_ref_postepaie_activite;
ALTER TABLE pai_ref_postepaie_activite DROP population_id;
CREATE UNIQUE INDEX un_ref_postepaie_activite ON pai_ref_postepaie_activite (activite_id, typejour_id);
select * from pai_ref_postepaie_activite;

ALTER TABLE pai_tournee CHANGE nb_reperage nb_reperage_bf INT NOT NULL, ADD nb_reperage_bdc INT NOT NULL ;

update pai_prd_tournee ppt
inner join pai_tournee pt on ppt.tournee_id=pt.id
set ppt.produit_id=234
,ppt.nbcli=0
,ppt.nbcli_unique=0
,ppt.nbadr=0
where pt.date_distrib='2014-08-22' and ppt.produit_id=98 and depot_id=14;

ALTER TABLE pai_prd_tournee ADD paie_qte NUMERIC(9, 2) NOT NULL, ADD paie_taux NUMERIC(8, 3) NOT NULL, ADD paie_mnt NUMERIC(9, 2) NOT NULL;