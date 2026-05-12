<?php

/**
 * Aplica les capçaleres CORS per permetre el frontend separat.
 *
 * @return void
 */
function cors()
{
    $origin = $_SERVER['HTTP_ORIGIN'] ?? ''; // Llegeix l'origen real del navegador.
    $allowedOrigins = array_filter(array_map('trim', explode(',', getenv('ALLOWED_ORIGINS') ?: ''))); // Origens permesos a Docker.

    if ($origin != '' && in_array($origin, $allowedOrigins, true)) { // Accepta nomes el frontend configurat.
        header("Access-Control-Allow-Origin: $origin"); // Autoritza aquest origen concret.
        header('Access-Control-Allow-Credentials: true'); // Permet enviar la cookie de sessio.
        header('Vary: Origin'); // Evita cachejar una resposta CORS per a un altre origen.
    }

    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS'); // Metodes HTTP de la API.
    header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token'); // Capçaleres que pot enviar el frontend.

    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { // Respon la peticio previa CORS del navegador.
        http_response_code(204); // No cal cos de resposta en el preflight.
        exit; // Atura l'endpoint abans d'arribar a la logica CRUD.
    }
}

/**
 * Prepara qualsevol endpoint de la API perquè respongui en JSON.
 *
 * @return void
 */
function api_start()
{
    cors(); // Aplica CORS abans de qualsevol resposta.
    header('Content-Type: application/json; charset=utf-8'); // Marca la resposta com JSON UTF-8.
    set_exception_handler('api_exception_handler'); // Evita que errors PHP surtin com HTML.
}

/**
 * Converteix excepcions no controlades en un error JSON generic.
 *
 * @param Throwable $exception Error capturat per PHP.
 * @return void
 */
function api_exception_handler($exception)
{
    if ($exception instanceof PDOException) { // Els errors de BD externa es responen com servei no disponible.
        json_error(503, 'No hi ha connexio amb la base de dades.'); // Error 503 per a fallades de BD.
    }

    json_error(500, 'Error intern de la API.'); // No mostra detalls interns de BD o servidor.
}

/**
 * Retorna una resposta correcta en format JSON i acaba l'execucio.
 *
 * @param array $data Dades extra que s'afegiran a la resposta.
 * @return void
 */
function json_ok($data = [])
{
    echo json_encode(['ok' => true] + $data, JSON_UNESCAPED_UNICODE); // Escriu ok=true i les dades rebudes.
    exit; // Evita que l'endpoint continuï executant codi.
}

/**
 * Retorna una resposta d'error en format JSON i acaba l'execucio.
 *
 * @param int $code Codi HTTP que s'ha de retornar.
 * @param string $message Missatge curt per explicar l'error.
 * @return void
 */
function json_error($code, $message)
{
    http_response_code($code); // Assigna el codi HTTP de l'error.
    echo json_encode([ // Manté el mateix format d'error a tota la API.
        'ok' => false, // Indica que l'operació ha fallat.
        'error' => [
            'status' => $code, // Inclou el codi d'estat a la resposta.
            'message' => $message, // Inclou el missatge explicatiu.
        ],
    ], JSON_UNESCAPED_UNICODE); // Codifica l'array com a JSON.
    exit; // Atura l'execucio despres de respondre.
}

/**
 * Llegeix el cos JSON d'una peticio i el converteix en array PHP.
 *
 * @return array Dades rebudes o array buit si no hi ha JSON valid.
 */
function input_json()
{
    $json = file_get_contents('php://input'); // Llegeix el cos cru de la peticio HTTP.
    $data = json_decode($json, true); // Converteix el JSON en array associatiu.

    if (!is_array($data)) { // Comprova si el JSON no era valid o no era un objecte.
        return []; // Retorna array buit per evitar avisos en llegir camps.
    }

    return $data; // Retorna les dades preparades.
}

/**
 * Permet nomes un metode HTTP concret en un endpoint.
 *
 * @param string $method Metode permes, per exemple GET o POST.
 * @return void
 */
function only_method($method)
{
    if ($_SERVER['REQUEST_METHOD'] != $method) { // Compara el metode rebut amb el metode esperat.
        json_error(405, 'Metode no permes.'); // Respon 405 si el metode no toca.
    }
}

/**
 * Valida que els camps obligatoris arribin informats.
 *
 * @param array $data Dades rebudes del frontend.
 * @param array $fields Camps obligatoris.
 * @return void
 */
function require_fields($data, $fields)
{
    foreach ($fields as $field) { // Revisa camp per camp per donar un error clar.
        if (!isset($data[$field]) || trim((string) $data[$field]) === '') { // Comprova si el camp existeix i no està buit.
            json_error(400, 'Falta el camp obligatori: ' . $field . '.'); // Respon 400 si falta el camp.
        }
    }
}

/**
 * Converteix un camp buit en null per guardar-lo a MariaDB.
 *
 * @param array $data Dades rebudes del frontend.
 * @param string $field Camp opcional.
 * @return mixed Valor rebut o null si esta buit.
 */
function nullable_field($data, $field)
{
    if (!isset($data[$field]) || $data[$field] === '') { // Els camps opcionals del SQL poden quedar a NULL.
        return null; // Retorna NULL per a la base de dades.
    }

    return $data[$field]; // Retorna el valor real quan el frontend l'ha enviat.
}

/**
 * Indica si la peticio espera resposta JSON.
 *
 * @return bool True si es peticio API o Accept JSON.
 */
function wants_json()
{
    $path = $_SERVER['REQUEST_URI'] ?? ''; // Obté la URI sol·licitada.
    $accept = $_SERVER['HTTP_ACCEPT'] ?? ''; // Obté la capçalera Accept.

    return strpos($path, '/api/') !== false || strpos($accept, 'application/json') !== false; // Comprova si és una ruta API o accepta JSON.
}
