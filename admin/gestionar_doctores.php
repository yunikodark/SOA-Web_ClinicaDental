<?php
require_once __DIR__ . '/../includes/config.php';
verificar_rol('administrador');

// Usamos estas variables para mostrar mensajes de éxito o error.
// Mantenemos el sistema de mensajes de sesión para errores generales
$mensaje = $_SESSION['mensaje'] ?? null;
$tipo_mensaje = $_SESSION['tipo_mensaje'] ?? null;
unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);

// Nueva variable para controlar el SweetAlert de éxito de AGREGAR DOCTOR
$doctor_agregado_exito = false; 

$errors = []; // Inicializar array de errores para el formulario de agregar

// --- LÓGICA PARA PROCESAR FORMULARIOS ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    // Acción para AGREGAR un nuevo doctor
    if ($action == 'agregar_doctor') {
        $nombre = trim($_POST['nombre']);
        $apellido = trim($_POST['apellido']);
        $correo = trim($_POST['correo']);
        $password = $_POST['password'];
        $id_especialidad = $_POST['id_especialidad'];
        $celular = trim($_POST['celular'] ?? '');
        $dni = trim($_POST['dni'] ?? '');

        // *** VALIDACIONES para el nuevo doctor ***
        if (empty($nombre)) {
            $errors['nombre'] = "El nombre es obligatorio.";
        }
        if (empty($apellido)) {
            $errors['apellido'] = "El apellido es obligatorio.";
        }
        if (empty($correo)) {
            $errors['correo'] = "El correo es obligatorio.";
        } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $errors['correo'] = "Formato de correo inválido.";
        } else {
            // Validar que el correo no exista
            $stmt_check_correo = $mysqli->prepare("SELECT id_usuario FROM usuarios WHERE correo = ?");
            $stmt_check_correo->bind_param("s", $correo);
            $stmt_check_correo->execute();
            $resultado_check_correo = $stmt_check_correo->get_result();
            if ($resultado_check_correo->num_rows > 0) {
                $errors['correo'] = "Este correo electrónico ya está registrado.";
            }
            $stmt_check_correo->close();
        }

        if (empty($password)) {
            $errors['password'] = "La contraseña es obligatoria.";
        } elseif (strlen($password) < 6) {
            $errors['password'] = "La contraseña debe tener al menos 6 caracteres.";
        }

        if (empty($id_especialidad)) {
            $errors['id_especialidad'] = "La especialidad es obligatoria.";
        }

        // Validación para Celular (9 dígitos numéricos)
        if (empty($celular)) {
            $errors['celular'] = "El celular es obligatorio.";
        } elseif (!ctype_digit($celular) || strlen($celular) !== 9) {
            $errors['celular'] = "El celular debe contener exactamente 9 dígitos numéricos.";
        }

        // Validación para DNI (8 dígitos numéricos)
        if (empty($dni)) {
            $errors['dni'] = "El DNI es obligatorio.";
        } elseif (!ctype_digit($dni) || strlen($dni) !== 8) {
            $errors['dni'] = "El DNI debe contener exactamente 8 dígitos numéricos.";
        } else {
            // Verificar si el DNI ya existe en la tabla usuarios
            $stmt_check_dni = $mysqli->prepare("SELECT id_usuario FROM usuarios WHERE dni = ?");
            $stmt_check_dni->bind_param("s", $dni);
            $stmt_check_dni->execute();
            $resultado_check_dni = $stmt_check_dni->get_result();
            if ($resultado_check_dni->num_rows > 0) {
                $errors['dni'] = "Este DNI ya está registrado por otro usuario.";
            }
            $stmt_check_dni->close();
        }

        // Si no hay errores de validación, proceder con la inserción
        if (empty($errors)) {
            $mysqli->begin_transaction();
            try {
                // 3. Insertar en la tabla 'usuarios' con los nuevos campos
                $sql_user = "INSERT INTO usuarios (nombre, apellido, celular, dni, correo, password, rol) VALUES (?, ?, ?, ?, ?, ?, 'doctor')";
                $stmt_user = $mysqli->prepare($sql_user);
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt_user->bind_param("ssssss", $nombre, $apellido, $celular, $dni, $correo, $hashed_password);
                $stmt_user->execute();
                $id_usuario_nuevo = $stmt_user->insert_id;
                $stmt_user->close();

                // 4. Insertar en la tabla 'doctores'
                $sql_doc = "INSERT INTO doctores (id_usuario, id_especialidad) VALUES (?, ?)";
                $stmt_doc = $mysqli->prepare($sql_doc);
                $stmt_doc->bind_param("ii", $id_usuario_nuevo, $id_especialidad);
                $stmt_doc->execute();
                $stmt_doc->close();

                $mysqli->commit();
                // ¡CAMBIO CLAVE AQUÍ! Establecer la bandera de éxito para el SweetAlert
                $doctor_agregado_exito = true; 
                // No hay redirección directa aquí, el JS manejará el SweetAlert

            } catch (Exception $e) {
                $mysqli->rollback();
                $_SESSION['mensaje'] = 'Error al agregar el doctor: ' . $e->getMessage();
                $_SESSION['tipo_mensaje'] = 'danger';
                // Redirigir en caso de error para mostrar el mensaje de sesión
                header('Location: ' . BASE_URL . 'admin/doctores.php');
                exit;
            }
        } else {
            // Si hay errores de validación, se establecen los mensajes para la alerta general
            $_SESSION['mensaje'] = 'Por favor, corrige los errores en el formulario de agregar doctor.';
            $_SESSION['tipo_mensaje'] = 'danger';
            // No redirigimos aquí para que los campos del formulario queden con los valores y errores
        }
    }
    
    // Acción para CAMBIAR ESTADO (activar/desactivar) del doctor
    if ($action == 'cambiar_estado_doctor' && !empty($_POST['id_usuario'])) {
        $id_usuario_cambio = $_POST['id_usuario'];

        $stmt_estado = $mysqli->prepare("SELECT estado FROM usuarios WHERE id_usuario = ? AND rol = 'doctor'");
        $stmt_estado->bind_param("i", $id_usuario_cambio);
        $stmt_estado->execute();
        $result = $stmt_estado->get_result();
        
        if ($result->num_rows > 0) {
            $estado_actual = $result->fetch_assoc()['estado'];
            $nuevo_estado = ($estado_actual == 'activo') ? 'inactivo' : 'activo';
            
            $stmt_update = $mysqli->prepare("UPDATE usuarios SET estado = ? WHERE id_usuario = ?");
            $stmt_update->bind_param("si", $nuevo_estado, $id_usuario_cambio);
            if ($stmt_update->execute()) {
                $_SESSION['mensaje'] = 'Estado del doctor actualizado correctamente.';
                $_SESSION['tipo_mensaje'] = 'success';
            } else {
                $_SESSION['mensaje'] = 'Error al actualizar el estado del doctor.';
                $_SESSION['tipo_mensaje'] = 'danger';
            }
            $stmt_update->close();
        } else {
            $_SESSION['mensaje'] = 'Doctor no encontrado.';
            $_SESSION['tipo_mensaje'] = 'danger';
        }
        $stmt_estado->close();
        header('Location: ' . BASE_URL . 'admin/gestionar_doctores.php'); 
        exit;
    }
}

