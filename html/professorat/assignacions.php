<?php // Inicia el bloc de codi PHP

/** // Inici del bloc de comentaris de documentació
 * Gestio d'assignacions de material a alumnes. // Descripció de la funcionalitat
 * // Línia buida de documentació
 * @package CAS3 // Defineix el paquet
 */ // Final del bloc de comentaris

require_once __DIR__ . '/../includes/layout.php'; // Inclou el fitxer de gestió de la interfície
require_once __DIR__ . '/../includes/db.php'; // Inclou el fitxer de connexió a la base de dades

require_web_professor(); // Verifica permisos de professor

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Comprova si la petició és POST
    verify_form_csrf(); // Valida el token de seguretat CSRF
    $action = $_POST['action'] ?? ''; // Obté l'acció del formulari

    try { // Inicia bloc de gestió d'errors
        if ($action === 'create') { // Si l'acció és crear una nova assignació
            $idAlumne = (int) ($_POST['idAlumne'] ?? 0); // Obté l'ID de l'alumne
            $idMaterial = (int) ($_POST['idMaterial'] ?? 0); // Obté l'ID del material
            $dataInici = $_POST['dataInici'] ?: date('Y-m-d'); // Obté la data d'inici

            if ($idAlumne <= 0 || $idMaterial <= 0) { // Valida camps obligatoris
                flash('Cal seleccionar alumne i material.', 'error'); // Missatge d'error si falten dades
            } else { // Si les dades són correctes
                $active = db_fetch_one( // Comprova si el material ja està assignat
                    'SELECT id FROM Assignacions WHERE idMaterial = ? AND (dataFinal IS NULL OR dataFinal >= CURDATE()) LIMIT 1', // SQL de cerca d'assignació activa
                    [$idMaterial] // Paràmetre del material
                ); // Final de la comprovació

                if ($active) { // Si ja té una assignació activa
                    flash('Aquest material ja te una assignacio activa.', 'error'); // Missatge d'error
                } else { // Si està lliure
                    db_execute( // Crea la nova assignació
                        'INSERT INTO Assignacions (idMaterial, idAlumne, dataInici, dataFinal) VALUES (?, ?, ?, ?)', // SQL d'inserció
                        [$idMaterial, $idAlumne, $dataInici, $_POST['dataFinal'] ?: null] // Paràmetres per a la inserció
                    ); // Final de l'execució de la inserció
                    flash('Assignacio creada correctament.'); // Missatge d'èxit
                } // Tanca la comprovació de material lliure
            } // Tanca la validació de dades
        } // Tanca acció de creació

        if ($action === 'close') { // Si l'acció és tancar (finalitzar) una assignació
            $id = (int) ($_POST['id'] ?? 0); // Obté l'ID de l'assignació
            $dataFinal = $_POST['dataFinal'] ?: date('Y-m-d'); // Obté la data final

            if ($id > 0) { // Si l'ID és vàlid
                db_execute('UPDATE Assignacions SET dataFinal = ? WHERE id = ?', [$dataFinal, $id]); // Actualitza la data final
                flash('Assignacio actualitzada correctament.'); // Missatge d'èxit
            } // Tanca validació d'ID
        } // Tanca acció de tancament

        if ($action === 'delete') { // Si l'acció és eliminar una assignació
            $id = (int) ($_POST['id'] ?? 0); // Obté l'ID

            if ($id > 0) { // Si l'ID és vàlid
                db_execute('DELETE FROM Assignacions WHERE id = ?', [$id]); // Elimina el registre
                flash('Assignacio eliminada correctament.'); // Missatge d'èxit
            } // Tanca validació d'ID
        } // Tanca acció d'eliminació
    } catch (Throwable $exception) { // Captura qualsevol error
        error_log('Error gestio assignacions: ' . $exception->getMessage()); // Registra l'error
        flash('No s\'ha pogut guardar el canvi.', 'error'); // Missatge d'error per a l'usuari
    } // Tanca bloc try-catch

    redirect_to('professorat/assignacions.php'); // Redirigeix de nou al llistat d'assignacions
} // Tanca bloc POST

