    INSERT INTO pai_ev_qualite(idtrt,employe_id,datev,qualite)
    SELECT _idtrt,e.employe_id,e.d,
    CASE WHEN e.nbabo<>0 THEN 
      (SELECT SUM(coalesce(r.nbrec_abonne,0))*1000/coalesce(e.nbabo,0) 
      FROM pai_reclamation r
      INNER JOIN pai_tournee t on r.tournee_id=t.id
      WHERE e.employe_id=t.employe_id
      AND t.date_distrib between e.dCtr AND e.f 
--      AND t.date_distrib between e.d AND e.f 
      AND t.typejour_id=1/*='S' ATTENTION, faut-il prendre les jours ferie */ 
      GROUP BY t.employe_id)
    ELSE 0
    END
    FROM pai_ev_emp_pop e
    INNER JOIN ref_population rp on e.population_id=rp.id
    WHERE (e.typetournee_id=1 and rp.code in ('EMIDPO','EMDDPO') -- SDVP : que porteur
    OR    e.typetournee_id=2) -- Neo/Media : tout le monde
    ;