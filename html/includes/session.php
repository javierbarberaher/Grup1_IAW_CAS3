<?php

/**
 * Gestio segura de sessions PHP.
 *
 * @package CAS3
 */

require_once __DIR__ . '/config.php'; // Carrega la configuració general

/**
 * Indica si la peticio actual arriba per HTTPS.
 *
 * @return bool True si la peticio es segura.
 */
function is_https_request()
{
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') { // Comprova la variable HTTPS estàndard
        return true; // Retorna cert si HTTPS està activat
    }

    if (($_SERVER['SERVER_PORT'] ?? '') === '443') { // Comprova si el port és el 443
        return true; // Retorna cert si el port és el de HTTPS
    }

    return strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'; // Comprova la capçalera de proxy/load balancer
}

/**
 * Retorna el valor SameSite configurat per la cookie de sessio.
 *
 * @return string Valor SameSite acceptat per PHP.
 */
function session_same_site()
{
    $sameSite = env_value('SESSION_SAMESITE', 'Lax'); // Llegeix la configuració SameSite de l'entorn
    $allowed = ['Lax', 'Strict', 'None']; // Llista de valors permesos per SameSite

    return in_array($sameSite, $allowed, true) ? $sameSite : 'Lax'; // Retorna el valor si és vàlid, altrament 'Lax'
}

/**
 * Inicia la sessio PHP amb atributs de cookie segurs.
 *
 * @return void
 */
function start_session()
{
    if (session_status() === PHP_SESSION_ACTIVE) { // Si la sessió ja està en marxa...
        return; // ...no fem res
    }

    ini_set('session.use_strict_mode', '1'); // Evita que es pugui usar un ID de sessió no inicialitzat pel servidor
    ini_set('session.use_only_cookies', '1'); // Força l'ús exclusiu de cookies per a la sessió (no per URL)

    $sameSite = session_same_site(); // Obté la política SameSite
    $secure = env_bool('SESSION_SECURE', is_https_request()); // Defineix si la cookie ha de ser Secure

    if ($sameSite === 'None') { // Si SameSite és None...
        $secure = true; // ...la cookie ha de ser obligatòriament Secure per normativa dels navegadors
    }

    session_name(env_value('SESSION_NAME', 'CAS3SESSID')); // Defineix el nom de la cookie de sessió
    session_set_cookie_params([ // Estableix els paràmetres de seguretat de la cookie
        'lifetime' => 0, // La cookie caduca en tancar el navegador
        'path' => BASE_URL, // Defineix el camí de validesa de la cookie
        'secure' => $secure, // Només s'envia per connexions segures si està activat
        'httponly' => true, // Impedeix l'accés a la cookie des de JavaScript
        'samesite' => $sameSite, // Aplica la política de protecció contra CSRF
    ]);

    session_start(); // Inicia formalment la sessió PHP
}

/**
 * Desa a sessio les dades basiques de l'usuari autenticat.
 *
 * @param array $user Usuari validat pel login.
 * @return void
 */
function login_user($user)
{
    start_session(); // Assegura que la sessió està activa
    session_regenerate_id(true); // Regenera l'ID de sessió per prevenir fixació de sessió

    $_SESSION['user'] = [ // Desa les dades de l'usuari a la sessió
        'id' => (int) $user['id'], // ID únic de l'usuari
        'correu' => $user['correu'], // Correu de l'usuari
        'nom' => $user['nom'] ?? $user['correu'], // Nom a mostrar (o correu si no n'hi ha)
        'rol' => $user['rol'], // Rol de l'usuari (PROFESSOR/ALUMNE)
        'idAlumne' => isset($user['idAlumne']) && $user['idAlumne'] !== null ? (int) $user['idAlumne'] : null, // ID d'alumne si escau
    ];

    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Genera un nou token CSRF segur per a la sessió
}

/**
 * Retorna l'usuari guardat a la sessio actual.
 *
 * @return array|null Usuari autenticat o null si no hi ha login.
 */
function user()
{
    start_session(); // Assegura que la sessió està activa

    return $_SESSION['user'] ?? null; // Retorna les dades de l'usuari o null si no n'hi ha
}

/**
 * Obliga a tenir sessio iniciada dins d'un endpoint API.
 *
 * @return array Dades de l'usuari autenticat.
 */
function require_login()
{
    $currentUser = user(); // Intenta obtenir l'usuari de la sessió

    if (!$currentUser) { // Si no hi ha usuari...
        json_error(401, "Has d'iniciar sessio."); // ...respon amb error 401 (No autoritzat)
    }

    return $currentUser; // Retorna les dades de l'usuari si està loguejat
}

/**
 * Obliga que l'usuari autenticat sigui professor dins d'un endpoint API.
 *
 * @return array Dades de l'usuari professor.
 */
function require_professor()
{
    $currentUser = require_login(); // Força que hi hagi sessió activa

    if ($currentUser['rol'] !== ROLE_PROFESSOR) { // Si l'usuari no és professor...
        json_error(403, 'No tens permisos.'); // ...respon amb error 403 (Prohibit)
    }

    return $currentUser; // Retorna les dades de l'usuari si té el rol correcte
}

/**
 * Retorna el token CSRF de la sessio o en crea un si falta.
 *
 * @return string Token CSRF de la sessio.
 */
function csrf_token()
{
    start_session(); // Assegura que la sessió està activa

    if (empty($_SESSION['csrf_token'])) { // Si no hi ha token CSRF encara...
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // ...en genera un de nou de 32 bytes
    }

    return $_SESSION['csrf_token']; // Retorna el token de la sessió
}

/**
 * Llegeix el token CSRF enviat per capçalera API o formulari HTML.
 *
 * @return string Token rebut o cadena buida.
 */
function request_csrf_token()
{
    return $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? ''); // Prioritza la capçalera HTTP i després el camp POST
}

/**
 * Valida el token CSRF en peticions que modifiquen dades.
 *
 * @return void
 */
function check_csrf()
{
    if ($_SERVER['REQUEST_METHOD'] === 'GET') { // Si la petició és GET...
        return; // ...no cal validar CSRF
    }

    start_session(); // Assegura que la sessió està activa
    $token = request_csrf_token(); // Obté el token de la petició

    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) { // Si els tokens no coincideixen...
        json_error(419, 'Token CSRF incorrecte.'); // ...respon amb error 419
    }
}

/**
 * Tanca la sessio de l'usuari actual.
 *
 * @return void
 */
function logout_user()
{
    start_session(); // Assegura que la sessió està activa
    $_SESSION = []; // Buida totes les variables de sessió

    if (ini_get('session.use_cookies')) { // Si s'usen cookies per a la sessió...
        $params = session_get_cookie_params(); // Obté els paràmetres de la cookie actual
        setcookie(session_name(), '', [ // Sobreescriu la cookie amb una caducada
            'expires' => time() - 42000, // Data de caducitat en el passat
            'path' => $params['path'], // Mateix camí
            'domain' => $params['domain'] ?? '', // Mateix domini
            'secure' => $params['secure'], // Mateix estat de seguretat
            'httponly' => $params['httponly'], // Mateix estat HTTPOnly
            'samesite' => $params['samesite'] ?? 'Lax', // Mateixa política SameSite
        ]);
    }

    session_destroy(); // Destrueix la sessió al servidor
}
