-- création des users et du premier organisateur
INSERT INTO T_USER_USR (USR_ID, USR_FNAME, USR_NAME, USR_EMAIL, USR_ISORG, USR_PASSWORD) VALUES
                                                                                             (1, 'Istrator', 'Admin', 'admin@kickscore.com', 1, '$2y$13$2pvAxpIFoQo9Xi.BD2zLBOLeUn0YtqPK2cgBbjaFzOaqikonXZcEm'),
                                                                                             (6, 'Jean', 'Dupont', 'jean.dupont@example.com', 0, '$2y$13$6s4Os6BZxJIp4MxCoMIglOIsogINTIvyCdVrCDJpNwdGV3BJGpzb6'),
                                                                                             (7, 'Alice', 'Dubois', 'alice.dubois@example.com', 0, '$2y$13$6s4Os6BZxJIp4MxCoMIglOIsogINTIvyCdVrCDJpNwdGV3BJGpzb6'),
                                                                                             (8, 'Paul', 'Lefevre', 'paul.lefevre@example.com', 0, '$2y$13$6s4Os6BZxJIp4MxCoMIglOIsogINTIvyCdVrCDJpNwdGV3BJGpzb6'),
                                                                                             (9, 'Julie', 'Rousseau', 'julie.rousseau@example.com', 0, '$2y$13$6s4Os6BZxJIp4MxCoMIglOIsogINTIvyCdVrCDJpNwdGV3BJGpzb6'),
                                                                                             (10, 'Antoine', 'Mercier', 'antoine.mercier@example.com', 0, '$2y$13$6s4Os6BZxJIp4MxCoMIglOIsogINTIvyCdVrCDJpNwdGV3BJGpzb6'),
                                                                                             (11, 'Léa', 'Vincent', 'lea.vincent@example.com', 0, '$2y$13$6s4Os6BZxJIp4MxCoMIglOIsogINTIvyCdVrCDJpNwdGV3BJGpzb6'),
                                                                                             (12, 'Thomas', 'Roux', 'thomas.roux@example.com', 0, '$2y$13$6s4Os6BZxJIp4MxCoMIglOIsogINTIvyCdVrCDJpNwdGV3BJGpzb6'),
                                                                                             (13, 'Sarah', 'Fournier', 'sarah.fournier@example.com', 0, '$2y$13$6s4Os6BZxJIp4MxCoMIglOIsogINTIvyCdVrCDJpNwdGV3BJGpzb6'),
                                                                                             (14, 'Nicolas', 'Girard', 'nicolas.girard@example.com', 0, '$2y$13$6s4Os6BZxJIp4MxCoMIglOIsogINTIvyCdVrCDJpNwdGV3BJGpzb6'),
                                                                                             (15, 'Camille', 'Morel', 'camille.morel@example.com', 0, '$2y$13$6s4Os6BZxJIp4MxCoMIglOIsogINTIvyCdVrCDJpNwdGV3BJGpzb6');

-- création des teams
INSERT INTO T_TEAM_TEA (TEA_ID, TEA_NAME, TEA_STRUCTURE, CREATOR_ID) VALUES
 (5, 'Les Phénix', 'École d''ingénieurs', 6),
 (6, 'Les Panthères', 'Université', 7),
 (7, 'Les Requins', 'IUT', 8),
 (8, 'Les Loups', 'École de commerce', 9),
 (9, 'Les Cobras', 'Université', 10),
 (10, 'Les Faucons', 'IUT', 11),
 (11, 'Les Scorpions', 'École d''ingénieurs', 12),
 (12, 'Les Ours', 'Université', 13),
 (13, 'Les Guépards', 'École de commerce', 14),
 (14, 'Les Vautours', 'IUT', 15);

