<?php

/**
 * Autenticacio i control d'acces per a la web i la API.
 *
 * El projecte no modifica l'esquema SQL per crear taula d'usuaris. Per això
 * usa comptes configurables per variables d'entorn i contrasenyes amb hash.
 *
 * @package CAS3
 */

require_once __DIR__ . '/config.php'; // Carrega la configuració general
require_once __DIR__ . '/db.php'; // Carrega les funcions de base de dades
require_once __DIR__ . '/session.php'; // Carrega la gestió de sessions
require_once __DIR__ . '/helpers.php'; // Carrega funcions auxiliars

/**
 * Retorna els comptes configurats per iniciar sessio.
 *
 * @return array Llista de comptes amb hash de contrasenya.
 */
function auth_accounts()
{
    return [ // Retorna un array amb els usuaris de fallback
        [
            'id' => 1, // Identificador únic per al professor
            'correu' => env_value('API_PROFESSOR_EMAIL', 'professor@iesmontsia.org'), // Correu del professorat
            'password_hash' => env_value('API_PROFESSOR_PASSWORD_HASH', '$2y$10$abcdefghijklmnopqrstuuiQo633qBvjHafFtT7zjq9sxAnCMbaFi'), // Hash de la contrasenya
            'rol' => ROLE_PROFESSOR, // Assigna el rol de professorat
            'nom' => env_value('API_PROFESSOR_NAME', 'Professorat'), // Nom a mostrar
            'idAlumne' => null, // El professor no té ID d'alumne associat
        ],
        [
            'id' => 2, // Identificador únic per a l'alumne
            'correu' => env_value('API_STUDENT_EMAIL', 'alumne@iesmontsia.org'), // Correu de l'alumnat
            'password_hash' => env_value('API_STUDENT_PASSWORD_HASH', '$2y$10$abcdefghijklmnopqrstuu41ogBvzNqENxRSa3f9kXTolemOvxrke'), // Hash de la contrasenya
            'rol' => ROLE_STUDENT, // Assigna el rol d'alumnat
            'nom' => env_value('API_STUDENT_NAME', 'Alumnat'), // Nom a mostrar
            'idAlumne' => (int) env_value('API_STUDENT_ID', '1'), // ID d'alumne associat
        ],
    ];
}

/**
 * Cerca un compte de fallback pel correu.
 *
 * @param string $email Correu rebut al login.
 * @return array|null Compte trobat o null.
 */
function find_fallback_auth_account($email)
{
    foreach (auth_accounts() as $account) { // Itera sobre els comptes de fallback
        if (strcasecmp($account['correu'], $email) === 0) { // Compara correus sense distingir majúscules
            return $account; // Retorna el compte si coincideix
        }
    }

    return null; // Retorna null si no es troba el compte
}

/**
 * Cerca un usuari actiu a la taula Usuaris.
 *
 * @param string $email Correu rebut al login.
 * @return array|null Usuari actiu o null.
 */
function find_database_user($email)
{
    return db_fetch_one( // Executa la consulta a la base de dades
        'SELECT id, nom, cognom1, cognom2, correu, contrasenya_hash, rol, idAlumne, actiu
         FROM Usuaris
         WHERE correu = ? AND actiu = 1
         LIMIT 1', // Busca un usuari actiu pel seu correu
        [$email] // Passa el correu com a paràmetre segur
    );
}

/**
 * Prepara les dades publiques d'un usuari de base de dades.
 *
 * @param array $row Fila de la taula Usuaris.
 * @return array Usuari preparat per sessio.
 */
function database_user_to_session_user($row)
{
    return [ // Retorna les dades normalitzades per a la sessió
        'id' => (int) $row['id'], // Converteix l'ID a enter
        'correu' => $row['correu'], // Manté el correu electrònic
        'password_hash' => $row['contrasenya_hash'], // Manté el hash per a validació si cal
        'rol' => strtoupper($row['rol']), // Normalitza el rol a majúscules
        'nom' => trim(implode(' ', array_filter([ // Construeix el nom complet
            $row['nom'] ?? '', // Nom de pila
            $row['cognom1'] ?? '', // Primer cognom
        ]))),
        'idAlumne' => $row['idAlumne'] !== null ? (int) $row['idAlumne'] : null, // ID d'alumne opcional
    ];
}

