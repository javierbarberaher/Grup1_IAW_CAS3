<?php // Inicia el bloc de codi PHP

/** // Inici del bloc de comentaris de documentació
 * Llistat de dispositius agrupats per aula i tipus. // Descripció de la funcionalitat
 * // Línia buida de documentació
 * @package CAS3 // Defineix el paquet
 */ // Final del bloc de comentaris

require_once __DIR__ . '/../includes/layout.php'; // Inclou el fitxer de gestió de la interfície
require_once __DIR__ . '/../includes/db.php'; // Inclou el fitxer de connexió a la base de dades

require_web_professor(); // Verifica permisos de professor

$filters = [ // Defineix els filtres per a la consulta
    'ubicacio' => isset($_GET['ubicacio']) ? (int) $_GET['ubicacio'] : 0, // Filtre d'ID d'ubicació (aula)
    'tipus' => isset($_GET['tipus']) ? (int) $_GET['tipus'] : 0, // Filtre d'ID de tipus de material
]; // Tanca l'array de filtres

$ubicacions = db_fetch_all('SELECT id, nom FROM Ubicacions ORDER BY nom'); // Obté totes les ubicacions per al filtre
$tipusMaterial = db_fetch_all('SELECT id, tipus, model FROM TipusMaterial ORDER BY tipus, model'); // Obté tots els tipus de material per al filtre

$conditions = []; // Inicialitza array per a les condicions SQL
$params = []; // Inicialitza array per als paràmetres de la consulta

if ($filters['ubicacio'] > 0) { // Si s'ha seleccionat una ubicació
    $conditions[] = 'u.id = ?'; // Afegeix condició per ID d'ubicació
    $params[] = $filters['ubicacio']; // Afegeix el valor del paràmetre
} // Tanca filtre d'ubicació

if ($filters['tipus'] > 0) { // Si s'ha seleccionat un tipus de material
    $conditions[] = 'tm.id = ?'; // Afegeix condició per ID de tipus
    $params[] = $filters['tipus']; // Afegeix el valor del paràmetre
} // Tanca filtre de tipus

$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : ''; // Construeix la clàusula WHERE si hi ha condicions

$rows = db_fetch_all( // Obté el recompte de dispositius per aula i tipus
    "SELECT u.nom AS aula, tm.tipus, tm.model, COUNT(m.id) AS total
     FROM Material m
     INNER JOIN Ubicacions u ON u.id = m.idUbicacio
     INNER JOIN TipusMaterial tm ON tm.id = m.idTipus
     $where
     GROUP BY u.id, u.nom, tm.id, tm.tipus, tm.model
     ORDER BY u.nom, tm.tipus, tm.model",
    $params // Passa els paràmetres de la consulta
); // Final de la consulta per aules

$totalsByType = db_fetch_all( // Obté el total global per cada tipus/model segons el filtre
    "SELECT tm.tipus, tm.model, COUNT(m.id) AS total
     FROM Material m
     INNER JOIN TipusMaterial tm ON tm.id = m.idTipus
     INNER JOIN Ubicacions u ON u.id = m.idUbicacio
     $where
     GROUP BY tm.id, tm.tipus, tm.model
     ORDER BY tm.tipus, tm.model",
    $params // Passa els paràmetres de la consulta
); // Final de la consulta de totals

$byClassroom = []; // Inicialitza array per agrupar els resultats per aula
foreach ($rows as $row) { // Itera sobre cada fila de resultats per aula
    $byClassroom[$row['aula']][] = $row; // Agrupa les dades dins l'array utilitzant el nom de l'aula com a clau
} // Tanca el bucle d'agrupació

render_page('professorat/dispositius_aula', [ // Renderitza la vista del llistat per aules
    'filters' => $filters, // Passa els filtres actuals
    'ubicacions' => $ubicacions, // Passa la llista d'ubicacions
    'tipusMaterial' => $tipusMaterial, // Passa la llista de tipus de material
    'byClassroom' => $byClassroom, // Passa les dades agrupades per aula
    'totalsByType' => $totalsByType, // Passa els totals globals per tipus
], 'Dispositius per aula'); // Títol de la pàgina
