<?php

/**
 * Endpoint CRUD d'incidencies.
 *
 * El professor pot crear, editar i eliminar incidencies.
 * L'alumne nomes consulta incidencies del material que te assignat o vinculat.
 */

require_once __DIR__ . '/../includes/http.php'; // Carrega funcions HTTP i JSON.
require_once __DIR__ . '/../includes/db.php'; // Carrega la connexio PDO i next_id().
require_once __DIR__ . '/../includes/schema.php'; // Carrega el mapa de taules basat en el SQL de Ignora.
require_once __DIR__ . '/../includes/session.php'; // Carrega login, rols i CSRF.

api_start(); // Prepara la resposta JSON.
$method = $_SERVER['REQUEST_METHOD']; // Guarda el mètode HTTP rebut (GET, POST, etc.).
$user = require_login(); // Obliga a tenir sessió iniciada i guarda l'usuari.

if ($method != 'GET') { // Si la petició vol modificar dades (POST, PUT, DELETE)...
    require_professor(); // Comprova que el rol sigui de professor.
    check_csrf(); // Valida el token CSRF per seguretat.
    $data = input_json(); // Llegeix i descodifica les dades JSON de la petició.
}

$pdo = db(); // Estableix la connexió amb la base de dades.

$incidents = schema_table('incidents'); // Obté el nom real de la taula d'incidències.
$material = schema_table('material'); // Obté el nom real de la taula de material.
$states = schema_table('states'); // Obté el nom real de la taula d'estats.
$students = schema_table('students'); // Obté el nom real de la taula d'alumnes.
$assignments = schema_table('assignments'); // Obté el nom real de la taula d'assignacions.

$incidentId = schema_field('incidents', 'id'); // Camp ID d'incidències.
$incidentInfo = schema_field('incidents', 'info'); // Camp d'informació de la incidència.
$incidentOpened = schema_field('incidents', 'opened_at'); // Camp de data d'obertura.
$incidentClosed = schema_field('incidents', 'closed_at'); // Camp de data de tancament.
$incidentStudentId = schema_field('incidents', 'student_id'); // Camp ID d'alumne en incidències.
$incidentDeviceId = schema_field('incidents', 'device_id'); // Camp ID de dispositiu en incidències.
$incidentStateId = schema_field('incidents', 'state_id'); // Camp ID d'estat en incidències.

$materialId = schema_field('material', 'id'); // Camp ID de material.
$materialInventory = schema_field('material', 'inventory_id'); // Camp ID d'inventari.
$materialDeptLabel = schema_field('material', 'department_label'); // Camp d'etiqueta de departament.
$materialSerial = schema_field('material', 'serial_number'); // Camp de número de sèrie.
$stateId = schema_field('states', 'id'); // Camp ID d'estats.
$stateName = schema_field('states', 'status'); // Camp de text de l'estat.
$studentId = schema_field('students', 'id'); // Camp ID d'alumnes.
$studentName = schema_field('students', 'name'); // Camp nom d'alumnes.
$studentSurname1 = schema_field('students', 'surname1'); // Camp primer cognom d'alumnes.
$studentSurname2 = schema_field('students', 'surname2'); // Camp segon cognom d'alumnes.
$assignmentStudentId = schema_field('assignments', 'student_id'); // Camp ID alumne en assignacions.
$assignmentMaterialId = schema_field('assignments', 'material_id'); // Camp ID material en assignacions.
$assignmentEnd = schema_field('assignments', 'end_date'); // Camp data final en assignacions.

if ($method == 'GET') { // Si s'ha sol·licitat una consulta...
    $select = "SELECT i.$incidentId AS id,
                      i.$incidentInfo AS informacio,
                      i.$incidentOpened AS dataOberta,
                      i.$incidentClosed AS dataTancada,
                      i.$incidentStudentId AS idAlumne,
                      CONCAT_WS(' ', a.$studentName, a.$studentSurname1, a.$studentSurname2) AS alumne,
                      i.$incidentDeviceId AS idDispositiu,
                      COALESCE(m.$materialInventory, m.$materialDeptLabel, m.$materialSerial, CAST(m.$materialId AS CHAR)) AS material,
                      i.$incidentStateId AS idEstat,
                      e.$stateName AS estat
               FROM $incidents i
               LEFT JOIN $students a ON a.$studentId = i.$incidentStudentId
               LEFT JOIN $material m ON m.$materialId = i.$incidentDeviceId
               LEFT JOIN $states e ON e.$stateId = i.$incidentStateId"; // Defineix la consulta SELECT amb JOINs.

    if ($user['rol'] == 'ALUMNE') { // Si l'usuari és un alumne...
        $sql = $select . " WHERE i.$incidentStudentId = ?
                  OR EXISTS (
                      SELECT 1
                      FROM $assignments asg
                      WHERE asg.$assignmentStudentId = ?
                        AND asg.$assignmentMaterialId = i.$incidentDeviceId
                        AND asg.$assignmentEnd IS NULL
                  )
                  ORDER BY i.$incidentId DESC"; // Filtra per les seves incidències o material assignat.
        $stmt = $pdo->prepare($sql); // Prepara la consulta.
        $stmt->execute([$user['idAlumne'], $user['idAlumne']]); // Executa amb l'ID de l'alumne.
    } else { // Si l'usuari és un professor...
        $stmt = $pdo->query($select . " ORDER BY i.$incidentId DESC"); // Consulta totes les incidències.
    }

    json_ok(['data' => $stmt->fetchAll()]); // Retorna els resultats en JSON.
}

