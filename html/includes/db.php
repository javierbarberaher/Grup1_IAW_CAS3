<?php

/**
 * Helpers de connexio PDO per a MariaDB/MySQL.
 *
 * @package CAS3
 */

require_once __DIR__ . '/config.php'; // Carrega la configuració per obtenir les credencials

/**
 * Retorna una connexio PDO compartida amb la base de dades externa.
 *
 * La configuracio surt de DB_HOST, DB_NAME, DB_USER i DB_PASSWORD. Si falta
 * alguna variable, es llença una excepcio controlada pels endpoints o pagines.
 *
 * @return PDO Connexio activa amb la base de dades.
 * @throws RuntimeException Si falta configuracio obligatoria.
 */
function db()
{
    static $pdo = null; // Variable estàtica per reutilitzar la connexió

    if ($pdo instanceof PDO) { // Si la connexió ja existeix...
        return $pdo; // ...la retornem directament
    }

    $host = env_value('DB_HOST'); // Host del servidor de BD
    $dbname = env_value('DB_NAME'); // Nom de la base de dades
    $user = env_value('DB_USER'); // Usuari de la BD
    $pass = env_value('DB_PASSWORD'); // Contrasenya de la BD

    if (!$host || !$dbname || !$user || $pass === null) { // Valida que tinguem tota la configuració
        throw new RuntimeException('Falta configurar DB_HOST, DB_NAME, DB_USER o DB_PASSWORD.'); // Error si falten dades
    }

    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4"; // Construeix el DSN per a PDO
    $pdo = new PDO($dsn, $user, $pass, [ // Crea la nova instància de PDO
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Activa el llançament d'excepcions en cas d'error
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Retorna les files com a arrays associatius per defecte
        PDO::ATTR_EMULATE_PREPARES => false, // Desactiva l'emulació de consultes preparades per seguretat
    ]);

    return $pdo; // Retorna la connexió establerta
}

/**
 * Executa una consulta SELECT preparada i retorna totes les files.
 *
 * @param string $sql Consulta SQL amb placeholders.
 * @param array $params Parametres de la consulta.
 * @return array Files retornades.
 */
function db_fetch_all($sql, $params = [])
{
    $stmt = db()->prepare($sql); // Prepara la consulta SQL
    $stmt->execute($params); // Executa la consulta amb els paràmetres

    return $stmt->fetchAll(); // Retorna totes les files obtingudes
}

/**
 * Executa una consulta SELECT preparada i retorna una sola fila.
 *
 * @param string $sql Consulta SQL amb placeholders.
 * @param array $params Parametres de la consulta.
 * @return array|null Fila trobada o null.
 */
function db_fetch_one($sql, $params = [])
{
    $stmt = db()->prepare($sql); // Prepara la consulta SQL
    $stmt->execute($params); // Executa la consulta amb els paràmetres
    $row = $stmt->fetch(); // Obté la primera fila

    return $row ?: null; // Retorna la fila o null si no n'hi ha cap
}

/**
 * Executa una consulta preparada de modificacio.
 *
 * @param string $sql Consulta INSERT, UPDATE o DELETE.
 * @param array $params Parametres de la consulta.
 * @return int Nombre de files afectades.
 */
function db_execute($sql, $params = [])
{
    $stmt = db()->prepare($sql); // Prepara la consulta de modificació
    $stmt->execute($params); // Executa la operació amb els paràmetres

    return $stmt->rowCount(); // Retorna el nombre de files que s'han modificat
}

/**
 * Calcula el seguent id quan un endpoint necessita inserir l'id manualment.
 *
 * Tot i que l'esquema actual usa AUTO_INCREMENT, aquesta funcio es conserva
 * per compatibilitat amb els endpoints API existents.
 *
 * @param PDO $pdo Connexio activa amb MariaDB.
 * @param string $table Nom de taula ja protegit amb schema_table().
 * @param string $idField Nom del camp id ja protegit amb schema_field().
 * @return int Seguent id disponible segons la taula.
 */
function next_id($pdo, $table, $idField)
{
    $stmt = $pdo->query("SELECT COALESCE(MAX($idField), 0) + 1 AS next_id FROM $table"); // Calcula el màxim actual + 1

    return (int) $stmt->fetchColumn(); // Retorna el resultat convertit a enter
}
