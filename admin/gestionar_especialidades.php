<?php
require_once __DIR__ . '/../includes/config.php';
verificar_rol('administrador');
$mensaje = '';
$tipo_mensaje = '';

// --- LÓGICA PARA PROCESAR FORMULARIOS (sin cambios) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ... Tu código de manejo de POST se mantiene igual ...
    $action = $_POST['action'] ?? '';

    // Acción para agregar nueva especialidad
    if ($action == 'agregar' && !empty($_POST['nombre_especialidad'])) {
        $nombre = trim($_POST['nombre_especialidad']);
        $stmt = $mysqli->prepare("INSERT INTO especialidades (nombre_especialidad) VALUES (?)");
        $stmt->bind_param("s", $nombre);
        if ($stmt->execute()) {
            $mensaje = 'Especialidad agregada correctamente.';
            $tipo_mensaje = 'success';
        } else {
            $mensaje = $mysqli->errno === 1062 ? 'Error: Esa especialidad ya existe.' : 'Error al agregar la especialidad.';
            $tipo_mensaje = 'danger';
        }
        $stmt->close();
    }

    // Acción para editar el nombre de una especialidad
    if ($action == 'editar' && !empty($_POST['id_especialidad']) && !empty($_POST['nombre_especialidad'])) {
        $id = $_POST['id_especialidad'];
        $nombre = trim($_POST['nombre_especialidad']);
        $stmt = $mysqli->prepare("UPDATE especialidades SET nombre_especialidad = ? WHERE id_especialidad = ?");
        $stmt->bind_param("si", $nombre, $id);
        if ($stmt->execute()) {
            $mensaje = 'Especialidad actualizada correctamente.';
            $tipo_mensaje = 'success';
        } else {
            $mensaje = $mysqli->errno === 1062 ? 'Error: Ese nombre de especialidad ya existe.' : 'Error al actualizar.';
            $tipo_mensaje = 'danger';
        }
        $stmt->close();
    }

    // Acción para cambiar el estado (activar/desactivar)
    if ($action == 'cambiar_estado' && !empty($_POST['id_especialidad'])) {
        $id = $_POST['id_especialidad'];
        $estado_actual = $mysqli->query("SELECT estado FROM especialidades WHERE id_especialidad = $id")->fetch_assoc()['estado'];
        $nuevo_estado = ($estado_actual == 'activo') ? 'inactivo' : 'activo';
        
        $stmt = $mysqli->prepare("UPDATE especialidades SET estado = ? WHERE id_especialidad = ?");
        $stmt->bind_param("si", $nuevo_estado, $id);
        if ($stmt->execute()) {
            $mensaje = 'Estado de la especialidad actualizado.';
            $tipo_mensaje = 'success';
        } else {
            $mensaje = 'Error al cambiar el estado.';
            $tipo_mensaje = 'danger';
        }
        $stmt->close();
    }
}

// Obtener todas las especialidades para mostrarlas en la lista
$especialidades = $mysqli->query("SELECT * FROM especialidades ORDER BY nombre_especialidad");

// --- NUEVO: Obtener todos los doctores y agruparlos por especialidad ---
$doctores_por_especialidad = [];
$query_doctores = "SELECT u.nombre, u.apellido, d.id_especialidad 
                   FROM usuarios u 
                   JOIN doctores d ON u.id_usuario = d.id_usuario 
                   WHERE u.rol = 'doctor' AND u.estado = 'activo'";
$resultado_doctores = $mysqli->query($query_doctores);
while ($doc = $resultado_doctores->fetch_assoc()) {
    $doctores_por_especialidad[$doc['id_especialidad']][] = $doc['nombre'] . ' ' . $doc['apellido'];
}

require_once ROOT_PATH . '/includes/header.php';
?>

<h1>Gestionar Especialidades</h1>
<?php if ($mensaje): ?>
    <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($mensaje); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-5 mb-4">
        <div class="card shadow-sm">
            <div class="card-header fw-bold">Agregar Nueva Especialidad</div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="agregar">
                    <div class="mb-3">
                        <label for="nombre_especialidad" class="form-label">Nombre de la Especialidad</label>
                        <input type="text" id="nombre_especialidad" name="nombre_especialidad" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Agregar</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header fw-bold">Lista de Especialidades y Doctores Asociados</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nombre y Doctores Asignados</th>
                            <th class="text-center">Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($especialidades->num_rows > 0): ?>
                            <?php while ($e = $especialidades->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($e['nombre_especialidad']); ?></strong>
                                        <?php 
                                        // Verificar si hay doctores para esta especialidad
                                        if (isset($doctores_por_especialidad[$e['id_especialidad']])): ?>
                                            <ul class="list-unstyled small mt-2 mb-0">
                                            <?php foreach ($doctores_por_especialidad[$e['id_especialidad']] as $nombre_doctor): ?>
                                                <li><i class="bi bi-person-fill me-1"></i><?php echo htmlspecialchars($nombre_doctor); ?></li>
                                            <?php endforeach; ?>
                                            </ul>
                                        <?php else: ?>
                                            <p class="small text-muted mt-2 mb-0">No hay doctores asignados.</p>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?php echo $e['estado'] == 'activo' ? 'bg-success' : 'bg-secondary'; ?>">
                                            <?php echo ucfirst($e['estado']); ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEditar" data-id="<?php echo $e['id_especialidad']; ?>" data-nombre="<?php echo htmlspecialchars($e['nombre_especialidad']); ?>">
                                            Editar
                                        </button>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="cambiar_estado">
                                            <input type="hidden" name="id_especialidad" value="<?php echo $e['id_especialidad']; ?>">
                                            <button type="submit" class="btn btn-sm <?php echo $e['estado'] == 'activo' ? 'btn-danger' : 'btn-success'; ?>">
                                                <?php echo $e['estado'] == 'activo' ? 'Desactivar' : 'Activar'; ?>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted">No hay especialidades registradas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
    </div>

<script>
    // ... tu script se mantiene igual ...
</script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>