/**
 * Valida credencials contra la taula Usuaris.
 *
 * @param string $email Correu del formulari.
 * @param string $password Contrasenya en clar rebuda del formulari.
 * @return array|null Usuari validat o null.
 */
function authenticate_credentials($email, $password)
{
    try {
        $databaseUser = find_database_user($email); // Intenta trobar l'usuari a la BD

        if ($databaseUser && password_verify($password, $databaseUser['contrasenya_hash'])) { // Verifica la contrasenya
            return database_user_to_session_user($databaseUser); // Retorna l'usuari si és vàlid
        }
    } catch (Throwable $exception) { // Captura qualsevol error
        error_log('Error autenticant amb Usuaris: ' . $exception->getMessage()); // Registra l'error al log
    }

    $account = find_fallback_auth_account($email); // Si falla la BD, prova amb els comptes de fallback

    if (!$account || !password_verify($password, $account['password_hash'])) { // Verifica la contrasenya del fallback
        return null; // Retorna null si tampoc coincideix
    }

    return $account; // Retorna el compte de fallback validat
}

/**
 * Indica si hi ha usuari autenticat.
 *
 * @return bool True si hi ha sessio activa.
 */
function is_authenticated()
{
    return user() !== null; // Comprova si la funció user() retorna dades
}

/**
 * Indica si l'usuari actual es professor.
 *
 * @return bool True si el rol es professor.
 */
function is_professor()
{
    $currentUser = user(); // Obté l'usuari actual de la sessió

    return $currentUser && $currentUser['rol'] === ROLE_PROFESSOR; // Verifica si té el rol de professor
}

/**
 * Indica si l'usuari actual es alumne.
 *
 * @return bool True si el rol es alumne.
 */
function is_student()
{
    $currentUser = user(); // Obté l'usuari actual de la sessió

    return $currentUser && $currentUser['rol'] === ROLE_STUDENT; // Verifica si té el rol d'alumne
}

/**
 * Requereix autenticacio per a una pagina web.
 *
 * @return array Usuari autenticat.
 */
function require_web_auth()
{
    $currentUser = user(); // Intenta obtenir l'usuari actual

    if (!$currentUser) { // Si no hi ha sessió...
        redirect_to('login.php'); // ...redirigeix a la pàgina de login
    }

    return $currentUser; // Retorna l'usuari si està autenticat
}

/**
 * Requereix rol de professor per a una pagina web.
 *
 * @return array Usuari professor.
 */
function require_web_professor()
{
    $currentUser = require_web_auth(); // Força que l'usuari estigui autenticat

    if ($currentUser['rol'] !== ROLE_PROFESSOR) { // Si no és professor...
        http_response_code(403); // ...envia codi d'error 403 (Prohibit)
        require __DIR__ . '/../errors/403.php'; // Carrega la vista d'error
        exit; // Atura l'execució
    }

    return $currentUser; // Retorna l'usuari si té el rol correcte
}

/**
 * Requereix rol d'alumne per a una pagina web.
 *
 * @return array Usuari alumne.
 */
function require_web_student()
{
    $currentUser = require_web_auth(); // Força que l'usuari estigui autenticat

    if ($currentUser['rol'] !== ROLE_STUDENT) { // Si no és alumne...
        http_response_code(403); // ...envia codi d'error 403 (Prohibit)
        require __DIR__ . '/../errors/403.php'; // Carrega la vista d'error
        exit; // Atura l'execució
    }

    return $currentUser; // Retorna l'usuari si té el rol correcte
}

/**
 * Redirigeix un usuari autenticat al panell del seu rol.
 *
 * @param array $currentUser Dades de sessio.
 * @return void
 */
function redirect_after_login($currentUser)
{
    if ($currentUser['rol'] === ROLE_PROFESSOR) { // Si és professor...
        redirect_to('professorat/index.php'); // ...va al panell de professorat
    }

    redirect_to('alumnat/index.php'); // Si no, va al panell d'alumnat
}

/**
 * Retorna una etiqueta humana per al rol.
 *
 * @param string|null $role Rol intern.
 * @return string Etiqueta visual.
 */
function role_label($role)
{
    if ($role === ROLE_PROFESSOR) { // Si el rol és de professor...
        return 'Professorat'; // ...retorna el text amable
    }

    if ($role === ROLE_STUDENT) { // Si el rol és d'alumne...
        return 'Alumnat'; // ...retorna el text amable
    }

    return 'Visitant'; // Per a qualsevol altre cas
}
