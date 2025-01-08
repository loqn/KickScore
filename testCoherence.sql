DROP PROCEDURE IF EXISTS UserNull;
DROP PROCEDURE IF EXISTS TeamNull;
DROP PROCEDURE IF EXISTS MailNotUnique;
DROP PROCEDURE IF EXISTS ScoresNegatifs;
DROP PROCEDURE IF EXISTS RencontresIdentiques;
DROP PROCEDURE IF EXISTS MemberNull;

DELIMITER $$


CREATE PROCEDURE UserNull()
BEGIN

    DECLARE my_usr_id INT;
    DECLARE my_usr_email VARCHAR(255);
    DECLARE my_usr_fname VARCHAR(255);
    DECLARE my_usr_name VARCHAR(255);
    DECLARE my_usr_isorg boolean;
    DECLARE my_usr_password VARCHAR(255);
    DECLARE done INT DEFAULT 0;

    DECLARE c CURSOR FOR SELECT USR_ID, USR_FNAME, USR_NAME, USR_EMAIL, USR_ISORG, USR_PASSWORD FROM T_USER_USR;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    OPEN c;

    boucle_curseur: LOOP
    
        FETCH c INTO my_usr_ID, my_usr_fname, my_usr_name, my_usr_email, my_usr_isorg, my_usr_password;

        IF done = 1 THEN
            LEAVE boucle_curseur;
        END IF;

        IF my_usr_id IS NULL THEN
            SIGNAL SQLSTATE '45000' 
                SET MESSAGE_TEXT = 'ERROR: usr_id is null in user', 
                    MYSQL_ERRNO = 1001;
        END IF;

        IF my_usr_fname IS NULL THEN
            SIGNAL SQLSTATE '45000' 
                SET MESSAGE_TEXT = 'ERROR: usr_fname is null in user', 
                    MYSQL_ERRNO = 1002;
        END IF;

        IF my_usr_name IS NULL THEN
            SIGNAL SQLSTATE '45000' 
                SET MESSAGE_TEXT = 'ERROR: usr_name is null in user', 
                    MYSQL_ERRNO = 1003;
        END IF;

        IF my_usr_email IS NULL THEN
            SIGNAL SQLSTATE '45000' 
                SET MESSAGE_TEXT = 'ERROR: usr_email is null in user', 
                    MYSQL_ERRNO = 1004;
        END IF;

        IF my_usr_isorg IS NULL THEN
            SIGNAL SQLSTATE '45000' 
                SET MESSAGE_TEXT = 'ERROR: usr_isorg is null in user', 
                    MYSQL_ERRNO = 1005;
        END IF;

        IF my_usr_password IS NULL THEN
            SIGNAL SQLSTATE '45000' 
                SET MESSAGE_TEXT = 'ERROR: usr_password is null in user', 
                    MYSQL_ERRNO = 1006;
        END IF;

    END LOOP boucle_curseur;

    SELECT "test 1 passed : No necessary attribute missing in user." AS test1;
    CLOSE c;

END$$


CREATE PROCEDURE TeamNull()
BEGIN
    DECLARE my_tea_id2 INT;
    DECLARE my_tea_name2 VARCHAR(255);
    DECLARE done INT DEFAULT 0;

    DECLARE c CURSOR FOR SELECT TEA_ID, TEA_NAME FROM T_TEAM_TEA;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    OPEN c;

    boucle_curseur: LOOP
    
        FETCH c INTO my_tea_id2, my_tea_name2;

        IF done = 1 THEN
            LEAVE boucle_curseur;
        END IF;

        IF my_tea_id2 IS NULL THEN
            SIGNAL SQLSTATE '45000' 
                SET MESSAGE_TEXT = 'ERROR: TEA_ID is null in team', 
                    MYSQL_ERRNO = 1007;
        END IF;

        IF my_tea_name2 IS NULL THEN
            SIGNAL SQLSTATE '45000' 
                SET MESSAGE_TEXT = 'ERROR: TEA_NAME is null in team', 
                    MYSQL_ERRNO = 1008;
        END IF;

    END LOOP boucle_curseur;

    SELECT "test 2 passed : No necessary attribute missing in team." AS test2;
    CLOSE c;

END$$


