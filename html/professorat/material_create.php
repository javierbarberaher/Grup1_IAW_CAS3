<?php // Inicia el bloc de codi PHP

/** // Inici del bloc de comentaris de documentació
 * Alta de nou material o maquinari. // Descripció de la funcionalitat
 * // Línia buida de documentació
 * @package CAS3 // Defineix el paquet
 */ // Final del bloc de comentaris

require_once __DIR__ . '/../includes/layout.php'; // Inclou el fitxer de gestió de la interfície
require_once __DIR__ . '/../includes/db.php'; // Inclou el fitxer de connexió a la base de dades

require_web_professor(); // Verifica permisos de professor

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Comprova si la petició és POST
    verify_form_csrf(); // Valida el token de seguretat CSRF

    try { // Inicia bloc de gestió d'errors
        $idTipus = (int) ($_POST['idTipus'] ?? 0); // Obté l'ID del tipus de material triat

        if ($idTipus <= 0) { // Si no s'ha triat un tipus existent (se'n vol crear un de nou)
            $tipus = trim($_POST['tipus'] ?? ''); // Obté el nom del nou tipus
            $model = trim($_POST['model'] ?? ''); // Obté el model
            $origen = trim($_POST['origen'] ?? ''); // Obté l'origen

            if ($tipus === '' || $model === '' || $origen === '') { // Valida que s'hagin informat els nous camps
                flash('Cal seleccionar un tipus existent o informar tipus, model i origen nous.', 'error'); // Missatge d'error si falten dades
                redirect_to('professorat/material_create.php'); // Redirigeix per tornar a intentar-ho
            } // Tanca validació de nou tipus

            db_execute( // Insereix el nou tipus de material a la base de dades
                'INSERT INTO TipusMaterial (tipus, model, origen) VALUES (?, ?, ?)', // SQL d'inserció
                [$tipus, $model, $origen] // Paràmetres del nou tipus
            ); // Final de la inserció de tipus
            $idTipus = (int) db()->lastInsertId(); // Obté l'ID del tipus acabat de crear
        } // Tanca el bloc de creació de nou tipus

        $idInventari = trim($_POST['idInventari'] ?? ''); // Obté l'identificador d'inventari
        $idUbicacio = (int) ($_POST['idUbicacio'] ?? 0); // Obté l'ID de la ubicació

        if ($idInventari === '' || $idUbicacio <= 0) { // Valida camps obligatoris del material
            flash('Cal informar inventari i ubicacio.', 'error'); // Missatge d'error
            redirect_to('professorat/material_create.php'); // Redirigeix
        } // Tanca validació de material

        db_execute( // Insereix el nou element de material
            'INSERT INTO Material
                (idTipus, idInventari, etiquetaDepInf, numSerie, macEthernet, macWifi, SACE, dataAdquisicio, idUbicacio)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)', // SQL d'inserció de material
            [ // Array de paràmetres
                $idTipus, // ID del tipus (existent o nou)
                $idInventari, // ID d'inventari
                trim($_POST['etiquetaDepInf'] ?? '') ?: null, // Etiqueta DepInf o null
                trim($_POST['numSerie'] ?? '') ?: null, // Número de sèrie o null
                trim($_POST['macEthernet'] ?? '') ?: null, // MAC Ethernet o null
                trim($_POST['macWifi'] ?? '') ?: null, // MAC Wi-Fi o null
                trim($_POST['SACE'] ?? '') ?: null, // Codi SACE o null
                $_POST['dataAdquisicio'] ?: null, // Data d'adquisició o null
                $idUbicacio, // Ubicació inicial
            ] // Final de l'array de paràmetres
        ); // Final de la inserció de material

        flash('Material creat correctament.'); // Missatge d'èxit
        redirect_to('professorat/material.php'); // Redirigeix al llistat de material
    } catch (Throwable $exception) { // Captura qualsevol error durant el procés
        error_log('Error crear material: ' . $exception->getMessage()); // Registra l'error
        flash('No s\'ha pogut crear el material.', 'error'); // Missatge d'error per a l'usuari
        redirect_to('professorat/material_create.php'); // Redirigeix per tornar a intentar-ho
    } // Tanca bloc try-catch
} // Tanca bloc POST

$tipusMaterial = db_fetch_all('SELECT id, tipus, model, origen FROM TipusMaterial ORDER BY tipus, model'); // Obté tots els tipus de material per al desplegable
$ubicacions = db_fetch_all('SELECT id, nom FROM Ubicacions ORDER BY nom'); // Obté totes les ubicacions per al desplegable

render_page('professorat/material_create', [ // Renderitza la vista del formulari de creació
    'tipusMaterial' => $tipusMaterial, // Passa els tipus de material
    'ubicacions' => $ubicacions, // Passa les ubicacions
], 'Nou material'); // Títol de la pàgina
