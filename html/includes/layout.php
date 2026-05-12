<?php

/**
 * Renderitzat simple de vistes i plantilles comunes.
 *
 * @package CAS3
 */

require_once __DIR__ . '/auth.php'; // Carrega funcions d'autenticació
require_once __DIR__ . '/csrf.php'; // Carrega funcions de protecció CSRF
require_once __DIR__ . '/helpers.php'; // Carrega funcions auxiliars de format i URLs

/**
 * Retorna la ruta absoluta d'una vista.
 *
 * @param string $view Nom de la vista sense extensio.
 * @return string Ruta absoluta.
 */
function view_path($view)
{
    return __DIR__ . '/../views/' . trim($view, '/') . '.php'; // Construeix la ruta al fitxer de la vista
}

/**
 * Inclou una vista amb variables locals.
 *
 * @param string $view Nom de la vista.
 * @param array $data Variables disponibles dins la vista.
 * @return void
 */
function render_view($view, $data = [])
{
    $file = view_path($view); // Obté la ruta del fitxer

    if (!is_file($file)) { // Si el fitxer no existeix...
        throw new RuntimeException('Vista no trobada: ' . $view); // ...llança una excepció
    }

    extract($data, EXTR_SKIP); // Converteix l'array en variables locals, sense sobreescriure les existents
    require $file; // Carrega i executa el fitxer de la vista
}

/**
 * Renderitza una pagina completa amb header, nav i footer.
 *
 * @param string $view Nom de la vista principal.
 * @param array $data Variables per a la vista.
 * @param string $title Titol de pagina.
 * @return void
 */
function render_page($view, $data = [], $title = '')
{
    $pageTitle = $title; // Assigna el títol de la pàgina
    $currentUser = user(); // Obté l'usuari autenticat de la sessió
    $flashMessages = flash_messages(); // Obté els missatges temporals pendents

    require view_path('partials/header');
    echo '<div class="app-shell">';
    if (
        $currentUser
        && (($currentUser['rol'] ?? '') === ROLE_PROFESSOR || ($currentUser['rol'] ?? '') === ROLE_STUDENT)
    ) {
        require view_path('partials/sidebar');
    }
    echo '<div class="app-content">';
    require view_path('partials/flash');
    echo '<main class="page-shell">';
    render_view($view, $data);
    echo '</main>';
    echo '</div></div>';
    require view_path('partials/footer');
}

/**
 * Renderitza una pagina d'error HTML.
 *
 * @param int $status Codi HTTP.
 * @param string $title Titol curt.
 * @param string $message Missatge visible.
 * @return void
 */
function render_error_page($status, $title, $message)
{
    http_response_code($status); // Estableix el codi d'estat HTTP de l'error
    render_page('errors/error', [ // Renderitza la pàgina d'error genèrica
        'status' => $status, // Passa el codi d'estat
        'title' => $title, // Passa el títol de l'error
        'message' => $message, // Passa el missatge detallat
    ], $title); // Estableix el títol de la pestanya del navegador
}
