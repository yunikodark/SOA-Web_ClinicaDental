<?php
require_once __DIR__ . '/../includes/config.php';
verificar_rol('paciente');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirigir(BASE_URL . 'paciente/mis_citas.php');
}

$id_cita = intval($_GET['id']);
$id_usuario = $_SESSION['id_usuario'];

$sql = "SELECT rc.*, c.fecha_cita, c.hora_cita, u_doc.nombre as doc_nombre, u_doc.apellido as doc_apellido
        FROM citas c
        LEFT JOIN registros_citas rc ON c.id_cita = rc.id_cita
        JOIN pacientes p ON c.id_paciente = p.id_paciente
        JOIN doctores d ON c.id_doctor = d.id_doctor
        JOIN usuarios u_doc ON d.id_usuario = u_doc.id_usuario
        WHERE c.id_cita = ? AND p.id_usuario = ?";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ii", $id_cita, $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$resultado) {
    die("No se encontró el registro de la cita o no tienes permiso para acceder a él.");
}
?>

<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<h1>Detalle de la Cita</h1>
<p class="text-muted">
    Cita del <?php echo date("d/m/Y", strtotime($resultado['fecha_cita'])); ?> 
    a las <?php echo date("h:i A", strtotime($resultado['hora_cita'])); ?> 
    con Dr(a). <?php echo htmlspecialchars($resultado['doc_nombre'] . ' ' . $resultado['doc_apellido']); ?>
</p>
<hr>

<div class="card mb-3"><div class="card-header fw-bold">Anotaciones del Doctor</div><div class="card-body"><p class="card-text"><?php echo nl2br(htmlspecialchars($resultado['anotaciones_doctor'] ?? 'Sin anotaciones.')); ?></p></div></div>
<div class="card mb-3"><div class="card-header fw-bold">Recomendaciones</div><div class="card-body"><p class="card-text"><?php echo nl2br(htmlspecialchars($resultado['recomendaciones'] ?? 'Sin recomendaciones.')); ?></p></div></div>
<div class="card mb-3"><div class="card-header fw-bold">Tratamiento Indicado</div><div class="card-body"><p class="card-text"><?php echo nl2br(htmlspecialchars($resultado['tratamiento'] ?? 'Sin tratamiento específico.')); ?></p></div></div>


<div class="mt-4">
    
    <a href="<?php echo BASE_URL; ?>paciente/exportar_pdf.php?id=<?php echo $id_cita; ?>" class="btn btn-danger">
        <i class="bi bi-file-earmark-pdf"></i> Exportar a PDF
    </a>
</div>

<a href="<?php echo BASE_URL; ?>paciente/mis_citas.php" class="btn btn-secondary mt-4"><i class="bi bi-arrow-left"></i> Volver a Mis Citas</a>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>