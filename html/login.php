<?php // Inicia el bloc de codi PHP

/**
 * Formulari d'inici de sessio web.
 *
 * @package CAS3
 */

require_once __DIR__ . '/includes/layout.php'; // Inclou el sistema de plantilles i layout

$currentUser = user(); // Obté l'usuari actual de la sessio

if ($currentUser) { // Comprova si ja hi ha una sessio activa
    redirect_after_login($currentUser); // Redirigeix a la pagina principal si ja esta autenticat
} // Finalitza el bloc condicional

$error = ''; // Inicialitza la variable d'error
$correu = trim($_POST['correu'] ?? ''); // Obté i neteja el correu del formulari

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Comprova si la peticio es de tipus POST
    verify_form_csrf(); // Verifica el token CSRF per seguretat

    $password = $_POST['password'] ?? ''; // Obté la contrasenya del formulari

    if ($correu === '' || $password === '') { // Comprova si falten camps obligatoris
        $error = 'Cal informar el correu i la contrasenya.'; // Defineix missatge d'error per camps buits
    } elseif (!filter_var($correu, FILTER_VALIDATE_EMAIL)) { // Valida el format del correu
        $error = 'El format del correu electronic no es valid.'; // Defineix missatge d'error per format invalid
    } else { // Si les validacions basiques son correctes
        $account = authenticate_credentials($correu, $password); // Intenta autenticar les credencials

        if ($account) { // Si l'autenticacio es correcte
            login_user($account); // Registra l'usuari a la sessio
            redirect_after_login(user()); // Redirigeix segons el rol de l'usuari
        } // Finalitza el bloc d'autenticacio correcte

        $error = 'Credencials incorrectes.'; // Defineix missatge d'error per credencials fallides
    } // Finalitza el bloc de validacio i autenticacio
} // Finalitza el bloc de processament POST

render_page('login', [ // Renderitza la vista de login
    'error' => $error, // Passa la variable d'error a la vista
    'correu' => $correu, // Passa el correu per mantenir-lo al formulari
], 'Acces'); // Defineix el titol de la pagina
