<?php
require_once __DIR__ . '/../includes/config.php';
verificar_rol('paciente');

$id_usuario = $_SESSION['id_usuario'];

$sql = "SELECT c.id_cita, c.fecha_cita, c.hora_cita, 
               u.nombre as nombre_doctor, u.apellido as apellido_doctor, e.nombre_especialidad,
               rc.anotaciones_doctor, rc.recomendaciones, rc.tratamiento
        FROM citas c
        JOIN doctores d ON c.id_doctor = d.id_doctor
        JOIN usuarios u ON d.id_usuario = u.id_usuario
        JOIN especialidades e ON d.id_especialidad = e.id_especialidad
        JOIN pacientes p ON c.id_paciente = p.id_paciente
        LEFT JOIN registros_citas rc ON c.id_cita = rc.id_cita
        WHERE p.id_usuario = ? AND c.estado = 'completada'
        ORDER BY c.fecha_cita DESC, c.hora_cita DESC";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$citas_completadas = $stmt->get_result();
$stmt->close();
?>

<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<h1>Historial Clínico de Citas</h1>
<p>Aquí puedes ver un resumen de todas tus citas que ya han sido completadas, con las notas del doctor.</p>

<div class="accordion" id="historialAccordion">
    <?php if ($citas_completadas->num_rows > 0): ?>
        <?php while($cita = $citas_completadas->fetch_assoc()): ?>
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading-<?php echo $cita['id_cita']; ?>">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo $cita['id_cita']; ?>" aria-expanded="false" aria-controls="collapse-<?php echo $cita['id_cita']; ?>">
                        <strong><?php echo date("d/m/Y", strtotime($cita['fecha_cita'])); ?></strong> - Dr(a). <?php echo htmlspecialchars($cita['nombre_doctor'] . ' ' . $cita['apellido_doctor']); ?> (<?php echo htmlspecialchars($cita['nombre_especialidad']); ?>)
                    </button>
                </h2>
                <div id="collapse-<?php echo $cita['id_cita']; ?>" class="accordion-collapse collapse" aria-labelledby="heading-<?php echo $cita['id_cita']; ?>" data-bs-parent="#historialAccordion">
                    <div class="accordion-body">
                        <h5>Anotaciones</h5>
                        <p><?php echo !empty($cita['anotaciones_doctor']) ? nl2br(htmlspecialchars($cita['anotaciones_doctor'])) : 'No hay anotaciones.'; ?></p>
                        <hr>
                        <h5>Recomendaciones</h5>
                        <p><?php echo !empty($cita['recomendaciones']) ? nl2br(htmlspecialchars($cita['recomendaciones'])) : 'No hay recomendaciones.'; ?></p>
                         <hr>
                        <h5>Tratamiento</h5>
                        <p><?php echo !empty($cita['tratamiento']) ? nl2br(htmlspecialchars($cita['tratamiento'])) : 'No se especificó tratamiento.'; ?></p>
                        <a href="<?php echo BASE_URL; ?>paciente/ver_detalle_cita.php?id=<?php echo $cita['id_cita']; ?>" class="btn btn-sm btn-outline-primary">Ver Detalle Completo</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-info">No tienes citas completadas en tu historial.</div>
    <?php endif; ?>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>