CREATE PROCEDURE MailNotUnique()
BEGIN

    DECLARE my_email_id INT;
    DECLARE my_email_id2 INT;
    DECLARE my_usr_email VARCHAR(255);
    DECLARE my_usr_email2 VARCHAR(255);
    DECLARE done INT DEFAULT 0;

    DECLARE c CURSOR FOR SELECT usr_id, usr_email FROM T_USER_USR;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    OPEN c;

    boucle_curseur: LOOP

        FETCH c INTO my_email_id, my_usr_email;

        IF done = 1 THEN
            LEAVE boucle_curseur;
        END IF;

            boucle_curseur2: LOOP

                FETCH c INTO my_email_id2, my_usr_email2;

                IF done = 1 THEN
                    LEAVE boucle_curseur2;
                END IF;
                
                IF my_usr_email = my_usr_email2 and my_email_id != my_email_id2 THEN
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

    DECLARE my_greenScore INT;
    DECLARE my_blueScore INT;
    DECLARE done INT DEFAULT 0;

    DECLARE c CURSOR FOR SELECT MAT_GREENSCORE, MAT_BLUESCORE FROM T_MATCH_MAT;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    OPEN c;

    boucle_curseur: LOOP
    
        FETCH c INTO my_greenScore, my_blueScore;

        IF done = 1 THEN
            LEAVE boucle_curseur;
        END IF;

        IF my_greenScore < 0 THEN
            SIGNAL SQLSTATE '45000' 
                SET MESSAGE_TEXT = 'ERROR: TEA_ID is null in team ERROR: green score < 0', 
                    MYSQL_ERRNO = 1010;
        END IF;

        IF my_blueScore < 0 THEN
            SELECT 'ERROR: blue score < 0' AS Message;
        END IF;

    END LOOP boucle_curseur;

    SELECT "test 4 passed : No negative score." AS test4;
    CLOSE c;

END$$


CREATE PROCEDURE RencontresIdentiques()
BEGIN

    DECLARE my_id_tea_blue INT;
    DECLARE my_id_tea_green INT;
    DECLARE my_id_mat1 INT;
    DECLARE my_id_mat2 INT;
    DECLARE done INT DEFAULT 0;

    DECLARE c CURSOR FOR SELECT MAT_ID, TEA_ID, TEA_ID_MAT_TEAMGREEN FROM T_MATCH_MAT;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    OPEN c;

    boucle_curseur: LOOP

        FETCH c INTO my_id_mat1, my_id_tea_green, my_id_tea_blue;

        IF done = 1 THEN
            LEAVE boucle_curseur;
        END IF;

        boucle_curseur2: LOOP
    
            FETCH c INTO my_id_mat2, my_id_tea_green, my_id_tea_blue;

            IF done = 1 THEN
                LEAVE boucle_curseur2;
            END IF;

            IF my_id_tea_blue = my_id_tea_green and my_id_mat1 != my_id_mat2 THEN
                SIGNAL SQLSTATE '45000' 
                SET MESSAGE_TEXT = 'ERROR: TEA_ID is null in team ERROR: green score < 0', 
                    MYSQL_ERRNO = 1010;
            END IF;

        END LOOP boucle_curseur2;

    END LOOP boucle_curseur;

    SELECT "test 5 passed : No match with the same teams but." AS test5;    
    CLOSE c;

END$$


CREATE PROCEDURE MemberNull()
BEGIN
    DECLARE my_mbr_id INT;
    DECLARE my_mbr_fname VARCHAR(255);
    DECLARE my_mbr_name VARCHAR(255);
    DECLARE my_mbr_email VARCHAR(255);
    DECLARE done INT DEFAULT 0;

    DECLARE c CURSOR FOR SELECT MBR_ID, MBR_FNAME, MBR_NAME, MBR_EMAIL FROM T_MEMBER_MBR;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    OPEN c;

    boucle_curseur: LOOP
        FETCH c INTO my_mbr_id, my_mbr_fname, my_mbr_name, my_mbr_email;

        IF done = 1 THEN
            LEAVE boucle_curseur;
        END IF;

        IF my_mbr_fname IS NULL THEN
            SIGNAL SQLSTATE '45000' 
                SET MESSAGE_TEXT = 'ERROR: mem_fname is null in member', 
                    MYSQL_ERRNO = 1011;
        END IF;

        IF my_mbr_name IS NULL THEN
            SIGNAL SQLSTATE '45000' 
                SET MESSAGE_TEXT = 'ERROR: mem_name is null in member', 
                    MYSQL_ERRNO = 1012;
        END IF;

        IF my_mbr_email IS NULL THEN
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
