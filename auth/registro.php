<?php
// Habilitar la visualización de errores (QUITAR EN PRODUCCIÓN)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir el archivo de configuración, que ahora está en la misma carpeta.
require_once __DIR__ . '/config.php'; // CAMBIO CLAVE AQUÍ

// Inicializar variables para los campos del formulario
$nombre = $apellido = $correo = $password_original_plano = $confirm_password = "";
$celular = $dni = $fecha_nacimiento = $direccion = "";

$errors = [];

// Procesar el formulario solo si se envió con el método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validaciones del lado del servidor

    $nombre = trim($_POST["nombre"]);
    if (empty($nombre)) {
        $errors['nombre'] = "Por favor, ingresa tu nombre.";
    }

    $apellido = trim($_POST["apellido"]);
    if (empty($apellido)) {
        $errors['apellido'] = "Por favor, ingresa tu apellido.";
    }

    $correo = trim($_POST["correo"]);
    if (empty($correo)) {
        $errors['correo'] = "Por favor, ingresa tu correo.";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errors['correo'] = "Formato de correo inválido.";
    } else {
        $stmt = $mysqli->prepare("SELECT id_usuario FROM usuarios WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors['correo'] = "Este correo electrónico ya está registrado.";
        }
        $stmt->close();
    }

    $password_original_plano = $_POST["password"];
    if (empty($password_original_plano)) {
        $errors['password'] = "Por favor, ingresa una contraseña.";
    } elseif (strlen($password_original_plano) < 6) {
        $errors['password'] = "La contraseña debe tener al menos 6 caracteres.";
    }

    $confirm_password = $_POST["confirm_password"];
    if (empty($errors['password']) && ($password_original_plano != $confirm_password)) {
        $errors['confirm_password'] = "Las contraseñas no coinciden.";
    }

    $celular = trim($_POST["celular"]);
    if (empty($celular)) {
        $errors['celular'] = "Por favor, ingresa tu número de celular.";
    } elseif (!ctype_digit($celular) || strlen($celular) !== 9) {
        $errors['celular'] = "El celular debe contener exactamente 9 dígitos numéricos.";
    }

    $dni = trim($_POST["dni"]);
    if (empty($dni)) {
        $errors['dni'] = "Por favor, ingresa tu DNI.";
    } elseif (!ctype_digit($dni) || strlen($dni) !== 8) {
        $errors['dni'] = "El DNI debe contener exactamente 8 dígitos numéricos.";
    } else {
        $stmt = $mysqli->prepare("SELECT id_usuario FROM usuarios WHERE dni = ?");
        $stmt->bind_param("s", $dni);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors['dni'] = "Este DNI ya está registrado.";
        }
        $stmt->close();
    }

    $fecha_nacimiento = trim($_POST["fecha_nacimiento"]);
    if (empty($fecha_nacimiento)) {
        $errors['fecha_nacimiento'] = "Por favor, ingresa tu fecha de nacimiento.";
    } elseif (strtotime($fecha_nacimiento) === false) { 
        $errors['fecha_nacimiento'] = "Formato de fecha de nacimiento inválido.";
    } else {
        $fecha_minima_nacimiento = date('Y-m-d', strtotime('-18 years'));
        $fecha_actual = date('Y-m-d'); 

        if ($fecha_nacimiento > $fecha_actual || $fecha_nacimiento > $fecha_minima_nacimiento) {
            $errors['fecha_nacimiento'] = "Debes ser mayor de 18 años y la fecha no puede ser en el futuro.";
        }
    }

    $direccion = trim($_POST["direccion"]);
    if (empty($direccion)) {
        $errors['direccion'] = "Por favor, ingresa tu dirección.";
    }

    if (empty($errors)) {
        $mysqli->begin_transaction();
        try {
            $sql_user = "INSERT INTO usuarios (nombre, apellido, celular, dni, correo, password, rol) VALUES (?, ?, ?, ?, ?, ?, 'paciente')";
            $stmt_user = $mysqli->prepare($sql_user);
            
            $hashed_password = password_hash($password_original_plano, PASSWORD_DEFAULT);
            $stmt_user->bind_param("ssssss", $nombre, $apellido, $celular, $dni, $correo, $hashed_password);
            $stmt_user->execute();
            $id_usuario_nuevo = $stmt_user->insert_id;
            $stmt_user->close();
            
            $sql_paciente = "INSERT INTO pacientes (id_usuario, fecha_nacimiento, direccion) VALUES (?, ?, ?)";
            $stmt_paciente = $mysqli->prepare($sql_paciente);
            $stmt_paciente->bind_param("iss", $id_usuario_nuevo, $fecha_nacimiento, $direccion);
            $stmt_paciente->execute();
            $stmt_paciente->close();

            $mysqli->commit();
            
            // --- ENVÍO DE CORREO DE BIENVENIDA CON DATOS ---
            $asunto = "¡Bienvenido a nuestra Clínica Dental!";
            $cuerpo_html = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { width: 80%; max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9; }
                        h2 { color: #007bff; }
                        .credentials { background-color: #e9ecef; padding: 15px; border-radius: 5px; margin-top: 20px; }
                        .credentials p { margin: 5px 0; }
                        .footer { margin-top: 20px; font-size: 0.9em; color: #777; }
                        a { color: #007bff; text-decoration: none; }
                        a:hover { text-decoration: underline; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <h2>¡Hola, " . htmlspecialchars($nombre) . " " . htmlspecialchars($apellido) . "!</h2>
                        <p>Tu cuenta en nuestra Clínica Dental ha sido creada exitosamente. Estamos emocionados de tenerte con nosotros.</p>
                        <p>Aquí están los detalles de tu cuenta:</p>
                        <div class='credentials'>
                            <p><strong>Nombre Completo:</strong> " . htmlspecialchars($nombre) . " " . htmlspecialchars($apellido) . "</p>
                            <p><strong>Correo (Usuario):</strong> " . htmlspecialchars($correo) . "</p>
                            <p><strong>Contraseña:</strong> " . htmlspecialchars($password_original_plano) . "</p>
                            <p><strong>Celular:</strong> " . htmlspecialchars($celular) . "</p>
                            <p><strong>DNI:</strong> " . htmlspecialchars($dni) . "</p>
                            <p><strong>Fecha de Nacimiento:</strong> " . htmlspecialchars(date('d/m/Y', strtotime($fecha_nacimiento))) . "</p>
                            <p><strong>Dirección:</strong> " . htmlspecialchars($direccion) . "</p>
                        </div>
                        <p>Puedes iniciar sesión en cualquier momento con tu correo y contraseña en el siguiente enlace:</p>
                        <p><a href='" . BASE_URL . "auth/login.php' style='display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Ir a Iniciar Sesión</a></p>
                        <p>Si tienes alguna pregunta, no dudes en contactarnos.</p>
                        <p>Atentamente,<br>El equipo de la Clínica Dental</p>
                        <div class='footer'>
                            Este es un correo automático, por favor no lo respondas.
                        </div>
                    </div>
                </body>
                </html>
            ";
            
            if (enviar_correo($correo, $nombre . ' ' . $apellido, $asunto, $cuerpo_html)) {
                redirigir(BASE_URL . 'auth/login.php?registro=exitoso&mail_sent=true');
            } else {
                redirigir(BASE_URL . 'auth/login.php?registro=exitoso&mail_sent=false');
            }

        } catch (Exception $e) {
            $mysqli->rollback();
            $errors['general'] = "Algo salió mal durante el registro: " . $e->getMessage() . ". Por favor, inténtalo de nuevo.";
        }
    }
}
?>

<?php require_once ROOT_PATH . '/includes/header.php'; // Asumo que ROOT_PATH ya está definido en config.php ?>

<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
        <div class="card shadow">
            <div class="card-body p-4">
                <h2 class="text-center mb-4">Registro de Paciente</h2>

                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-danger"><?php echo $errors['general']; ?></div>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" novalidate>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" name="nombre" id="nombre" class="form-control <?php echo (!empty($errors['nombre'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($nombre); ?>" required>
                            <?php if(!empty($errors['nombre'])): ?><div class="invalid-feedback"><?php echo $errors['nombre']; ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="apellido" class="form-label">Apellido</label>
                            <input type="text" name="apellido" id="apellido" class="form-control <?php echo (!empty($errors['apellido'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($apellido); ?>" required>
                            <?php if(!empty($errors['apellido'])): ?><div class="invalid-feedback"><?php echo $errors['apellido']; ?></div><?php endif; ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="celular" class="form-label">Celular</label>
                            <input type="tel" name="celular" id="celular" class="form-control <?php echo (!empty($errors['celular'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($celular); ?>" required pattern="[0-9]{9}" title="Debe contener exactamente 9 dígitos numéricos" maxlength="9">
                            <?php if(!empty($errors['celular'])): ?><div class="invalid-feedback"><?php echo $errors['celular']; ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="dni" class="form-label">DNI</label>
                            <input type="text" name="dni" id="dni" class="form-control <?php echo (!empty($errors['dni'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($dni); ?>" required pattern="[0-9]{8}" title="Debe contener exactamente 8 dígitos numéricos" maxlength="8">
                            <?php if(!empty($errors['dni'])): ?><div class="invalid-feedback"><?php echo $errors['dni']; ?></div><?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="correo" class="form-label">Correo Electrónico</label>
                        <input type="email" name="correo" id="correo" class="form-control <?php echo (!empty($errors['correo'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($correo); ?>" required>
                        <?php if(!empty($errors['correo'])): ?><div class="invalid-feedback"><?php echo $errors['correo']; ?></div><?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                        <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control <?php echo (!empty($errors['fecha_nacimiento'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($fecha_nacimiento); ?>" required max="<?php echo date('Y-m-d', strtotime('now - 18 years')); ?>">
                        <?php if(!empty($errors['fecha_nacimiento'])): ?><div class="invalid-feedback"><?php echo $errors['fecha_nacimiento']; ?></div><?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="direccion" class="form-label">Dirección</label>
                        <textarea name="direccion" id="direccion" class="form-control <?php echo (!empty($errors['direccion'])) ? 'is-invalid' : ''; ?>" rows="3" required><?php echo htmlspecialchars($direccion); ?></textarea>
                        <?php if(!empty($errors['direccion'])): ?><div class="invalid-feedback"><?php echo $errors['direccion']; ?></div><?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" name="password" id="password" class="form-control <?php echo (!empty($errors['password'])) ? 'is-invalid' : ''; ?>" required>
                        <?php if(!empty($errors['password'])): ?><div class="invalid-feedback"><?php echo $errors['password']; ?></div><?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control <?php echo (!empty($errors['confirm_password'])) ? 'is-invalid' : ''; ?>" required>
                        <?php if(!empty($errors['confirm_password'])): ?><div class="invalid-feedback"><?php echo $errors['confirm_password']; ?></div><?php endif; ?>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Registrarse</button>
                    </div>
                    <p class="text-center mt-3">
                        ¿Ya tienes una cuenta? <a href="<?php echo BASE_URL; ?>auth/login.php">Inicia sesión aquí</a>.
                    </p>
                </form>
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

    if (celularInput) {
        restrictToNumbersAndLength(celularInput, 9);
    }
    if (dniInput) {
        restrictToNumbersAndLength(dniInput, 8);
    }
});
</script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>