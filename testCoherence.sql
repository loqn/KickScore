-- Se connecter à une base spécifique
USE etu_matchabrier;

-- Vérifier la connexion en listant les tables
SHOW TABLES;

-- Exécuter une requête simple
SELECT COUNT(*) FROM T_USER_USR;

-- Insérer des utilisateurs dans T_USER_USR
INSERT INTO T_USER_USR (USR_ID, USR_FNAME, USR_NAME, USR_EMAIL, USR_PASSWORD) VALUES 
(1, 'test','Alice', 'alice@example.com', 'password123'),
(2, 'test', 'bob@example.com', 'securepass'),
(3, 'test', 'charlie@example.com', 'mypassword');

-- Insérer des équipes dans T_TEAM_TEA
INSERT INTO T_TEAM_TEA (TEA_ID, TEA_NAME, TEA_STRUCTURE, TEA_GAMESPLAYED, TEA_WIN, TEA_LOSS, TEA_POINTS) VALUES 
(1, 'Team Red', 'Professional', 10, 6, 4, 18),
(2, 'Team Blue', 'Amateur', 8, 3, 5, 9),
(3, 'Team Green', 'Professional', 12, 8, 4, 24);

-- Insérer des championnats dans T_CHAMPIONSHIP_CHP
INSERT INTO T_CHAMPIONSHIP_CHP (CHP_ID, CHP_NAME) VALUES 
(1, 'National Championship'),
(2, 'Regional Cup');

-- Insérer des matchs dans T_MATCH_MAT
INSERT INTO T_MATCH_MAT (MAT_ID, TEA_ID, MAT_OPPONENT, MAT_SCORE, MAT_DATE) VALUES 
(1, 1, 'Team Blue', '3-1', '2025-01-01'),
(2, 1, 'Team Green', '2-2', '2025-01-05'),
(3, 2, 'Team Red', '1-3', '2025-01-10'),
(4, 3, 'Team Blue', '4-0', '2025-01-15');

-- Vérifier le résultat avec quelques requêtes simples
SELECT * FROM T_USER_USR;
SELECT * FROM T_TEAM_TEA;
SELECT * FROM T_CHAMPIONSHIP_CHP;
SELECT * FROM T_MATCH_MAT;


DELIMITER $$

CREATE PROCEDURE DeuxEquipesParRencontre()
BEGIN
    DECLARE id1 INT;
    DECLARE id2 INT;

    DECLARE c1 CURSOR FOR SELECT id1, id2 FROM T_MATCH_MAT;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET id1 = NULL, id2 = NULL;

    OPEN c1;

    boucle_curseur: LOOP
        FETCH c1 INTO id1, id2;

        IF id1 IS NULL THEN 
            LEAVE boucle_curseur;
        END IF;

        IF id1 IS NULL OR id2 IS NULL THEN
            SELECT 'Test : Une ou plusieurs colonnes sont nulles';
        END IF;
    END LOOP boucle_curseur;

    CLOSE c1;
END$$

DELIMITER ;

call DeuxEquipesParRencontre();

ROLLBACK;

