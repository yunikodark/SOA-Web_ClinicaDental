<?php
require_once __DIR__ . '/../includes/config.php';
// Redirige si no está logueado, ya que no sabríamos qué perfil editar.
verificar_sesion(); 

$id_usuario = $_SESSION['id_usuario'];
$mensaje = '';
$tipo_mensaje = '';

// --- Lógica para procesar el formulario cuando se envía (POST) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recoger y limpiar datos del formulario
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    // Nuevos campos para 'usuarios'
    $celular = trim($_POST['celular'] ?? ''); // Asegurar que no sea null
    $dni = trim($_POST['dni'] ?? '');       // Asegurar que no sea null

    // *** VALIDACIONES ADICIONALES PARA CELULAR Y DNI EN LA EDICIÓN ***
    $errors = []; // Inicializar array de errores para este formulario
    
    // Validación para Nombre y Apellido (si lo consideras necesario para la edición)
    if (empty($nombre)) {
        $errors['nombre'] = "El nombre no puede estar vacío.";
    }
    if (empty($apellido)) {
        $errors['apellido'] = "El apellido no puede estar vacío.";
    }

    // Validación para Celular
    if (empty($celular)) {
        $errors['celular'] = "El celular es obligatorio.";
    } elseif (!ctype_digit($celular) || strlen($celular) !== 9) {
        $errors['celular'] = "El celular debe contener exactamente 9 dígitos numéricos.";
    }

    // Validación para DNI
    if (empty($dni)) {
        $errors['dni'] = "El DNI es obligatorio.";
    } elseif (!ctype_digit($dni) || strlen($dni) !== 8) {
        $errors['dni'] = "El DNI debe contener exactamente 8 dígitos numéricos.";
    } else {
        // Verificar si el DNI ya existe, pero PERMITIR que sea el DNI DEL PROPIO USUARIO
        $stmt_dni_check = $mysqli->prepare("SELECT id_usuario FROM usuarios WHERE dni = ? AND id_usuario != ?");
        $stmt_dni_check->bind_param("si", $dni, $id_usuario);
        $stmt_dni_check->execute();
        $stmt_dni_check->store_result();
        if ($stmt_dni_check->num_rows > 0) {
            $errors['dni'] = "Este DNI ya está registrado por otro usuario.";
        }
        $stmt_dni_check->close();
    }

    // Si no hay errores de validación, proceder con la transacción
    if (empty($errors)) {
        $mysqli->begin_transaction();
        try {
            // 1. Actualizar la tabla 'usuarios' incluyendo celular y dni
            $stmt_user = $mysqli->prepare("UPDATE usuarios SET nombre = ?, apellido = ?, celular = ?, dni = ? WHERE id_usuario = ?");
            // Ahora necesitamos 4 's' (string) y una 'i' (integer)
            $stmt_user->bind_param("ssssi", $nombre, $apellido, $celular, $dni, $id_usuario);
            $stmt_user->execute();
            $stmt_user->close();

            // 2. Si el rol es 'paciente', actualizar también la tabla 'pacientes'
            if ($_SESSION['rol'] == 'paciente') {
                $fecha_nacimiento = !empty($_POST['fecha_nacimiento']) ? trim($_POST['fecha_nacimiento']) : null;
                $direccion = trim($_POST['direccion']);
                
                // *** VALIDACIONES PARA FECHA_NACIMIENTO Y DIRECCION EN LA EDICIÓN ***
                if (empty($fecha_nacimiento)) {
                    $errors['fecha_nacimiento'] = "La fecha de nacimiento es obligatoria.";
                } elseif (strtotime($fecha_nacimiento) === false) { 
                    $errors['fecha_nacimiento'] = "Formato de fecha de nacimiento inválido.";
                } else {
                    // Validar que la fecha no sea en el futuro y que el usuario sea mayor de 18 años
                    $fecha_minima_nacimiento = date('Y-m-d', strtotime('-18 years'));
                    $fecha_actual_hoy = date('Y-m-d'); 
                    if ($fecha_nacimiento > $fecha_actual_hoy || $fecha_nacimiento > $fecha_minima_nacimiento) {
                        $errors['fecha_nacimiento'] = "Debes ser mayor de 18 años y la fecha no puede ser en el futuro.";
                    }
                }

                if (empty($direccion)) {
                    $errors['direccion'] = "La dirección es obligatoria.";
                }

                // Si no hay errores específicos de paciente, proceder con la actualización
                if (empty($errors)) { // Verificamos que no haya nuevos errores tras las validaciones de paciente
                    $stmt_paciente = $mysqli->prepare("UPDATE pacientes SET fecha_nacimiento = ?, direccion = ? WHERE id_usuario = ?");
                    $stmt_paciente->bind_param("ssi", $fecha_nacimiento, $direccion, $id_usuario);
                    $stmt_paciente->execute();
                    $stmt_paciente->close();
                } else {
                    // Si hay errores específicos del paciente, revertimos la transacción y mostramos mensaje
                    $mysqli->rollback();
                    $mensaje = 'Error de validación en la información de paciente.';
                    $tipo_mensaje = 'danger';
                    // Salimos de este bloque para que se muestren los errores en el formulario
                    goto end_transaction_logic; 
                }
            }

            $mysqli->commit();
            $mensaje = 'Perfil actualizado correctamente.';
            $tipo_mensaje = 'success';
            // Actualizar variables de sesión si es necesario para el header/nav (ej. nombre)
            $_SESSION['nombre'] = $nombre; 
            $_SESSION['celular'] = $celular; // También podrías actualizar el celular en sesión si lo usas.
            $_SESSION['dni'] = $dni;         // Y el DNI.

        } catch (Exception $e) {
            $mysqli->rollback();
            $mensaje = 'Error al actualizar el perfil: ' . $e->getMessage();
            $tipo_mensaje = 'danger';
        }
    } else {
        // Si hay errores de validación iniciales (nombre, apellido, celular, dni)
        $mensaje = 'Por favor, corrige los errores en el formulario.';
        $tipo_mensaje = 'danger';
    }
    end_transaction_logic:; // Etiqueta para goto
}

