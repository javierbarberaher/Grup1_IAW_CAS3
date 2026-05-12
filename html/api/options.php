<?php

/**
 * Endpoint d'opcions per als formularis del professor.
 *
 * Retorna llistes petites que ajuden a crear o editar material, alumnes,
 * incidencies i assignacions.
 */

require_once __DIR__ . '/../includes/http.php'; // Carrega funcions HTTP i JSON.
require_once __DIR__ . '/../includes/db.php'; // Carrega la connexio PDO.
require_once __DIR__ . '/../includes/schema.php'; // Carrega el mapa de taules basat en el SQL de Ignora.
require_once __DIR__ . '/../includes/session.php'; // Carrega login i control de rols.

api_start(); // Prepara CORS i resposta JSON.
only_method('GET'); // Aquest endpoint nomes consulta opcions.
require_professor(); // Les opcions de gestio nomes son per al professor.

$pdo = db(); // Obre la connexio amb MariaDB.
$types = schema_table('material_types'); // Obté el nom real de la taula de tipus.
$locations = schema_table('locations'); // Obté el nom real de la taula d'ubicacions.
$states = schema_table('states'); // Obté el nom real de la taula d'estats.
$students = schema_table('students'); // Obté el nom real de la taula d'alumnes.
$material = schema_table('material'); // Obté el nom real de la taula de material.

$typeId = schema_field('material_types', 'id'); // Camp ID del tipus.
$typeName = schema_field('material_types', 'type'); // Camp de text del tipus.
$typeModel = schema_field('material_types', 'model'); // Camp del model.
$typeOrigin = schema_field('material_types', 'origin'); // Camp d'origen.
$locationId = schema_field('locations', 'id'); // Camp ID d'ubicació.
$locationName = schema_field('locations', 'name'); // Camp de nom d'ubicació.
$stateId = schema_field('states', 'id'); // Camp ID d'estat.
$stateName = schema_field('states', 'status'); // Camp de text de l'estat.
$studentId = schema_field('students', 'id'); // Camp ID d'alumne.
$studentName = schema_field('students', 'name'); // Camp de nom d'alumne.
$studentSurname1 = schema_field('students', 'surname1'); // Camp primer cognom.
$studentSurname2 = schema_field('students', 'surname2'); // Camp segon cognom.
$materialId = schema_field('material', 'id'); // Camp ID de material.
$materialInventory = schema_field('material', 'inventory_id'); // Camp ID d'inventari.
$materialDeptLabel = schema_field('material', 'department_label'); // Camp d'etiqueta de departament.
$materialSerial = schema_field('material', 'serial_number'); // Camp de número de sèrie.

json_ok([ // Retorna totes les opcions necessaries per als formularis.
    'data' => [ // Agrupa les llistes dins la clau data.
        'tipusMaterial' => $pdo->query("SELECT $typeId AS id, $typeName AS tipus, $typeModel AS model, $typeOrigin AS origen FROM $types ORDER BY $typeName, $typeModel")->fetchAll(), // Obté els tipus de material.
        'ubicacions' => $pdo->query("SELECT $locationId AS id, $locationName AS nom FROM $locations ORDER BY $locationName")->fetchAll(), // Obté les ubicacions disponibles.
        'estats' => $pdo->query("SELECT $stateId AS id, $stateName AS estat FROM $states ORDER BY $stateId")->fetchAll(), // Obté els estats d'incidència.
        'alumnes' => $pdo->query("SELECT $studentId AS id, CONCAT_WS(' ', $studentName, $studentSurname1, $studentSurname2) AS nomComplet FROM $students ORDER BY $studentSurname1, $studentName")->fetchAll(), // Obté la llista d'alumnes.
        'material' => $pdo->query("SELECT $materialId AS id, $materialInventory AS idInventari, $materialDeptLabel AS etiquetaDepInf, $materialSerial AS numSerie, COALESCE($materialInventory, $materialDeptLabel, $materialSerial, CAST($materialId AS CHAR)) AS nomMaterial FROM $material ORDER BY $materialId")->fetchAll(), // Obté la llista de material.
    ],
]);
