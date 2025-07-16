<?php
require_once __DIR__ . '/../includes/config.php';

// Si el usuario ya está logueado, redirigirlo a su dashboard correspondiente
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    $rol = $_SESSION['rol'];
    if ($rol == 'paciente') redirigir(BASE_URL . 'paciente/index.php');
    if ($rol == 'doctor') redirigir(BASE_URL . 'doctor/index.php');
    if ($rol == 'administrador') redirigir(BASE_URL . 'admin/index.php');
}

$correo = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = trim($_POST["correo"]);
    $password = $_POST["password"];

    if (empty($correo) || empty($password)) {
        $error = "El correo y la contraseña son obligatorios.";
    } else {
        // --- CAMBIO 1: Se añade la columna 'estado' a la consulta ---
        $sql = "SELECT id_usuario, nombre, apellido, correo, password, rol, estado FROM usuarios WHERE correo = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $correo);
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                
                // Primero, verificamos la contraseña
                if (password_verify($password, $user['password'])) {

                    // --- CAMBIO 2: Se añade la verificación del estado ---
                    // Si la contraseña es correcta, AHORA verificamos si la cuenta está activa
                    if ($user['estado'] === 'activo') {
                        // El usuario está activo, iniciamos la sesión
                        $_SESSION["loggedin"] = true;
                        $_SESSION["id_usuario"] = $user['id_usuario'];
                        $_SESSION["correo"] = $user['correo'];
                        $_SESSION["nombre"] = $user['nombre'];
                        $_SESSION["rol"] = $user['rol'];

                        // Redirigir al dashboard correspondiente
                        $rol = $user['rol'];
                        if ($rol == 'paciente') redirigir(BASE_URL . 'paciente/index.php');
                        if ($rol == 'doctor') redirigir(BASE_URL . 'doctor/index.php');
                        if ($rol == 'administrador') redirigir(BASE_URL . 'admin/index.php');

                    } else {
                        // La cuenta existe pero está inactiva
                        $error = "Tu cuenta ha sido desactivada. Contacta al administrador.";
                    }

                } else {
                    // La contraseña no es válida
                    $error = "La contraseña que ingresaste no es válida.";
                }
            } else {
                $error = "No se encontró ninguna cuenta con ese correo electrónico.";
            }
        } else {
            $error = "¡Ups! Algo salió mal. Por favor, inténtalo de nuevo más tarde.";
        }
        $stmt->close();
    }
}
?>


<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<div class="login-page-container">
    <div class="row g-0">
        <div class="col-lg-6 d-none d-lg-block login-image-side">
            <div class="login-welcome-message">
                <h2>Bienvenido de Nuevo</h2>
                <p>Gestiona tus citas y tu salud bucal en un solo lugar.</p>
            </div>
        </div>

        <div class="col-lg-6 login-form-side">
            <div class="login-card">
                <div class="text-center mb-4">
                    <img src="<?php echo BASE_URL; ?>assets/img/logo.png" alt="Logo Clínica" class="login-logo">
                </div>
                
                <h2 class="text-center">Iniciar Sesión</h2>
                <p class="text-center text-muted mb-4">Ingresa a tu portal de paciente.</p>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if (isset($_GET['registro']) && $_GET['registro'] == 'exitoso'): ?>
                    <div class="alert alert-success">¡Registro exitoso! Ya puedes iniciar sesión.</div>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group-with-icon mb-3">
                        <i class="bi bi-envelope"></i>
                        <input type="email" name="correo" id="correo" class="form-control" placeholder="Correo Electrónico" value="<?php echo htmlspecialchars($correo); ?>" required>
                    </div>

                    <div class="form-group-with-icon mb-4">
                        <i class="bi bi-lock"></i>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Contraseña" required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Ingresar</button>
                    </div>
                    <p class="text-center mt-4">
                        ¿No tienes una cuenta? <a href="<?php echo BASE_URL; ?>auth/registro.php">Regístrate aquí</a>.
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>