<?php // Inicia el bloc de codi PHP

/** // Inici del bloc de comentaris de documentació
 * Llistat i manteniment basic d'alumnes. // Descripció de la funcionalitat del fitxer
 * // Línia buida de documentació
 * @package CAS3 // Defineix el paquet al qual pertany el fitxer
 */ // Final del bloc de comentaris de documentació

require_once __DIR__ . '/../includes/layout.php'; // Inclou el fitxer de gestió de la interfície
require_once __DIR__ . '/../includes/db.php'; // Inclou el fitxer de connexió i funcions de base de dades

require_web_professor(); // Verifica permisos de professor

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Comprova si la petició és de tipus POST
    verify_form_csrf(); // Valida el token CSRF per seguretat
    $action = $_POST['action'] ?? ''; // Obté l'acció a realitzar des del formulari

    if ($action === 'delete') { // Si l'acció és eliminar un alumne
        try { // Inicia el bloc de captura d'errors
            $id = (int) ($_POST['id'] ?? 0); // Obté i valida l'ID de l'alumne

            if ($id > 0) { // Si l'ID és vàlid
                db_execute('DELETE FROM Alumnes WHERE id = ?', [$id]); // Executa la sentència d'eliminació
                flash('Alumne eliminat correctament.'); // Mostra un missatge d'èxit
            } // Tanca la validació de l'ID
        } catch (Throwable $exception) { // Captura qualsevol error durant l'eliminació
            error_log('Error eliminant alumne: ' . $exception->getMessage()); // Registra l'error al log del servidor
            flash('No es pot eliminar aquest alumne perquè te dades vinculades.', 'error'); // Mostra un missatge d'error a l'usuari
        } // Final del bloc try-catch

        redirect_to('professorat/alumnes.php'); // Redirigeix de nou al llistat d'alumnes
    } // Tanca el bloc d'eliminació

    $data = [ // Recull les dades de l'alumne del formulari
        'nom' => trim($_POST['nom'] ?? ''), // Neteja el nom
        'cognom1' => trim($_POST['cognom1'] ?? ''), // Neteja el primer cognom
        'cognom2' => trim($_POST['cognom2'] ?? ''), // Neteja el segon cognom
        'correu' => trim($_POST['correu'] ?? ''), // Neteja el correu
        'grupClasse' => trim($_POST['grupClasse'] ?? ''), // Neteja el grup/classe
    ]; // Tanca l'array de dades

    try { // Inicia el bloc de captura d'errors per creació/actualització
        if ($data['nom'] === '' || $data['cognom1'] === '' || $data['correu'] === '' || $data['grupClasse'] === '') { // Valida camps obligatoris
            flash('Cal omplir nom, primer cognom, correu i grup.', 'error'); // Missatge si falten camps
        } elseif (!filter_var($data['correu'], FILTER_VALIDATE_EMAIL)) { // Valida el format del correu
            flash('El correu electronic no es valid.', 'error'); // Missatge si el correu no és vàlid
        } elseif ($action === 'create') { // Si l'acció és crear un nou alumne
            db_execute( // Executa la inserció a la base de dades
                'INSERT INTO Alumnes (nom, cognom1, cognom2, correu, grupClasse) VALUES (?, ?, ?, ?, ?)', // SQL d'inserció
                [$data['nom'], $data['cognom1'], $data['cognom2'] ?: null, $data['correu'], $data['grupClasse']] // Valors a inserir
            ); // Final de l'execució d'inserció
            flash('Alumne creat correctament.'); // Missatge d'èxit en la creació
        } elseif ($action === 'update') { // Si l'acció és actualitzar un alumne existent
            $id = (int) ($_POST['id'] ?? 0); // Obté l'ID de l'alumne
            db_execute( // Executa l'actualització a la base de dades
                'UPDATE Alumnes SET nom = ?, cognom1 = ?, cognom2 = ?, correu = ?, grupClasse = ? WHERE id = ?', // SQL d'actualització
                [$data['nom'], $data['cognom1'], $data['cognom2'] ?: null, $data['correu'], $data['grupClasse'], $id] // Valors a actualitzar
            ); // Final de l'execució d'actualització
            flash('Alumne actualitzat correctament.'); // Missatge d'èxit en l'actualització
        } // Tanca els condicionals d'acció
    } catch (Throwable $exception) { // Captura errors en el procés de guardar
        error_log('Error gestio alumnes: ' . $exception->getMessage()); // Registra l'error al log
        flash('No s\'ha pogut guardar l\'alumne.', 'error'); // Missatge d'error a l'usuari
    } // Final del bloc try-catch

    redirect_to('professorat/alumnes.php'); // Redirigeix de nou al llistat d'alumnes
} // Tanca el bloc de processament POST

$search = trim($_GET['q'] ?? ''); // Obté el terme de cerca de la URL
$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0; // Obté l'ID de l'alumne per editar si n'hi ha
$params = []; // Inicialitza els paràmetres de la consulta
$where = ''; // Inicialitza la clàusula WHERE

if ($search !== '') { // Si s'ha especificat una cerca
    $where = 'WHERE nom LIKE ? OR cognom1 LIKE ? OR cognom2 LIKE ? OR correu LIKE ? OR grupClasse LIKE ?'; // Defineix la condició de cerca
    $like = '%' . $search . '%'; // Prepara el patró LIKE
    $params = [$like, $like, $like, $like, $like]; // Assigna el patró a cada paràmetre
} // Tanca el bloc de cerca

$alumnes = db_fetch_all( // Obté la llista d'alumnes de la base de dades
    "SELECT id, nom, cognom1, cognom2, correu, grupClasse
     FROM Alumnes
     $where
     ORDER BY cognom1, cognom2, nom
     LIMIT 200",
    $params // Passa els paràmetres de cerca
); // Final de la consulta d'alumnes

$editStudent = $editId > 0 // Si estem editant un alumne
    ? db_fetch_one('SELECT id, nom, cognom1, cognom2, correu, grupClasse FROM Alumnes WHERE id = ?', [$editId]) // Obté les dades de l'alumne a editar
    : null; // Si no s'edita, assigna null

render_page('professorat/alumnes', [ // Renderitza la vista del llistat d'alumnes
    'alumnes' => $alumnes, // Passa la llista d'alumnes
    'search' => $search, // Passa el terme de cerca
    'editStudent' => $editStudent, // Passa les dades de l'alumne en edició
], 'Alumnes'); // Títol de la pàgina