-- membres d'équipes
INSERT INTO T_MEMBER_MBR (MBR_ID, MBR_FNAME, MBR_NAME, MBR_EMAIL, TEA_ID, USR_ID) VALUES
-- Phénix
(11, 'Jean', 'Dupont', 'jean.dupont@example.com', 5, 6),
(12, 'Claire', 'Lambert', 'claire.lambert@example.com', 5, NULL),
(13, 'Louis', 'Martin', 'louis.martin@example.com', 5, NULL),
-- Panthères
(14, 'Alice', 'Dubois', 'alice.dubois@example.com', 6, 7),
(15, 'Vincent', 'Bertrand', 'vincent.bertrand@example.com', 6, NULL),
(16, 'Marine', 'Durand', 'marine.durand@example.com', 6, NULL),
-- Requins
(17, 'Paul', 'Lefevre', 'paul.lefevre@example.com', 7, 8),
(18, 'Lucie', 'Leroux', 'lucie.leroux@example.com', 7, NULL),
(19, 'Maxime', 'Girard', 'maxime.girard@example.com', 7, NULL),
-- Loups
(20, 'Julie', 'Rousseau', 'julie.rousseau@example.com', 8, 9),
(21, 'Simon', 'Moreau', 'simon.moreau@example.com', 8, NULL),
(22, 'Chloé', 'Legrand', 'chloe.legrand@example.com', 8, NULL),
-- Cobras
(23, 'Antoine', 'Mercier', 'antoine.mercier@example.com', 9, 10),
(24, 'Eva', 'Dubois', 'eva.dubois@example.com', 9, NULL),
(25, 'Théo', 'Petit', 'theo.petit@example.com', 9, NULL),
-- Faucons
(26, 'Léa', 'Vincent', 'lea.vincent@example.com', 10, 11),
(27, 'Hugo', 'Laurent', 'hugo.laurent@example.com', 10, NULL),
(28, 'Emma', 'Leroy', 'emma.leroy@example.com', 10, NULL),
-- Scorpions
(29, 'Thomas', 'Roux', 'thomas.roux@example.com', 11, 12),
(30, 'Inès', 'Dupuis', 'ines.dupuis@example.com', 11, NULL),
(31, 'Lucas', 'Fontaine', 'lucas.fontaine@example.com', 11, NULL),
-- Ours
(32, 'Sarah', 'Fournier', 'sarah.fournier@example.com', 12, 13),
(33, 'Nathan', 'Roussel', 'nathan.roussel@example.com', 12, NULL),
(34, 'Louise', 'Mercier', 'louise.mercier@example.com', 12, NULL),
-- Guépards
(35, 'Nicolas', 'Girard', 'nicolas.girard@example.com', 13, 14),
(36, 'Julia', 'Roux', 'julia.roux@example.com', 13, NULL),
(37, 'Alexandre', 'Lemaire', 'alexandre.lemaire@example.com', 13, NULL),
-- Vautours
(38, 'Camille', 'Morel', 'camille.morel@example.com', 14, 15),
(39, 'Baptiste', 'Lefevre', 'baptiste.lefevre@example.com', 14, NULL),
(40, 'Manon', 'Gauthier', 'manon.gauthier@example.com', 14, NULL);

-- création des championnats
INSERT INTO T_CHAMPIONSHIP_CHP (CHP_ID, CHP_NAME, CHP_DATE_START, CHP_DATE_END, CHP_DESCRIPTION, USR_ID) VALUES
(1, 'Championnat Printemps 2025', '2025-03-01', '2025-05-31', 'Championnat de la saison printemps 2025', 1),
(2, 'Championnat Été 2025', '2025-06-01', '2025-08-31', 'Championnat de la saison été 2025', 1),
(3, 'Championnat Automne 2025', '2025-09-01', '2025-11-30', 'Championnat de la saison automne 2025', 1),
(4, 'Championnat Hiver 2024', '2024-12-01', '2025-02-28', 'Championnat de la saison hiver 2024', 1),
(5, 'Championnat Universitaire 2025', '2024-10-01', '2024-11-30', 'Championnat annuel universitaire', 1),
(6, 'Coupe de l''IUT 2025', '2024-09-01', '2024-09-30', 'Coupe spéciale IUT', 1);
-- inscription des équipes aux championnats
INSERT INTO T_CHAMPIONSHIP_TEAM_CHT (TEA_ID, CHP_ID) VALUES
-- championnat Printemps
(5, 1), (6, 1), (7, 1), (8, 1), (9, 1),
-- championnat Été
(5, 2), (6, 2), (7, 2), (8, 2), (9, 2), (10, 2), (11, 2),
-- championnat Automne
(5, 3), (6, 3), (7, 3), (8, 3), (9, 3), (10, 3), (11, 3), (12, 3), (13, 3), (14, 3),
-- championnat Hiver 2024
(5, 4), (6, 4), (7, 4), (8, 4), (9, 4), (10, 4),
-- championnat Universitaire 2025
(5, 5), (6, 5), (8, 5), (9, 5), (11, 5), (12, 5),
-- Coupe de l'IUT 2025
(7, 6), (10, 6), (14, 6);

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
(9, 'Terrain C - Automne', 3),
-- terrains Hiver 2024
(10, 'Terrain Rouge - Hiver', 4),
(11, 'Terrain Bleu - Hiver', 4),
(12, 'Terrain Vert - Hiver', 4),
-- terrains Championnat Universitaire
(13, 'Grand Terrain Campus', 5),
(14, 'Terrain Faculté des Sciences', 5),
(15, 'Terrain Campus Nord', 5),
(16, 'Terrain Campus Sud', 5),
-- terrains Coupe IUT
(17, 'Terrain IUT Principal', 6),
(18, 'Terrain IUT Secondaire', 6);

