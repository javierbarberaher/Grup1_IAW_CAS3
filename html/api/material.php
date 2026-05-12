<?php

/**
 * Endpoint CRUD de material.
 *
 * El professor pot fer les quatre operacions CRUD.
 * L'alumne nomes consulta el material que te assignat i actiu.
 */

require_once __DIR__ . '/../includes/http.php'; // Carrega funcions HTTP i JSON.
require_once __DIR__ . '/../includes/db.php'; // Carrega la connexio a MariaDB i next_id().
require_once __DIR__ . '/../includes/schema.php'; // Carrega el mapa de taules basat en el SQL de Ignora.
require_once __DIR__ . '/../includes/session.php'; // Carrega login, rols i CSRF.

api_start(); // Prepara la resposta de la API.
$method = $_SERVER['REQUEST_METHOD']; // Guarda el mètode HTTP rebut (GET, POST, etc.).
$user = require_login(); // Obliga a tenir sessió activa i guarda l'usuari.

if ($method != 'GET') { // Si la petició vol modificar dades (POST, PUT, DELETE)...
    require_professor(); // Comprova que el rol sigui de professor.
    check_csrf(); // Valida el token CSRF per seguretat.
    $data = input_json(); // Llegeix i descodifica les dades JSON.
}

$pdo = db(); // Estableix la connexió amb la base de dades.

$material = schema_table('material'); // Obté el nom de la taula de material.
$types = schema_table('material_types'); // Obté el nom de la taula de tipus.
$locations = schema_table('locations'); // Obté el nom de la taula d'ubicacions.
$assignments = schema_table('assignments'); // Obté el nom de la taula d'assignacions.

$materialId = schema_field('material', 'id'); // Camp ID de material.
$materialTypeId = schema_field('material', 'type_id'); // Camp ID de tipus en material.
$materialInventory = schema_field('material', 'inventory_id'); // Camp ID d'inventari.
$materialDeptLabel = schema_field('material', 'department_label'); // Camp d'etiqueta de departament.
$materialSerial = schema_field('material', 'serial_number'); // Camp de número de sèrie.
$materialMacEthernet = schema_field('material', 'mac_ethernet'); // Camp MAC Ethernet.
$materialMacWifi = schema_field('material', 'mac_wifi'); // Camp MAC WiFi.
$materialSace = schema_field('material', 'sace'); // Camp SACE.
$materialDate = schema_field('material', 'acquisition_date'); // Camp de data d'adquisició.
$materialLocationId = schema_field('material', 'location_id'); // Camp ID d'ubicació en material.

$typeId = schema_field('material_types', 'id'); // Camp ID de tipus de material.
$typeName = schema_field('material_types', 'type'); // Camp tipus.
$typeModel = schema_field('material_types', 'model'); // Camp model.
$typeOrigin = schema_field('material_types', 'origin'); // Camp origen.
$locationId = schema_field('locations', 'id'); // Camp ID d'ubicacions.
$locationName = schema_field('locations', 'name'); // Camp nom d'ubicacions.
$assignmentStudentId = schema_field('assignments', 'student_id'); // Camp ID alumne en assignacions.
$assignmentMaterialId = schema_field('assignments', 'material_id'); // Camp ID material en assignacions.
$assignmentEnd = schema_field('assignments', 'end_date'); // Camp data final en assignacions.

if ($method == 'GET') { // Si s'ha sol·licitat una consulta...
    $select = "SELECT m.$materialId AS id,
                      m.$materialTypeId AS idTipus,
                      tm.$typeName AS tipus,
                      tm.$typeModel AS model,
                      tm.$typeOrigin AS origen,
                      m.$materialInventory AS idInventari,
                      m.$materialDeptLabel AS etiquetaDepInf,
                      m.$materialSerial AS numSerie,
                      m.$materialMacEthernet AS macEthernet,
                      m.$materialMacWifi AS macWifi,
                      m.$materialSace AS SACE,
                      m.$materialDate AS dataAdquisicio,
                      m.$materialLocationId AS idUbicacio,
                      u.$locationName AS ubicacio,
                      COALESCE(m.$materialInventory, m.$materialDeptLabel, m.$materialSerial, CAST(m.$materialId AS CHAR)) AS nomMaterial
               FROM $material m
               INNER JOIN $types tm ON tm.$typeId = m.$materialTypeId
               INNER JOIN $locations u ON u.$locationId = m.$materialLocationId"; // Defineix el SELECT amb JOINs.

    if ($user['rol'] == 'ALUMNE') { // Si l'usuari és un alumne...
        $sql = "SELECT m.$materialId AS id,
                       m.$materialTypeId AS idTipus,
                       tm.$typeName AS tipus,
                       tm.$typeModel AS model,
                       tm.$typeOrigin AS origen,
                       m.$materialInventory AS idInventari,
                       m.$materialDeptLabel AS etiquetaDepInf,
                       m.$materialSerial AS numSerie,
                       m.$materialMacEthernet AS macEthernet,
                       m.$materialMacWifi AS macWifi,
                       m.$materialSace AS SACE,
                       m.$materialDate AS dataAdquisicio,
                       m.$materialLocationId AS idUbicacio,
                       u.$locationName AS ubicacio,
                       COALESCE(m.$materialInventory, m.$materialDeptLabel, m.$materialSerial, CAST(m.$materialId AS CHAR)) AS nomMaterial
                FROM $assignments a
                INNER JOIN $material m ON m.$materialId = a.$assignmentMaterialId
                INNER JOIN $types tm ON tm.$typeId = m.$materialTypeId
                INNER JOIN $locations u ON u.$locationId = m.$materialLocationId
                WHERE a.$assignmentStudentId = ? AND a.$assignmentEnd IS NULL
                ORDER BY m.$materialId"; // Consulta només el material assignat i actiu.
        $stmt = $pdo->prepare($sql); // Prepara la consulta.
        $stmt->execute([$user['idAlumne']]); // Executa amb l'ID de l'alumne.
    } else { // Si l'usuari és un professor...
        $stmt = $pdo->query($select . " ORDER BY m.$materialId"); // Consulta tot el material.
    }

    json_ok(['data' => $stmt->fetchAll()]); // Retorna els resultats en JSON.
}

