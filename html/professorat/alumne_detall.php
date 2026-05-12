<?php // Inicia el bloc de codi PHP

/** // Inici del bloc de comentaris de documentació
 * Detall d'un alumne i dels seus dispositius assignats. // Descripció de la funcionalitat
 * // Línia buida de documentació
 * @package CAS3 // Defineix el paquet
 */ // Final del bloc de comentaris

require_once __DIR__ . '/../includes/layout.php'; // Inclou el fitxer de gestió de la interfície
require_once __DIR__ . '/../includes/db.php'; // Inclou el fitxer de connexió a la base de dades

require_web_professor(); // Verifica que l'usuari sigui un professor

$id = (int) ($_GET['id'] ?? ($_POST['idAlumne'] ?? 0)); // Obté l'ID de l'alumne de la URL o del formulari

if ($id <= 0) { // Si l'ID no és vàlid
    render_error_page(404, 'Alumne no trobat', 'No s\'ha indicat cap alumne valid.'); // Mostra pàgina d'error 404
    exit; // Atura l'execució
} // Tanca la validació de l'ID

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Comprova si s'ha enviat el formulari
    verify_form_csrf(); // Valida el token de seguretat CSRF
    $action = $_POST['action'] ?? ''; // Obté l'acció a realitzar

    try { // Bloc per gestionar possibles errors
        if ($action === 'update_student') { // Si es volen actualitzar les dades de l'alumne
            $data = [ // Recull les dades del formulari
                trim($_POST['nom'] ?? ''), // Nom de l'alumne
                trim($_POST['cognom1'] ?? ''), // Primer cognom
                trim($_POST['cognom2'] ?? '') ?: null, // Segon cognom o null
                trim($_POST['correu'] ?? ''), // Correu electrònic
                trim($_POST['grupClasse'] ?? ''), // Grup o classe
                $id, // ID de l'alumne
            ]; // Tanca l'array de dades

            db_execute('UPDATE Alumnes SET nom = ?, cognom1 = ?, cognom2 = ?, correu = ?, grupClasse = ? WHERE id = ?', $data); // Actualitza la base de dades
            flash('Dades de l\'alumne actualitzades.'); // Missatge d'èxit
        } // Tanca l'acció d'actualització

        if ($action === 'create_assignment') { // Si es vol assignar material nou
            $idMaterial = (int) ($_POST['idMaterial'] ?? 0); // Obté l'ID del material
            $dataInici = $_POST['dataInici'] ?: date('Y-m-d'); // Obté la data d'inici o la de hui

            if ($idMaterial <= 0) { // Valida que s'hagi triat material
                flash('Cal seleccionar un material.', 'error'); // Missatge d'error si no s'ha triat
            } else { // Si el material és vàlid
                $active = db_fetch_one( // Comprova si el material ja està assignat actualment
                    'SELECT id FROM Assignacions WHERE idMaterial = ? AND (dataFinal IS NULL OR dataFinal >= CURDATE()) LIMIT 1', // SQL de cerca d'assignació activa
                    [$idMaterial] // Paràmetre del material
                ); // Final de la comprovació

                if ($active) { // Si ja té una assignació activa
                    flash('Aquest material ja esta assignat.', 'error'); // Missatge d'error
                } else { // Si està lliure
                    db_execute( // Crea la nova assignació
                        'INSERT INTO Assignacions (idMaterial, idAlumne, dataInici, dataFinal) VALUES (?, ?, ?, NULL)', // SQL d'inserció
                        [$idMaterial, $id, $dataInici] // Valors a inserir
                    ); // Final de la inserció
                    flash('Material assignat a l\'alumne.'); // Missatge d'èxit
                } // Tanca la comprovació de material lliure
            } // Tanca la validació d'ID de material
        } // Tanca l'acció de crear assignació

        if ($action === 'close_assignment') { // Si es vol tancar una assignació
            $idAssignacio = (int) ($_POST['idAssignacio'] ?? 0); // Obté l'ID de l'assignació
            $dataFinal = $_POST['dataFinal'] ?: date('Y-m-d'); // Obté la data final o la de hui
            db_execute('UPDATE Assignacions SET dataFinal = ? WHERE id = ? AND idAlumne = ?', [$dataFinal, $idAssignacio, $id]); // Actualitza la data final
            flash('Estat de l\'assignacio actualitzat.'); // Missatge d'èxit
        } // Tanca l'acció de tancar assignació

        if ($action === 'delete_assignment') { // Si es vol eliminar una assignació
            $idAssignacio = (int) ($_POST['idAssignacio'] ?? 0); // Obté l'ID de l'assignació
            db_execute('DELETE FROM Assignacions WHERE id = ? AND idAlumne = ?', [$idAssignacio, $id]); // Elimina l'assignació de la base de dades
            flash('Assignacio eliminada.'); // Missatge d'èxit
        } // Tanca l'acció d'eliminar assignació
    } catch (Throwable $exception) { // Captura qualsevol error
        error_log('Error detall alumne: ' . $exception->getMessage()); // Registra l'error al servidor
        flash('No s\'ha pogut guardar el canvi.', 'error'); // Missatge d'error per a l'usuari
    } // Tanca el bloc try-catch

    redirect_to('professorat/alumne_detall.php?id=' . $id); // Redirigeix a la mateixa pàgina
} // Tanca el bloc POST

