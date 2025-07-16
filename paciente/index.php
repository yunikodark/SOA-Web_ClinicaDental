<?php
require_once __DIR__ . '/../includes/config.php';
verificar_rol('paciente');

$id_usuario = $_SESSION['id_usuario'];

// --- OBTENER DATOS DEL DASHBOARD DEL PACIENTE ---
$stmt_proxima_cita = $mysqli->prepare("SELECT c.fecha_cita, c.hora_cita, u_doc.nombre AS nombre_doctor, u_doc.apellido AS apellido_doctor FROM citas c JOIN doctores d ON c.id_doctor = d.id_doctor JOIN usuarios u_doc ON d.id_usuario = u_doc.id_usuario JOIN pacientes p ON c.id_paciente = p.id_paciente WHERE p.id_usuario = ? AND c.fecha_cita >= CURDATE() AND c.estado = 'agendada' ORDER BY c.fecha_cita ASC, c.hora_cita ASC LIMIT 1");
$stmt_proxima_cita->bind_param("i", $id_usuario);
$stmt_proxima_cita->execute();
$proxima_cita = $stmt_proxima_cita->get_result()->fetch_assoc();
$stmt_proxima_cita->close();

$stmt_total_citas = $mysqli->prepare("SELECT COUNT(c.id_cita) AS total FROM citas c JOIN pacientes p ON c.id_paciente = p.id_paciente WHERE p.id_usuario = ?");
$stmt_total_citas->bind_param("i", $id_usuario);
$stmt_total_citas->execute();
$total_citas = $stmt_total_citas->get_result()->fetch_assoc()['total'];
$stmt_total_citas->close();

// --- OBTENER ESPECIALIDADES PARA LA VISTA DE SERVICIOS ---
$especialidades_result = $mysqli->query("SELECT nombre_especialidad FROM especialidades ORDER BY id_especialidad LIMIT 6");

// --- LISTA DE IMÁGENES ---
// Aquí defines las URLs de tus imágenes en el orden que quieres que aparezcan.
$lista_imagenes = [
    'https://clinicadentalastigarraga.com/wp-content/uploads/molde-tratamiento-de-ortodoncia.jpg',
    'https://grados.uemc.es/hs-fs/hubfs/Blog/Im%C3%A1genes/render-tratamiento-endodoncia.jpg?width=1000&height=699&name=render-tratamiento-endodoncia.jpg',
    'https://www.fundaciojosepfinestres.cat/assets/arxius/b094f1c14ceada99f3c4c37844ab668b.jpg', // Usando tu logo local
    'https://staticnew-prod.topdoctors.cl/files/Image/large/57580a4b230a1ece722ff9a51df45934.jpg',
    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTfV606PWwXzT1udq7ZmT3Qpj4Pn6qWyBfkwQ&s',
    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS20QGCpfJoqFvvZKOPHwpi_KqgOL5rQhIc4g&s'
];
$imagen_por_defecto = 'https://via.placeholder.com/150/CCCCCC/FFFFFF?text=Servicio';

?>

<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<h1 class="mb-4">Panel del Paciente</h1>
<p class="lead">Bienvenido de nuevo, <?php echo htmlspecialchars($_SESSION['nombre']); ?>.</p>
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card text-white bg-success h-100">
            <div class="card-body d-flex flex-column">
                <h5 class="card-title"><i class="bi bi-calendar-check"></i> Tu Próxima Cita</h5>
                <?php if ($proxima_cita): ?>
                    <p class="card-text fs-4">
                        <?php echo date("d/m/Y", strtotime($proxima_cita['fecha_cita'])); ?>
                        a las <?php echo date("h:i A", strtotime($proxima_cita['hora_cita'])); ?>
                    </p>
                    <p class="mt-auto">Con: Dr(a). <?php echo htmlspecialchars($proxima_cita['nombre_doctor'] . ' ' . $proxima_cita['apellido_doctor']); ?></p>
                <?php else: ?>
                    <p class="card-text flex-grow-1">No tienes próximas citas agendadas.</p>
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>paciente/agendar_cita.php" class="btn btn-outline-light mt-3">Agendar Nueva Cita</a>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card bg-light h-100">
            <div class="card-body d-flex flex-column">
                <h5 class="card-title"><i class="bi bi-card-list"></i> Historial de Citas</h5>
                <p class="card-text fs-4">
                    Tienes un total de <strong><?php echo $total_citas; ?></strong> citas en tu historial.
                </p>
                <p class="flex-grow-1">Consulta el detalle de tus citas pasadas y futuras.</p>
                <a href="<?php echo BASE_URL; ?>paciente/mis_citas.php" class="btn btn-primary mt-3">Ver Todas Mis Citas</a>
            </div>
        </div>
    </div>
</div>

<section class="services-section mt-5">
    <h2 class="section-title">Nuestros Servicios Dentales</h2>
    <div class="row">
        <?php if ($especialidades_result && $especialidades_result->num_rows > 0): ?>
            <?php
                $i = 0; // Inicializamos un contador para las imágenes
                while($esp = $especialidades_result->fetch_assoc()):
            ?>
                <?php
                    // Asignamos la imagen del array. Si no existe, usamos la de por defecto.
                    $image_url = isset($lista_imagenes[$i]) ? $lista_imagenes[$i] : $imagen_por_defecto;
                ?>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="service-item">
                        <a href="<?php echo BASE_URL; ?>paciente/agendar_cita.php">
                            <img src="<?php echo $image_url; ?>" alt="<?php echo htmlspecialchars($esp['nombre_especialidad']); ?>">
                            <h4><?php echo htmlspecialchars($esp['nombre_especialidad']); ?></h4>
                        </a>
                    </div>
                </div>
            <?php
                $i++; // Pasamos a la siguiente imagen para la siguiente especialidad
                endwhile;
            ?>
        <?php else: ?>
            <p class="text-center">No hay servicios disponibles en este momento.</p>
        <?php endif; ?>
    </div>
</section>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>