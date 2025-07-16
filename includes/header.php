<?php
// Incluir la configuración solo si no se ha hecho antes.
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/config.php';
}
// Obtenemos el nombre del archivo actual para saber qué enlace marcar como "activo"
$current_page = basename($_SERVER['PHP_SELF']);
$rol = $_SESSION['rol'] ?? 'publico'; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clínica Dental</title>
    <meta name="base-url" content="<?php echo BASE_URL; ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <style>
        /* Pega esto al final de tu assets/css/style.css si no lo tienes */

/* Estilos para la Cabecera (Header/Navbar) */
.navbar-brand img {
    max-height: 40px; 
}
.navbar-light .navbar-nav .nav-link {
    color: #00897B; 
    font-weight: 500;
    margin: 0 10px;
    transition: color 0.3s;
}
.navbar-light .navbar-nav .nav-link.active,
.navbar-light .navbar-nav .nav-link:hover {
    color: #004D40;
    font-weight: 700;
}
.user-icon-link {
    color: #424242;
}
.user-icon-link:hover {
    color: #000;
}
.user-icon-link i {
    font-size: 1.8rem;
}
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

<?php
// ==================================================================
// LÓGICA PARA MOSTRAR LA CABECERA CORRECTA SEGÚN EL ROL
// ==================================================================
?>

<?php if ($rol == 'doctor' || $rol == 'administrador'): ?>

    <?php
    $nav_class = ''; 
    if ($rol == 'administrador') $nav_class = 'bg-danger'; 
    if ($rol == 'doctor') $nav_class = 'bg-success';
    ?>
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm <?php echo $nav_class; ?>">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?php echo BASE_URL; ?>index.php">
                <i class="bi bi-shield-lock-fill"></i> 
                <?php echo ($rol == 'administrador') ? 'Panel de Admin' : 'Panel de Doctor'; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <?php if ($rol == 'administrador'): ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/index.php">Menu</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/gestionar_doctores.php">Doctores</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/ver_usuarios.php">Usuarios</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/gestionar_horarios.php">Horarios</a></li>
                    <?php else: // Doctor ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>doctor/index.php">Menu</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>doctor/agenda.php">Mi Agenda</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>doctor/mis_pacientes.php">Mis Pacientes</a></li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarUserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION["nombre"]); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php $url_perfil = ($rol == 'doctor') ? BASE_URL . 'doctor/editar_perfil.php' : BASE_URL . 'paciente/editar_perfil.php'; ?>
                            <li><a class="dropdown-item" href="<?php echo $url_perfil; ?>">Editar Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>auth/logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar Sesión</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

<?php else: ?>

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
      <div class="container">
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>index.php"><img src="<?php echo BASE_URL; ?>assets/img/logo.png" alt="Logo Clínica Dental"></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item"><a class="nav-link <?php if ($current_page == 'index.php') echo 'active'; ?>" href="<?php echo BASE_URL; ?>index.php">Inicio</a></li>
                <li class="nav-item"><a class="nav-link <?php if ($current_page == 'agendar_cita.php') echo 'active'; ?>" href="<?php echo BASE_URL; ?>paciente/agendar_cita.php">Agendar Cita</a></li>
                <li class="nav-item"><a class="nav-link <?php if ($current_page == 'nosotros.php') echo 'active'; ?>" href="<?php echo BASE_URL; ?>nosotros.php">Nosotros</a></li>
                <li class="nav-item"><a class="nav-link <?php if ($current_page == 'contacto.php') echo 'active'; ?>" href="<?php echo BASE_URL; ?>contacto.php">Contacto</a></li>
            </ul>
            <ul class="navbar-nav">
                <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): // Menú de Paciente logueado ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link user-icon-link" href="#" id="navbarUserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-person-circle"></i></a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li class="dropdown-header">Hola, <?php echo htmlspecialchars($_SESSION["nombre"]); ?></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>paciente/index.php">Mi Panel</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>paciente/mis_citas.php">Mis Citas</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>paciente/editar_perfil.php">Editar Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>auth/logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar Sesión</a></li>
                        </ul>
                    </li>
                <?php else: // Menú para visitantes no logueados ?>
                    <li class="nav-item"><a class="btn btn-outline-primary" href="<?php echo BASE_URL; ?>auth/login.php">Iniciar Sesión</a></li>
                <?php endif; ?>
            </ul>
        </div>
      </div>
    </nav>

<?php endif; ?>

<div class="container mt-4 mb-5 flex-grow-1">