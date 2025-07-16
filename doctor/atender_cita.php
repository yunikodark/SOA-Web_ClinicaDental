<?php
require_once __DIR__ . '/../includes/config.php';
verificar_rol('doctor');

if (!isset($_GET['id_cita']) || !is_numeric($_GET['id_cita'])) {
    redirigir(BASE_URL . 'doctor/agenda.php');
}

$id_cita = intval($_GET['id_cita']);
$id_usuario_doctor = $_SESSION['id_usuario'];

// Verificar si la cita pertenece al doctor logueado (usando método compatible)
$sql_check = "SELECT c.*, u.nombre, u.apellido FROM citas c 
              JOIN doctores d ON c.id_doctor = d.id_doctor
              JOIN pacientes p ON c.id_paciente = p.id_paciente
              JOIN usuarios u ON p.id_usuario = u.id_usuario
              WHERE c.id_cita = ? AND d.id_usuario = ?";
$stmt_check = $mysqli->prepare($sql_check);
$stmt_check->bind_param("ii", $id_cita, $id_usuario_doctor);
$stmt_check->execute();
$cita = $stmt_check->get_result()->fetch_assoc();
$stmt_check->close();

if (!$cita) {
    die("Cita no encontrada o no tienes permiso para atenderla.");
}

$mensaje = '';
$tipo_mensaje = '';

// --- LÓGICA DE GUARDADO CORREGIDA Y MEJORADA ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $cita['estado'] == 'agendada') {
    $anotaciones = trim($_POST['anotaciones']);
    $recomendaciones = trim($_POST['recomendaciones']);
    $tratamiento = trim($_POST['tratamiento']);

    $mysqli->begin_transaction();
    try {
        // Usaremos un solo comando SQL para insertar o actualizar el registro de la cita.
        // La tabla 'registros_citas' tiene una clave ÚNICA en 'id_cita', lo que permite usar esta sintaxis.
        $sql_reg = "INSERT INTO registros_citas (id_cita, anotaciones_doctor, recomendaciones, tratamiento) 
                    VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                    anotaciones_doctor = VALUES(anotaciones_doctor),
                    recomendaciones = VALUES(recomendaciones),
                    tratamiento = VALUES(tratamiento)";
        
        $stmt_reg = $mysqli->prepare($sql_reg);
        // El bind_param necesita los 4 valores para la parte del INSERT.
        $stmt_reg->bind_param("isss", $id_cita, $anotaciones, $recomendaciones, $tratamiento);
        $stmt_reg->execute();
        $stmt_reg->close();
        
        // Actualizar estado de la cita a 'completada'
        $sql_update = "UPDATE citas SET estado = 'completada' WHERE id_cita = ?";
        $stmt_update = $mysqli->prepare($sql_update);
        $stmt_update->bind_param("i", $id_cita);
        $stmt_update->execute();
        $stmt_update->close();

        $mysqli->commit();
        $mensaje = 'Registro de la cita guardado y marcada como completada.';
        $tipo_mensaje = 'success';
        
        // Recargar los datos de la cita para reflejar el estado 'completada' en la página actual
        $cita['estado'] = 'completada';

    } catch (Exception $e) {
        $mysqli->rollback();
        $mensaje = 'Error al guardar el registro: ' . $e->getMessage();
        $tipo_mensaje = 'danger';
    }
}
?>

<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<h1>Atender Cita</h1>
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <h5 class="card-title">Detalles de la Cita</h5>
        <p class="card-text">
            <strong>Paciente:</strong> <?php echo htmlspecialchars($cita['nombre'] . ' ' . $cita['apellido']); ?><br>
            <strong>Fecha:</strong> <?php echo date("d/m/Y", strtotime($cita['fecha_cita'])); ?> a las <?php echo date("h:i A", strtotime($cita['hora_cita'])); ?>
        </p>
        <a href="ver_historial_paciente.php?id_paciente=<?php echo $cita['id_paciente']; ?>" class="btn btn-sm btn-outline-info">Ver Historial Completo del Paciente</a>
    </div>
</div>

<?php if ($mensaje): ?>
<div class="alert alert-<?php echo $tipo_mensaje; ?>"><?php echo $mensaje; ?></div>
<?php endif; ?>

<?php if ($cita['estado'] == 'agendada'): ?>
    <div class="card">
        <div class="card-header fw-bold">Registrar Información de la Consulta</div>
        <div class="card-body">
            <form action="atender_cita.php?id_cita=<?php echo $id_cita; ?>" method="POST">
                <div class="mb-3">
                    <label for="anotaciones" class="form-label">Anotaciones del Doctor</label>
                    <textarea name="anotaciones" id="anotaciones" rows="5" class="form-control" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="recomendaciones" class="form-label">Recomendaciones</label>
                    <textarea name="recomendaciones" id="recomendaciones" rows="3" class="form-control"></textarea>
                </div>
                <div class="mb-3">
                    <label for="tratamiento" class="form-label">Tratamiento</label>
                    <textarea name="tratamiento" id="tratamiento" rows="3" class="form-control"></textarea>
                </div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Guardar y Completar Cita</button>
                <a href="agenda.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-info">Esta cita ya ha sido completada. La información ha sido guardada.</div>
     <a href="agenda.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Volver a la Agenda</a>
<?php endif; ?>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>