<?php
// Iniciar la sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- CONFIGURACIÓN PARA DESARROLLO ---
// Muestra todos los errores en pantalla para facilitar la depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- CONFIGURACIÓN DE LA APLICACIÓN ---

// Rutas del proyecto (¡MUY IMPORTANTE!)
// ROOT_PATH es la ruta física en el servidor (ej: C:/xampp/htdocs/gestion_citas_medicas)
define('ROOT_PATH', dirname(__DIR__)); 
// BASE_URL es la URL pública (ej: http://localhost/gestion_citas_medicas/)
define('BASE_URL', 'http://localhost/gestion_citas_medicas/');

// Configuración de la base de datos
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // Tu usuario de MySQL
define('DB_PASSWORD', '');     // Tu contraseña de MySQL
define('DB_NAME', 'gestion_citas_medicas');

// --- CONEXIÓN A LA BASE DE DATOS ---
$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verificar la conexión
if($mysqli === false){
    die("ERROR CRÍTICO: No se pudo conectar a la base de datos. " . $mysqli->connect_error);
}

// Establecer el charset a UTF-8
$mysqli->set_charset("utf8mb4");


// --- FUNCIONES GLOBALES DE AYUDA ---

/**
 * Redirige a una página usando una URL absoluta y detiene la ejecución.
 * @param string $url La URL a la que redirigir.
 */
function redirigir($url) {
    if (!headers_sent()) {
        header("Location: " . $url);
        exit();
    } else {
        echo "<script>window.location.href='$url';</script>";
        exit();
    }
}

/**
 * Verifica si un usuario ha iniciado sesión. Si no, lo redirige al login.
 */
function verificar_sesion() {
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        redirigir(BASE_URL . 'auth/login.php');
    }
}

/**
 * Verifica si el usuario logueado tiene un rol específico.
 * Si no, muestra un error de acceso denegado.
 * @param string $rol El rol requerido ('paciente', 'doctor', 'administrador').
 */
function verificar_rol($rol_requerido) {
    verificar_sesion(); // Primero, asegura que esté logueado
    if ($_SESSION["rol"] !== $rol_requerido) {
        // Redirigir a una página de error o al dashboard correspondiente
        // Por ahora, un mensaje simple es suficiente.
        http_response_code(403); // Forbidden
        die("Acceso Denegado. No tienes los permisos necesarios para ver esta página.");
    }
}
?>