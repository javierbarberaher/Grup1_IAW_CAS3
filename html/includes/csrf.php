<?php

/**
 * Helpers CSRF per a formularis HTML.
 *
 * @package CAS3
 */

require_once __DIR__ . '/session.php'; // Carrega la gestió de sessions per accedir al token

/**
 * Retorna el camp ocult CSRF per inserir dins d'un formulari.
 *
 * @return string HTML del camp ocult.
 */
function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">'; // Genera el camp ocult HTML escapant el token
}

/**
 * Valida CSRF en formularis POST i redirigeix amb error si falla.
 *
 * @return void
 */
function verify_form_csrf()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { // Si no és una petició POST...
        return; // ...no cal validar el CSRF
    }

    start_session(); // Assegura que la sessió està iniciada
    $token = request_csrf_token(); // Obté el token enviat a la petició

    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) { // Compara el token de sessió amb el rebut
        http_response_code(419); // Codi HTTP 419 (Page Expired) si el token no és vàlid
        exit('Token CSRF incorrecte.'); // Atura l'execució amb un missatge d'error
    }
}