-- les résultats de team pour chaque championnat auxquelles elles participent
INSERT INTO T_TEAMRESULTS_TRS (TRS_ID, TRS_WIN, TRS_DRAW, TRS_LOSSES, TRS_POINTS, TRS_GAMESPLAYED, TEA_ID, CHP_ID) VALUES
-- résultats championnat Printemps
(13, 0, 0, 0, 0, 0, 5, 1),
(14, 0, 0, 0, 0, 0, 6, 1),
(15, 0, 0, 0, 0, 0, 7, 1),
(16, 0, 0, 0, 0, 0, 8, 1),
(17, 0, 0, 0, 0, 0, 9, 1),
-- résultats championnat Été
(18, 0, 0, 0, 0, 0, 5, 2),
(19, 0, 0, 0, 0, 0, 6, 2),
(20, 0, 0, 0, 0, 0, 7, 2),
(21, 0, 0, 0, 0, 0, 8, 2),
(22, 0, 0, 0, 0, 0, 9, 2),
(23, 0, 0, 0, 0, 0, 10, 2),
(24, 0, 0, 0, 0, 0, 11, 2),
-- résultats championnat Automne
(25, 0, 0, 0, 0, 0, 5, 3),
(26, 0, 0, 0, 0, 0, 6, 3),
(27, 0, 0, 0, 0, 0, 7, 3),
(28, 0, 0, 0, 0, 0, 8, 3),
(29, 0, 0, 0, 0, 0, 9, 3),
(30, 0, 0, 0, 0, 0, 10, 3),
(31, 0, 0, 0, 0, 0, 11, 3),
(32, 0, 0, 0, 0, 0, 12, 3),
(33, 0, 0, 0, 0, 0, 13, 3),
(34, 0, 0, 0, 0, 0, 14, 3),
-- résultats championnat Hiver 2024
(35, 0, 0, 0, 0, 0, 5, 4),
(36, 0, 0, 0, 0, 0, 6, 4),
(37, 0, 0, 0, 0, 0, 7, 4),
(38, 0, 0, 0, 0, 0, 8, 4),
(39, 0, 0, 0, 0, 0, 9, 4),
(40, 0, 0, 0, 0, 0, 10, 4),
-- résultats championnat Universitaire 2025
(41, 0, 0, 0, 0, 0, 5, 5),
(42, 0, 0, 0, 0, 0, 6, 5),
(43, 0, 0, 0, 0, 0, 8, 5),
(44, 0, 0, 0, 0, 0, 9, 5),
(45, 0, 0, 0, 0, 0, 11, 5),
(46, 0, 0, 0, 0, 0, 12, 5),
-- résultats Coupe de l'IUT 2025
(47, 0, 0, 0, 0, 0, 7, 6),
(48, 0, 0, 0, 0, 0, 10, 6),
(49, 0, 0, 0, 0, 0, 14, 6);

-- status des matchs
INSERT INTO T_STATUS_STS (STS_NAME) VALUES
                                        ('CANCELED'),
                                        ('FORFEITED'),
                                        ('DONE'),
                                        ('COMING'),
                                        ('IN_PROGRESS');