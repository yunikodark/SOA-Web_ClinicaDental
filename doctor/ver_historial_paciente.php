<?php
require_once __DIR__ . '/../includes/config.php';
verificar_rol('doctor');

if (!isset($_GET['id_paciente']) || !is_numeric($_GET['id_paciente'])) {
    redirigir(BASE_URL . 'doctor/mis_pacientes.php');
}
$id_paciente = intval($_GET['id_paciente']);
$id_usuario_doctor = $_SESSION['id_usuario'];

// Obtener ID Doctor
$stmt_id_doc = $mysqli->prepare("SELECT id_doctor FROM doctores WHERE id_usuario = ?");
$stmt_id_doc->bind_param("i", $id_usuario_doctor);
$stmt_id_doc->execute();
$id_doctor = $stmt_id_doc->get_result()->fetch_assoc()['id_doctor'];
$stmt_id_doc->close();

// Obtener datos del paciente
$sql_pac_info = "SELECT u.nombre, u.apellido, u.correo, p.fecha_nacimiento, p.direccion FROM usuarios u JOIN pacientes p ON u.id_usuario = p.id_usuario WHERE p.id_paciente = ?";
$stmt_pac_info = $mysqli->prepare($sql_pac_info);
$stmt_pac_info->bind_param("i", $id_paciente);
$stmt_pac_info->execute();
$paciente_info = $stmt_pac_info->get_result()->fetch_assoc();
$stmt_pac_info->close();

if (!$paciente_info) { die("Paciente no encontrado."); }

// Historial de citas completadas (sin cambios, ya estaba bien)
$stmt_citas = $mysqli->prepare("SELECT c.id_cita, c.fecha_cita, c.hora_cita, rc.* FROM citas c LEFT JOIN registros_citas rc ON c.id_cita = rc.id_cita WHERE c.id_paciente = ? AND c.id_doctor = ? AND c.estado = 'completada' ORDER BY c.fecha_cita DESC");
$stmt_citas->bind_param("ii", $id_paciente, $id_doctor);
$stmt_citas->execute();
$citas_historial = $stmt_citas->get_result();

// --- CONSULTA CORREGIDA Y MÁS ESTRICTA ---
// Solo trae historiales que tienen un id_cita que corresponde a una cita con el doctor actual.
$sql_subido = "SELECT h.archivo_historial, h.fecha_subida
               FROM historial_medico h
               JOIN citas c ON h.id_cita = c.id_cita
               WHERE h.id_paciente = ? AND c.id_doctor = ?
               ORDER BY h.fecha_subida DESC";
$stmt_subido = $mysqli->prepare($sql_subido);
$stmt_subido->bind_param("ii", $id_paciente, $id_doctor); // Se mantienen los dos parámetros
$stmt_subido->execute();
$historial_subido = $stmt_subido->get_result();
?>
<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<h1>Historial Clínico de: <?php echo htmlspecialchars($paciente_info['nombre'] . ' ' . $paciente_info['apellido']); ?></h1>
<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title">Información del Paciente</h5>
        <p class="card-text mb-1"><strong>Correo:</strong> <?php echo htmlspecialchars($paciente_info['correo']); ?></p>
        <p class="card-text mb-0"><strong>Fecha de Nacimiento:</strong> <?php echo $paciente_info['fecha_nacimiento'] ? date('d/m/Y', strtotime($paciente_info['fecha_nacimiento'])) : 'No especificada'; ?></p>
    </div>
</div>

<ul class="nav nav-tabs" id="historialTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="citas-tab" data-bs-toggle="tab" data-bs-target="#citas" type="button" role="tab" aria-controls="citas" aria-selected="true">Historial de Citas Conmigo</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="documentos-tab" data-bs-toggle="tab" data-bs-target="#documentos" type="button" role="tab" aria-controls="documentos" aria-selected="false">Documentos del Paciente</button>
    </li>
</ul>

<div class="tab-content border border-top-0 p-3" id="historialTabContent">
  <div class="tab-pane fade show active" id="citas" role="tabpanel" aria-labelledby="citas-tab">
    <div class="accordion" id="accordionCitas">
        <?php if ($citas_historial->num_rows > 0): while($cita = $citas_historial->fetch_assoc()): ?>
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading<?php echo $cita['id_cita']; ?>">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $cita['id_cita']; ?>">
                        <strong>Cita del <?php echo date('d/m/Y', strtotime($cita['fecha_cita'])); ?></strong>
                    </button>
                </h2>
                <div id="collapse<?php echo $cita['id_cita']; ?>" class="accordion-collapse collapse" data-bs-parent="#accordionCitas">
                    <div class="accordion-body">
                        <?php if($cita['id_registro_cita']): ?>
                            <h5>Anotaciones:</h5><p><?php echo nl2br(htmlspecialchars($cita['anotaciones_doctor'])); ?></p>
                            <h5>Recomendaciones:</h5><p><?php echo nl2br(htmlspecialchars($cita['recomendaciones'])); ?></p>
                            <h5>Tratamiento:</h5><p><?php echo nl2br(htmlspecialchars($cita['tratamiento'])); ?></p>
                        <?php else: ?>
                            <p class="text-muted">No se encontraron registros detallados para esta cita.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endwhile; else: ?>
            <p class="mt-3 text-muted">El paciente no tiene citas completadas contigo.</p>
        <?php endif; $stmt_citas->close(); ?>
    </div>
  </div>
  <div class="tab-pane fade" id="documentos" role="tabpanel" aria-labelledby="documentos-tab">
    <div class="list-group mt-3">
        <?php if ($historial_subido->num_rows > 0): while($doc = $historial_subido->fetch_assoc()): ?>
            <a href="<?php echo BASE_URL . htmlspecialchars($doc['archivo_historial']); ?>" target="_blank" class="list-group-item list-group-item-action">
                <i class="bi bi-file-earmark-text me-2"></i> <?php echo basename($doc['archivo_historial']); ?>
                <small class="text-muted float-end">Subido el: <?php echo date('d/m/Y', strtotime($doc['fecha_subida'])); ?></small>
            </a>
        <?php endwhile; else: ?>
            <p class="mt-3 text-muted">El paciente no ha subido ningún documento en las citas registradas contigo.</p>
        <?php endif; $stmt_subido->close(); ?>
    </div>
  </div>
</div>
<br>
<a href="mis_pacientes.php" class="btn btn-secondary mt-3"><i class="bi bi-arrow-left"></i> Volver a Mis Pacientes</a>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>