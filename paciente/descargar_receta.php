<?php
require_once __DIR__ . '/../includes/config.php';
verificar_rol('paciente');

if (!isset($_GET['id_cita']) || !is_numeric($_GET['id_cita'])) {
    die("ID de cita no especificado.");
}

$id_cita = intval($_GET['id_cita']);
$id_usuario = $_SESSION['id_usuario'];

// Verificar que la receta pertenece al paciente que ha iniciado sesión
$sql = "SELECT rc.documento_receta 
        FROM registros_citas rc
        JOIN citas c ON rc.id_cita = c.id_cita
        JOIN pacientes p ON c.id_paciente = p.id_paciente
        WHERE rc.id_cita = ? AND p.id_usuario = ? AND rc.documento_receta IS NOT NULL";
        
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ii", $id_cita, $id_usuario);
$stmt->execute();
$stmt->bind_result($ruta_documento);
$stmt->fetch();
$stmt->close();

if ($ruta_documento) {
    // Construir la ruta física completa y segura al archivo
    $ruta_completa = ROOT_PATH . '/' . $ruta_documento;

    if (file_exists($ruta_completa)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream'); // Fuerza la descarga
        header('Content-Disposition: attachment; filename="' . basename($ruta_completa) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($ruta_completa));
        // Limpiar el buffer de salida antes de leer el archivo
        ob_clean();
        flush();
        readfile($ruta_completa);
        exit;
    } else {
        die("Error: El archivo no fue encontrado en el servidor. Ruta: " . htmlspecialchars($ruta_completa));
    }
} else {
    die("No tienes permiso para descargar este archivo o el archivo no existe.");
}
?>