$alumne = db_fetch_one('SELECT id, nom, cognom1, cognom2, correu, grupClasse FROM Alumnes WHERE id = ?', [$id]); // Obté les dades de l'alumne

if (!$alumne) { // Si l'alumne no existeix
    render_error_page(404, 'Alumne no trobat', 'Aquest alumne no existeix a la base de dades.'); // Mostra error 404
    exit; // Atura l'execució
} // Tanca validació d'existència

$assignments = db_fetch_all( // Obté tot l'historial d'assignacions de l'alumne
    'SELECT asg.id AS idAssignacio, asg.dataInici, asg.dataFinal,
            m.id AS idMaterial, m.idInventari, m.etiquetaDepInf, m.numSerie,
            tm.tipus, tm.model, u.nom AS ubicacio,
            inc.id AS idIncidencia, e.estat AS estatIncidencia
     FROM Assignacions asg
     INNER JOIN Material m ON m.id = asg.idMaterial
     INNER JOIN TipusMaterial tm ON tm.id = m.idTipus
     INNER JOIN Ubicacions u ON u.id = m.idUbicacio
     LEFT JOIN Incidencies inc ON inc.idDispositiu = m.id AND inc.dataTancada IS NULL
     LEFT JOIN Estats e ON e.id = inc.idEstat
     WHERE asg.idAlumne = ?
     ORDER BY asg.dataInici DESC, asg.id DESC',
    [$id] // Paràmetre de l\'ID de l'alumne
); // Final de la consulta d'assignacions

$availableMaterial = db_fetch_all( // Obté el material disponible per ser assignat
    'SELECT m.id, m.idInventari, m.etiquetaDepInf, m.numSerie, tm.tipus, tm.model
     FROM Material m
     INNER JOIN TipusMaterial tm ON tm.id = m.idTipus
     WHERE NOT EXISTS (
        SELECT 1 FROM Assignacions a
        WHERE a.idMaterial = m.id AND (a.dataFinal IS NULL OR a.dataFinal >= CURDATE())
     )
     ORDER BY tm.tipus, tm.model, m.idInventari, m.id'
); // Final de la consulta de material disponible

$incidents = db_fetch_all( // Obté les incidències relacionades amb aquest alumne
    'SELECT i.id, i.informacio, i.dataOberta, i.dataTancada, e.estat,
            COALESCE(m.idInventari, m.etiquetaDepInf, m.numSerie, CAST(m.id AS CHAR)) AS material
     FROM Incidencies i
     INNER JOIN Material m ON m.id = i.idDispositiu
     LEFT JOIN Estats e ON e.id = i.idEstat
     WHERE i.idAlumne = ?
     ORDER BY i.dataOberta DESC, i.id DESC',
    [$id] // Paràmetre de l\'ID de l'alumne
); // Final de la consulta d'incidències

render_page('professorat/alumne_detall', [ // Renderitza la pàgina de detall
    'alumne' => $alumne, // Passa les dades de l'alumne
    'assignments' => $assignments, // Passa les assignacions
    'availableMaterial' => $availableMaterial, // Passa el material disponible
    'incidents' => $incidents, // Passa les incidències
], 'Detall alumne'); // Títol de la pàgina
