<?php
// Habilitar la visualización de errores (QUITAR EN PRODUCCIÓN)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/config.php';
verificar_rol('administrador');

$id_usuario = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_usuario) {
    $_SESSION['mensaje'] = 'Error: ID de doctor no válido.';
    $_SESSION['tipo_mensaje'] = 'danger';
    redirigir(BASE_URL . 'admin/doctores.php');
}

$errors = [];
$doctor_editado_exito = false;

// Inicializar $doctor con valores predeterminados para evitar "Undefined variable" en el HTML
// Esto asegura que $doctor siempre sea un array, incluso si las consultas fallan o no hay datos.
$doctor = [
    'nombre' => '',
    'apellido' => '',
    'correo' => '',
    'celular' => '',
    'dni' => '',
    'id_especialidad' => ''
];

// --- Lógica para procesar la actualización del doctor ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recoger datos del formulario
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $correo = trim($_POST['correo']);
    $celular = trim($_POST['celular'] ?? '');
    $dni = trim($_POST['dni'] ?? '');
    $id_especialidad = $_POST['id_especialidad'];
    $password = $_POST['password'];

    // *** VALIDACIONES ***
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
        $stmt_check_correo = $mysqli->prepare("SELECT id_usuario FROM usuarios WHERE correo = ? AND id_usuario != ?");
        $stmt_check_correo->bind_param("si", $correo, $id_usuario);
        $stmt_check_correo->execute();
        $resultado_check_correo = $stmt_check_correo->get_result();
        if ($resultado_check_correo->num_rows > 0) {
            $errors['correo'] = "Este correo electrónico ya está registrado por otro usuario.";
        }
        $stmt_check_correo->close();
    }

    if (empty($celular)) {
        $errors['celular'] = "El celular es obligatorio.";
    } elseif (!ctype_digit($celular) || strlen($celular) !== 9) {
        $errors['celular'] = "El celular debe contener exactamente 9 dígitos numéricos.";
    }

    if (empty($dni)) {
        $errors['dni'] = "El DNI es obligatorio.";
    } elseif (!ctype_digit($dni) || strlen($dni) !== 8) {
        $errors['dni'] = "El DNI debe contener exactamente 8 dígitos numéricos.";
    } else {
        $stmt_check_dni = $mysqli->prepare("SELECT id_usuario FROM usuarios WHERE dni = ? AND id_usuario != ?");
        $stmt_check_dni->bind_param("si", $dni, $id_usuario);
        $stmt_check_dni->execute();
        $resultado_check_dni = $stmt_check_dni->get_result();
        if ($resultado_check_dni->num_rows > 0) {
            $errors['dni'] = "Este DNI ya está registrado por otro usuario.";
        }
        $stmt_check_dni->close();
    }

    if (empty($id_especialidad)) {
        $errors['id_especialidad'] = "La especialidad es obligatoria.";
    }

    if (!empty($password) && strlen($password) < 6) {
        $errors['password'] = "La nueva contraseña debe tener al menos 6 caracteres.";
    }

    // Si no hay errores de validación, proceder con la transacción
    if (empty($errors)) {
        $mysqli->begin_transaction();
        try {
            // Construcción dinámica del UPDATE y bind_param para 'usuarios'
            $sql_user_parts = ["nombre = ?", "apellido = ?", "correo = ?", "celular = ?", "dni = ?"];
            $bind_types = "sssss";
            // Asegurarse de que las variables usadas en bind_param sean L-values (referencias)
            $bind_values = [&$nombre, &$apellido, &$correo, &$celular, &$dni]; 

            if (!empty($password)) {
                $sql_user_parts[] = "password = ?";
                $bind_types .= "s";
                $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Hash de la contraseña
                $bind_values[] = &$hashed_password;
            }
            
            $sql_user = "UPDATE usuarios SET " . implode(", ", $sql_user_parts) . " WHERE id_usuario = ?";
            $bind_types .= "i";
            $bind_values[] = &$id_usuario; // El ID de usuario también es una referencia al final

            $stmt_user = $mysqli->prepare($sql_user);
            
            // Usar '...' para desempaquetar el array de valores como argumentos individuales
            // Esto es lo que permite que 'bind_param' reciba referencias dinámicamente.
            if ($stmt_user === false) {
                 throw new Exception("Error al preparar la consulta de usuario: " . $mysqli->error);
            }
            $stmt_user->bind_param($bind_types, ...$bind_values);
            $stmt_user->execute();
            $stmt_user->close();

            // Actualizar la tabla 'doctores'
            $stmt_doc = $mysqli->prepare("UPDATE doctores SET id_especialidad = ? WHERE id_usuario = ?");
            if ($stmt_doc === false) {
                throw new Exception("Error al preparar la consulta de doctor: " . $mysqli->error);
            }
            $stmt_doc->bind_param("ii", $id_especialidad, $id_usuario);
            $stmt_doc->execute();
            $stmt_doc->close();
            
            $mysqli->commit();
            $doctor_editado_exito = true; 

        } catch (Exception $e) {
            $mysqli->rollback();
            $_SESSION['mensaje'] = 'Error al actualizar el doctor: ' . $e->getMessage();
            $_SESSION['tipo_mensaje'] = 'danger';
            header('Location: ' . BASE_URL . 'admin/doctores.php'); 
            exit;
        }
    } else {
        // Si hay errores de validación, rellenar $doctor con los datos del POST
        // para que el formulario los muestre.
        $doctor['nombre'] = $nombre;
        $doctor['apellido'] = $apellido;
        $doctor['correo'] = $correo;
        $doctor['celular'] = $celular;
        $doctor['dni'] = $dni;
        $doctor['id_especialidad'] = $id_especialidad;
        // No es necesario establecer la contraseña aquí, ya que no se muestra en el campo.

        // Establecer el mensaje de error general para la alerta de Bootstrap
        $_SESSION['mensaje'] = 'Por favor, corrige los errores en el formulario.';
        $_SESSION['tipo_mensaje'] = 'danger';
        // Aquí no hay redirección, el script continúa para mostrar el formulario con errores
    }
}

