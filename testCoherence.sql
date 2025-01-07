-- Se connecter à une base spécifique
USE etu_matchabrier;

-- Vérifier la connexion en listant les tables
SHOW TABLES;


-- Vérifier le résultat avec quelques requêtes simples
SELECT * FROM T_USER_USR;
SELECT * FROM T_TEAM_TEA;
SELECT * FROM T_CHAMPIONSHIP_CHP;
SELECT * FROM T_MATCH_MAT;

DROP PROCEDURE IF EXISTS UserNull;
DROP PROCEDURE IF EXISTS TeamNull;
DROP PROCEDURE IF EXISTS MailNotUnique;
DELIMITER $$


CREATE PROCEDURE UserNull()
BEGIN
    DECLARE usr_id INT;
    DECLARE tea_id INT;
    DECLARE usr_email VARCHAR(255);
    DECLARE usr_fname VARCHAR(255);
    DECLARE usr_name VARCHAR(255);
    DECLARE usr_isorg boolean;
    DECLARE usr_password VARCHAR(255);
    DECLARE done INT DEFAULT 0;

    DECLARE c CURSOR FOR SELECT * FROM T_USER_USR;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    OPEN c;

    boucle_curseur: LOOP
        FETCH c INTO usr_ID, tea_ID, usr_fname, usr_name, usr_email, usr_isorg, usr_password;

        IF done = 1 THEN 
            LEAVE boucle_curseur;
        END IF;

        IF usr_id IS NULL THEN
            SELECT 'ERROR: usr_id is null' AS Message;
        END IF;

        IF usr_fname IS NULL THEN
            SELECT 'ERROR: usr_fname is null' AS Message;
        END IF;

        IF usr_name IS NULL THEN
            SELECT 'ERROR: usr_name is null' AS Message;
        END IF;

        IF usr_email IS NULL THEN
            SELECT 'ERROR: usr_email is null' AS Message;
        END IF;

        IF usr_isorg IS NULL THEN
            SELECT 'ERROR: usr_isorg is null' AS Message;
        END IF;

        IF usr_password IS NULL THEN
            SELECT 'ERROR: usr_password is null' AS Message;
        END IF;

    END LOOP boucle_curseur;

    CLOSE c;
END$$


CREATE PROCEDURE TeamNull()
BEGIN
    DECLARE tea_id INT;
    DECLARE tea_name VARCHAR(255);
    DECLARE done INT DEFAULT 0;


    DECLARE c CURSOR FOR SELECT tea_ID, tea_name FROM T_TEAM_TEA;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    OPEN c;

    boucle_curseur: LOOP
        FETCH c INTO tea_ID, tea_name;

        IF done = 1 THEN 
            LEAVE boucle_curseur;
        END IF;

        IF tea_id IS NULL THEN
            SELECT 'ERROR: usr_id is null' AS Message;
        END IF;

        IF tea_name IS NULL THEN
            SELECT 'ERROR: usr_fname is null' AS Message;
        END IF;

    END LOOP boucle_curseur;

    CLOSE c;
END$$


CREATE PROCEDURE MailNotUnique()
BEGIN
    DECLARE usr_email VARCHAR(255);
    DECLARE done INT DEFAULT 0;

    DECLARE c CURSOR FOR SELECT usr_email FROM T_USER_USR;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    OPEN c;

    boucle_curseur: LOOP
        FETCH c INTO usr_email;

        IF done = 1 THEN
            LEAVE boucle_curseur;
        END IF;

        SELECT CONCAT('Processing email: ', usr_email) AS Email_Info;

    END LOOP boucle_curseur;

    -- Fermer le curseur
    CLOSE c;
END$$


DELIMITER ; 

call UserNull;
call TeamNull;

ROLLBACK;

