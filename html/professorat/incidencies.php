<?php // Inicia el bloc de codi PHP

/** // Inici del bloc de comentaris de documentació
 * Gestio d'incidencies de dispositius. // Descripció de la funcionalitat
 * // Línia buida de documentació
 * @package CAS3 // Defineix el paquet
 */ // Final del bloc de comentaris

require_once __DIR__ . '/../includes/layout.php'; // Inclou el fitxer de gestió de la interfície
require_once __DIR__ . '/../includes/db.php'; // Inclou el fitxer de connexió a la base de dades

require_web_professor(); // Verifica permisos de professor

/** // Inici de documentació de funció
 * Retorna l'id de l'estat "Tancada" si existeix. // Descripció de la funció
 * // Línia buida
 * @return int|null Identificador de l'estat tancat. // Defineix el tipus de retorn
 */ // Final de documentació de funció
function closed_state_id() // Declara la funció per obtenir l'ID de l'estat tancat
{ // Obre el cos de la funció
    $row = db_fetch_one('SELECT id FROM Estats WHERE estat = ? LIMIT 1', ['Tancada']); // Cerca l'estat anomenat 'Tancada'

    return $row ? (int) $row['id'] : null; // Retorna l'ID com a enter o null si no el troba
} // Tanca la funció

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Comprova si la petició és POST
    verify_form_csrf(); // Valida el token de seguretat CSRF
    $action = $_POST['action'] ?? ''; // Obté l'acció a realitzar

    try { // Inicia bloc de gestió d'errors
        if ($action === 'create') { // Si l'acció és crear una incidència
            $informacio = trim($_POST['informacio'] ?? ''); // Obté la descripció
            $idAlumne = (int) ($_POST['idAlumne'] ?? 0); // Obté l'ID de l'alumne
            $idDispositiu = (int) ($_POST['idDispositiu'] ?? 0); // Obté l'ID del dispositiu
            $idEstat = (int) ($_POST['idEstat'] ?? 0); // Obté l'ID de l'estat inicial
            $dataOberta = $_POST['dataOberta'] ?: date('Y-m-d'); // Obté la data d'obertura

            if ($informacio === '' || $idAlumne <= 0 || $idDispositiu <= 0 || $idEstat <= 0) { // Valida camps obligatoris
                flash('Cal informar alumne, dispositiu, estat i descripcio.', 'error'); // Missatge d'error si falten dades
            } else { // Si les dades són vàlides
                db_execute( // Insereix la nova incidència
                    'INSERT INTO Incidencies (informacio, dataOberta, dataTancada, idAlumne, idDispositiu, idEstat)
                     VALUES (?, ?, ?, ?, ?, ?)', // SQL d'inserció
                    [$informacio, $dataOberta, $_POST['dataTancada'] ?: null, $idAlumne, $idDispositiu, $idEstat] // Paràmetres per a la inserció
                ); // Final de l'execució de la inserció
                flash('Incidencia creada correctament.'); // Missatge d'èxit
            } // Tanca validació de dades
        } // Tanca acció de creació

        if ($action === 'update') { // Si l'acció és actualitzar una incidència
            $id = (int) ($_POST['id'] ?? 0); // Obté l'ID de la incidència
            $idEstat = (int) ($_POST['idEstat'] ?? 0); // Obté el nou ID d'estat
            $dataTancada = $_POST['dataTancada'] ?: null; // Obté la data de tancament si n'hi ha

            if ($id > 0 && $idEstat > 0) { // Si l'ID i l'estat són vàlids
                db_execute('UPDATE Incidencies SET idEstat = ?, dataTancada = ? WHERE id = ?', [$idEstat, $dataTancada, $id]); // Actualitza la incidència
                flash('Incidencia actualitzada.'); // Missatge d'èxit
            } // Tanca validació d'ID
        } // Tanca acció d'actualització

        if ($action === 'close') { // Si l'acció és tancar la incidència ràpidament
            $id = (int) ($_POST['id'] ?? 0); // Obté l'ID de la incidència
            $closedState = closed_state_id(); // Obté l'ID de l'estat tancat

            if ($id > 0 && $closedState) { // Si tenim ID i l'estat tancat existeix
                db_execute('UPDATE Incidencies SET dataTancada = CURDATE(), idEstat = ? WHERE id = ?', [$closedState, $id]); // Actualitza a data d'avui i estat tancat
            } elseif ($id > 0) { // Si no trobem l'estat però tenim l'ID
                db_execute('UPDATE Incidencies SET dataTancada = CURDATE() WHERE id = ?', [$id]); // Només actualitza la data de tancament
            } // Tanca condicional de tancament

            flash('Incidencia tancada.'); // Missatge d'èxit
        } // Tanca acció de tancament

        if ($action === 'delete') { // Si l'acció és eliminar la incidència
            $id = (int) ($_POST['id'] ?? 0); // Obté l'ID

            if ($id > 0) { // Si l'ID és vàlid
                db_execute('DELETE FROM Incidencies WHERE id = ?', [$id]); // Elimina la incidència
                flash('Incidencia eliminada.'); // Missatge d'èxit
            } // Tanca validació d'ID
        } // Tanca acció d'eliminació
    } catch (Throwable $exception) { // Captura qualsevol error
        error_log('Error gestio incidencies: ' . $exception->getMessage()); // Registra l'error
        flash('No s\'ha pogut guardar la incidencia.', 'error'); // Missatge d'error per a l'usuari
    } // Tanca bloc try-catch

    redirect_to('professorat/incidencies.php'); // Redirigeix al llistat d'incidències
} // Tanca bloc POST

