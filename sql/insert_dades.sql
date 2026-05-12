-- Especifiquem la database on volem fer el insert
USE modem_gestio;

-- Ubicacions (Sales i habitacions del centre)
INSERT INTO Ubicacions (nom) VALUES
('A101'),
('A102'),
('A103'),
('A201'),
('A202'),
('B101'),
('B102'),
('B201'),
('TALL1'),
('MAGTZ');

-- TipusMaterial (Tipus de dispositius)
INSERT INTO TipusMaterial (tipus, model, origen) VALUES
('Portàtil', 'HP ProBook 450 G8', 'Departament Educació'),
('Portàtil', 'HP ProBook 450 G10', 'Departament Educació'),
('Portàtil', 'Lenovo ThinkPad L14 Gen3', 'Departament Educació'),
('Portàtil', 'Lenovo ThinkPad L15 Gen4', 'Departament Educació'),
('Monitor', 'Dell P2422H 24"', 'Centre'),
('Monitor', 'AOC 24B2XH 23.8"', 'Centre'),
('Teclat', 'Logitech K120 USB', 'Centre'),
('Ratolí', 'Logitech M185 Wireless', 'Centre'),
('Ratolí', 'HP USB Optical Mouse', 'Centre'),
('Auriculars', 'Jabra Evolve2 30 USB', 'Departament Educació'),
('Tablet', 'iPad 10a generació', 'Departament Educació'),
('Carregador', 'HP 65W USB-C', 'Departament Educació'),
('Carregador', 'Lenovo 65W USB-C', 'Departament Educació'),
('Webcam', 'Logitech C920 HD', 'Centre'),
('Disc dur extern', 'Seagate Expansion 1TB', 'Centre');

-- Alumnes (self explanatory)
INSERT INTO Alumnes (nom, cognom1, cognom2, correu, grupClasse) VALUES
('Marc', 'Garcia', 'López', 'marcgarcia@insmonsia.cat', 'ASIX1'),
('Laia', 'Martínez', 'Roca', 'laiamartinez@insmonsia.cat', 'ASIX1'),
('Pol', 'Ferrer', 'Vidal', 'polferrer@insmonsia.cat', 'ASIX1'),
('Aina', 'Castelló', 'Bel', 'ainacastello@insmonsia.cat', 'ASIX1'),
('Jan', 'Roig', 'Mestre', 'janroig@insmonsia.cat', 'ASIX1'),
('Nuria', 'Soler', NULL, 'nuriasoler@insmonsia.cat', 'ASIX2'),
('Arnau', 'Pons', 'Serra', 'arnaupons@insmonsia.cat', 'ASIX2'),
('Mireia', 'Cabré', 'Fonts', 'mireiacabre@insmonsia.cat', 'ASIX2'),
('Gerard', 'Llopis', 'Arnal', 'gerardllopis@insmonsia.cat', 'ASIX2'),
('Carla', 'Ribas', 'Mas', 'carlaribas@insmonsia.cat', 'DAW1'),
('Oriol', 'Blanc', 'Puig', 'oriolblanc@insmonsia.cat', 'DAW1'),
('Marina', 'Segura', NULL, 'marinasegura@insmonsia.cat', 'DAW1'),
('Biel', 'Torrent', 'Camps', 'bieltorrent@insmonsia.cat', 'DAW1'),
('Marta', 'Valls', 'Costa', 'martavalls@insmonsia.cat', 'DAW2'),
('Jordi', 'Montseny', 'Llop', 'jordimontseny@insmonsia.cat', 'DAW2'),
('Txell', 'Barberà', 'Solé', 'txellbarbera@insmonsia.cat', 'DAW2'),
('Pau', 'Esteve', 'Grau', 'pauesteve@insmonsia.cat', 'DAW2'),
('Rut', 'Forcadell', 'Riera', 'rutforcadell@insmonsia.cat', 'SMX1'),
('Àlex', 'Querol', 'Fabra', 'alexquerol@insmonsia.cat', 'SMX1'),
('Clàudia', 'Subirats', 'Mora', 'claudiasubirats@insmonsia.cat', 'SMX2');

