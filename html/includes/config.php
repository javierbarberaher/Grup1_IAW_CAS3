<?php

/**
 * Configuracio general de l'aplicacio CAS3.
 *
 * Aquest fitxer centralitza constants i lectura de variables d'entorn. Les
 * credencials de base de dades no es guarden en codi PHP: sempre arriben per
 * Docker Compose o per l'entorn del servidor.
 *
 * @package CAS3
 */

/** Nom public de l'aplicacio. */
define('APP_NAME', env_value('APP_NAME', 'Gestio de material - Institut Montsia')); // Defineix el nom de l'aplicació

/** Prefix d'URL on esta publicada l'aplicacio. */
define('BASE_URL', normalize_base_url(env_value('APP_BASE_URL', '/'))); // Defineix la URL base normalitzada

/** Rol intern per al professorat. */
define('ROLE_PROFESSOR', 'PROFESSOR'); // Constant per al rol de professor

/** Rol intern per a l'alumnat. */
define('ROLE_STUDENT', 'ALUMNE'); // Constant per al rol d'alumne

/**
 * Retorna una variable d'entorn amb valor per defecte.
 *
 * @param string $key Nom de la variable.
 * @param string|null $default Valor per defecte si no existeix o esta buida.
 * @return string|null Valor trobat o valor per defecte.
 */
function env_value($key, $default = null)
{
    $value = getenv($key); // Intenta llegir la variable d'entorn

    if ($value === false || $value === '') { // Si no existeix o està buida...
        return $default; // ...retorna el valor per defecte
    }

    return $value; // Retorna el valor trobat
}

/**
 * Normalitza el prefix base de l'aplicacio.
 *
 * @param string|null $base Prefix rebut per entorn.
 * @return string Prefix amb barra inicial i final.
 */
function normalize_base_url($base)
{
    $base = trim((string) $base); // Elimina espais en blanc als extrems

    if ($base === '') { // Si la base està buida...
        return '/'; // ...retorna l'arrel
    }

    $trimmed = trim($base, '/'); // Elimina les barres dels extrems

    if ($trimmed === '') { // Si després de retallar queda buit...
        return '/'; // ...retorna l'arrel
    }

    return '/' . $trimmed . '/'; // Retorna la base amb barres al principi i al final
}

/**
 * Converteix valors textuals d'entorn en boolea.
 *
 * @param string $key Nom de la variable d'entorn.
 * @param bool $default Valor usat si no esta definida.
 * @return bool Valor boolea resultant.
 */
function env_bool($key, $default = false)
{
    $value = env_value($key); // Obté el valor de la variable d'entorn

    if ($value === null) { // Si no està definida...
        return $default; // ...retorna el valor per defecte
    }

    return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true); // Comprova si el valor representa "cert"
}
