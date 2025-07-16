<?php
require_once __DIR__ . '/../../includes/config.php';

// Asegurarse de que se ha enviado un id_especialidad
if (!isset($_GET['id_especialidad'])) {
    echo json_encode([]); // Devuelve un array vacío si no hay especialidad
    exit;
}

$id_especialidad = (int)$_GET['id_especialidad'];

$query = "SELECT d.id_doctor, u.nombre, u.apellido
          FROM doctores d
          JOIN usuarios u ON d.id_usuario = u.id_usuario
          WHERE d.id_especialidad = ? AND u.estado = 'activo'";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $id_especialidad);
$stmt->execute();
$result = $stmt->get_result();

$doctores = [];
while ($row = $result->fetch_assoc()) {
    $doctores[] = [
        'id_doctor' => $row['id_doctor'],
        'nombre_completo' => htmlspecialchars($row['nombre'] . ' ' . $row['apellido'])
    ];
}

header('Content-Type: application/json');
echo json_encode($doctores);
?>