// Obtener la lista completa de doctores para la tabla
$query_doctores = "SELECT u.id_usuario, u.nombre, u.apellido, u.correo, u.celular, u.dni, u.estado, e.nombre_especialidad 
                   FROM usuarios u 
                   LEFT JOIN doctores d ON u.id_usuario = d.id_usuario 
                   LEFT JOIN especialidades e ON d.id_especialidad = e.id_especialidad 
                   WHERE u.rol = 'doctor' 
                   ORDER BY u.apellido, u.nombre";
$doctores = $mysqli->query($query_doctores);

// Obtener solo las especialidades activas para el formulario de agregar
$especialidades = $mysqli->query("SELECT * FROM especialidades WHERE estado = 'activo' ORDER BY nombre_especialidad");

require_once ROOT_PATH . '/includes/header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<h1>Gestionar Doctores</h1>
<?php if ($mensaje): ?>
    <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($mensaje); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-5 mb-4">
           <div class="card shadow-sm">
               <div class="card-header fw-bold">Agregar Nuevo Doctor</div>
               <div class="card-body">
                    <form method="POST" novalidate>
                        <input type="hidden" name="action" value="agregar_doctor">
                        
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" name="nombre" id="nombre" class="form-control <?php echo (isset($errors['nombre'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>" required>
                            <?php if(isset($errors['nombre'])): ?><div class="invalid-feedback"><?php echo $errors['nombre']; ?></div><?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label for="apellido" class="form-label">Apellido</label>
                            <input type="text" name="apellido" id="apellido" class="form-control <?php echo (isset($errors['apellido'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($_POST['apellido'] ?? ''); ?>" required>
                            <?php if(isset($errors['apellido'])): ?><div class="invalid-feedback"><?php echo $errors['apellido']; ?></div><?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="celular" class="form-label">Celular</label>
                            <input type="tel" name="celular" id="celular" class="form-control <?php echo (isset($errors['celular'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($_POST['celular'] ?? ''); ?>" required pattern="[0-9]{9}" title="Debe contener exactamente 9 dígitos numéricos" maxlength="9">
                            <?php if(isset($errors['celular'])): ?><div class="invalid-feedback"><?php echo $errors['celular']; ?></div><?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label for="dni" class="form-label">DNI</label>
                            <input type="text" name="dni" id="dni" class="form-control <?php echo (isset($errors['dni'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($_POST['dni'] ?? ''); ?>" required pattern="[0-9]{8}" title="Debe contener exactamente 8 dígitos numéricos" maxlength="8">
                            <?php if(isset($errors['dni'])): ?><div class="invalid-feedback"><?php echo $errors['dni']; ?></div><?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="correo" class="form-label">Correo Electrónico</label>
                            <input type="email" name="correo" id="correo" class="form-control <?php echo (isset($errors['correo'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($_POST['correo'] ?? ''); ?>" required>
                            <?php if(isset($errors['correo'])): ?><div class="invalid-feedback"><?php echo $errors['correo']; ?></div><?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña Provisional</label>
                            <input type="password" name="password" id="password" class="form-control <?php echo (isset($errors['password'])) ? 'is-invalid' : ''; ?>" required>
                            <?php if(isset($errors['password'])): ?><div class="invalid-feedback"><?php echo $errors['password']; ?></div><?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label for="id_especialidad" class="form-label">Especialidad</label>
                            <select name="id_especialidad" id="id_especialidad" class="form-select <?php echo (isset($errors['id_especialidad'])) ? 'is-invalid' : ''; ?>" required>
                                <option value="">-- Seleccionar Especialidad --</option>
                                <?php 
                                $selected_especialidad = $_POST['id_especialidad'] ?? ''; 
                                while($e = $especialidades->fetch_assoc()) {
                                    $selected = ($e['id_especialidad'] == $selected_especialidad) ? 'selected' : '';
                                    echo "<option value='{$e['id_especialidad']}' {$selected}>".htmlspecialchars($e['nombre_especialidad'])."</option>";
                                }
                                $especialidades->data_seek(0); // Restablecer el puntero para la tabla
                                ?>
                            </select>
                            <?php if(isset($errors['id_especialidad'])): ?><div class="invalid-feedback"><?php echo $errors['id_especialidad']; ?></div><?php endif; ?>
                        </div>
                        <button type="submit" class="btn btn-primary">Agregar Doctor</button>
                    </form>
               </div>
           </div>
    </div>
    
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header fw-bold">Lista de Doctores</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Nombre Completo</th>
                                <th>Especialidad</th>
                                <th>Contacto</th> 
                                <th class="text-center">Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($doctores && $doctores->num_rows > 0): ?>
                                <?php while ($doc = $doctores->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <?php echo htmlspecialchars($doc['nombre'] . ' ' . $doc['apellido']); ?>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($doc['correo']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($doc['nombre_especialidad'] ?? 'No asignada'); ?></td>
                                        <td>
                                            <small class="text-muted">Cel: <?php echo htmlspecialchars($doc['celular'] ?? 'N/A'); ?></small><br>
                                            <small class="text-muted">DNI: <?php echo htmlspecialchars($doc['dni'] ?? 'N/A'); ?></small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge <?php echo $doc['estado'] == 'activo' ? 'bg-success' : 'bg-secondary'; ?>">
                                                <?php echo ucfirst($doc['estado']); ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="editar_doctor.php?id=<?php echo $doc['id_usuario']; ?>" class="btn btn-sm btn-warning">Editar</a>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="cambiar_estado_doctor">
                                                <input type="hidden" name="id_usuario" value="<?php echo $doc['id_usuario']; ?>">
                                                <button type="submit" class="btn btn-sm <?php echo $doc['estado'] == 'activo' ? 'btn-danger' : 'btn-info'; ?>">
                                                    <?php echo $doc['estado'] == 'activo' ? 'Desactivar' : 'Activar'; ?>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No hay doctores registrados.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const celularInput = document.getElementById('celular');
    const dniInput = document.getElementById('dni');

    function restrictToNumbersAndLength(inputElement, maxLength) {
        if (!inputElement) return;

        inputElement.addEventListener('input', function(event) {
            let value = this.value;
            value = value.replace(/[^0-9]/g, ''); 
            if (value.length > maxLength) {
                value = value.slice(0, maxLength);
            }
            this.value = value;
        });

        inputElement.addEventListener('paste', function(event) {
            event.preventDefault(); 
            let paste = (event.clipboardData || window.clipboardData).getData('text');
            paste = paste.replace(/[^0-9]/g, ''); 
            
            const currentSelectionStart = this.selectionStart;
            const currentSelectionEnd = this.selectionEnd;
            const oldValue = this.value;
            
            let newValue = oldValue.substring(0, currentSelectionStart) + paste + oldValue.substring(currentSelectionEnd);
            if (newValue.length > maxLength) {
                newValue = newValue.slice(0, maxLength);
            }
            this.value = newValue;

            const newCursorPosition = currentSelectionStart + Math.min(paste.length, maxLength - oldValue.length + (currentSelectionEnd - currentSelectionStart));
            this.setSelectionRange(newCursorPosition, newCursorPosition);
        });
    }

    // Aplicar la restricción a los campos de celular y DNI en el formulario "Agregar Nuevo Doctor"
    restrictToNumbersAndLength(celularInput, 9);
    restrictToNumbersAndLength(dniInput, 8);


    // --- Lógica del SweetAlert para "Doctor Agregado" ---
    const doctorAgregadoConExito = <?php echo json_encode($doctor_agregado_exito); ?>;
    const gestionarDoctoresUrl = '<?php echo BASE_URL; ?>admin/gestionar_doctores.php'; // Para seguir registrando
    const adminDashboardUrl = '<?php echo BASE_URL; ?>admin/index.php';       // Para "Más tarde"

    if (doctorAgregadoConExito) {
        Swal.fire({
            title: '¡Doctor Agregado con Éxito!',
            text: '¿Qué deseas hacer ahora?',
            icon: 'success',
            showCancelButton: true,
            confirmButtonColor: '#28a745', // Verde
            cancelButtonColor: '#007bff', // Azul
            confirmButtonText: 'Seguir Registrando',
            cancelButtonText: 'Más Tarde',
            reverseButtons: true // Invierte el orden de los botones
        }).then((result) => {
            if (result.isConfirmed) {
                // Si el usuario elige "Seguir Registrando", recargar la página actual
                window.location.href = gestionarDoctoresUrl;
            } else {
                // Si el usuario elige "Más Tarde" o cierra el modal
                window.location.href = adminDashboardUrl;
            }
        });
    }
});
</script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>