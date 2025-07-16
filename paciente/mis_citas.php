<?php
require_once __DIR__ . '/../includes/config.php';
verificar_rol('paciente');

$id_usuario = $_SESSION['id_usuario'];

$sql = "SELECT c.id_cita, c.fecha_cita, c.hora_cita, c.estado, 
               u.nombre as nombre_doctor, u.apellido as apellido_doctor, e.nombre_especialidad
        FROM citas c
        JOIN doctores d ON c.id_doctor = d.id_doctor
        JOIN usuarios u ON d.id_usuario = u.id_usuario
        JOIN especialidades e ON d.id_especialidad = e.id_especialidad
        JOIN pacientes p ON c.id_paciente = p.id_paciente
        WHERE p.id_usuario = ?
        ORDER BY c.fecha_cita DESC, c.hora_cita DESC";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$citas = $stmt->get_result();
$stmt->close();
?>

<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<h1>Mis Citas</h1>
<p>Aquí puedes ver el historial completo de tus citas, tanto pasadas como futuras.</p>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Doctor</th>
                        <th>Especialidad</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($citas->num_rows > 0): ?>
                        <?php while($cita = $citas->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date("d/m/Y", strtotime($cita['fecha_cita'])); ?></td>
                                <td><?php echo date("h:i A", strtotime($cita['hora_cita'])); ?></td>
                                <td><?php echo 'Dr(a). ' . htmlspecialchars($cita['nombre_doctor'] . ' ' . $cita['apellido_doctor']); ?></td>
                                <td><?php echo htmlspecialchars($cita['nombre_especialidad']); ?></td>
                                <td>
                                    <?php
                                        $estado = $cita['estado'];
                                        $badge_class = 'bg-secondary';
                                        if ($estado == 'agendada') $badge_class = 'bg-primary';
                                        if ($estado == 'completada') $badge_class = 'bg-success';
                                        if ($estado == 'cancelada') $badge_class = 'bg-danger';
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($estado); ?></span>
                                </td>
                                <td>
                                    <?php if ($cita['estado'] == 'completada'): ?>
                                        <a href="ver_detalle_cita.php?id=<?php echo $cita['id_cita']; ?>" class="btn btn-info btn-sm" title="Ver Detalle">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No tienes citas registradas. <a href="agendar_cita.php">¡Agenda una ahora!</a></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>