-- SELECT d.libelle,g.code FROM depot d INNER JOIN groupe_tournee g ON g.depot_id=d.id WHERE heure_debut='00:00';

UPDATE groupe_tournee g SET heure_debut='00:00';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='03:00',g.libelle='77N' WHERE d.code LIKE '%04' AND g.code LIKE '%M';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='03:00',g.libelle='77N' WHERE d.code LIKE '%04' AND g.code LIKE '%O';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='03:30',g.libelle='NOBS PMA' WHERE d.code LIKE '%04' AND g.code LIKE '%W';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='03:30 ',g.libelle='NOBS PMA' WHERE d.code LIKE '%04' AND g.code LIKE '%Y';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:00',g.libelle='77S' WHERE d.code LIKE '%07' AND g.code LIKE '%B';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:00',g.libelle='77S' WHERE d.code LIKE '%07' AND g.code LIKE '%Y';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:30',g.libelle='Dimanche' WHERE d.code LIKE '%07' AND g.code LIKE '%F';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:00',g.libelle='78' WHERE d.code LIKE '%10' AND g.code LIKE '%A';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:30',g.libelle='Dimanche' WHERE d.code LIKE '%10' AND g.code LIKE '%D';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:00',g.libelle='Nouvel Obs // Point //Express // Paris Match' WHERE d.code LIKE '%10' AND g.code LIKE '%W';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:00',g.libelle='91' WHERE d.code LIKE '%13' AND g.code LIKE '%H';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:00',g.libelle='NOBS' WHERE d.code LIKE '%13' AND g.code LIKE '%W';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:30',g.libelle='94' WHERE d.code LIKE '%13' AND g.code LIKE '%Z';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:30',g.libelle='Dimanche' WHERE d.code LIKE '%13' AND g.code LIKE '%H';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:00',g.libelle='91' WHERE d.code LIKE '%14' AND g.code LIKE '%V';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:00',g.libelle='94' WHERE d.code LIKE '%18' AND g.code LIKE '%B';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:30',g.libelle='Dimanche' WHERE d.code LIKE '%18' AND g.code LIKE '%D';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:00',g.libelle='' WHERE d.code LIKE '%18' AND g.code LIKE '%W';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:00',g.libelle='93 et 95' WHERE d.code LIKE '%23' AND g.code LIKE '%S';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:00',g.libelle='95' WHERE d.code LIKE '%24' AND g.code LIKE '%O';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:00',g.libelle='NOBS PMA' WHERE d.code LIKE '%24' AND g.code LIKE '%W';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:30',g.libelle='Dimanche' WHERE d.code LIKE '%24' AND g.code LIKE '%';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='05:00',g.libelle='75' WHERE d.code LIKE '%28' AND g.code LIKE '%B';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:00',g.libelle='92 zone st cloud' WHERE d.code LIKE '%28' AND g.code LIKE '%C';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:30',g.libelle='92 zone nanterre ' WHERE d.code LIKE '%28' AND g.code LIKE '%N';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:00',g.libelle='94' WHERE d.code LIKE '%29' AND g.code LIKE '%A';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:30',g.libelle='92' WHERE d.code LIKE '%29' AND g.code LIKE '%B';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='05:00',g.libelle='75 + 2 GMS' WHERE d.code LIKE '%29' AND g.code LIKE '%C';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='05:00',g.libelle='Dimanche' WHERE d.code LIKE '%29' AND g.code LIKE '%D';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:30',g.libelle='93' WHERE d.code LIKE '%31' AND g.code LIKE '%A';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='03:30',g.libelle='77N' WHERE d.code LIKE '%31' AND g.code LIKE '%U';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:30',g.libelle='' WHERE d.code LIKE '%31' AND g.code LIKE '%Y';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:00',g.libelle='78' WHERE d.code LIKE '%33' AND g.code LIKE '%C';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:00',g.libelle='Dimanche' WHERE d.code LIKE '%33' AND g.code LIKE '%D';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:30',g.libelle='92' WHERE d.code LIKE '%33' AND g.code LIKE '%G';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:00',g.libelle='95' WHERE d.code LIKE '%33' AND g.code LIKE '%P';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='03:30',g.libelle='60' WHERE d.code LIKE '%34' AND g.code LIKE '%D';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='03:30',g.libelle='60' WHERE d.code LIKE '%34' AND g.code LIKE '%3';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:30',g.libelle='Dimanche' WHERE d.code LIKE '%35' AND g.code LIKE '%D';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:00',g.libelle='77' WHERE d.code LIKE '%35' AND g.code LIKE '%M';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:00',g.libelle='91' WHERE d.code LIKE '%35' AND g.code LIKE '%S';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:00',g.libelle='77+91' WHERE d.code LIKE '%35' AND g.code LIKE '%W';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='03:30',g.libelle='77' WHERE d.code LIKE '%36' AND g.code LIKE '%H';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:30',g.libelle='93' WHERE d.code LIKE '%36' AND g.code LIKE '%I';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:00',g.libelle='94' WHERE d.code LIKE '%36' AND g.code LIKE '%J';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='03:30',g.libelle='94' WHERE d.code LIKE '%36' AND g.code LIKE '%W';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:00',g.libelle='' WHERE d.code LIKE '%36' AND g.code LIKE '%3';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:30',g.libelle='Dimanche' WHERE d.code LIKE '%39' AND g.code LIKE '%D';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:00',g.libelle='91+77' WHERE d.code LIKE '%39' AND g.code LIKE '%E';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:30',g.libelle='93' WHERE d.code LIKE '%40' AND g.code LIKE '%A';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='05:00',g.libelle='75' WHERE d.code LIKE '%40' AND g.code LIKE '%B';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='05:00',g.libelle='92' WHERE d.code LIKE '%40' AND g.code LIKE '%C';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:00',g.libelle='specifique' WHERE d.code LIKE '%40' AND g.code LIKE '%W';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:00',g.libelle='specifique' WHERE d.code LIKE '%40' AND g.code LIKE '%Z';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:30',g.libelle='93' WHERE d.code LIKE '%41' AND g.code LIKE '%O';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:30',g.libelle='94' WHERE d.code LIKE '%41' AND g.code LIKE '%P';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:30',g.libelle='tournée NOBS' WHERE d.code LIKE '%41' AND g.code LIKE '%W';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:30',g.libelle='tournée ELLE' WHERE d.code LIKE '%41' AND g.code LIKE '%Y';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:30',g.libelle='Dimanche' WHERE d.code LIKE '%45' AND g.code LIKE '%D';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:00',g.libelle='78' WHERE d.code LIKE '%45' AND g.code LIKE '%M';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='04:00',g.libelle='NOBS/PMA' WHERE d.code LIKE '%45' AND g.code LIKE '%W';
UPDATE groupe_tournee g INNER JOIN depot d ON g.depot_id=d.id  SET heure_debut='00:00',g.libelle='TOURNEE PB ACCES' WHERE d.code LIKE '%45' AND g.code LIKE '%9';
UPDATE groupe_tournee g SET heure_debut='04:00' WHERE heure_debut='00:00';