$filters = [ // Defineix els filtres de la llista
    'tipus' => isset($_GET['tipus']) ? (int) $_GET['tipus'] : 0, // Filtre per tipus de material
    'alumne' => isset($_GET['alumne']) ? (int) $_GET['alumne'] : 0, // Filtre per alumne
    'estat' => $_GET['estat'] ?? '', // Filtre per estat (assignat/lliure)
]; // Tanca l'array de filtres

$tipusMaterial = db_fetch_all('SELECT id, tipus, model FROM TipusMaterial ORDER BY tipus, model'); // Obté tipus de material per al desplegable
$alumnes = db_fetch_all('SELECT id, nom, cognom1, cognom2, grupClasse FROM Alumnes ORDER BY cognom1, cognom2, nom'); // Obté alumnes per al desplegable
$availableMaterial = db_fetch_all( // Obté material que no està assignat actualment
    'SELECT m.id, m.idInventari, m.etiquetaDepInf, m.numSerie, tm.tipus, tm.model
     FROM Material m
     INNER JOIN TipusMaterial tm ON tm.id = m.idTipus
     WHERE NOT EXISTS (
        SELECT 1 FROM Assignacions a
        WHERE a.idMaterial = m.id AND (a.dataFinal IS NULL OR a.dataFinal >= CURDATE())
     )
     ORDER BY tm.tipus, m.idInventari, m.id'
); // Final de consulta de material disponible

$conditions = []; // Inicialitza array de condicions SQL
$params = []; // Inicialitza array de paràmetres SQL

if ($filters['tipus'] > 0) { // Si s'ha filtrat per tipus
    $conditions[] = 'tm.id = ?'; // Condició d'ID de tipus
    $params[] = $filters['tipus']; // Afegeix el valor
} // Tanca filtre per tipus

if ($filters['alumne'] > 0) { // Si s'ha filtrat per alumne
    $conditions[] = 'al.id = ?'; // Condició d'ID d'alumne
    $params[] = $filters['alumne']; // Afegeix el valor
} // Tanca filtre per alumne

if ($filters['estat'] === 'assignat') { // Si es filtren només els assignats
    $conditions[] = 'asg.id IS NOT NULL'; // Condició que hi hagi assignació
} // Tanca filtre d'assignats

if ($filters['estat'] === 'lliure') { // Si es filtren només els lliures
    $conditions[] = 'asg.id IS NULL'; // Condició que no hi hagi assignació
} // Tanca filtre de lliures

$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : ''; // Construeix la clàusula WHERE

$assignments = db_fetch_all( // Obté el llistat d'assignacions actuals segons els filtres
    "SELECT m.id AS idMaterial,
            m.idInventari,
            m.etiquetaDepInf,
            m.numSerie,
            tm.tipus,
            tm.model,
            u.nom AS ubicacio,
            asg.id AS idAssignacio,
            asg.dataInici,
            asg.dataFinal,
            al.id AS idAlumne,
            CONCAT_WS(' ', al.nom, al.cognom1, al.cognom2) AS alumne,
            al.grupClasse
     FROM Material m
     INNER JOIN TipusMaterial tm ON tm.id = m.idTipus
     INNER JOIN Ubicacions u ON u.id = m.idUbicacio
     LEFT JOIN Assignacions asg ON asg.idMaterial = m.id AND (asg.dataFinal IS NULL OR asg.dataFinal >= CURDATE())
     LEFT JOIN Alumnes al ON al.id = asg.idAlumne
     $where
     ORDER BY tm.tipus, tm.model, m.idInventari, m.id",
    $params // Passa els paràmetres de la consulta
); // Final de la consulta d'assignacions

render_page('professorat/assignacions', [ // Renderitza la vista de gestió d'assignacions
    'filters' => $filters, // Passa els filtres actuals
    'tipusMaterial' => $tipusMaterial, // Passa els tipus de material
    'alumnes' => $alumnes, // Passa la llista d'alumnes
    'availableMaterial' => $availableMaterial, // Passa el material disponible
    'assignments' => $assignments, // Passa el llistat d'assignacions obtingut
], 'Assignacions'); // Títol de la pàgina
