<?php
require_once __DIR__ . '/../includes/config.php';
verificar_rol('administrador');

if ($_SERVER['REQUEST_METHOD'] != 'POST' || empty($_POST['fecha_inicio']) || empty($_POST['fecha_fin'])) {
    die("Parámetros inválidos para generar el reporte.");
}

$fecha_inicio = $_POST['fecha_inicio'];
$fecha_fin = $_POST['fecha_fin'];
$id_especialidad = !empty($_POST['id_especialidad']) ? intval($_POST['id_especialidad']) : null;

$sql = "SELECT 
            c.fecha_cita, 
            c.hora_cita,
            c.estado,
            CONCAT(up.nombre, ' ', up.apellido) as paciente,
            CONCAT(ud.nombre, ' ', ud.apellido) as doctor,
            e.nombre_especialidad
        FROM citas c
        JOIN pacientes p ON c.id_paciente = p.id_paciente
        JOIN usuarios up ON p.id_usuario = up.id_usuario
        JOIN doctores d ON c.id_doctor = d.id_doctor
        JOIN usuarios ud ON d.id_usuario = ud.id_usuario
        JOIN especialidades e ON d.id_especialidad = e.id_especialidad
        WHERE c.fecha_cita BETWEEN ? AND ?";

$params = [$fecha_inicio, $fecha_fin];
$types = "ss";

if ($id_especialidad) {
    $sql .= " AND e.id_especialidad = ?";
    $params[] = $id_especialidad;
    $types .= "i";
}

$sql .= " ORDER BY c.fecha_cita, c.hora_cita";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$resultado = $stmt->get_result();

// Preparar el archivo CSV para descarga
$nombre_archivo = "reporte_citas_" . date('Ymd') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $nombre_archivo);

$output = fopen('php://output', 'w');
// Escribir la fila de cabecera
fputcsv($output, ['Fecha', 'Hora', 'Estado', 'Paciente', 'Doctor', 'Especialidad']);

// Escribir los datos de las citas
while ($fila = $resultado->fetch_assoc()) {
    fputcsv($output, $fila);
}

fclose($output);
$stmt->close();
exit();
?>