<?php
require_once __DIR__ . '/../includes/config.php';
verificar_rol('doctor');

$id_usuario = $_SESSION['id_usuario'];
$mensaje = '';
$tipo_mensaje = '';

// --- Lógica para procesar el formulario cuando se envía ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $id_especialidad = intval($_POST['id_especialidad']);

    $mysqli->begin_transaction();
    try {
        // 1. Actualizar la tabla 'usuarios' (sin teléfono)
        $stmt_user = $mysqli->prepare("UPDATE usuarios SET nombre = ?, apellido = ? WHERE id_usuario = ?");
        $stmt_user->bind_param("ssi", $nombre, $apellido, $id_usuario);
        $stmt_user->execute();
        $stmt_user->close();

        // 2. Actualizar la tabla 'doctores'
        $stmt_doc = $mysqli->prepare("UPDATE doctores SET id_especialidad = ? WHERE id_usuario = ?");
        $stmt_doc->bind_param("ii", $id_especialidad, $id_usuario);
        $stmt_doc->execute();
        $stmt_doc->close();

        $mysqli->commit();
        $mensaje = 'Perfil actualizado correctamente.';
        $tipo_mensaje = 'success';
        $_SESSION['nombre'] = $nombre; // Actualizar el nombre en la sesión
    } catch (Exception $e) {
        $mysqli->rollback();
        $mensaje = 'Error al actualizar el perfil: ' . $e->getMessage();
        $tipo_mensaje = 'danger';
    }
}

// --- Lógica para obtener los datos actuales del doctor (consulta limpia y sin teléfono) ---
$sql_get = "SELECT u.nombre, u.apellido, u.correo, d.id_especialidad
            FROM usuarios u
            JOIN doctores d ON u.id_usuario = d.id_usuario
            WHERE u.id_usuario = ?";

$stmt_get = $mysqli->prepare($sql_get);
if ($stmt_get === false) {
    die("Error al preparar la consulta para obtener datos del doctor: " . $mysqli->error);
}
$stmt_get->bind_param("i", $id_usuario);
$stmt_get->execute();
$doctor = $stmt_get->get_result()->fetch_assoc();
$stmt_get->close();

if (!$doctor) {
    die("Error al cargar los datos del doctor.");
}

// Obtener todas las especialidades para el menú desplegable
$especialidades = $mysqli->query("SELECT id_especialidad, nombre_especialidad FROM especialidades ORDER BY nombre_especialidad");
?>

<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<h1>Editar Perfil de Doctor</h1>
<p>Mantén tu información personal y profesional actualizada.</p>

<?php if ($mensaje): ?>
    <div class="alert alert-<?php echo $tipo_mensaje; ?>"><?php echo $mensaje; ?></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($doctor['nombre']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="apellido" class="form-label">Apellido</label>
                    <input type="text" class="form-control" id="apellido" name="apellido" value="<?php echo htmlspecialchars($doctor['apellido']); ?>" required>
                </div>
            </div>
            <div class="mb-3">
                 <label for="correo" class="form-label">Correo Electrónico</label>
                 <input type="email" class="form-control" id="correo" value="<?php echo htmlspecialchars($doctor['correo']); ?>" disabled readonly>
                 <small class="form-text text-muted">El correo electrónico no se puede cambiar.</small>
            </div>
            
            <hr>
            <h5 class="mt-4">Información Profesional</h5>
            <div class="mb-3">
                <label for="id_especialidad" class="form-label">Especialidad</label>
                <select class="form-select" id="id_especialidad" name="id_especialidad" required>
                    <option value="">-- Selecciona una especialidad --</option>
                    <?php if ($especialidades->num_rows > 0): ?>
                        <?php while($esp = $especialidades->fetch_assoc()): ?>
                            <option value="<?php echo $esp['id_especialidad']; ?>" <?php echo ($esp['id_especialidad'] == $doctor['id_especialidad']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($esp['nombre_especialidad']); ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            <a href="<?php echo BASE_URL; ?>doctor/index.php" class="btn btn-secondary">Volver al Dashboard</a>
        </form>
    </div>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>