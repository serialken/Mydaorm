
/* --- Fonction Mysql REGEX_REPLACE_1 : Remplace un par un les caracteres --- */
/*
Exemple : 
> select REGEX_REPLACE_1('[^a-zA-Z0-9\-]','|','2my test3_text-to. check \\ my- sql (regular) ,expressions ._,');
    => 2my|test3|text-to||check|||my-|sql||regular|||expressions||||

> SELECT REGEX_REPLACE_1('[a-fi]', '-', 'aeiouy');
    => ---ouy
*/
DROP FUNCTION IF EXISTS  `REGEX_REPLACE_1`;
CREATE FUNCTION  `REGEX_REPLACE_1`(pattern VARCHAR(1000), replacement VARCHAR(1000), original VARCHAR(1000))

RETURNS VARCHAR(1000)
DETERMINISTIC
BEGIN 
 DECLARE temp VARCHAR(1000); 
 DECLARE ch VARCHAR(1); 
 DECLARE i INT;
 SET i = 1;
 SET temp = '';
 IF original REGEXP pattern THEN 
  loop_label: LOOP 
   IF i>CHAR_LENGTH(original) THEN
    LEAVE loop_label;  
   END IF;
   SET ch = SUBSTRING(original,i,1);
   IF NOT ch REGEXP pattern THEN
    SET temp = CONCAT(temp,ch);
   ELSE
    SET temp = CONCAT(temp,replacement);
   END IF;
   SET i=i+1;
  END LOOP;
 ELSE
  SET temp = original;
 END IF;
 RETURN temp;
END;


DROP FUNCTION IF EXISTS  `get_distance_kilometre`;
CREATE FUNCTION `get_distance_kilometre`(lat1 DOUBLE, lng1 DOUBLE, lat2 DOUBLE, lng2 DOUBLE) RETURNS double
    NO SQL
    DETERMINISTIC
BEGIN
 DECLARE rlo1 DOUBLE;
    DECLARE rla1 DOUBLE;
    DECLARE rlo2 DOUBLE;
    DECLARE rla2 DOUBLE;
    DECLARE dlo DOUBLE;
    DECLARE dla DOUBLE;
    DECLARE a DOUBLE;
 
    SET rlo1 = RADIANS(lng1);
    SET rla1 = RADIANS(lat1);
    SET rlo2 = RADIANS(lng2);
    SET rla2 = RADIANS(lat2);
    SET dlo = (rlo2 - rlo1) / 2;
    SET dla = (rla2 - rla1) / 2;
    SET a = SIN(dla) * SIN(dla) + COS(rla1) * COS(rla2) * SIN(dlo) * SIN(dlo);

RETURN (6378137 * 2 * ATAN2(SQRT(a), SQRT(1 - a))) / 1000;
END

/*
DELIMITER $$
CREATE FUNCTION  `REGEX_REPLACE`(pattern VARCHAR(1000),replacement VARCHAR(1000),original VARCHAR(1000))

RETURNS VARCHAR(1000)
DETERMINISTIC
BEGIN 
 DECLARE temp VARCHAR(1000); 
 DECLARE ch VARCHAR(1); 
 DECLARE i INT;
 SET i = 1;
 SET temp = '';
 IF original REGEXP pattern THEN 
  loop_label: LOOP 
   IF i>CHAR_LENGTH(original) THEN
    LEAVE loop_label;  
   END IF;
   SET ch = SUBSTRING(original,i,1);
   IF NOT ch REGEXP pattern THEN
    SET temp = CONCAT(temp,ch);
   ELSE
    SET temp = CONCAT(temp,replacement);
   END IF;
   SET i=i+1;
  END LOOP;
 ELSE
  SET temp = original;
 END IF;
 RETURN temp;
END$$
DELIMITER ;
*/