-- Estats (Estats d'una incidència)
INSERT INTO Estats (estat) VALUES
('Oberta'),
('En reparació'),
('Esperant recanvi'),
('Resolta'),
('Tancada');

-- Material (dispositius concrets del centre)
INSERT INTO Material (idTipus, idInventari, etiquetaDepInf, numSerie, macEthernet, macWifi, SACE, dataAdquisicio, idUbicacio) VALUES

-- Portàtils HP ProBook 450 G8 de l'aula A101
(1, 'INV-0001', 'DEP-HP8-001', 'SN-5CD2045KXR', 'A4:5D:36:11:22:01', 'B8:27:EB:11:22:01', 'SACE-001', '2023-09-01', 1),
(1, 'INV-0002', 'DEP-HP8-002', 'SN-5CD2045KXS', 'A4:5D:36:11:22:02', 'B8:27:EB:11:22:02', 'SACE-002', '2023-09-01', 1),
(1, 'INV-0003', 'DEP-HP8-003', 'SN-5CD2045KXT', 'A4:5D:36:11:22:03', 'B8:27:EB:11:22:03', 'SACE-003', '2023-09-01', 1),
(1, 'INV-0004', 'DEP-HP8-004', 'SN-5CD2045KXU', 'A4:5D:36:11:22:04', 'B8:27:EB:11:22:04', 'SACE-004', '2023-09-01', 1),
(1, 'INV-0005', 'DEP-HP8-005', 'SN-5CD2045KXV', 'A4:5D:36:11:22:05', 'B8:27:EB:11:22:05', 'SACE-005', '2023-09-01', 1),

-- Portàtils HP ProBook 450 G10 de l'aula A102
(2, 'INV-0006', 'DEP-HP10-001', 'SN-5CD3187NRA', 'A4:5D:36:33:44:01', 'B8:27:EB:33:44:01', 'SACE-006', '2024-09-01', 2),
(2, 'INV-0007', 'DEP-HP10-002', 'SN-5CD3187NRB', 'A4:5D:36:33:44:02', 'B8:27:EB:33:44:02', 'SACE-007', '2024-09-01', 2),
(2, 'INV-0008', 'DEP-HP10-003', 'SN-5CD3187NRC', 'A4:5D:36:33:44:03', 'B8:27:EB:33:44:03', 'SACE-008', '2024-09-01', 2),

-- Portàtils Lenovo ThinkPad L14 de l'aula A201
(3, 'INV-0009', 'DEP-LN14-001', 'SN-PF4ABCDE01', '54:E1:AD:55:66:01', '60:D8:19:55:66:01', 'SACE-009', '2024-01-15', 4),
(3, 'INV-0010', 'DEP-LN14-002', 'SN-PF4ABCDE02', '54:E1:AD:55:66:02', '60:D8:19:55:66:02', 'SACE-010', '2024-01-15', 4),
(3, 'INV-0011', 'DEP-LN14-003', 'SN-PF4ABCDE03', '54:E1:AD:55:66:03', '60:D8:19:55:66:03', 'SACE-011', '2024-01-15', 4),

-- Portàtils Lenovo ThinkPad L15 per a préstec o magatzem
(4, 'INV-0012', 'DEP-LN15-001', 'SN-PF5FGHIJ01', '54:E1:AD:77:88:01', '60:D8:19:77:88:01', 'SACE-012', '2025-01-10', 10),
(4, 'INV-0013', 'DEP-LN15-002', 'SN-PF5FGHIJ02', '54:E1:AD:77:88:02', '60:D8:19:77:88:02', 'SACE-013', '2025-01-10', 10),

-- Monitors Dell de l'aula A101
(5, 'INV-0014', 'DEP-DL-001', 'SN-CN0P24H001', NULL, NULL, 'SACE-014', '2023-09-01', 1),
(5, 'INV-0015', 'DEP-DL-002', 'SN-CN0P24H002', NULL, NULL, 'SACE-015', '2023-09-01', 1),
(5, 'INV-0016', 'DEP-DL-003', 'SN-CN0P24H003', NULL, NULL, 'SACE-016', '2023-09-01', 1),

-- Monitors AOC de l'aula A102
(6, 'INV-0017', 'DEP-AOC-001', 'SN-AOCB2X001', NULL, NULL, 'SACE-017', '2024-09-01', 2),
(6, 'INV-0018', 'DEP-AOC-002', 'SN-AOCB2X002', NULL, NULL, 'SACE-018', '2024-09-01', 2),

-- Teclats de l'aula A101
(7, 'INV-0019', NULL, 'SN-LGK120-001', NULL, NULL, NULL, '2023-09-01', 1),
(7, 'INV-0020', NULL, 'SN-LGK120-002', NULL, NULL, NULL, '2023-09-01', 1),
(7, 'INV-0021', NULL, 'SN-LGK120-003', NULL, NULL, NULL, '2023-09-01', 1),

-- Ratolins Logitech de l'aula A101
(8, 'INV-0022', NULL, 'SN-LGM185-001', NULL, 'C0:A5:3E:AA:BB:01', NULL, '2023-09-01', 1),
(8, 'INV-0023', NULL, 'SN-LGM185-002', NULL, 'C0:A5:3E:AA:BB:02', NULL, '2023-09-01', 1),

-- Ratolins HP a l'aula A102
(9, 'INV-0024', NULL, 'SN-HPOPT-001', NULL, NULL, NULL, '2024-09-01', 2),
(9, 'INV-0025', NULL, 'SN-HPOPT-002', NULL, NULL, NULL, '2024-09-01', 2),

-- Auriculars a l'aula B101
(10, 'INV-0026', 'DEP-JBR-001', 'SN-JBR30-001', NULL, NULL, NULL, '2024-03-01', 6),
(10, 'INV-0027', 'DEP-JBR-002', 'SN-JBR30-002', NULL, NULL, NULL, '2024-03-01', 6),

-- Tablets iPad a l'aula B101
(11, 'INV-0028', 'DEP-IPD-001', 'SN-DMPXYZ001', NULL, 'F4:5C:89:CC:DD:01', 'SACE-028', '2024-09-15', 6),
(11, 'INV-0029', 'DEP-IPD-002', 'SN-DMPXYZ002', NULL, 'F4:5C:89:CC:DD:02', 'SACE-029', '2024-09-15', 6),
(11, 'INV-0030', 'DEP-IPD-003', 'SN-DMPXYZ003', NULL, 'F4:5C:89:CC:DD:03', 'SACE-030', '2024-09-15', 6),

-- Carregadors HP al magatzem
(12, 'INV-0031', NULL, 'SN-HPCH65-001', NULL, NULL, NULL, '2023-09-01', 10),
(12, 'INV-0032', NULL, 'SN-HPCH65-002', NULL, NULL, NULL, '2023-09-01', 10),

-- Carregadors Lenovo al magatzem
(13, 'INV-0033', NULL, 'SN-LNCH65-001', NULL, NULL, NULL, '2025-01-10', 10),

-- Webcams de l'aula B201
(14, 'INV-0034', 'DEP-WBC-001', 'SN-LGC920-001', NULL, NULL, NULL, '2024-06-01', 8),
(14, 'INV-0035', 'DEP-WBC-002', 'SN-LGC920-002', NULL, NULL, NULL, '2024-06-01', 8),

-- Discs durs externs del taller
(15, 'INV-0036', NULL, 'SN-SGEXP-001', NULL, NULL, NULL, '2025-02-01', 9),
(15, 'INV-0037', NULL, 'SN-SGEXP-002', NULL, NULL, NULL, '2025-02-01', 9);

-- Assignacions (material assignat a alumnes)
INSERT INTO Assignacions (idMaterial, idAlumne, dataInici, dataFinal) VALUES

-- ASIX1: portàtils i perifèrics a l'aula A101
(1, 1, '2025-09-15', NULL),
(2, 2, '2025-09-15', NULL),
(3, 3, '2025-09-15', NULL),
(4, 4, '2025-09-15', NULL),
(5, 5, '2025-09-15', NULL),
(14, 1, '2025-09-15', NULL),
(15, 2, '2025-09-15', NULL),
(19, 1, '2025-09-15', NULL),
(20, 2, '2025-09-15', NULL),
(22, 1, '2025-09-15', NULL),
(23, 2, '2025-09-15', NULL),

-- ASIX2: portàtils a l'aula A102
(6, 6, '2025-09-15', NULL),
(7, 7, '2025-09-15', NULL),
(8, 8, '2025-09-15', NULL),
(17, 6, '2025-09-15', NULL),
(18, 7, '2025-09-15', NULL),

-- DAW1: portàtils Lenovo a l'aula A201
(9, 10, '2025-09-18', NULL),
(10, 11, '2025-09-18', NULL),
(11, 12, '2025-09-18', NULL),

-- DAW1: tablets
(28, 10, '2025-10-01', NULL),
(29, 11, '2025-10-01', NULL),

-- Préstec portàtil que ja ha sigut retornat
(12, 14, '2025-10-01', '2025-12-20'),

-- Préstec portàtil actiu
(13, 9, '2026-01-15', NULL),

-- Préstec disc dur extern
(36, 15, '2025-11-10', '2026-02-28'),
(37, 16, '2026-02-01', NULL);

-- Incidencies (self explanatory)
INSERT INTO Incidencies (informacio, dataOberta, dataTancada, idAlumne, idDispositiu, idEstat) VALUES
('La pantalla del portàtil mostra línies horitzontals intermitents a la part inferior. El problema apareix després de 20 minuts d\'ús.', '2025-10-05', NULL, 1, 1, 2),
('El teclat no respon correctament: les tecles F5, F6 i la lletra Ç no funcionen. Provat amb driver actualitzat sense èxit.', '2025-10-12', '2025-11-02', 2, 2, 5),
('El portàtil no carrega la bateria, només funciona endollat a la corrent. Carregador verificat amb un altre equip i funciona.', '2025-11-01', NULL, 3, 3, 3),
('El monitor no encén. El LED d\'alimentació no s\'il·lumina. Cable verificat i funcional amb un altre monitor.', '2025-11-15', '2025-12-01', 1, 14, 5),
('La tablet no connecta a la xarxa WiFi del centre. Sí que es connecta a altres xarxes. Reset de xarxa fet sense resultat.', '2025-12-01', '2025-12-10', 10, 28, 4),
('Portàtil en préstec retornat amb la carcassa trencada a la cantonada inferior dreta. Funciona correctament però necessita reparació estètica.', '2025-12-20', NULL, 14, 12, 1),
('El ratolí sense fils perd connexió constantment. Piles canviades i receptor USB provat en diferents ports.', '2026-01-10', '2026-01-20', 1, 22, 5),
('La webcam no és detectada pel sistema operatiu. Provada en Windows i Linux sense èxit. Possible avaria de hardware.', '2026-01-22', NULL, 15, 34, 2),
('Disc dur extern fa sorolls de clicks i no és reconegut per l\'ordinador. Possible fallada mecànica del disc.', '2026-02-05', NULL, 16, 37, 3),
('El portàtil Lenovo en préstec presenta la pantalla blava (BSOD) de forma aleatòria. Ja s\'ha reinstal·lat el sistema operatiu però el problema persisteix.', '2026-03-01', NULL, 9, 13, 2);

