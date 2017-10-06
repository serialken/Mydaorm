	  SELECT 'NB JOUR',e.matricule,e.rc,g.poste,e.d,COUNT(DISTINCT h.date_distrib),ev.qte
	  FROM pai_ev_emp_pop_depot_hst e
    INNER JOIN ref_population rp on e.population_id=rp.id
	  INNER JOIN pai_ev_heure_hst h ON e.employe_id=h.employe_id /*AND e.depot_id=h.depot_id*/ AND h.date_distrib BETWEEN e.d AND e.f
	  INNER JOIN pai_ref_postepaie_general g ON g.code='JTP'
    INNER JOIN pai_ev_hst ev on e.idtrt=ev.idtrt and e.matricule=ev.matricule and e.d=ev.datev and ev.poste='JTPX'
	  WHERE (e.typetournee_id=1 AND h.typejour_id=1 		-- SDVP et semaine
	  OR 	 e.typetournee_id=2 AND h.typejour_id IN (1,3)) 	-- Neo/Media et semaine+ferie
	  AND rp.code in ('EMDDPP','EMIDPP','EMIDTB','EMIDTE','EMDDTB','EMDDTE') -- pour les polyvalent on prend également les activités en compte
    AND coalesce(h.activite_id,0) not in (-1,-10) -- on ne prend pas les activtés bidons "complément heures garanties"
    and e.idtrt=h.idtrt and e.idtrt in (select id from pai_int_traitement where typetrt like '%CLOTURE%' and anneemois>='201509')
	  GROUP BY e.matricule,e.rc,g.code,e.d,g.libelle
    having COUNT(DISTINCT h.date_distrib)<>ev.qte
  UNION ALL
	  SELECT 'NB JOUR',e.matricule,e.rc,g.poste,e.d,COUNT(DISTINCT h.date_distrib),ev.qte
	  FROM pai_ev_emp_pop_depot_hst e
    INNER JOIN ref_population rp on e.population_id=rp.id
	  INNER JOIN pai_ev_heure_hst h ON e.employe_id=h.employe_id /*AND e.depot_id=h.depot_id*/ AND h.date_distrib BETWEEN e.d AND e.f
    -- INNER JOIN pai_ev_tournee t ON e.employe_id=t.employe_id /*AND e.depot_id=t.depot_id*/ AND t.date_distrib BETWEEN e.d AND e.f
    LEFT OUTER JOIN ref_activite ra on h.activite_id=ra.id
	  INNER JOIN pai_ref_postepaie_general g ON g.code='JTP'
    INNER JOIN pai_ev_hst ev on e.idtrt=ev.idtrt and e.matricule=ev.matricule and e.d=ev.datev and ev.poste='JTPX'
	  WHERE (e.typetournee_id=1 AND h.typejour_id=1 		    -- SDVP et semaine
	  OR 	 e.typetournee_id=2 AND h.typejour_id IN (1,3)) 	-- Media et semaine+ferie
	  AND rp.code in ('EMIDPO','EMDDPO','EMIDRB','EMIDRE','EMDDRB','EMDDRE')
    and coalesce(ra.est_JTPX,true) -- On ne prend que les tournées ou les activités qui on le flag JTPX
    and e.idtrt=h.idtrt and e.idtrt in (select id from pai_int_traitement where typetrt like '%CLOTURE%' and anneemois>='201509')
	  GROUP BY e.matricule, e.rc, g.code, e.d, g.libelle
    having COUNT(DISTINCT h.date_distrib)<>ev.qte;
    
    select * from pai_ev_hst where poste='JTPX'
    
  insert into tmp_JTPX  (NB JOUR, matricule, rc, poste, d, nouveau, ancien) VALUES
('NB JOUR', '7000266720', '000001', 'JTPX', '21/09/2015 00:00:00', '17', 16.00), 
('NB JOUR', 'MEA3445820', '900001', 'JTPX', '21/09/2015 00:00:00', '26', 22.00), 
('NB JOUR', 'MEK7553320', '900001', 'JTPX', '21/08/2015 00:00:00', '25', 22.00), 
('NB JOUR', 'MEK7553320', '900001', 'JTPX', '21/09/2015 00:00:00', '20', 18.00), 
('NB JOUR', 'MEK9486120', '900001', 'JTPX', '21/09/2015 00:00:00', '26', 25.00), 
('NB JOUR', 'MEK9487120', '900001', 'JTPX', '21/08/2015 00:00:00', '26', 25.00), 
('NB JOUR', 'MEK9487120', '900001', 'JTPX', '21/09/2015 00:00:00', '26', 22.00), 
('NB JOUR', 'MEK9493020', '900001', 'JTPX', '21/09/2015 00:00:00', '26', 25.00), 
('NB JOUR', 'MEL9512520', '900001', 'JTPX', '21/09/2015 00:00:00', '26', 25.00), 
('NB JOUR', 'MEM7845120', '900001', 'JTPX', '21/09/2015 00:00:00', '25', 24.00), 
('NB JOUR', 'MET0170720', '900001', 'JTPX', '21/09/2015 00:00:00', '25', 24.00), 
('NB JOUR', 'Z005028320', '900003', 'JTPX', '28/09/2015 00:00:00', '20', 18.00)
select * from employe where nom='IDEKAR'