<?php
require_once __DIR__ . '/../includes/config.php';

// Establecer la cabecera para devolver JSON
header('Content-Type: application/json');

// Validar que el ID de especialidad se haya recibido y sea un número
if (!isset($_GET['id_especialidad']) || !is_numeric($_GET['id_especialidad'])) {
    echo json_encode(['error' => 'ID de especialidad no válido']);
    exit;
}

$id_especialidad = intval($_GET['id_especialidad']);

$sql = "SELECT d.id_doctor, u.nombre, u.apellido 
        FROM doctores d
        JOIN usuarios u ON d.id_usuario = u.id_usuario
        WHERE d.id_especialidad = ?
        ORDER BY u.apellido, u.nombre";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $id_especialidad);
$stmt->execute();
$result = $stmt->get_result();

$doctores = [];
while ($row = $result->fetch_assoc()) {
    // Crear un campo 'nombre_completo' para facilitar su uso en JavaScript
    $row['nombre_completo'] = 'Dr(a). ' . $row['nombre'] . ' ' . $row['apellido'];
    $doctores[] = $row;
}

$stmt->close();

// Devolver el array de doctores en formato JSON
echo json_encode($doctores);
?>