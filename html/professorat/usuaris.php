<?php // Inicia el bloc de codi PHP

/** // Inici del bloc de comentaris de documentació
 * Gestio d'usuaris d'acces a l'aplicacio. // Descripció de la funcionalitat del fitxer
 * // Línia buida de documentació
 * @package CAS3 // Defineix el paquet al qual pertany el fitxer
 */ // Final del bloc de comentaris de documentació

require_once __DIR__ . '/../includes/layout.php'; // Inclou el fitxer de gestió de la interfície
require_once __DIR__ . '/../includes/db.php'; // Inclou el fitxer de connexió i funcions de base de dades

$currentUser = require_web_professor(); // Verifica permisos i obté l'usuari actual loguejat

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Comprova si es tracta d'una petició POST
    verify_form_csrf(); // Valida el token CSRF per evitar atacs
    $action = $_POST['action'] ?? ''; // Obté l'acció (create, update, delete)

    try { // Inicia bloc per gestionar excepcions
        if ($action === 'create' || $action === 'update') { // Si l'acció és crear o actualitzar
            $id = (int) ($_POST['id'] ?? 0); // Obté l'ID de l'usuari
            $nom = trim($_POST['nom'] ?? ''); // Obté i neteja el nom
            $cognom1 = trim($_POST['cognom1'] ?? ''); // Obté i neteja el primer cognom
            $cognom2 = trim($_POST['cognom2'] ?? '') ?: null; // Obté el segon cognom o null si està buit
            $correu = trim($_POST['correu'] ?? ''); // Obté i neteja el correu
            $rol = $_POST['rol'] === ROLE_PROFESSOR ? ROLE_PROFESSOR : ROLE_STUDENT; // Assigna el rol segons la selecció
            $idAlumne = $rol === ROLE_STUDENT && (int) ($_POST['idAlumne'] ?? 0) > 0 ? (int) $_POST['idAlumne'] : null; // Vincula amb un alumne si el rol és estudiant
            $actiu = isset($_POST['actiu']) ? 1 : 0; // Comprova si l'usuari està marcat com actiu
            $password = $_POST['password'] ?? ''; // Obté la contrasenya del formulari

            if ($nom === '' || $cognom1 === '' || $correu === '' || !filter_var($correu, FILTER_VALIDATE_EMAIL)) { // Valida camps requerits i format de correu
                flash('Cal informar nom, cognom i un correu valid.', 'error'); // Mostra error si la validació falla
            } elseif ($action === 'create') { // Si estem creant un nou usuari
                if ($password === '') { // La contrasenya és obligatòria per a nous usuaris
                    flash('Cal informar una contrasenya inicial.', 'error'); // Missatge d'error si falta contrasenya
                } else { // Si les dades són correctes per crear
                    db_execute( // Executa la inserció de l'usuari
                        'INSERT INTO Usuaris (nom, cognom1, cognom2, correu, contrasenya_hash, rol, idAlumne, actiu)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)', // SQL d'inserció
                        [$nom, $cognom1, $cognom2, $correu, password_hash($password, PASSWORD_DEFAULT), $rol, $idAlumne, $actiu] // Valors, xifrant la contrasenya
                    ); // Final de l'execució d'inserció
                    flash('Usuari creat correctament.'); // Missatge d'èxit
                } // Tanca validació de contrasenya inicial
            } elseif ($id > 0) { // Si estem actualitzant un usuari existent
                db_execute( // Executa l'actualització de dades bàsiques
                    'UPDATE Usuaris SET nom = ?, cognom1 = ?, cognom2 = ?, correu = ?, rol = ?, idAlumne = ?, actiu = ? WHERE id = ?', // SQL d'actualització
                    [$nom, $cognom1, $cognom2, $correu, $rol, $idAlumne, $actiu, $id] // Valors per actualitzar
                ); // Final de l'execució d'actualització

                if ($password !== '') { // Si s'ha introduït una nova contrasenya
                    db_execute('UPDATE Usuaris SET contrasenya_hash = ? WHERE id = ?', [password_hash($password, PASSWORD_DEFAULT), $id]); // Actualitza el hash de la contrasenya
                } // Tanca l'actualització de contrasenya

                flash('Usuari actualitzat correctament.'); // Missatge d'èxit
            } // Tanca els condicionals d'acció create/update
        } // Tanca el bloc d'accions create/update

        if ($action === 'delete') { // Si l'acció és eliminar un usuari
            $id = (int) ($_POST['id'] ?? 0); // Obté l'ID de l'usuari a eliminar

            if ($id === (int) $currentUser['id']) { // Impedeix que un usuari s'elimini a si mateix
                flash('No pots eliminar el teu propi usuari mentre tens sessio oberta.', 'error'); // Mostra error si intenta auto-eliminar-se
            } elseif ($id > 0) { // Si l'ID és vàlid i no és el propi
                db_execute('DELETE FROM Usuaris WHERE id = ?', [$id]); // Executa l'eliminació
                flash('Usuari eliminat.'); // Missatge d'èxit
            } // Tanca la validació d'eliminació
        } // Tanca l'acció delete
    } catch (Throwable $exception) { // Captura qualsevol error durant el procés
        error_log('Error gestio usuaris: ' . $exception->getMessage()); // Registra l'error
        flash('No s\'ha pogut guardar l\'usuari. Revisa correu duplicat o alumne ja vinculat.', 'error'); // Missatge d'error general
    } // Final del bloc try-catch

    redirect_to('professorat/usuaris.php'); // Redirigeix de nou a la pàgina de gestió
} // Tanca el bloc POST

$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0; // Comprova si es demana editar un usuari concret

$users = db_fetch_all( // Obté la llista de tots els usuaris
    'SELECT u.id, u.nom, u.cognom1, u.cognom2, u.correu, u.rol, u.idAlumne, u.actiu, u.creatEl,
            CONCAT_WS(" ", a.nom, a.cognom1, a.cognom2) AS alumne
     FROM Usuaris u
     LEFT JOIN Alumnes a ON a.id = u.idAlumne
     ORDER BY u.rol, u.cognom1, u.nom'
); // Final de la consulta d'usuaris

$students = db_fetch_all('SELECT id, nom, cognom1, cognom2, grupClasse FROM Alumnes ORDER BY cognom1, cognom2, nom'); // Obté llista d'alumnes per al desplegable de vinculació
$editUser = $editId > 0 // Si estem en mode edició
    ? db_fetch_one('SELECT id, nom, cognom1, cognom2, correu, rol, idAlumne, actiu FROM Usuaris WHERE id = ?', [$editId]) // Obté les dades de l'usuari a editar
    : null; // Si no, assigna null

render_page('professorat/usuaris', [ // Renderitza la vista de gestió d'usuaris
    'users' => $users, // Passa la llista d'usuaris
    'students' => $students, // Passa la llista d'estudiants per vincular
    'editUser' => $editUser, // Passa l'usuari que s'està editant
    'currentUser' => $currentUser, // Passa l'usuari actual loguejat
], 'Usuaris'); // Títol de la pàgina
