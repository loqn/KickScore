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
DROP PROCEDURE IF EXISTS ScoresNegatifs;
DROP PROCEDURE IF EXISTS RencontresIdentiques;
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

        /*case 
          when usr_id IS NULL then SELECT 'ERROR: usr_id is null in user' AS Message;
          when 
          else 
        end;*/

        IF usr_id IS NULL THEN
            SELECT 'ERROR: usr_id is null in user' AS Message;
        END IF;

        IF usr_fname IS NULL THEN
            SELECT 'ERROR: usr_fname is null in user' AS Message;
        END IF;

        IF usr_name IS NULL THEN
            SELECT 'ERROR: usr_name is null in user' AS Message;
        END IF;

        IF usr_email IS NULL THEN
            SELECT 'ERROR: usr_email is null in user' AS Message;
        END IF;

        IF usr_isorg IS NULL THEN
            SELECT 'ERROR: usr_isorg is null in user' AS Message;
        END IF;

        IF usr_password IS NULL THEN
            SELECT 'ERROR: usr_password is null in user' AS Message;
        END IF;

    END LOOP boucle_curseur;

    CLOSE c;
END$$


CREATE PROCEDURE TeamNull()
BEGIN
    DECLARE tea_id2 INT;
    DECLARE tea_name2 VARCHAR(255);
    DECLARE done INT DEFAULT 0;

    DECLARE c CURSOR FOR SELECT TEA_ID, TEA_NAME FROM T_TEAM_TEA;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    OPEN c;

    boucle_curseur: LOOP
    
        FETCH c INTO tea_id2, tea_name2;

        IF done = 1 THEN
            LEAVE boucle_curseur;
        END IF;

        IF tea_id2 IS NULL THEN
            SELECT 'ERROR: TEA_ID is null in team' AS Message;
        END IF;

        IF tea_name2 IS NULL THEN
            SELECT 'ERROR: TEA_NAME is null in team' AS Message;
        END IF;

    END LOOP boucle_curseur;

    CLOSE c;
END$$


CREATE PROCEDURE MailNotUnique()
BEGIN
    DECLARE email_id INT;
    DECLARE email_id2 INT;
    DECLARE usr_email VARCHAR(255);
    DECLARE usr_email2 VARCHAR(255);
    DECLARE done INT DEFAULT 0;

    DECLARE c CURSOR FOR SELECT email_id, usr_email FROM T_USER_USR;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    OPEN c;

    boucle_curseur: LOOP
        FETCH c INTO email_id, usr_email;

        IF done = 1 THEN
            LEAVE boucle_curseur;
        END IF;

            boucle_curseur2: LOOP
                FETCH c INTO email_id2, usr_email2;

                IF done = 1 THEN
                    LEAVE boucle_curseur2;
                END IF;
                
                IF usr_email = usr_email2 and email_id != email_id2 THEN
                    SELECT 'ERROR: usr' AS Message;
                END IF;

            END LOOP boucle_curseur2;
    

    END LOOP boucle_curseur;

    CLOSE c;
END$$

CREATE PROCEDURE ScoresNegatifs()
BEGIN
    DECLARE greenScore INT;
    DECLARE blueScore INT;
    DECLARE done INT DEFAULT 0;

    DECLARE c CURSOR FOR SELECT MAT_GREENSCORE, MAT_BLUESCORE FROM T_MATCH_MAT;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    OPEN c;

    boucle_curseur: LOOP
    
        FETCH c INTO greenScore, blueScore;

        IF done = 1 THEN
            LEAVE boucle_curseur;
        END IF;

        IF greenScore < 0 THEN
            SELECT 'ERROR: green score < 0' AS Message;
        END IF;

        IF blueScore < 0 THEN
            SELECT 'ERROR: blue score < 0' AS Message;
        END IF;

    END LOOP boucle_curseur;

    CLOSE c;
END$$


CREATE PROCEDURE RencontresIdentiques()
BEGIN
    DECLARE id_tea_blue INT;
    DECLARE id_tea_green INT;
    DECLARE id_mat1 INT;
    DECLARE id_mat2 INT;
    DECLARE done INT DEFAULT 0;

    DECLARE c CURSOR FOR SELECT MAT_ID, TEA_ID, TEA_ID_MAT_TEAMGREEN FROM T_MATCH_MAT;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    OPEN c;

    boucle_curseur: LOOP

        FETCH c INTO id_mat1 ,id_tea_green, id_tea_blue;

        IF done = 1 THEN
            LEAVE boucle_curseur;
        END IF;

        boucle_curseur2: LOOP
    
            FETCH c INTO id_mat2 ,id_tea_green, id_tea_blue;

            IF done = 1 THEN
                LEAVE boucle_curseur2;
            END IF;

            IF id_tea_blue = id_tea_green and id_mat1 != id_mat2 THEN
                LEAVE boucle_curseur2;
            END IF;

        END LOOP boucle_curseur2;
        

    END LOOP boucle_curseur;

    CLOSE c;
END$$



DELIMITER ; 





call UserNull;
call TeamNull;
call MailNotUnique;
call ScoresNegatifs;
call RencontresIdentiques;

ROLLBACK;

