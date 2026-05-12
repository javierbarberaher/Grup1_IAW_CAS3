<?php // Inicia el bloc de codi PHP

/** // Inici del bloc de comentaris de documentació
 * Panell principal del professorat. // Descripció de la funcionalitat del fitxer
 * // Línia buida de documentació
 * @package CAS3 // Defineix el paquet al qual pertany el fitxer
 */ // Final del bloc de comentaris de documentació

require_once __DIR__ . '/../includes/layout.php'; // Inclou el fitxer de gestió de la interfície
require_once __DIR__ . '/../includes/db.php'; // Inclou el fitxer de connexió i funcions de base de dades

require_web_professor(); // Verifica que l'usuari té permisos de professor per accedir a aquesta pàgina

$stats = [ // Defineix un array amb estadístiques globals
    'alumnes' => (int) db_fetch_one('SELECT COUNT(*) AS total FROM Alumnes')['total'], // Compta el nombre total d'alumnes
    'material' => (int) db_fetch_one('SELECT COUNT(*) AS total FROM Material')['total'], // Compta el nombre total d'elements de material
    'assignacions' => (int) db_fetch_one('SELECT COUNT(*) AS total FROM Assignacions WHERE dataFinal IS NULL OR dataFinal >= CURDATE()')['total'], // Compta les assignacions actives o futures
    'incidencies' => (int) db_fetch_one('SELECT COUNT(*) AS total FROM Incidencies WHERE dataTancada IS NULL')['total'], // Compta les incidències obertes
]; // Finalitza la definició de l'array d'estadístiques

$materialByType = db_fetch_all( // Obté el recompte de material agrupat per tipus i model
    'SELECT tm.tipus, tm.model, COUNT(m.id) AS total
     FROM TipusMaterial tm
     LEFT JOIN Material m ON m.idTipus = tm.id
     GROUP BY tm.id, tm.tipus, tm.model
     ORDER BY tm.tipus, tm.model'
); // Final de la consulta de material per tipus

$recentIncidents = db_fetch_all( // Obté la llista de les incidències obertes més recents
    'SELECT i.id, i.informacio, i.dataOberta, e.estat,
            COALESCE(m.idInventari, m.etiquetaDepInf, m.numSerie, CAST(m.id AS CHAR)) AS material,
            CONCAT_WS(" ", a.nom, a.cognom1, a.cognom2) AS alumne
     FROM Incidencies i
     LEFT JOIN Estats e ON e.id = i.idEstat
     LEFT JOIN Material m ON m.id = i.idDispositiu
     LEFT JOIN Alumnes a ON a.id = i.idAlumne
     WHERE i.dataTancada IS NULL
     ORDER BY i.dataOberta DESC, i.id DESC
     LIMIT 5'
); // Final de la consulta d\'incidències recents

render_page('professorat/index', [ // Renderitza la vista del panell del professorat
    'stats' => $stats, // Passa les estadístiques a la vista
    'materialByType' => $materialByType, // Passa el material per tipus a la vista
    'recentIncidents' => $recentIncidents, // Passa les incidències recents a la vista
], 'Panell professorat'); // Títol de la pàgina