$filter = $_GET['filter'] ?? 'obertes'; // Obté el filtre de la URL (obertes per defecte)
$where = $filter === 'totes' ? '' : 'WHERE i.dataTancada IS NULL'; // Defineix la clàusula WHERE segons el filtre

$states = db_fetch_all('SELECT id, estat FROM Estats ORDER BY id'); // Obté tots els estats possibles
$students = db_fetch_all('SELECT id, nom, cognom1, cognom2, grupClasse FROM Alumnes ORDER BY cognom1, cognom2, nom'); // Obté llista d'alumnes
$materials = db_fetch_all( // Obté llista de materials identificats
    'SELECT m.id, m.idInventari, m.etiquetaDepInf, m.numSerie, tm.tipus, tm.model
     FROM Material m
     INNER JOIN TipusMaterial tm ON tm.id = m.idTipus
     ORDER BY tm.tipus, tm.model, m.idInventari, m.id'
); // Final de consulta de materials

$incidents = db_fetch_all( // Obté el llistat d'incidències segons el filtre
    "SELECT i.id, i.informacio, i.dataOberta, i.dataTancada, i.idEstat,
            e.estat,
            a.id AS idAlumne,
            CONCAT_WS(' ', a.nom, a.cognom1, a.cognom2) AS alumne,
            a.grupClasse,
            m.id AS idMaterial,
            COALESCE(m.idInventari, m.etiquetaDepInf, m.numSerie, CAST(m.id AS CHAR)) AS material,
            tm.tipus,
            tm.model
     FROM Incidencies i
     INNER JOIN Alumnes a ON a.id = i.idAlumne
     INNER JOIN Material m ON m.id = i.idDispositiu
     INNER JOIN TipusMaterial tm ON tm.id = m.idTipus
     LEFT JOIN Estats e ON e.id = i.idEstat
     $where
     ORDER BY i.dataTancada IS NOT NULL, i.dataOberta DESC, i.id DESC"
); // Final de la consulta d'incidències

render_page('professorat/incidencies', [ // Renderitza la vista de gestió d'incidències
    'filter' => $filter, // Passa el filtre actual
    'states' => $states, // Passa la llista d'estats
    'students' => $students, // Passa la llista d'alumnes
    'materials' => $materials, // Passa la llista de materials
    'incidents' => $incidents, // Passa la llista d'incidències obtinguda
], 'Incidencies'); // Títol de la pàgina
