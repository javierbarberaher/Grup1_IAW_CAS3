<?php // Inicia el bloc de codi PHP

/**
 * Panell de l'alumnat.
 *
 * @package CAS3
 */

require_once __DIR__ . '/../includes/layout.php'; // Inclou el sistema de plantilles
require_once __DIR__ . '/../includes/db.php'; // Inclou la connexió i utilitats de base de dades

$currentUser = require_web_student(); // Requereix que la sessió correspongui a un alumne
$idAlumne = (int) ($currentUser['idAlumne'] ?? 0); // Obté l'identificador d'alumne vinculat a l'usuari

if ($idAlumne <= 0) { // Si l'usuari no té alumne associat
    render_error_page(403, 'Acces denegat', 'Aquest usuari no te cap alumne vinculat.'); // Mostra un error controlat
    exit; // Atura l'execució
}

$alumne = db_fetch_one( // Obté les dades bàsiques de l'alumne autenticat
    'SELECT id, nom, cognom1, cognom2, grupClasse
     FROM Alumnes
     WHERE id = ?',
    [$idAlumne]
);

if (!$alumne) { // Si no existeix l'alumne vinculat
    render_error_page(404, 'Alumne no trobat', 'No s\'han trobat les dades de l\'alumne autenticat.'); // Mostra error funcional
    exit; // Atura l'execució
}

$assignments = db_fetch_all( // Obté les assignacions de l'alumne i el seu estat actual
    'SELECT asg.id AS idAssignacio, asg.dataInici, asg.dataFinal,
            m.id AS idMaterial, m.idInventari, m.etiquetaDepInf, m.numSerie,
            tm.tipus, tm.model, u.nom AS ubicacio,
            inc.id AS idIncidencia, e.estat AS estatIncidencia
     FROM Assignacions asg
     INNER JOIN Material m ON m.id = asg.idMaterial
     INNER JOIN TipusMaterial tm ON tm.id = m.idTipus
     INNER JOIN Ubicacions u ON u.id = m.idUbicacio
     LEFT JOIN Incidencies inc ON inc.idDispositiu = m.id AND inc.dataTancada IS NULL
     LEFT JOIN Estats e ON e.id = inc.idEstat
     WHERE asg.idAlumne = ?
     ORDER BY asg.dataInici DESC, asg.id DESC',
    [$idAlumne]
);

$incidents = db_fetch_all( // Obté les incidències obertes i històriques de l'alumne
    'SELECT i.id, i.informacio, i.dataOberta, i.dataTancada, e.estat,
            tm.tipus, tm.model,
            COALESCE(m.idInventari, m.etiquetaDepInf, m.numSerie, CAST(m.id AS CHAR)) AS material
     FROM Incidencies i
     INNER JOIN Material m ON m.id = i.idDispositiu
     INNER JOIN TipusMaterial tm ON tm.id = m.idTipus
     LEFT JOIN Estats e ON e.id = i.idEstat
     WHERE i.idAlumne = ?
     ORDER BY i.dataOberta DESC, i.id DESC',
    [$idAlumne]
);

render_page('alumnat/index', [ // Renderitza la pantalla d'alumnat amb les dades necessàries
    'alumne' => $alumne,
    'assignments' => $assignments,
    'incidents' => $incidents,
], 'El meu material');
