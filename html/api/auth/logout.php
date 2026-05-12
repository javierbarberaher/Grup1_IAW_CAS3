<?php

/**
 * Endpoint per tancar la sessio.
 *
 * Es protegeix amb login i CSRF perquè modifica l'estat de la sessio.
 */

require_once __DIR__ . '/../../includes/http.php'; // Carrega funcions per respondre en JSON.
require_once __DIR__ . '/../../includes/session.php'; // Carrega funcions de sessio i CSRF.

api_start(); // Prepara CORS i la resposta en format JSON.
only_method('POST'); // Restringeix l'accés només a peticions de tipus POST.
require_login(); // Verifica que l'usuari estigui autenticat abans de tancar sessió.
check_csrf(); // Valida el token CSRF per prevenir tancaments de sessió no desitjats.

logout_user(); // Destrueix la sessió actual i elimina les dades de l'usuari.

json_ok(); // Retorna una resposta JSON confirmant l'èxit de l'operació.
