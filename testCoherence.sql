-- Se connecter à une base spécifique
USE etu_matchabrier;
/*
-- Vérifier la connexion en listant les tables
SHOW TABLES;


-- Vérifier le résultat avec quelques requêtes simples
SELECT * FROM T_USER_USR;
SELECT * FROM T_TEAM_TEA;
SELECT * FROM T_CHAMPIONSHIP_CHP;
SELECT * FROM T_MATCH_MAT;
*/

DROP PROCEDURE IF EXISTS UserNull;
DROP PROCEDURE IF EXISTS TeamNull;
DROP PROCEDURE IF EXISTS MailNotUnique;
DROP PROCEDURE IF EXISTS ScoresNegatifs;
DROP PROCEDURE IF EXISTS RencontresIdentiques;
DROP PROCEDURE IF EXISTS MemberNull;
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
            SIGNAL SQLSTATE '45000' 
                SET MESSAGE_TEXT = 'ERROR: usr_id is null in user', 
                    MYSQL_ERRNO = 1001;
        END IF;

        IF usr_fname IS NULL THEN
            SIGNAL SQLSTATE '45000' 
                SET MESSAGE_TEXT = 'ERROR: usr_fname is null in user', 
                    MYSQL_ERRNO = 1002;
        END IF;

        IF usr_name IS NULL THEN
            SIGNAL SQLSTATE '45000' 
                SET MESSAGE_TEXT = 'ERROR: usr_name is null in user', 
                    MYSQL_ERRNO = 1003;
        END IF;

        IF usr_email IS NULL THEN
            SIGNAL SQLSTATE '45000' 
                SET MESSAGE_TEXT = 'ERROR: usr_email is null in user', 
                    MYSQL_ERRNO = 1004;
        END IF;

        IF usr_isorg IS NULL THEN
            SIGNAL SQLSTATE '45000' 
                SET MESSAGE_TEXT = 'ERROR: usr_isorg is null in user', 
                    MYSQL_ERRNO = 1005;
        END IF;

        IF usr_password IS NULL THEN
            SIGNAL SQLSTATE '45000' 
                SET MESSAGE_TEXT = 'ERROR: usr_password is null in user', 
                    MYSQL_ERRNO = 1006;
        END IF;

    END LOOP boucle_curseur;

    select "test 1 passed : No necessary attribute missing in user." as test1;
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
            SIGNAL SQLSTATE '45000' 
                SET MESSAGE_TEXT = 'ERROR: TEA_ID is null in team', 
                    MYSQL_ERRNO = 1007;
        END IF;

        IF tea_name2 IS NULL THEN
            SIGNAL SQLSTATE '45000' 
                SET MESSAGE_TEXT = 'ERROR: TEA_NAME is null in team', 
                    MYSQL_ERRNO = 1008;
        END IF;

    END LOOP boucle_curseur;

    select "test 2 passed : No necessary attribute missing in team." as test2;
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
                SIGNAL SQLSTATE '45000' 
                    SET MESSAGE_TEXT = 'ERROR: Two matchs with the same teams', 
                        MYSQL_ERRNO = 1009;
                END IF;

            END LOOP boucle_curseur2;
    

    END LOOP boucle_curseur;

    CLOSE c;

    select "test 3 passed : All mails are unique." as test3;
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
            SIGNAL SQLSTATE '45000' 
                SET MESSAGE_TEXT = 'ERROR: TEA_ID is null in team ERROR: green score < 0', 
                    MYSQL_ERRNO = 1010;
        END IF;

        IF blueScore < 0 THEN
            SELECT 'ERROR: blue score < 0' AS Message;
        END IF;

    END LOOP boucle_curseur;

    select "test 4 passed : No negative score." as test4;
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
                SIGNAL SQLSTATE '45000' 
                SET MESSAGE_TEXT = 'ERROR: TEA_ID is null in team ERROR: green score < 0', 
                    MYSQL_ERRNO = 1010;
            END IF;

        END LOOP boucle_curseur2;
        
    select "test 5 passed : No match with the same teams but." as test5;
    END LOOP boucle_curseur;

    CLOSE c;
END$$

CREATE PROCEDURE MemberNull()
BEGIN
    DECLARE mem_id INT;
    DECLARE mem_fname2 VARCHAR(255);
    DECLARE mem_name2 VARCHAR(255);
    DECLARE mem_email2 VARCHAR(255);
    DECLARE done INT DEFAULT 0;

    DECLARE c CURSOR FOR SELECT MEM_ID, MEM_FNAME, MEM_NAME, MEM_EMAIL FROM T_MEMBER_MEM;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    OPEN c;

    boucle_curseur: LOOP
        FETCH c INTO mem_id, mem_fname2, mem_name2, mem_email2;

        IF done = 1 THEN
            LEAVE boucle_curseur;
        END IF;

        IF mem_fname2 IS NULL THEN
            SIGNAL SQLSTATE '45000' 
                SET MESSAGE_TEXT = 'ERROR: mem_fname is null in member', 
                    MYSQL_ERRNO = 1011;
        END IF;

        IF mem_name2 IS NULL THEN
            SIGNAL SQLSTATE '45000' 
                SET MESSAGE_TEXT = 'ERROR: mem_name is null in member', 
                    MYSQL_ERRNO = 1012;
        END IF;

        IF mem_email2 IS NULL THEN
            SIGNAL SQLSTATE '45000' 
                SET MESSAGE_TEXT = 'ERROR: mem_email is null in member', 
                    MYSQL_ERRNO = 1013;
        END IF;

    END LOOP boucle_curseur;

    select "test 6 passed : No necessary attribute missing in team" as test6;


    CLOSE c;
END$$

DELIMITER ; 





call UserNull;
call TeamNull;
call MailNotUnique;
call ScoresNegatifs;
call RencontresIdentiques;
call MemberNull;