// --- Lógica para obtener los datos actuales y mostrarlos en el formulario (GET o POST con errores) ---
// La consulta se hace siempre para obtener los datos más recientes o los que se intentaron enviar.
$query_doctor = "SELECT u.nombre, u.apellido, u.correo, u.celular, u.dni, d.id_especialidad 
                 FROM usuarios u 
                 JOIN doctores d ON u.id_usuario = d.id_usuario 
                 WHERE u.id_usuario = ? AND u.rol = 'doctor'";
$stmt_doc_get = $mysqli->prepare($query_doctor); // Renombrado a $stmt_doc_get para evitar conflicto
if ($stmt_doc_get === false) {
    die("Error al preparar la consulta para obtener datos del doctor: " . $mysqli->error);
}
$stmt_doc_get->bind_param("i", $id_usuario);
$stmt_doc_get->execute();
$doctor_from_db = $stmt_doc_get->get_result()->fetch_assoc();
$stmt_doc_get->close(); // Cerrar el statement

// Si no se encontró el doctor en la BD, redirigir
if (!$doctor_from_db) {
    $_SESSION['mensaje'] = 'Error: Doctor no encontrado o rol incorrecto.';
    $_SESSION['tipo_mensaje'] = 'danger';
    redirigir(BASE_URL . 'admin/doctores.php');
}

// Combinar los datos: los datos de POST (si existen y hay errores) tienen prioridad,
// de lo contrario, se usan los datos de la base de datos.
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($errors)) {
    // Si hubo un POST y errores, $doctor ya fue poblado con los valores de $_POST
    // No hacemos array_merge aquí para evitar sobrescribir los valores de $_POST con los de la BD
    // (a menos que quieras una lógica más compleja de fusión si un campo de POST está vacío)
} else {
    // Si es GET o si el POST fue exitoso (y $doctor_editado_exito es true),
    // usamos los datos de la base de datos (que serán los actualizados si el POST fue exitoso).
    $doctor = $doctor_from_db;
}

// Obtener todas las especialidades activas para el dropdown
$especialidades = $mysqli->query("SELECT id_especialidad, nombre_especialidad FROM especialidades WHERE estado = 'activo' ORDER BY nombre_especialidad");

// Recuperar mensajes de sesión (si es que no se manejó en el POST)
$mensaje = $_SESSION['mensaje'] ?? null;
$tipo_mensaje = $_SESSION['tipo_mensaje'] ?? null;
unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);

require_once ROOT_PATH . '/includes/header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<h1>Editar Doctor</h1>
<p>
    <a href="doctores.php" class="btn btn-secondary">
        &larr; Volver a la lista
    </a>
</p>

