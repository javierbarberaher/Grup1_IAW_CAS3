<?php // Inicia el bloc de codi PHP

/**
 * Tanca la sessio de l'usuari.
 *
 * @package CAS3
 */

require_once __DIR__ . '/includes/auth.php'; // Inclou les funcions d'autenticacio
require_once __DIR__ . '/includes/csrf.php'; // Inclou les funcions de proteccio CSRF

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Comprova si la peticio es de tipus POST
    verify_form_csrf(); // Verifica el token CSRF per seguretat
    logout_user(); // Executa la funcio de tancament de sessio
} // Finalitza el bloc condicional de la peticio

redirect_to('login.php'); // Redirigeix l'usuari a la pagina de login
