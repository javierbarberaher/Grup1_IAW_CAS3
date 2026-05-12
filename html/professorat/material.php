<?php // Inicia el bloc de codi PHP

/** // Inici del bloc de comentaris de documentació
 * Llistat de material del centre. // Descripció de la funcionalitat
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
        if ($action === 'update') { // Si l'acció és actualitzar un material
            $id = (int) ($_POST['id'] ?? 0); // Obté l'ID del material
            $idTipus = (int) ($_POST['idTipus'] ?? 0); // Obté l'ID del tipus de material
            $idUbicacio = (int) ($_POST['idUbicacio'] ?? 0); // Obté l'ID de la ubicació
            $idInventari = trim($_POST['idInventari'] ?? ''); // Obté i neteja l'ID d'inventari

            if ($id <= 0 || $idTipus <= 0 || $idUbicacio <= 0 || $idInventari === '') { // Valida camps obligatoris
                flash('Cal informar inventari, tipus i ubicacio.', 'error'); // Missatge d'error si falten dades
            } else { // Si les dades són vàlides
                db_execute( // Executa l'actualització a la base de dades
                    'UPDATE Material
                     SET idTipus = ?, idInventari = ?, etiquetaDepInf = ?, numSerie = ?, macEthernet = ?,
                         macWifi = ?, SACE = ?, dataAdquisicio = ?, idUbicacio = ?
                     WHERE id = ?', // SQL d'actualització
                    [ // Array de valors per a la sentència
                        $idTipus, // Nou ID de tipus
                        $idInventari, // Nou ID d'inventari
                        trim($_POST['etiquetaDepInf'] ?? '') ?: null, // Etiqueta DepInf o null
                        trim($_POST['numSerie'] ?? '') ?: null, // Número de sèrie o null
                        trim($_POST['macEthernet'] ?? '') ?: null, // MAC Ethernet o null
                        trim($_POST['macWifi'] ?? '') ?: null, // MAC Wi-Fi o null
                        trim($_POST['SACE'] ?? '') ?: null, // Codi SACE o null
                        $_POST['dataAdquisicio'] ?: null, // Data d'adquisició o null
                        $idUbicacio, // Nova ubicació
                        $id, // ID del registre a actualitzar
                    ] // Final de l'array de valors
                ); // Final de l'execució de l'actualització
                flash('Material actualitzat correctament.'); // Missatge d'èxit
            } // Tanca la validació de dades
        } // Tanca l'acció d'actualització

        if ($action === 'delete') { // Si l'acció és eliminar material
            $id = (int) ($_POST['id'] ?? 0); // Obté l'ID del material

            if ($id > 0) { // Si l'ID és vàlid
                db_execute('DELETE FROM Material WHERE id = ?', [$id]); // Executa l'eliminació
                flash('Material eliminat correctament.'); // Missatge d'èxit
            } // Tanca la validació de l'ID
        } // Tanca l'acció d'eliminació
    } catch (Throwable $exception) { // Captura qualsevol error
        error_log('Error gestio material: ' . $exception->getMessage()); // Registra l'error
        flash('No s\'ha pogut guardar el canvi. Revisa si el material te assignacions o incidencies vinculades.', 'error'); // Missatge d'error per a l'usuari
    } // Tanca el bloc try-catch

    redirect_to('professorat/material.php'); // Redirigeix de nou al llistat de material
} // Tanca el bloc POST

$filters = [ // Defineix els filtres de cerca i llistat
    'tipus' => isset($_GET['tipus']) ? (int) $_GET['tipus'] : 0, // Filtre per tipus de material
    'ubicacio' => isset($_GET['ubicacio']) ? (int) $_GET['ubicacio'] : 0, // Filtre per ubicació
    'q' => trim($_GET['q'] ?? ''), // Terme de cerca lliure
]; // Tanca l'array de filtres

$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0; // Comprova si s'està editant un element concret

$tipusMaterial = db_fetch_all('SELECT id, tipus, model FROM TipusMaterial ORDER BY tipus, model'); // Obté tots els tipus de material per als desplegables
$ubicacions = db_fetch_all('SELECT id, nom FROM Ubicacions ORDER BY nom'); // Obté totes les ubicacions per als desplegables
$editMaterial = $editId > 0 // Si estem en mode edició
    ? db_fetch_one('SELECT id, idTipus, idInventari, etiquetaDepInf, numSerie, macEthernet, macWifi, SACE, dataAdquisicio, idUbicacio FROM Material WHERE id = ?', [$editId]) // Obté les dades de l'element a editar
    : null; // Si no s'edita, assigna null

$conditions = []; // Inicialitza array de condicions per a la consulta
$params = []; // Inicialitza array de paràmetres

if ($filters['tipus'] > 0) { // Si s'ha filtrat per tipus
    $conditions[] = 'tm.id = ?'; // Afegeix condició d'ID de tipus
    $params[] = $filters['tipus']; // Afegeix el valor del tipus
} // Tanca filtre per tipus

if ($filters['ubicacio'] > 0) { // Si s'ha filtrat per ubicació
    $conditions[] = 'u.id = ?'; // Afegeix condició d'ID d'ubicació
    $params[] = $filters['ubicacio']; // Afegeix el valor de la ubicació
} // Tanca filtre per ubicació

if ($filters['q'] !== '') { // Si s'ha introduït un terme de cerca
    $conditions[] = '(m.idInventari LIKE ? OR m.etiquetaDepInf LIKE ? OR m.numSerie LIKE ? OR tm.model LIKE ?)'; // Afegeix condició de cerca en múltiples camps
    $like = '%' . $filters['q'] . '%'; // Prepara el patró LIKE
    array_push($params, $like, $like, $like, $like); // Afegeix el patró per a cada camp de cerca
} // Tanca filtre de cerca

$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : ''; // Construeix la clàusula WHERE si hi ha condicions

$materials = db_fetch_all( // Obté la llista de materials segons els filtres
    "SELECT m.id, m.idInventari, m.etiquetaDepInf, m.numSerie, m.macEthernet, m.macWifi,
            m.SACE, m.dataAdquisicio, tm.tipus, tm.model, tm.origen, u.nom AS ubicacio,
            asg.id AS idAssignacio,
            CONCAT_WS(' ', a.nom, a.cognom1, a.cognom2) AS alumne,
            a.id AS idAlumne,
            inc.id AS idIncidencia,
            e.estat AS estatIncidencia
     FROM Material m
     INNER JOIN TipusMaterial tm ON tm.id = m.idTipus
     INNER JOIN Ubicacions u ON u.id = m.idUbicacio
     LEFT JOIN Assignacions asg ON asg.idMaterial = m.id AND (asg.dataFinal IS NULL OR asg.dataFinal >= CURDATE())
     LEFT JOIN Alumnes a ON a.id = asg.idAlumne
     LEFT JOIN Incidencies inc ON inc.idDispositiu = m.id AND inc.dataTancada IS NULL
     LEFT JOIN Estats e ON e.id = inc.idEstat
     $where
     ORDER BY tm.tipus, tm.model, m.idInventari, m.id
     LIMIT 300",
    $params // Passa els paràmetres de la consulta
); // Final de la consulta de materials

render_page('professorat/material', [ // Renderitza la vista del llistat de material
    'filters' => $filters, // Passa els filtres aplicats
    'tipusMaterial' => $tipusMaterial, // Passa els tipus de material per als filtres
    'ubicacions' => $ubicacions, // Passa les ubicacions per als filtres
    'editMaterial' => $editMaterial, // Passa les dades de l'element en edició
    'materials' => $materials, // Passa la llista de materials obtinguda
], 'Material'); // Títol de la pàgina
