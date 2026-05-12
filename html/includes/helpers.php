<?php

/**
 * Funcions petites reutilitzables per a vistes i controladors.
 *
 * @package CAS3
 */

require_once __DIR__ . '/config.php'; // Carrega configuració general
require_once __DIR__ . '/session.php'; // Carrega gestió de sessions

/**
 * Escapa una dada per mostrar-la en HTML.
 *
 * @param mixed $value Valor a escapar.
 * @return string Valor segur per HTML.
 */
function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); // Escapa caràcters especials d'HTML
}

/**
 * Construeix una URL interna respectant BASE_URL.
 *
 * @param string $path Ruta relativa dins de html/.
 * @return string URL relativa preparada.
 */
function url($path = '')
{
    return BASE_URL . ltrim($path, '/'); // Construeix la URL completa des de l'arrel de l'aplicació
}

/**
 * Construeix una URL d'asset.
 *
 * @param string $path Ruta relativa dins de assets/.
 * @return string URL de l'asset.
 */
function asset_url($path)
{
    return url('assets/' . ltrim($path, '/')); // Retorna la URL per a fitxers estàtics (CSS, JS, etc.)
}

/**
 * Redirigeix a una ruta interna.
 *
 * @param string $path Ruta relativa.
 * @return void
 */
function redirect_to($path)
{
    header('Location: ' . url($path)); // Envia la capçalera HTTP de redirecció
    exit; // Atura l'execució immediatament
}

/**
 * Desa un missatge flash a la sessio.
 *
 * @param string $message Text del missatge.
 * @param string $type Tipus visual: success, error, warning o info.
 * @return void
 */
function flash($message, $type = 'success')
{
    start_session(); // Assegura que la sessió està oberta
    $_SESSION['flash'][] = [ // Afegeix el missatge a la llista de flash
        'message' => $message, // Text que veurà l'usuari
        'type' => $type, // Classe visual del missatge
    ];
}

/**
 * Retorna i elimina els missatges flash pendents.
 *
 * @return array Llista de missatges flash.
 */
function flash_messages()
{
    start_session(); // Assegura que la sessió està oberta
    $messages = $_SESSION['flash'] ?? []; // Obté els missatges o un array buit
    unset($_SESSION['flash']); // Elimina els missatges perquè només es mostrin un cop

    return $messages; // Retorna la llista de missatges
}

/**
 * Retorna el nom complet d'un alumne.
 *
 * @param array $student Fila de la taula Alumnes.
 * @return string Nom complet sense dobles espais.
 */
function student_full_name($student)
{
    return trim(implode(' ', array_filter([ // Concatena les parts del nom amb un espai
        $student['nom'] ?? '', // Nom de l'alumne
        $student['cognom1'] ?? '', // Primer cognom
        $student['cognom2'] ?? '', // Segon cognom
    ], static function ($part) { // Filtra elements buits
        return $part !== null && $part !== ''; // Només manté parts amb contingut real
    })));
}

/**
 * Retorna una etiqueta curta per identificar material.
 *
 * @param array $material Fila de material.
 * @return string Identificador llegible.
 */
function material_label($material)
{
    foreach (['idInventari', 'etiquetaDepInf', 'numSerie', 'nomMaterial'] as $field) { // Prova diversos camps per ordre de prioritat
        if (!empty($material[$field])) { // Si el camp té dades...
            return (string) $material[$field]; // ...el retorna com a etiqueta
        }
    }

    return '#' . (string) ($material['id'] ?? $material['idMaterial'] ?? ''); // Fallback a l'ID si no hi ha cap etiqueta
}

/**
 * Mostra una data o un guio si esta buida.
 *
 * @param string|null $date Data en format SQL.
 * @return string Data preparada per HTML.
 */
function display_date($date)
{
    return $date ? h($date) : '&mdash;'; // Retorna la data escapada o una ratlla si és buida
}

/**
 * Retorna text per a valors opcionals.
 *
 * @param mixed $value Valor nullable.
 * @return string Valor escapado o guio.
 */
function display_value($value)
{
    if ($value === null || $value === '') { // Si el valor és nul o buit...
        return '&mdash;'; // ...retorna una ratlla horitzontal
    }

    return h($value); // Retorna el valor escapat per seguretat
}

/**
 * Retalla un text llarg per a taules.
 *
 * @param string|null $text Text original.
 * @param int $length Longitud maxima.
 * @return string Text retallat.
 */
function excerpt($text, $length = 120)
{
    $text = trim((string) $text); // Converteix a string i retalla espais

    if (function_exists('mb_strlen') && mb_strlen($text) > $length) { // Si té mbstring i el text supera el límit...
        return mb_substr($text, 0, $length - 1) . '...'; // ...el talla i afegeix punts suspensius
    }

    if (!function_exists('mb_strlen') && strlen($text) > $length) { // Si no té mbstring i supera el límit...
        return substr($text, 0, $length - 1) . '...'; // ...el talla de forma bàsica
    }

    return $text; // Retorna el text sencer si és curt
}

/**
 * Retorna la classe activa per a un enllaç de navegacio.
 *
 * @param string $path Ruta esperada.
 * @return string Classe CSS.
 */
function nav_active($path)
{
    $current = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/'); // Obté la ruta actual de la petició
    $expected = trim(url($path), '/'); // Obté la ruta esperada normalitzada

    return $current === $expected ? 'is-active' : ''; // Retorna 'is-active' si les rutes coincideixen
}

/**
 * Retorna true si una assignacio esta activa.
 *
 * @param array $assignment Fila d'Assignacions.
 * @return bool Estat actiu.
 */
function assignment_is_active($assignment)
{
    return empty($assignment['dataFinal']) || $assignment['dataFinal'] >= date('Y-m-d'); // Comprova si no hi ha data final o si encara no ha passat
}

/**
 * Retorna una classe CSS per a l'estat d'una incidencia.
 *
 * @param string|null $state Estat textual.
 * @return string Classe CSS.
 */
function status_class($state)
{
    $state = strtolower((string) $state); // Passa l'estat a minúscules per comparar

    if (strpos($state, 'tanc') !== false || strpos($state, 'resolt') !== false) { // Si l'estat indica finalització...
        return 'status-ok'; // ...retorna classe d'èxit
    }

    if (strpos($state, 'esper') !== false) { // Si l'estat indica espera...
        return 'status-warning'; // ...retorna classe d'avís
    }

    return 'status-danger'; // Per a estats crítics o oberts
}
