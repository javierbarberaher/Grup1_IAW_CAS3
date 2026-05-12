<?php

/**
 * Endpoint de comprovacio rapida de la API.
 *
 * Serveix per verificar que Apache, PHP i la connexio MariaDB responen.
 */

require_once __DIR__ . '/../includes/http.php'; // Carrega funcions HTTP i JSON.
require_once __DIR__ . '/../includes/db.php'; // Carrega la connexio PDO.

api_start(); // Prepara la resposta JSON.

try { // Intenta comprovar la base de dades externa.
    db()->query('SELECT 1'); // Fa una consulta minima per comprovar MariaDB.
} catch (Throwable $exception) { // Captura errors de connexio sense mostrar dades internes.
    json_error(503, 'La API funciona, pero no hi ha connexio amb la base de dades.'); // Retorna error 503 si falla la BD.
}

json_ok([ // Retorna l'estat del servei si tot es correcte.
    'service' => 'cas3-api', // Identifica aquesta API.
    'database' => 'ok', // Indica que la base de dades respon.
]);