if ($method == 'POST') { // Si s'ha sol·licitat crear una incidència...
    require_fields($data, ['informacio', 'idEstat']); // Verifica els camps obligatoris.
    $deviceId = $data['idDispositiu'] ?? ($data['idMaterial'] ?? null); // Obté l'ID del dispositiu per qualsevol dels dos noms.

    if ($deviceId === null || trim((string) $deviceId) === '') { // Valida que hi hagi un dispositiu vinculat.
        json_error(400, 'Falta el camp obligatori: idDispositiu.'); // Retorna error si falta el dispositiu.
    }

    $id = isset($data['id']) && $data['id'] !== '' ? (int) $data['id'] : next_id($pdo, $incidents, $incidentId); // Determina l'ID.

    $stmt = $pdo->prepare("INSERT INTO $incidents
        ($incidentId, $incidentInfo, $incidentOpened, $incidentClosed, $incidentStudentId, $incidentDeviceId, $incidentStateId)
        VALUES (?, ?, ?, ?, ?, ?, ?)"); // Prepara l'ordre INSERT.
    $stmt->execute([ // Executa la inserció amb les dades rebudes.
        $id, // Valor ID.
        $data['informacio'], // Valor informació.
        nullable_field($data, 'dataOberta') ?: date('Y-m-d'), // Valor data d'obertura.
        nullable_field($data, 'dataTancada'), // Valor data de tancament.
        nullable_field($data, 'idAlumne'), // Valor ID alumne (opcional).
        (int) $deviceId, // Valor ID dispositiu.
        (int) $data['idEstat'], // Valor ID estat.
    ]);

    json_ok(['id' => $id]); // Retorna l'ID del nou registre.
}

if ($method == 'PUT') { // Si s'ha sol·licitat actualitzar una incidència...
    require_fields($data, ['id', 'informacio', 'idEstat']); // Verifica ID i camps necessaris.
    $deviceId = $data['idDispositiu'] ?? ($data['idMaterial'] ?? null); // Obté l'ID del dispositiu.

    if ($deviceId === null || trim((string) $deviceId) === '') { // Valida que hi hagi un dispositiu.
        json_error(400, 'Falta el camp obligatori: idDispositiu.'); // Retorna error si no hi és.
    }

    $stmt = $pdo->prepare("UPDATE $incidents
        SET $incidentInfo = ?, $incidentOpened = ?, $incidentClosed = ?, $incidentStudentId = ?, $incidentDeviceId = ?, $incidentStateId = ?
        WHERE $incidentId = ?"); // Prepara l'ordre UPDATE.
    $stmt->execute([ // Executa l'actualització.
        $data['informacio'], // Nou valor d'informació.
        nullable_field($data, 'dataOberta') ?: date('Y-m-d'), // Nova data d'obertura.
        nullable_field($data, 'dataTancada'), // Nova data de tancament.
        nullable_field($data, 'idAlumne'), // Nou ID d'alumne.
        (int) $deviceId, // Nou ID de dispositiu.
        (int) $data['idEstat'], // Nou ID d'estat.
        (int) $data['id'], // ID del registre a modificar.
    ]);

    json_ok(); // Retorna resposta d'èxit.
}

if ($method == 'DELETE') { // Si s'ha sol·licitat eliminar una incidència...
    require_fields($data, ['id']); // Verifica l'ID.
    $stmt = $pdo->prepare("DELETE FROM $incidents WHERE $incidentId = ?"); // Prepara l'ordre DELETE.
    $stmt->execute([(int) $data['id']]); // Executa l'eliminació.
    json_ok(); // Retorna resposta d'èxit.
}

json_error(405, 'Metode no permes.'); // Retorna error 405 per mètodes no previstos.
