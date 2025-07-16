<?php
require_once __DIR__ . '/../includes/config.php';
verificar_rol('administrador');

$mensaje = '';
$tipo_mensaje = '';

// --- LÓGICA PARA PROCESAR FORMULARIOS (sin cambios) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    // Acción para agregar un nuevo horario
    if ($action == 'agregar_horario') {
        $id_doctor = $_POST['id_doctor'];
        $dia_semana = $_POST['dia_semana'];
        $hora_inicio = $_POST['hora_inicio'];
        $hora_fin = $_POST['hora_fin'];

        // Validación simple
        if ($hora_inicio >= $hora_fin) {
            $mensaje = 'Error: La hora de inicio no puede ser mayor o igual a la hora de fin.';
            $tipo_mensaje = 'danger';
        } else {
            $stmt = $mysqli->prepare("INSERT INTO horarios (id_doctor, dia_semana, hora_inicio, hora_fin) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $id_doctor, $dia_semana, $hora_inicio, $hora_fin);
            if ($stmt->execute()) {
                $mensaje = 'Horario agregado correctamente.';
                $tipo_mensaje = 'success';
            } else {
                $mensaje = 'Error al agregar el horario.';
                $tipo_mensaje = 'danger';
            }
            $stmt->close();
        }
    }

    // Acción para eliminar un horario
    if ($action == 'eliminar_horario' && !empty($_POST['id_horario'])) {
        $id_horario = $_POST['id_horario'];
        $stmt = $mysqli->prepare("DELETE FROM horarios WHERE id_horario = ?");
        $stmt->bind_param("i", $id_horario);
        if ($stmt->execute()) {
            $mensaje = 'Horario eliminado correctamente.';
            $tipo_mensaje = 'success';
        } else {
            $mensaje = 'Error al eliminar el horario.';
            $tipo_mensaje = 'danger';
        }
        $stmt->close();
    }
}

// --- CAMBIO 1: Modificar la consulta para incluir la especialidad ---
$query_doctores = "SELECT d.id_doctor, u.nombre, u.apellido, e.nombre_especialidad
                   FROM doctores d 
                   JOIN usuarios u ON d.id_usuario = u.id_usuario
                   JOIN especialidades e ON d.id_especialidad = e.id_especialidad
                   WHERE u.estado = 'activo' ORDER BY u.apellido, u.nombre";
$doctores_activos = $mysqli->query($query_doctores);

// Obtener todos los horarios para listarlos (sin cambios)
$query_horarios = "SELECT h.id_horario, h.dia_semana, h.hora_inicio, h.hora_fin, u.nombre, u.apellido
                   FROM horarios h
                   JOIN doctores d ON h.id_doctor = d.id_doctor
                   JOIN usuarios u ON d.id_usuario = u.id_usuario
                   ORDER BY u.apellido, u.nombre, FIELD(h.dia_semana, 'Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo')";
$resultado_horarios = $mysqli->query($query_horarios);

$horarios_agrupados = [];
while ($row = $resultado_horarios->fetch_assoc()) {
    $horarios_agrupados[$row['nombre'] . ' ' . $row['apellido']][] = $row;
}

require_once ROOT_PATH . '/includes/header.php';
?>

<h1>Gestionar Horarios de Doctores 📅</h1>
<?php if ($mensaje): ?>
<div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
    <?php echo htmlspecialchars($mensaje); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm">
            <div class="card-header fw-bold">Agregar Horario ⏰</div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="agregar_horario">
                    <div class="mb-3">
                        <label for="id_doctor" class="form-label">Doctor</label>
                        <select name="id_doctor" id="id_doctor" class="form-select" required>
                            <option value="">-- Seleccionar Doctor --</option>
                            <?php while ($doc = $doctores_activos->fetch_assoc()): ?>
                                <option value="<?php echo $doc['id_doctor']; ?>">
                                    <?php echo htmlspecialchars($doc['apellido'] . ', ' . $doc['nombre'] . ' (' . $doc['nombre_especialidad'] . ')'); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="dia_semana" class="form-label">Día de la Semana</label>
                        <select name="dia_semana" id="dia_semana" class="form-select" required>
                            <option value="Lunes">Lunes</option>
                            <option value="Martes">Martes</option>
                            <option value="Miércoles">Miércoles</option>
                            <option value="Jueves">Jueves</option>
                            <option value="Viernes">Viernes</option>
                            <option value="Sábado">Sábado</option>
                            <option value="Domingo">Domingo</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="hora_inicio" class="form-label">Hora Inicio</label>
                            <input type="time" name="hora_inicio" id="hora_inicio" class="form-control" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label for="hora_fin" class="form-label">Hora Fin</label>
                            <input type="time" name="hora_fin" id="hora_fin" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Agregar Horario</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header fw-bold">Horarios Programados</div>
            <div class="card-body">
                <?php if (empty($horarios_agrupados)): ?>
                    <p class="text-center text-muted">No hay horarios registrados.</p>
                <?php else: ?>
                    <?php foreach ($horarios_agrupados as $nombre_doctor => $horarios): ?>
                        <h5 class="mt-3">👨‍⚕️ <?php echo htmlspecialchars($nombre_doctor); ?></h5>
                        <ul class="list-group">
                            <?php foreach ($horarios as $h): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?php echo htmlspecialchars($h['dia_semana']); ?>:</strong>
                                        de <?php echo date("g:i A", strtotime($h['hora_inicio'])); ?>
                                        a <?php echo date("g:i A", strtotime($h['hora_fin'])); ?>
                                    </div>
                                    <form method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este horario?');">
                                        <input type="hidden" name="action" value="eliminar_horario">
                                        <input type="hidden" name="id_horario" value="<?php echo $h['id_horario']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>