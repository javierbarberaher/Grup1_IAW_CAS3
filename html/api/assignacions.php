<?php

/**
 * Endpoint CRUD d'assignacions de material.
 *
 * Dona suport al lloguer de dispositius: consultar, crear, editar i eliminar
 * assignacions entre alumnes i material.
 */

require_once __DIR__ . '/../includes/http.php'; // Carrega funcions HTTP i JSON.
require_once __DIR__ . '/../includes/db.php'; // Carrega la connexio PDO i next_id().
require_once __DIR__ . '/../includes/schema.php'; // Carrega el mapa de taules basat en el SQL de Ignora.
require_once __DIR__ . '/../includes/session.php'; // Carrega login, rols i CSRF.

api_start(); // Prepara la resposta de la API.
$method = $_SERVER['REQUEST_METHOD']; // Guarda el metode HTTP rebut (GET, POST, etc.).
$user = require_login(); // Obliga a tenir sessio iniciada i guarda l'usuari.

if ($method != 'GET') { // Si la peticio vol modificar dades (POST, PUT, DELETE)...
    require_professor(); // Nomes el professor pot modificar les assignacions.
    check_csrf(); // Protegeix els canvis amb el token CSRF.
    $data = input_json(); // Llegeix i descodifica les dades JSON de la peticio.
}

$pdo = db(); // Estableix la connexio amb la base de dades MariaDB.

$assignments = schema_table('assignments'); // Obté el nom real de la taula d'assignacions.
$students = schema_table('students'); // Obté el nom real de la taula d'alumnes.
$material = schema_table('material'); // Obté el nom real de la taula de material.

$assignmentId = schema_field('assignments', 'id'); // Obté el nom del camp ID d'assignacions.
$assignmentStudentId = schema_field('assignments', 'student_id'); // Obté el nom del camp ID d'alumne.
$assignmentMaterialId = schema_field('assignments', 'material_id'); // Obté el nom del camp ID de material.
$assignmentStart = schema_field('assignments', 'start_date'); // Obté el nom del camp de data d'inici.
$assignmentEnd = schema_field('assignments', 'end_date'); // Obté el nom del camp de data final.

$studentId = schema_field('students', 'id'); // Obté el nom del camp ID d'alumnes.
$studentName = schema_field('students', 'name'); // Obté el nom del camp nom d'alumnes.
$studentSurname1 = schema_field('students', 'surname1'); // Obté el nom del primer cognom d'alumnes.
$studentSurname2 = schema_field('students', 'surname2'); // Obté el nom del segon cognom d'alumnes.
$materialId = schema_field('material', 'id'); // Obté el nom del camp ID de material.
$materialInventory = schema_field('material', 'inventory_id'); // Obté el nom del camp d'ID d'inventari.
$materialDeptLabel = schema_field('material', 'department_label'); // Obté el nom del camp d'etiqueta del departament.
$materialSerial = schema_field('material', 'serial_number'); // Obté el nom del camp de numero de serie.

if ($method == 'GET') { // Si s'ha sol·licitat una consulta de dades...
    $sql = "SELECT asg.$assignmentId AS id,
                   asg.$assignmentStudentId AS idAlumne,
                   CONCAT_WS(' ', a.$studentName, a.$studentSurname1, a.$studentSurname2) AS alumne,
                   asg.$assignmentMaterialId AS idMaterial,
                   COALESCE(m.$materialInventory, m.$materialDeptLabel, m.$materialSerial, CAST(m.$materialId AS CHAR)) AS material,
                   asg.$assignmentStart AS dataInici,
                   asg.$assignmentEnd AS dataFinal
            FROM $assignments asg
            INNER JOIN $students a ON a.$studentId = asg.$assignmentStudentId
            INNER JOIN $material m ON m.$materialId = asg.$assignmentMaterialId"; // Consulta amb JOINs per obtenir dades relacionades.

    if ($user['rol'] == 'ALUMNE') { // Si l'usuari es un alumne...
        $stmt = $pdo->prepare($sql . " WHERE asg.$assignmentStudentId = ? ORDER BY asg.$assignmentId DESC"); // Filtra les seves assignacions.
        $stmt->execute([$user['idAlumne']]); // Executa la consulta amb el seu ID.
    } else { // Si l'usuari es un professor...
        $stmt = $pdo->query($sql . " ORDER BY asg.$assignmentId DESC"); // Consulta totes les assignacions.
    }

    json_ok(['data' => $stmt->fetchAll()]); // Retorna els resultats en format JSON.
}

if ($method == 'POST') { // Si s'ha sol·licitat crear una nova assignacio...
    require_fields($data, ['idAlumne', 'idMaterial']); // Verifica que hi hagi l'alumne i el material.
    $id = isset($data['id']) && $data['id'] !== '' ? (int) $data['id'] : next_id($pdo, $assignments, $assignmentId); // Determina l'ID o en genera un.

    $stmt = $pdo->prepare("INSERT INTO $assignments ($assignmentId, $assignmentMaterialId, $assignmentStudentId, $assignmentStart, $assignmentEnd)
                           VALUES (?, ?, ?, ?, ?)"); // Prepara l'ordre INSERT.
    $stmt->execute([ // Executa la insercio amb les dades rebudes.
        $id, // Valor per a l'ID.
        (int) $data['idMaterial'], // Valor per a l'ID de material.
        (int) $data['idAlumne'], // Valor per a l'ID d'alumne.
        nullable_field($data, 'dataInici') ?: date('Y-m-d'), // Valor per a la data d'inici (avui per defecte).
        nullable_field($data, 'dataFinal'), // Valor per a la data final (opcional).
    ]);

    json_ok(['id' => $id]); // Retorna l'ID del nou registre.
}

if ($method == 'PUT') { // Si s'ha sol·licitat actualitzar una assignacio...
    require_fields($data, ['id', 'idAlumne', 'idMaterial']); // Verifica l'ID i els camps obligatoris.

    $stmt = $pdo->prepare("UPDATE $assignments
                           SET $assignmentMaterialId = ?, $assignmentStudentId = ?, $assignmentStart = ?, $assignmentEnd = ?
                           WHERE $assignmentId = ?"); // Prepara l'ordre UPDATE.
    $stmt->execute([ // Executa l'actualitzacio.
        (int) $data['idMaterial'], // Nou ID de material.
        (int) $data['idAlumne'], // Nou ID d'alumne.
        nullable_field($data, 'dataInici') ?: date('Y-m-d'), // Nova data d'inici.
        nullable_field($data, 'dataFinal'), // Nova data final (opcional).
        (int) $data['id'], // ID del registre a actualitzar.
    ]);

    json_ok(); // Retorna una resposta d'exit.
}

if ($method == 'DELETE') { // Si s'ha sol·licitat eliminar una assignacio...
    require_fields($data, ['id']); // Verifica que s'hagi enviat l'ID.
    $stmt = $pdo->prepare("DELETE FROM $assignments WHERE $assignmentId = ?"); // Prepara l'ordre DELETE.
    $stmt->execute([(int) $data['id']]); // Executa l'eliminacio.
    json_ok(); // Retorna una resposta d'exit.
}

json_error(405, 'Metode no permes.'); // Retorna error 405 per mètodes no gestionats.
