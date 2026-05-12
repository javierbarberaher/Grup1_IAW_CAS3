<?php // Inicia el bloc de codi PHP

/**
 * Punt d'entrada web de l'aplicacio.
 *
 * Redirigeix al panell corresponent si hi ha sessio, o al login si encara no
 * s'ha iniciat sessio.
 *
 * @package CAS3
 */

require_once __DIR__ . '/includes/auth.php'; // Inclou el fitxer d'autenticacio

$currentUser = user(); // Obté l'usuari actual de la sessio

if ($currentUser) { // Comprova si hi ha un usuari autenticat
    redirect_after_login($currentUser); // Redirigeix l'usuari segons el seu rol
} // Finalitza el bloc condicional

redirect_to('login.php'); // Redirigeix a la pagina de login si no hi ha usuari