// --- Lógica para obtener los datos actuales y mostrarlos en el formulario (GET) ---
// Consulta para obtener todos los datos necesarios de usuarios y pacientes
$sql_get = "SELECT u.nombre, u.apellido, u.correo, u.celular, u.dni, p.fecha_nacimiento, p.direccion
            FROM usuarios u
            LEFT JOIN pacientes p ON u.id_usuario = p.id_usuario
            WHERE u.id_usuario = ?";

$stmt_get = $mysqli->prepare($sql_get);
if ($stmt_get === false) {
    die("Error al preparar la consulta para obtener datos del usuario: " . $mysqli->error);
}
$stmt_get->bind_param("i", $id_usuario);
$stmt_get->execute();
$usuario = $stmt_get->get_result()->fetch_assoc();
$stmt_get->close();

if (!$usuario) {
    die("Error crítico: No se pudieron cargar los datos del usuario.");
}
?>
<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<h1>Editar Mi Perfil</h1>
<p>Mantén tu información personal actualizada.</p>

<?php if ($mensaje): ?>
    <div class="alert alert-<?php echo $tipo_mensaje; ?>"><?php echo $mensaje; ?></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" novalidate>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control <?php echo (isset($errors['nombre'])) ? 'is-invalid' : ''; ?>" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                    <?php if(isset($errors['nombre'])): ?><div class="invalid-feedback"><?php echo $errors['nombre']; ?></div><?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="apellido" class="form-label">Apellido</label>
                    <input type="text" class="form-control <?php echo (isset($errors['apellido'])) ? 'is-invalid' : ''; ?>" id="apellido" name="apellido" value="<?php echo htmlspecialchars($usuario['apellido']); ?>" required>
                    <?php if(isset($errors['apellido'])): ?><div class="invalid-feedback"><?php echo $errors['apellido']; ?></div><?php endif; ?>
                </div>
            </div>
            <div class="mb-3">
                   <label for="correo" class="form-label">Correo Electrónico</label>
                   <input type="email" class="form-control" id="correo" value="<?php echo htmlspecialchars($usuario['correo']); ?>" disabled readonly>
                   <small class="form-text text-muted">El correo electrónico no se puede cambiar.</small>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="celular" class="form-label">Celular</label>
                    <input type="tel" class="form-control <?php echo (isset($errors['celular'])) ? 'is-invalid' : ''; ?>" id="celular" name="celular" value="<?php echo htmlspecialchars($usuario['celular'] ?? ''); ?>" required pattern="[0-9]{9}" title="Debe contener exactamente 9 dígitos numéricos" maxlength="9">
                    <?php if(isset($errors['celular'])): ?><div class="invalid-feedback"><?php echo $errors['celular']; ?></div><?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="dni" class="form-label">DNI</label>
                    <input type="text" class="form-control <?php echo (isset($errors['dni'])) ? 'is-invalid' : ''; ?>" id="dni" name="dni" value="<?php echo htmlspecialchars($usuario['dni'] ?? ''); ?>" required pattern="[0-9]{8}" title="Debe contener exactamente 8 dígitos numéricos" maxlength="8">
                    <?php if(isset($errors['dni'])): ?><div class="invalid-feedback"><?php echo $errors['dni']; ?></div><?php endif; ?>
                </div>
            </div>

            <?php if ($_SESSION['rol'] == 'paciente'): ?>
            <hr>
            <h5 class="mt-4">Información Adicional de Paciente</h5>
               <div class="mb-3">
                <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                <input type="date" class="form-control <?php echo (isset($errors['fecha_nacimiento'])) ? 'is-invalid' : ''; ?>" id="fecha_nacimiento" name="fecha_nacimiento" value="<?php echo htmlspecialchars($usuario['fecha_nacimiento'] ?? ''); ?>" required max="<?php echo date('Y-m-d', strtotime('now - 18 years')); ?>">
                <?php if(isset($errors['fecha_nacimiento'])): ?><div class="invalid-feedback"><?php echo $errors['fecha_nacimiento']; ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="direccion" class="form-label">Dirección</label>
                <textarea class="form-control <?php echo (isset($errors['direccion'])) ? 'is-invalid' : ''; ?>" id="direccion" name="direccion" rows="3" required><?php echo htmlspecialchars($usuario['direccion'] ?? ''); ?></textarea>
                <?php if(isset($errors['direccion'])): ?><div class="invalid-feedback"><?php echo $errors['direccion']; ?></div><?php endif; ?>
            </div>
            <?php endif; ?>
            
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const celularInput = document.getElementById('celular');
    const dniInput = document.getElementById('dni');

    // Función para limpiar caracteres no numéricos y aplicar maxlength
    function restrictToNumbersAndLength(inputElement, maxLength) {
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

    if (celularInput) {
        restrictToNumbersAndLength(celularInput, 9);
    }
    if (dniInput) {
        restrictToNumbersAndLength(dniInput, 8);
    }
});
</script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>