<?php

/**
 * Endpoint CRUD d'alumnes.
 *
 * GET permet consultar alumnes, POST crea, PUT edita i DELETE elimina.
 * Els alumnes nomes veuen el seu propi registre i el professor pot modificar.
 */

require_once __DIR__ . '/../includes/http.php'; // Carrega funcions comunes HTTP i JSON.
require_once __DIR__ . '/../includes/db.php'; // Carrega la connexio PDO i l'ajuda next_id().
require_once __DIR__ . '/../includes/schema.php'; // Carrega el mapa de taules basat en el SQL de Ignora.
require_once __DIR__ . '/../includes/session.php'; // Carrega login, rols i CSRF.

api_start(); // Prepara la resposta de la API.
$method = $_SERVER['REQUEST_METHOD']; // Guarda el metode HTTP rebut (GET, POST, etc.).
$user = require_login(); // Obliga a tenir sessio iniciada i guarda l'usuari.

if ($method != 'GET') { // Si la peticio vol modificar dades (POST, PUT, DELETE)...
    require_professor(); // Comprova que l'usuari tingui rol de professor.
    check_csrf(); // Valida el token CSRF per seguretat en operacions de canvi.
    $data = input_json(); // Llegeix i descodifica les dades JSON de la peticio.
}

$pdo = db(); // Estableix la connexio amb la base de dades MariaDB.

$students = schema_table('students'); // Obté el nom real de la taula d'alumnes.
$studentId = schema_field('students', 'id'); // Obté el nom real del camp ID.
$studentName = schema_field('students', 'name'); // Obté el nom real del camp nom.
$studentSurname1 = schema_field('students', 'surname1'); // Obté el nom real del primer cognom.
$studentSurname2 = schema_field('students', 'surname2'); // Obté el nom real del segon cognom.
$studentEmail = schema_field('students', 'email'); // Obté el nom real del correu electronic.
$studentGroup = schema_field('students', 'group'); // Obté el nom real del grup de classe.

if ($method == 'GET') { // Si s'ha sol·licitat una consulta de dades...
    $select = "SELECT $studentId AS id,
                      $studentName AS nom,
                      $studentSurname1 AS cognom1,
                      $studentSurname2 AS cognom2,
                      $studentEmail AS correu,
                      $studentGroup AS grupClasse
               FROM $students"; // Defineix la consulta SELECT amb els àlies corresponents.

    if ($user['rol'] == 'ALUMNE') { // Si l'usuari es un alumne...
        $stmt = $pdo->prepare($select . " WHERE $studentId = ?"); // Prepara la consulta filtrant pel seu ID.
        $stmt->execute([$user['idAlumne']]); // Executa la consulta amb l'ID de l'alumne loguejat.
    } else { // Si l'usuari es un professor...
        $stmt = $pdo->query($select . " ORDER BY $studentSurname1, $studentSurname2, $studentName"); // Consulta tots els alumnes ordenats.
    }

    json_ok(['data' => $stmt->fetchAll()]); // Retorna els resultats en format JSON.
}

if ($method == 'POST') { // Si s'ha sol·licitat crear un nou registre...
    require_fields($data, ['nom', 'cognom1', 'correu', 'grupClasse']); // Verifica que els camps obligatoris estiguin presents.
    $id = isset($data['id']) && $data['id'] !== '' ? (int) $data['id'] : next_id($pdo, $students, $studentId); // Determina l'ID o en genera un de nou.

    $stmt = $pdo->prepare("INSERT INTO $students ($studentId, $studentName, $studentSurname1, $studentSurname2, $studentEmail, $studentGroup)
                           VALUES (?, ?, ?, ?, ?, ?)"); // Prepara l'ordre INSERT.
    $stmt->execute([ // Executa la insercio amb les dades rebudes.
        $id, // Valor per al camp ID.
        $data['nom'], // Valor per al camp nom.
        $data['cognom1'], // Valor per al camp primer cognom.
        nullable_field($data, 'cognom2'), // Valor per al camp segon cognom (opcional).
        $data['correu'], // Valor per al camp correu.
        $data['grupClasse'], // Valor per al camp grup.
    ]);

    json_ok(['id' => $id]); // Retorna l'ID del nou registre creat.
}

if ($method == 'PUT') { // Si s'ha sol·licitat actualitzar un registre existent...
    require_fields($data, ['id', 'nom', 'cognom1', 'correu', 'grupClasse']); // Verifica que hi hagi l'ID i els camps necessaris.

    $stmt = $pdo->prepare("UPDATE $students
                           SET $studentName = ?, $studentSurname1 = ?, $studentSurname2 = ?, $studentEmail = ?, $studentGroup = ?
                           WHERE $studentId = ?"); // Prepara l'ordre UPDATE.
    $stmt->execute([ // Executa l'actualitzacio amb les noves dades.
        $data['nom'], // Nou valor per al nom.
        $data['cognom1'], // Nou valor per al primer cognom.
        nullable_field($data, 'cognom2'), // Nou valor per al segon cognom (opcional).
        $data['correu'], // Nou valor per al correu.
        $data['grupClasse'], // Nou valor per al grup.
        (int) $data['id'], // ID del registre a actualitzar.
    ]);

    json_ok(); // Retorna una resposta d'exit.
}

if ($method == 'DELETE') { // Si s'ha sol·licitat eliminar un registre...
    require_fields($data, ['id']); // Verifica que s'hagi enviat l'ID a esborrar.
    $stmt = $pdo->prepare("DELETE FROM $students WHERE $studentId = ?"); // Prepara l'ordre DELETE.
    $stmt->execute([(int) $data['id']]); // Executa l'eliminacio.
    json_ok(); // Retorna una resposta d'exit.
}

json_error(405, 'Metode no permes.'); // Si no s'ha entrat en cap mètode anterior, retorna error 405.
