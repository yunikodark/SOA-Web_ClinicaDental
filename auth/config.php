<?php
// Define ROOT_PATH y BASE_URL asumiendo que 'auth' está en la raíz de tu proyecto.
// Ejemplo: si tu proyecto está en C:/xampp/htdocs/gestion_citas_medicas/
// y este archivo está en C:/xampp/htdocs/gestion_citas_medicas/auth/config.php
define('ROOT_PATH', dirname(__DIR__)); // Sube un nivel desde 'auth' para llegar a 'gestion_citas_medicas/'
define('BASE_URL', 'http://localhost/gestion_citas_medicas/'); // Tu URL base

// --- Configuración de la Base de Datos ---
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Tu contraseña de MySQL
define('DB_NAME', 'gestion_citas_medicas'); // El nombre de tu base de datos

// Crear la conexión a la base de datos
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verificar conexión
if ($mysqli->connect_error) {
    die("Error de conexión a la base de datos: " . $mysqli->connect_error);
}

// Establecer el juego de caracteres a UTF-8 (importante para tildes y ñ)
$mysqli->set_charset("utf8");

// --- Funciones auxiliares (pueden ser movidas a un 'helpers.php' si crece mucho) ---

// Función para redirigir
function redirigir($url) {
    header("Location: $url");
    exit();
}

// Función para verificar sesión/rol (si es usada aquí, puede depender de $_SESSION)
function verificar_sesion() {
    session_start();
    if (!isset($_SESSION['id_usuario'])) {
        redirigir(BASE_URL . 'auth/login.php');
    }
}

function verificar_rol($rol_requerido) {
    session_start();
    if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== $rol_requerido) {
        // Podrías redirigir a una página de "Acceso Denegado"
        redirigir(BASE_URL . 'auth/login.php'); // O a tu página de inicio de sesión
    }
}

// ======================================================
// CONFIGURACIÓN DE PHPMailer PARA ENVÍO DE CORREOS
// ======================================================

// Requiere los archivos de PHPMailer.
// Dada tu imagen, asumo que los archivos .php están directamente en auth/phpMailer
require_once __DIR__ . '/phpMailer/PHPMailer.php';
require_once __DIR__ . '/phpMailer/SMTP.php';
require_once __DIR__ . '/phpMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Datos de configuración del servidor SMTP (ej. Gmail)
define('MAIL_HOST', 'smtp.gmail.com');       // Servidor SMTP (ej: smtp.gmail.com para Gmail)
define('MAIL_USERNAME', 'minimarketllauce14@gmail.com'); // **TU CORREO REAL**
define('MAIL_PASSWORD', 'cqgtkvzwumbbqoki'); // **TU CONTRASEÑA DE APLICACIÓN (PARA GMAIL)**
define('MAIL_PORT', 587);                   // Puerto SMTP (587 para TLS, 465 para SSL)
define('MAIL_ENCRYPTION', PHPMailer::ENCRYPTION_STARTTLS); // Cifrado (PHPMailer::ENCRYPTION_SMTPS para 465 si usas 465)
define('MAIL_FROM_EMAIL', 'tu_correo@gmail.com'); // Email que aparecerá como remitente
define('MAIL_FROM_NAME', 'Clínica Dental [ROY - UTP]'); // Nombre que aparecerá como remitente

// Función auxiliar para enviar correos
function enviar_correo($destinatario_email, $destinatario_nombre, $asunto, $cuerpo_html) {
    $mail = new PHPMailer(true); // Pasar 'true' habilita las excepciones

    try {
        $mail->isSMTP();                                            
        $mail->Host       = MAIL_HOST;                              
        $mail->SMTPAuth   = true;                                   
        $mail->Username   = MAIL_USERNAME;                          
        $mail->Password   = MAIL_PASSWORD;                          
        $mail->SMTPSecure = MAIL_ENCRYPTION;                        
        $mail->Port       = MAIL_PORT;                              
        $mail->CharSet    = 'UTF-8';                                

        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        $mail->addAddress($destinatario_email, $destinatario_nombre);

        $mail->isHTML(true);                                        
        $mail->Subject    = $asunto;
        $mail->Body       = $cuerpo_html;
        $mail->AltBody    = strip_tags($cuerpo_html);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("PHPMailer Error: {$mail->ErrorInfo} for recipient {$destinatario_email}");
        // Puedes agregar más manejo de errores aquí, como guardar en una tabla de logs
        return false;
    }
}

?>