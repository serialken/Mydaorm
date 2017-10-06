/*
INSERT INTO `page` (`ID`, `ID_ROUTE`, `DESC_COURT`, `DESCRIPTION`, `MENU`, `PAG_DEFAUT`, `SS_CAT_ID`) VALUES
(6676, 'liste_remplacement_jour', 'Détail Remplacement', 'Modèle - Détail Remplacement', '1', NULL, 660),
(6675, 'liste_remplacement', 'Remplacement', 'Modèle - Remplacement', '1', NULL, 660);
delete from page where id=6675
delete from page_element where pag_id=6675
delete from profil_page_element where page_elem_id  in (66751,66750)


INSERT INTO `page_element` (`id`, `pag_id`, `desc_court`, `libelle`, `oblig`) VALUES
(66760, 6676, 'VISU', 'Visualisation', 1),
(66750, 6675, 'VISU', 'Visualisation', 1),
(66751, 6675, 'MODIF', 'Modification', 0);



ALTER TABLE pai_tournee DROP typetournee_id;
ALTER TABLE ref_emp_societe CHANGE id id INT AUTO_INCREMENT NOT NULL;

*/
select * from modele_remplacement where id=3132
select * from modele_remplacement where employe_id=9747
select * from pai_mois
select * from modele_remplacement_jour where date_modif is not null
delete from modele_remplacement_jour;
delete from modele_journal where remplacement_id is not null;
delete from modele_remplacement;

          SELECT *
                    FROM modele_remplacement
                    WHERE contrattype_id = 6110
                    AND date_fin = '2016-10-25' + INTERVAL -1 DAY

SELECT id
FROM modele_remplacement
WHERE contrattype_id = 6110
AND date_fin = '2016-10-25' + INTERVAL -1 DAY

insert into modele_remplacement(depot_id,flux_id,empcycle_id,date_debut,date_fin,utilisateur_id,date_creation)
SELECT distinct
                    epd.depot_id,epd.flux_id,
                    ec.id,
                    greatest(pm.date_debut,ec.date_debut),
                    ec.date_fin,
                    0,
                    now()
                FROM emp_pop_depot epd
                INNER JOIN emp_cycle ec on epd.employe_id=ec.employe_id and ec.date_debut<=epd.date_fin and ec.date_fin>=epd.date_debut
                INNER JOIN pai_mois pm on pm.flux_id=epd.flux_id
                LEFT OUTER JOIN modele_remplacement r on ec.id=r.empcycle_id
                WHERE epd.typecontrat_id=1 -- CDD
                AND ec.date_fin>=pm.date_debut
                AND epd.date_fin>=pm.date_debut;
                
INSERT INTO modele_remplacement_jour(remplacement_id,jour_id,utilisateur_id,date_creation)
select r.id,1,0,now()
from modele_remplacement r
inner join emp_cycle ec on r.empcycle_id=ec.id
where dimanche;
INSERT INTO modele_remplacement_jour(remplacement_id,jour_id,utilisateur_id,date_creation)
select r.id,2,0,now()
from modele_remplacement r
inner join emp_cycle ec on r.empcycle_id=ec.id
where lundi;
INSERT INTO modele_remplacement_jour(remplacement_id,jour_id,utilisateur_id,date_creation)
select r.id,3,0,now()
from modele_remplacement r
inner join emp_cycle ec on r.empcycle_id=ec.id
where mardi;
INSERT INTO modele_remplacement_jour(remplacement_id,jour_id,utilisateur_id,date_creation)
select r.id,4,0,now()
from modele_remplacement r
inner join emp_cycle ec on r.empcycle_id=ec.id
where mercredi;
INSERT INTO modele_remplacement_jour(remplacement_id,jour_id,utilisateur_id,date_creation)
select r.id,5,0,now()
from modele_remplacement r
inner join emp_cycle ec on r.empcycle_id=ec.id
where jeudi;
INSERT INTO modele_remplacement_jour(remplacement_id,jour_id,utilisateur_id,date_creation)
select r.id,6,0,now()
from modele_remplacement r
inner join emp_cycle ec on r.empcycle_id=ec.id
where vendredi;
INSERT INTO modele_remplacement_jour(remplacement_id,jour_id,utilisateur_id,date_creation)
select r.id,7,0,now()
from modele_remplacement r
inner join emp_cycle ec on r.empcycle_id=ec.id
where samedi;


UPDATE modele_remplacement_jour mrj
                    INNER JOIN modele_remplacement mr ON mrj.remplacement_id=mr.id
                    LEFT OUTER JOIN pai_tournee pt ON pt.id=(select max(pt2.id) 
                                                            from pai_tournee pt2 
                                                            inner join modele_tournee_jour mtj on pt2.modele_tournee_jour_id=mtj.id
                                                            where mtj.tournee_id=3222 and mrj.jour_id=mtj.jour_id)
                    SET
                    mrj.tournee_id = 3222,
                    mrj.date_distrib = pt.date_distrib,
--                    mrj.valrem = pt.valrem,
                    mrj.duree = pt.duree_tournee,
                    mrj.nbcli = pt.nbcli,
                    mrj.utilisateur_id = 0,
                    mrj.date_modif = NOW()
                    WHERE mrj.remplacement_id=3
                    AND mrj.jour_id=2;
                    
UPDATE modele_remplacement_jour mrj
                    INNER JOIN modele_remplacement mr ON mrj.remplacement_id=mr.id
                    inner join ref_typetournee rtt on mr.flux_id=rtt.id
                    inner join pai_ref_remuneration prr_new on rtt.societe_id=prr_new.societe_id AND rtt.population_id=prr_new.population_id AND mr.date_debut between prr_new.date_debut and prr_new.date_fin
                    INNER JOIN (select mr.id
                        ,   modele_valrem(mr.date_debut,mr.flux_id,sec_to_time(sum(time_to_sec(mrj.duree))),sum(mrj.nbcli)) as valrem_moyen
                        ,   cal_modele_etalon(sec_to_time(sum(mrj.duree)),sum(mrj.nbcli)) as etalon_moyen
                        from modele_remplacement_jour mrj
                        INNER JOIN modele_remplacement mr ON mrj.remplacement_id=mr.id
                        WHERE mrj.remplacement_id=3 and mrj.jour_id<>1
                        group by mr.id) as em on em.id=e.id
                    SET mrj.tauxhoraire=prr_new.valeur
                    ,   mrj.valrem_moyen=em.valrem_moyen
                    ,   mrj.etalon_moyen=em.etalon_moyen
                    WHERE mrj.remplacement_id=3
                    AND mrj.jour_id<>1  ;
                    