<?php if ($mensaje): ?>
    <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($mensaje); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header fw-bold">
        Editando a: <?php echo htmlspecialchars($doctor['nombre'] . ' ' . $doctor['apellido']); ?>
    </div>
    <div class="card-body">
        <form method="POST" novalidate>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" id="nombre" name="nombre" class="form-control <?php echo (isset($errors['nombre'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($doctor['nombre']); ?>" required>
                    <?php if(isset($errors['nombre'])): ?><div class="invalid-feedback"><?php echo $errors['nombre']; ?></div><?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="apellido" class="form-label">Apellido</label>
                    <input type="text" id="apellido" name="apellido" class="form-control <?php echo (isset($errors['apellido'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($doctor['apellido']); ?>" required>
                    <?php if(isset($errors['apellido'])): ?><div class="invalid-feedback"><?php echo $errors['apellido']; ?></div><?php endif; ?>
                </div>
            </div>
            <div class="mb-3">
                <label for="correo" class="form-label">Correo Electrónico</label>
                <input type="email" id="correo" name="correo" class="form-control <?php echo (isset($errors['correo'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($doctor['correo']); ?>" required>
                <?php if(isset($errors['correo'])): ?><div class="invalid-feedback"><?php echo $errors['correo']; ?></div><?php endif; ?>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="celular" class="form-label">Celular</label>
                    <input type="tel" id="celular" name="celular" class="form-control <?php echo (isset($errors['celular'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($doctor['celular'] ?? ''); ?>" required pattern="[0-9]{9}" title="Debe contener exactamente 9 dígitos numéricos" maxlength="9">
                    <?php if(isset($errors['celular'])): ?><div class="invalid-feedback"><?php echo $errors['celular']; ?></div><?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="dni" class="form-label">DNI</label>
                    <input type="text" id="dni" name="dni" class="form-control <?php echo (isset($errors['dni'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($doctor['dni'] ?? ''); ?>" required pattern="[0-9]{8}" title="Debe contener exactamente 8 dígitos numéricos" maxlength="8">
                    <?php if(isset($errors['dni'])): ?><div class="invalid-feedback"><?php echo $errors['dni']; ?></div><?php endif; ?>
                </div>
            </div>

            <div class="mb-3">
                <label for="id_especialidad" class="form-label">Especialidad</label>
                <select id="id_especialidad" name="id_especialidad" class="form-select <?php echo (isset($errors['id_especialidad'])) ? 'is-invalid' : ''; ?>" required>
                    <option value="">-- Seleccionar --</option>
                    <?php while ($e = $especialidades->fetch_assoc()): ?>
                        <option value="<?php echo $e['id_especialidad']; ?>" <?php echo ($e['id_especialidad'] == $doctor['id_especialidad']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($e['nombre_especialidad']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <?php if(isset($errors['id_especialidad'])): ?><div class="invalid-feedback"><?php echo $errors['id_especialidad']; ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Nueva Contraseña</label>
                <input type="password" id="password" name="password" class="form-control <?php echo (isset($errors['password'])) ? 'is-invalid' : ''; ?>">
                <small class="form-text text-muted">Dejar en blanco para no cambiar la contraseña actual.</small>
                <?php if(isset($errors['password'])): ?><div class="invalid-feedback"><?php echo $errors['password']; ?></div><?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </form>
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

    restrictToNumbersAndLength(celularInput, 9);
    restrictToNumbersAndLength(dniInput, 8);


    // --- Lógica del SweetAlert para "Doctor Editado" ---
    const doctorEditadoConExito = <?php echo json_encode($doctor_editado_exito); ?>;
    // URL para "Ver Lista de Doctores" (el "seguir registrando" de tu solicitud)
    const gestionarDoctoresUrl = '<?php echo BASE_URL; ?>admin/gestionar_doctores.php'; 
    // URL para "Más tarde" (ir al panel principal del admin)
    const adminDashboardUrl = '<?php echo BASE_URL; ?>admin/index.php'; 

    if (doctorEditadoConExito) {
        Swal.fire({
            title: '¡Doctor Actualizado con Éxito!',
            text: 'La información del doctor ha sido guardada.',
            icon: 'success',
            showCancelButton: true,
            confirmButtonColor: '#28a745', // Verde
            cancelButtonColor: '#007bff', // Azul
            confirmButtonText: 'Ver Lista de Doctores',
            cancelButtonText: 'Ir al Panel Principal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Si el usuario elige "Ver Lista de Doctores"
                window.location.href = gestionarDoctoresUrl;
            } else {
                // Si el usuario elige "Ir al Panel Principal" o cierra el modal
                window.location.href = adminDashboardUrl;
            }
        });
    }
});
</script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>