<?php

/**
 * Endpoint de login.
 *
 * Rep correu i contrasenya en JSON, valida un usuari de prova configurat
 * per variables d'entorn i crea una sessio PHP amb rol i token CSRF.
 */

require_once __DIR__ . '/../../includes/http.php'; // Carrega funcions per respondre en JSON.
require_once __DIR__ . '/../../includes/auth.php'; // Carrega comptes configurables i validacio de credencials.

api_start(); // Prepara capçaleres CORS i resposta JSON.
only_method('POST'); // El login nomes accepta peticions POST.

$data = input_json(); // Llegeix i descodifica el JSON enviat pel frontend.
$correu = trim($data['correu'] ?? ''); // Obté el correu i elimina espais en blanc.
$password = $data['password'] ?? ''; // Obté la contrasenya de les dades rebudes.

if ($correu == '' || $password == '') { // Comprova que cap dels dos camps estigui buit.
    json_error(400, 'Falten dades.'); // Retorna error 400 si falten camps obligatoris.
}

$foundUser = authenticate_credentials($correu, $password); // Verifica les credencials contra la configuració.

if (!$foundUser) { // Si la validació de credencials ha fallat...
    json_error(401, 'Credencials incorrectes.'); // Retorna error 401 per accés no autoritzat.
}

login_user($foundUser); // Inicialitza la sessió de l'usuari i genera el token CSRF.

json_ok([ // Retorna les dades d'èxit de l'autenticació.
    'user' => user(), // Retorna la informació pública de l'usuari loguejat.
    'csrf_token' => csrf_token(), // Retorna el token CSRF necessari per a futures peticions.
]);