if ($method == 'POST') { // Si s'ha sol·licitat crear material...
    require_fields($data, ['idTipus', 'idUbicacio']); // Verifica camps obligatoris.
    $id = isset($data['id']) && $data['id'] !== '' ? (int) $data['id'] : next_id($pdo, $material, $materialId); // Determina l'ID.

    $stmt = $pdo->prepare("INSERT INTO $material
        ($materialId, $materialTypeId, $materialInventory, $materialDeptLabel, $materialSerial, $materialMacEthernet, $materialMacWifi, $materialSace, $materialDate, $materialLocationId)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"); // Prepara l'ordre INSERT.
    $stmt->execute([ // Executa la inserció amb les dades rebudes.
        $id, // Valor ID.
        (int) $data['idTipus'], // Valor ID de tipus.
        nullable_field($data, 'idInventari'), // Valor ID d'inventari (opcional).
        nullable_field($data, 'etiquetaDepInf'), // Valor etiqueta departament (opcional).
        nullable_field($data, 'numSerie'), // Valor número de sèrie (opcional).
        nullable_field($data, 'macEthernet'), // Valor MAC Ethernet (opcional).
        nullable_field($data, 'macWifi'), // Valor MAC WiFi (opcional).
        nullable_field($data, 'SACE'), // Valor SACE (opcional).
        nullable_field($data, 'dataAdquisicio'), // Valor data d'adquisició (opcional).
        (int) $data['idUbicacio'], // Valor ID d'ubicació.
    ]);

    json_ok(['id' => $id]); // Retorna l'ID del nou registre.
}

if ($method == 'PUT') { // Si s'ha sol·licitat actualitzar material...
    require_fields($data, ['id', 'idTipus', 'idUbicacio']); // Verifica ID i camps obligatoris.

    $stmt = $pdo->prepare("UPDATE $material
        SET $materialTypeId = ?, $materialInventory = ?, $materialDeptLabel = ?, $materialSerial = ?,
            $materialMacEthernet = ?, $materialMacWifi = ?, $materialSace = ?, $materialDate = ?, $materialLocationId = ?
        WHERE $materialId = ?"); // Prepara l'ordre UPDATE.
    $stmt->execute([ // Executa l'actualització.
        (int) $data['idTipus'], // Nou ID de tipus.
        nullable_field($data, 'idInventari'), // Nou ID d'inventari.
        nullable_field($data, 'etiquetaDepInf'), // Nova etiqueta.
        nullable_field($data, 'numSerie'), // Nou número de sèrie.
        nullable_field($data, 'macEthernet'), // Nova MAC Ethernet.
        nullable_field($data, 'macWifi'), // Nova MAC WiFi.
        nullable_field($data, 'SACE'), // Nou SACE.
        nullable_field($data, 'dataAdquisicio'), // Nova data d'adquisició.
        (int) $data['idUbicacio'], // Nou ID d'ubicació.
        (int) $data['id'], // ID del registre a modificar.
    ]);

    json_ok(); // Retorna resposta d'èxit.
}

if ($method == 'DELETE') { // Si s'ha sol·licitat eliminar material...
    require_fields($data, ['id']); // Verifica l'ID.
    $stmt = $pdo->prepare("DELETE FROM $material WHERE $materialId = ?"); // Prepara l'ordre DELETE.
    $stmt->execute([(int) $data['id']]); // Executa l'eliminació.
    json_ok(); // Retorna resposta d'èxit.
}

json_error(405, 'Metode no permes.'); // Retorna error 405 per mètodes no gestionats.
