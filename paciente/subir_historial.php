<?php
require_once __DIR__ . '/../includes/config.php';
verificar_rol('paciente');

$id_usuario = $_SESSION['id_usuario'];
$stmt_paciente = $mysqli->prepare("SELECT id_paciente FROM pacientes WHERE id_usuario = ?");
$stmt_paciente->bind_param("i", $id_usuario);
$stmt_paciente->execute();
$id_paciente = $stmt_paciente->get_result()->fetch_assoc()['id_paciente'];
$stmt_paciente->close();

$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["archivo_historial"])) {
    // Verificar si no hay errores en la subida
    if ($_FILES["archivo_historial"]["error"] === UPLOAD_ERR_OK) {
        $directorio_subida = ROOT_PATH . '/uploads/historiales_pacientes/';
        
        if (!is_dir($directorio_subida)) {
            mkdir($directorio_subida, 0777, true);
        }

        $nombre_archivo_original = basename($_FILES["archivo_historial"]["name"]);
        $extension_archivo = strtolower(pathinfo($nombre_archivo_original, PATHINFO_EXTENSION));
        $nombre_archivo_seguro = time() . '_' . uniqid() . '.' . $extension_archivo;
        
        $archivo_destino = $directorio_subida . $nombre_archivo_seguro;
        $ruta_bd = 'uploads/historiales_pacientes/' . $nombre_archivo_seguro;
        
        // Validaciones
        $permitidos = ['jpg', 'jpeg', 'png', 'pdf'];
        if (!in_array($extension_archivo, $permitidos)) {
            $mensaje = 'Solo se permiten archivos JPG, JPEG, PNG y PDF.';
            $tipo_mensaje = 'danger';
        } elseif ($_FILES["archivo_historial"]["size"] > 5000000) { // Límite de 5MB
            $mensaje = 'El archivo es demasiado grande (máximo 5MB).';
            $tipo_mensaje = 'danger';
        } else {
            if (move_uploaded_file($_FILES["archivo_historial"]["tmp_name"], $archivo_destino)) {
                $sql_insert = "INSERT INTO historial_medico (id_paciente, archivo_historial) VALUES (?, ?)";
                $stmt_insert = $mysqli->prepare($sql_insert);
                $stmt_insert->bind_param("is", $id_paciente, $ruta_bd);
                if ($stmt_insert->execute()) {
                    $mensaje = 'Archivo subido y registrado con éxito.';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = 'Error al guardar la información en la base de datos.';
                    $tipo_mensaje = 'danger';
                }
                $stmt_insert->close();
            } else {
                $mensaje = 'Hubo un error al mover el archivo subido.';
                $tipo_mensaje = 'danger';
            }
        }
    } else {
        $mensaje = 'Error al subir el archivo. Código: ' . $_FILES["archivo_historial"]["error"];
        $tipo_mensaje = 'danger';
    }
}

// --- OBTENER HISTORIALES (CÓDIGO CORREGIDO) ---
// Obtener historiales ya subidos por el paciente usando el método compatible
$sql_select = "SELECT archivo_historial, fecha_subida FROM historial_medico WHERE id_paciente = ? ORDER BY fecha_subida DESC";
$stmt_select = $mysqli->prepare($sql_select);
$stmt_select->bind_param("i", $id_paciente);
$stmt_select->execute();
$historiales = $stmt_select->get_result();

?>
<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<h1>Subir Historial Médico</h1>
<p>Puedes subir documentos importantes (PDF) o imágenes (JPG, PNG) de tu historial médico para que el doctor pueda revisarlos.</p>

<?php if ($mensaje): ?>
    <div class="alert alert-<?php echo $tipo_mensaje; ?>"><?php echo $mensaje; ?></div>
<?php endif; ?>

<div class="card mb-4 shadow-sm">
    <div class="card-body">
        <form action="subir_historial.php" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="archivo_historial" class="form-label">Selecciona el archivo (Máx 5MB)</label>
                <input class="form-control" type="file" name="archivo_historial" id="archivo_historial" required>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i> Subir Archivo</button>
        </form>
    </div>
</div>

<h3>Mis Archivos Subidos</h3>
<div class="list-group">
    <?php if ($historiales && $historiales->num_rows > 0): ?>
        <?php while($historial = $historiales->fetch_assoc()): ?>
            <a href="<?php echo BASE_URL . htmlspecialchars($historial['archivo_historial']); ?>" target="_blank" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                <span>
                    <i class="bi bi-file-earmark-text me-2"></i>
                    <?php echo basename($historial['archivo_historial']); ?>
                </span>
                <span class="badge bg-secondary rounded-pill"><?php echo date('d/m/Y H:i', strtotime($historial['fecha_subida'])); ?></span>
            </a>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="text-muted">Aún no has subido ningún archivo de historial.</p>
    <?php endif; ?>
    <?php $stmt_select->close(); // Cerramos el statement después de usarlo ?>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>