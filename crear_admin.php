<?php
require_once __DIR__ . '/includes/config.php';

$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $correo = trim($_POST['correo']);
    $password = $_POST['password'];

    if (empty($nombre) || empty($apellido) || empty($correo) || empty($password)) {
        $mensaje = 'Todos los campos son obligatorios.';
        $tipo_mensaje = 'danger';
    } else {
        // Verificar si el correo ya existe
        $stmt_check = $mysqli->prepare("SELECT id_usuario FROM usuarios WHERE correo = ?");
        $stmt_check->bind_param("s", $correo);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $mensaje = 'Ese correo ya está registrado. Intenta con otro.';
            $tipo_mensaje = 'warning';
        } else {
            // Insertar el nuevo administrador
            $sql = "INSERT INTO usuarios (nombre, apellido, correo, password, rol) VALUES (?, ?, ?, ?, 'administrador')";
            $stmt = $mysqli->prepare($sql);
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bind_param("ssss", $nombre, $apellido, $correo, $hashed_password);

            if ($stmt->execute()) {
                $mensaje = '¡Administrador creado con éxito! <strong>YA PUEDES BORRAR ESTE ARCHIVO (crear_admin.php)</strong>.';
                $tipo_mensaje = 'success';
            } else {
                $mensaje = 'Error al crear el administrador: ' . $stmt->error;
                $tipo_mensaje = 'danger';
            }
            $stmt->close();
        }
        $stmt_check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Administrador - Herramienta de Único Uso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h3>Crear Usuario Administrador (Solo para configuración inicial)</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($mensaje): ?>
                            <div class="alert alert-<?php echo $tipo_mensaje; ?>"><?php echo $mensaje; ?></div>
                        <?php endif; ?>

                        <p>Usa este formulario una única vez para crear tu cuenta de administrador. <strong>Después de crearlo, borra este archivo del servidor.</strong></p>
                        
                        <form action="crear_admin.php" method="POST">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input type="text" name="nombre" id="nombre" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="apellido" class="form-label">Apellido</label>
                                <input type="text" name="apellido" id="apellido" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="correo" class="form-label">Correo Electrónico</label>
                                <input type="email" name="correo" id="correo" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" name="password" id="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Crear Administrador</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>