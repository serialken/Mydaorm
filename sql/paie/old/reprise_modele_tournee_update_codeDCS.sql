DROP TABLE tmp_modele_tournee;
CREATE TABLE tmp_modele_tournee AS 
SELECT mt.id,
(SELECT MIN(mt2.id) FROM modele_tournee mt2 INNER JOIN groupe_tournee g2 ON mt2.groupe_id=g2.id WHERE g2.depot_id=g.depot_id) AS minid
FROM modele_tournee mt
INNER JOIN groupe_tournee g ON groupe_id=g.id;

UPDATE modele_tournee mt SET codeDCS='';
UPDATE modele_tournee mt
INNER JOIN tmp_modele_tournee tmt ON mt.id=tmt.id
INNER JOIN groupe_tournee g ON mt.groupe_id=g.id
SET codeDCS=(
	CASE
	WHEN mt.id+1-tmt.minid<100 THEN	CONCAT(CHAR(g.depot_id+64),LPAD(mt.id+1-tmt.minid,2,'0'))
	ELSE CONCAT(LPAD(mt.id+1-tmt.minid-99,2,'0'),CHAR(g.depot_id+64))
	END);
-- DROP TABLE tmp_modele_tournee;

CREATE UNIQUE INDEX UNIQ_EE7B87ACE07FDBA1 ON modele_tournee (codeDCS);


SELECT * FROM tmp_modele_tournee WHERE id IN (SELECT id FROM modele_tournee WHERE codeDCS IN (SELECT codeDCS FROM modele_tournee GROUP BY codeDCS HAVING COUNT(*)>1));
SELECT * FROM modele_tournee WHERE codeDCS IN (SELECT codeDCS FROM modele_tournee GROUP BY codeDCS HAVING COUNT(*)>1);
SELECT * FROM modele_tournee WHERE codeDCS='';

SELECT g.depot_id ,COUNT(*)
FROM modele_tournee mt
INNER JOIN groupe_tournee g ON mt.groupe_id=g.id
GROUP BY g.depot_id;


select * from modele_tournee where codeDCS like '%N%';
select * from modele_tournee where codeDCS like '%D%';
select * from modele_tournee where codeDCS like '%Z%';
select codeDCS,replace(codeDCS,'D','Z') from modele_tournee where codeDCS like '%D%';
update modele_tournee set codeDCS=replace(codeDCS,'D','Z')where codeDCS like '%D%';
update modele_tournee set codeDCS=replace(codeDCS,'N','D')where codeDCS like '%N%';