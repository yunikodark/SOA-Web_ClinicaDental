<?php
require_once __DIR__ . '/../includes/config.php';
verificar_rol('doctor');

$id_usuario_doctor = $_SESSION['id_usuario'];

// Obtener ID Doctor (compatible)
$stmt_id = $mysqli->prepare("SELECT id_doctor FROM doctores WHERE id_usuario = ?");
$stmt_id->bind_param("i", $id_usuario_doctor);
$stmt_id->execute();
$id_doctor = $stmt_id->get_result()->fetch_assoc()['id_doctor'];
$stmt_id->close();

// Obtener todas las citas (compatible)
$sql = "SELECT c.id_cita, c.fecha_cita, c.hora_cita, c.estado, u.nombre as nombre_paciente, u.apellido as apellido_paciente, p.id_paciente FROM citas c JOIN pacientes p ON c.id_paciente = p.id_paciente JOIN usuarios u ON p.id_usuario = u.id_usuario WHERE c.id_doctor = ? AND c.estado IN ('agendada', 'completada') ORDER BY c.fecha_cita DESC, c.hora_cita ASC";
$stmt_citas = $mysqli->prepare($sql);
$stmt_citas->bind_param("i", $id_doctor);
$stmt_citas->execute();
$citas = $stmt_citas->get_result();
?>

<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<h1>Mi Agenda de Citas</h1>
<p>Aquí puedes ver tus citas y atender a los pacientes.</p>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr><th>Fecha</th><th>Hora</th><th>Paciente</th><th>Estado</th><th class="text-center">Acciones</th></tr>
                </thead>
                <tbody>
                    <?php if ($citas->num_rows > 0): ?>
                        <?php while($cita = $citas->fetch_assoc()): ?>
                            <tr class="<?php echo ($cita['fecha_cita'] == date('Y-m-d') && $cita['estado'] == 'agendada') ? 'table-primary' : ''; ?>">
                                <td><?php echo date("d/m/Y", strtotime($cita['fecha_cita'])); ?></td>
                                <td><?php echo date("h:i A", strtotime($cita['hora_cita'])); ?></td>
                                <td><?php echo htmlspecialchars($cita['nombre_paciente'] . ' ' . $cita['apellido_paciente']); ?></td>
                                <td><span class="badge <?php echo ($cita['estado'] == 'agendada') ? 'bg-primary' : 'bg-success'; ?>"><?php echo ucfirst($cita['estado']); ?></span></td>
                                <td class="text-center">
                                    <a href="ver_historial_paciente.php?id_paciente=<?php echo $cita['id_paciente']; ?>" class="btn btn-info btn-sm" title="Ver Historial Clínico"><i class="bi bi-journal-text"></i> Historial</a>
                                    <?php if ($cita['estado'] == 'agendada'): ?>
                                    <a href="atender_cita.php?id_cita=<?php echo $cita['id_cita']; ?>" class="btn btn-success btn-sm" title="Atender Cita"><i class="bi bi-check-circle"></i> Atender</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">No tienes citas en tu agenda.</td></tr>
                    <?php endif; $stmt_citas->close(); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>