<?php

/**
 * Endpoint per consultar l'usuari autenticat.
 *
 * Serveix perquè el frontend comprovi si la sessio continua activa.
 */

require_once __DIR__ . '/../../includes/http.php'; // Carrega funcions per a la resposta en format JSON.
require_once __DIR__ . '/../../includes/session.php'; // Carrega funcions de sessió i autenticació.

api_start(); // Prepara les capçaleres CORS i el format de resposta JSON.
only_method('GET'); // Accepta exclusivament peticions de consulta de tipus GET.

json_ok([ // Retorna les dades de la sessió de l'usuari actual.
    'user' => require_login(), // Obliga a estar autenticat i obté la informació de l'usuari.
    'csrf_token' => csrf_token(), // Obté el token CSRF vigent per a la sessió actual.
]);
