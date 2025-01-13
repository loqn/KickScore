-- création des users et du premier organisateur
INSERT INTO T_USER_USR (USR_ID, USR_FNAME, USR_NAME, USR_EMAIL, USR_ISORG, USR_PASSWORD) VALUES
(1, 'Istrator', 'Admin', 'admin@kickscore.com', 1, '$2y$13$2pvAxpIFoQo9Xi.BD2zLBOLeUn0YtqPK2cgBbjaFzOaqikonXZcEm'),
(2, 'Marie', 'Martin', 'marie.martin@example.com', 0, '$2y$13$6s4Os6BZxJIp4MxCoMIglOIsogINTIvyCdVrCDJpNwdGV3BJGpzb6'),
(3, 'Pierre', 'Bernard', 'pierre.bernard@example.com', 0, '$2y$13$6s4Os6BZxJIp4MxCoMIglOIsogINTIvyCdVrCDJpNwdGV3BJGpzb6'),
(4, 'Sophie', 'Petit', 'sophie.petit@example.com', 0, '$2y$13$6s4Os6BZxJIp4MxCoMIglOIsogINTIvyCdVrCDJpNwdGV3BJGpzb6'),
(5, 'Lucas', 'Robert', 'lucas.robert@example.com', 0, '$2y$13$6s4Os6BZxJIp4MxCoMIglOIsogINTIvyCdVrCDJpNwdGV3BJGpzb6');


-- création des teams
INSERT INTO T_TEAM_TEA (TEA_ID, TEA_NAME, TEA_STRUCTURE, CREATOR_ID) VALUES
(1, 'Les Lions', 'Université', 2),
(2, 'Les Aigles', 'IUT', 3),
(3, 'Les Tigres', 'Université', 4),
(4, 'Les Dragons', '', 5);

-- membres d'équipes
INSERT INTO T_MEMBER_MBR (MBR_ID, MBR_FNAME, MBR_NAME, MBR_EMAIL, TEA_ID, USR_ID) VALUES
-- membres des Lions
(1, 'Marie', 'Martin', 'marie.martin@example.com', 1, 2),
(2, 'Anne', 'Leroy', 'anne.leroy@example.com', 1, NULL),
(3, 'Marc', 'Moreau', 'marc.moreau@example.com', 1, NULL),
-- membres des Aigles
(4, 'Pierre', 'Bernard', 'pierre.bernard@example.com', 2, 3),
(5, 'Thomas', 'Simon', 'thomas.simon@example.com', 2, NULL),
(6, 'Laura', 'Michel', 'laura.michel@example.com', 2, NULL),
-- membres des Tigres
(7, 'Sophie', 'Petit', 'sophie.petit@example.com', 3, 4),
(8, 'Emma', 'Garcia', 'emma.garcia@example.com', 3, NULL),
(9, 'Hugo', 'Martinez', 'hugo.martinez@example.com', 3, NULL),
-- membres des Dragons
(10, 'Lucas', 'Robert', 'lucas.robert@example.com', 4, 5);

-- création des championnats
INSERT INTO T_CHAMPIONSHIP_CHP (CHP_ID, CHP_NAME, CHP_DATE_START, CHP_DATE_END, CHP_DESCRIPTION, USR_ID) VALUES
(1, 'Championnat Printemps 2025', '2025-01-01', '2025-06-30', 'Championnat de la saison printemps 2025', 1),
(2, 'Championnat Été 2025', '2025-07-01', '2025-09-30', 'Championnat de la saison été 2025', 1),
(3, 'Championnat Automne 2025', '2025-10-01', '2025-12-31', 'Championnat de la saison automne 2025', 1);

-- inscription des équipes aux championnats
INSERT INTO T_CHAMPIONSHIP_TEAM_CHT (TEA_ID, CHP_ID) VALUES
-- championnat Printemps
(1, 1), (2, 1), (3, 1), (4, 1),
-- championnat Été
(2, 2), (3, 2), (4, 2),
-- championnat Automne
(1, 3), (3, 3);

-- fields
INSERT INTO T_FIELD_FLD (FLD_ID, FLD_NAME, CHP_ID) VALUES
-- terrains Printemps
(1, 'Terrain Central - Printemps', 1),
(2, 'Terrain Nord - Printemps', 1),
(3, 'Terrain Sud - Printemps', 1),
-- terrains Été
(4, 'Terrain Principal - Été', 2),
(5, 'Terrain Secondaire - Été', 2),
(6, 'Terrain Annexe - Été', 2),
-- terrains Automne
(7, 'Terrain A - Automne', 3),
(8, 'Terrain B - Automne', 3),
(9, 'Terrain C - Automne', 3);

-- les résultats de team pour chaque championnat auxquelles elles participent
INSERT INTO T_TEAMRESULTS_TRS (TRS_ID, TRS_WIN, TRS_DRAW, TRS_LOSSES, TRS_POINTS, TRS_GAMESPLAYED, TEA_ID, CHP_ID) VALUES
-- résultats championnat Printemps
(1, 0, 0, 0, 0, 0, 1, 1),
(2, 0, 0, 0, 0, 0, 2, 1),
(3, 0, 0, 0, 0, 0, 3, 1),
(4, 0, 0, 0, 0, 0, 4, 1),
-- résultats championnat Été
(6, 0, 0, 0, 0, 0, 2, 2),
(7, 0, 0, 0, 0, 0, 3, 2),
(8, 0, 0, 0, 0, 0, 4, 2),
-- résultats championnat Automne
(11, 0, 0, 0, 0, 0, 1, 3),
(12, 0, 0, 0, 0, 0, 3, 3);

-- status des matchs
INSERT INTO T_STATUS_STS (STS_NAME) VALUES
('CANCELED'),
('FORFEITED'),
('DONE'),
('COMING'),
('IN_PROGRESS');