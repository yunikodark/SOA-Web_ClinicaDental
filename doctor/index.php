<?php
require_once __DIR__ . '/../includes/config.php';
verificar_rol('doctor');

$id_usuario_doctor = $_SESSION['id_usuario'];
$nombre_doctor = $_SESSION['nombre'];

// Obtener el ID de Doctor (compatible)
$stmt_id = $mysqli->prepare("SELECT id_doctor FROM doctores WHERE id_usuario = ?");
$stmt_id->bind_param("i", $id_usuario_doctor);
$stmt_id->execute();
$id_doctor = $stmt_id->get_result()->fetch_assoc()['id_doctor'];
$stmt_id->close();

if (!$id_doctor) {
    die("Error: Perfil de doctor no encontrado o no vinculado.");
}

// Citas para hoy (compatible)
$hoy = date('Y-m-d');
$stmt_hoy = $mysqli->prepare("SELECT COUNT(*) as total FROM citas WHERE id_doctor = ? AND fecha_cita = ? AND estado = 'agendada'");
$stmt_hoy->bind_param("is", $id_doctor, $hoy);
$stmt_hoy->execute();
$citas_hoy = $stmt_hoy->get_result()->fetch_assoc()['total'];
$stmt_hoy->close();

// Citas para esta semana (compatible)
$inicio_semana = date('Y-m-d', strtotime('monday this week'));
$fin_semana = date('Y-m-d', strtotime('sunday this week'));
$stmt_semana = $mysqli->prepare("SELECT COUNT(*) as total FROM citas WHERE id_doctor = ? AND fecha_cita BETWEEN ? AND ? AND estado = 'agendada'");
$stmt_semana->bind_param("iss", $id_doctor, $inicio_semana, $fin_semana);
$stmt_semana->execute();
$citas_semana = $stmt_semana->get_result()->fetch_assoc()['total'];
$stmt_semana->close();

// Próxima cita del día (compatible)
$stmt_proxima = $mysqli->prepare("SELECT c.hora_cita, u.nombre, u.apellido FROM citas c JOIN pacientes p ON c.id_paciente = p.id_paciente JOIN usuarios u ON p.id_usuario = u.id_usuario WHERE c.id_doctor = ? AND c.fecha_cita = ? AND c.estado = 'agendada' ORDER BY c.hora_cita ASC LIMIT 1");
$stmt_proxima->bind_param("is", $id_doctor, $hoy);
$stmt_proxima->execute();
$proxima_cita_hoy = $stmt_proxima->get_result()->fetch_assoc();
$stmt_proxima->close();
?>
<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<h1>Panel del Doctor</h1>
<p class="lead">Bienvenido, Dr(a). <?php echo htmlspecialchars($nombre_doctor); ?>.</p>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card text-white bg-success h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div><h5 class="card-title">Citas para Hoy</h5><p class="card-text display-4"><?php echo $citas_hoy; ?></p></div>
                    <i class="bi bi-calendar-day fs-1 opacity-50"></i>
                </div>
                <a href="<?php echo BASE_URL; ?>doctor/agenda.php" class="btn btn-outline-light mt-2 stretched-link">Ver Agenda de Hoy</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card text-dark bg-warning h-100">
            <div class="card-body">
                 <div class="d-flex justify-content-between align-items-center">
                    <div><h5 class="card-title">Citas esta Semana</h5><p class="card-text display-4"><?php echo $citas_semana; ?></p></div>
                    <i class="bi bi-calendar-week fs-1 opacity-50"></i>
                </div>
                 <a href="<?php echo BASE_URL; ?>doctor/agenda.php" class="btn btn-outline-dark mt-2 stretched-link">Ver Agenda Completa</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card text-white bg-info h-100">
            <div class="card-body">
                <h5 class="card-title">Próxima Cita de Hoy</h5>
                <?php if ($proxima_cita_hoy): ?>
                    <p class="card-text fs-5"><i class="bi bi-person-fill"></i> Con: <?php echo htmlspecialchars($proxima_cita_hoy['nombre'] . ' ' . $proxima_cita_hoy['apellido']); ?></p>
                    <p class="fs-4 fw-bold"><i class="bi bi-clock-fill"></i> A las <?php echo date("h:i A", strtotime($proxima_cita_hoy['hora_cita'])); ?></p>
                <?php else: ?>
                    <p class="card-text mt-4">No tienes más citas agendadas para hoy.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<h2 class="mt-4">Accesos Directos</h2>
<hr>
<div class="row">
    <div class="col-md-6 mb-3">
        <a href="<?php echo BASE_URL; ?>doctor/agenda.php" class="text-decoration-none text-dark"><div class="card dashboard-card"><i class="bi bi-calendar-week"></i><h4>Mi Agenda Completa</h4><p class="text-muted small">Visualiza todas tus citas agendadas y atiende a tus pacientes.</p></div></a>
    </div>
    <div class="col-md-6 mb-3">
        <a href="<?php echo BASE_URL; ?>doctor/mis_pacientes.php" class="text-decoration-none text-dark"><div class="card dashboard-card"><i class="bi bi-people-fill"></i><h4>Mis Pacientes</h4><p class="text-muted small">Accede a la lista y los historiales de tus pacientes.</p></div></a>
    </div>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>