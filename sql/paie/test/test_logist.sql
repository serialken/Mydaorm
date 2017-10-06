selec`utilisateur`t COUNT(*) FROM client_a_servir_logist WHERE tournee_jour_id IS NULL;
SELECT COUNT(*) FROM client_a_servir_logist;

SELECT COUNT(*) FROM client_a_servir_logist WHERE tournee_jour_id IS  NULL AND date_distrib IS NOT NULL;
SELECT * FROM client_a_servir_logist WHERE tournee_jour_id IS  NULL AND date_distrib<>'0000-00-00';
SELECT DAYOFWEEK(CURDATE()) FROM DUAL;
SELECT CURDATE() FROM DUAL;
SELECT COUNT(*) FROM modele_tournee;

UPDATE client_a_servir_logist l
INNER JOIN modele_tournee_jour mtj ON l.tournee_jour_id=mtj.id 
INNER JOIN modele_tournee mt ON mtj.tournee_id=mt.`id`
INNER JOIN groupe_tournee gt ON mt.groupe_id=gt.id
SET l.tournee_jour_id=NULL
WHERE mtj.jour_id<>DAYOFWEEK(l.date_distrib) OR l.depot_id<>gt.depot_id OR l.flux_id<>gt.flux_id;


SELECT * FROM  client_a_servir_logist l
INNER JOIN modele_tournee_jour mtj ON l.tournee_jour_id=mtj.id 
INNER JOIN modele_tournee mt ON mtj.tournee_id=mt.`id`
INNER JOIN groupe_tournee gt ON mt.groupe_id=gt.id
WHERE mtj.jour_id<>DAYOFWEEK(l.date_distrib) OR l.depot_id<>gt.depot_id OR l.flux_id<>gt.flux_id
AND date_distrib='2014-07-02';

UPDATE client_a_servir_logist l SET tournee_jour_id=NULL;
COMMIT;

SELECT COUNT(*) FROM modele_tournee;
SELECT * FROM client_a_servir_logist WHERE date_distrib IS NULL;
      UPDATE client_a_servir_logist l
      SET tournee_jour_id=(SELECT id
                          FROM modele_tournee_jour mtj
                          WHERE mtj.jour_id=DAYOFWEEK(l.date_distrib)
                          AND mtj.tournee_id=(MOD(l.id+10000,1518)+1)
                         AND l.depot_id=gt.depot_id AND l.flux_id=gt.flux_id
                          )
      WHERE tournee_jour_id IS NULL AND date_distrib IS NOT NULL;
      
      SELECT l.id,l.tournee_jour_id,l.date_distrib,DAYOFWEEK(l.date_distrib),mtj.id,mtj.jour_id
      FROM client_a_servir_logist l
      LEFT OUTER JOIN modele_tournee_jour mtj
                          ON mtj.jour_id=DAYOFWEEK(l.date_distrib);
      WHERE l.id=67418;
     
CALL init_logist(NULL);     
SELECT DAYOFWEEK('2014-07-06');


SELECT date_distrib,COUNT(*) FROM client_a_servir_logist l GROUP BY date_distrib;
SELECT date_distrib,COUNT(*) FROM pai_tournee GROUP BY date_distrib;
SELECT * FROM client_a_servir_logist l WHERE tournee_jour_id IS NULL;
SELECT id FROM modele_tournee_jour mtj WHERE mtj.jour_id=1;