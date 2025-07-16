<?php
require_once __DIR__ . '/../includes/config.php';
verificar_rol('administrador');

// Estadísticas rápidas para el dashboard
$total_pacientes = $mysqli->query("SELECT COUNT(*) as total FROM pacientes")->fetch_assoc()['total'];
$total_doctores = $mysqli->query("SELECT COUNT(*) as total FROM doctores")->fetch_assoc()['total'];
$total_citas_mes = $mysqli->query("SELECT COUNT(*) as total FROM citas WHERE MONTH(fecha_cita) = MONTH(CURDATE()) AND YEAR(fecha_cita) = YEAR(CURDATE())")->fetch_assoc()['total'];
$total_especialidades = $mysqli->query("SELECT COUNT(*) as total FROM especialidades")->fetch_assoc()['total'];
// --- NUEVA LÍNEA: Contar los horarios definidos ---
$total_horarios = $mysqli->query("SELECT COUNT(*) as total FROM horarios")->fetch_assoc()['total'];
?>
<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<h1>Panel de Administración</h1>
<p class="lead">Gestión general del sistema de citas médicas.</p>

<div class="row">
    <div class="col-lg-3 col-6 mb-4">
        <div class="small-box bg-info p-3 rounded text-white shadow-sm">
            <div class="inner">
                <h3><?php echo $total_pacientes; ?></h3>
                <p>Pacientes Registrados</p>
            </div>
            <div class="icon fs-1 opacity-50"><i class="bi bi-people-fill"></i></div>
            <a href="<?php echo BASE_URL; ?>admin/ver_usuarios.php?rol=paciente" class="small-box-footer text-white">Más info <i class="bi bi-arrow-right-circle"></i></a>
        </div>
    </div>
     <div class="col-lg-3 col-6 mb-4">
        <div class="small-box bg-success p-3 rounded text-white shadow-sm">
            <div class="inner">
                <h3><?php echo $total_doctores; ?></h3>
                <p>Doctores Activos</p>
            </div>
            <div class="icon fs-1 opacity-50"><i class="bi bi-person-badge"></i></div>
            <a href="<?php echo BASE_URL; ?>admin/gestionar_doctores.php" class="small-box-footer text-white">Más info <i class="bi bi-arrow-right-circle"></i></a>
        </div>
    </div>
     <div class="col-lg-3 col-6 mb-4">
        <div class="small-box bg-warning p-3 rounded text-dark shadow-sm">
            <div class="inner">
                <h3><?php echo $total_citas_mes; ?></h3>
                <p>Citas este Mes</p>
            </div>
            <div class="icon fs-1 opacity-50"><i class="bi bi-calendar-event"></i></div>
            <a href="<?php echo BASE_URL; ?>admin/reportes.php" class="small-box-footer text-dark">Más info <i class="bi bi-arrow-right-circle"></i></a>
        </div>
    </div>
     <div class="col-lg-3 col-6 mb-4">
        <div class="small-box bg-secondary p-3 rounded text-white shadow-sm">
            <div class="inner">
                <h3><?php echo $total_especialidades; ?></h3>
                <p>Especialidades</p>
            </div>
            <div class="icon fs-1 opacity-50"><i class="bi bi-heart-pulse"></i></div>
            <a href="<?php echo BASE_URL; ?>admin/gestionar_especialidades.php" class="small-box-footer text-white">Más info <i class="bi bi-arrow-right-circle"></i></a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-3 col-6 mb-4">
        <div class="small-box bg-primary p-3 rounded text-white shadow-sm">
            <div class="inner">
                <h3><?php echo $total_horarios; ?></h3>
                <p>Horarios Definidos</p>
            </div>
            <div class="icon fs-1 opacity-50"><i class="bi bi-clock-history"></i></div>
            <a href="<?php echo BASE_URL; ?>admin/gestionar_horarios.php" class="small-box-footer text-white">Gestionar <i class="bi bi-arrow-right-circle"></i></a>
        </div>
    </div>
</div>


<h2 class="mt-4">Menú de Administración</h2>
<hr>
<div class="list-group">
  <a href="<?php echo BASE_URL; ?>admin/gestionar_doctores.php" class="list-group-item list-group-item-action"><i class="bi bi-person-badge-fill me-2"></i>Gestionar Doctores</a>
  <a href="<?php echo BASE_URL; ?>admin/gestionar_especialidades.php" class="list-group-item list-group-item-action"><i class="bi bi-heart-pulse-fill me-2"></i>Gestionar Especialidades</a>
  <a href="<?php echo BASE_URL; ?>admin/gestionar_horarios.php" class="list-group-item list-group-item-action"><i class="bi bi-clock-fill me-2"></i>Gestionar Horarios de Doctores</a>
  <a href="<?php echo BASE_URL; ?>admin/ver_usuarios.php" class="list-group-item list-group-item-action"><i class="bi bi-people-fill me-2"></i>Ver todos los Usuarios</a>
  <a href="<?php echo BASE_URL; ?>admin/reportes.php" class="list-group-item list-group-item-action"><i class="bi bi-file-earmark-bar-graph-fill me-2"></i>Generar Reportes de Citas</a>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>