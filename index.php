<?php
// Mantenemos la conexión a la configuración y la sesión
require_once __DIR__ . '/includes/config.php';

// Verificar si el usuario está logueado. Si no hay sesión iniciada, $_SESSION['id_usuario'] no existirá.
// Usaremos esta variable en el JavaScript para decidir si redirigir o no.
$usuario_logueado = isset($_SESSION['id_usuario']);

// --- OBTENER ESPECIALIDADES PARA LA VISTA DE SERVICIOS ---
// Esta lógica la traemos de la otra página para mostrar los servicios aquí
$especialidades_result = $mysqli->query("SELECT nombre_especialidad FROM especialidades ORDER BY id_especialidad LIMIT 6");

// --- LISTA DE IMÁGENES ---
// Asegúrate de que este array tenga las URLs de las imágenes para tus servicios
$lista_imagenes = [
    'https://clinicadentalastigarraga.com/wp-content/uploads/molde-tratamiento-de-ortodoncia.jpg',
    'https://grados.uemc.es/hs-fs/hubfs/Blog/Im%C3%A1genes/render-tratamiento-endodoncia.jpg?width=1000&height=699&name=render-tratamiento-endodoncia.jpg',
    'https://www.fundaciojosepfinestres.cat/assets/arxius/b094f1c14ceada99f3c4c37844ab668b.jpg', 
    'https://staticnew-prod.topdoctors.cl/files/Image/large/57580a4b230a1ece722ff9a51df45934.jpg',
    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTfV606PWwXzT1udq7ZmT3Qpj4Pn6qWyBfkwQ&s',
    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS20QGCpfJoqFvvZKOPHwpi_KqgOL5rQhIc4g&s', // Había una coma de más aquí en tu código original
    // 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS20QGCpfJoqFvvZKOPHwpi_KqgOL5rQhIc4g&s' // Esta línea estaba duplicada y causaba un error de sintaxis PHP
];
$imagen_por_defecto = 'https://via.placeholder.com/400/CCCCCC/FFFFFF?text=Servicio';

// Incluimos el header
require_once __DIR__ . '/includes/header.php';
?>

<section class="hero-section" >
    <div class="hero-content">
        <a href="<?php echo BASE_URL; ?>paciente/agendar_cita.php" class="btn btn-primary btn-lg hero-button">Agendar una Cita</a>
    </div>
</section>

<div class="container my-5">
    <section class="services-section">
        <h2 class="section-title">Nuestros Servicios Dentales</h2>
        <div class="row">
            <?php if ($especialidades_result && $especialidades_result->num_rows > 0): ?>
                <?php
                    $i = 0; // Inicializamos contador
                    while($esp = $especialidades_result->fetch_assoc()):
                ?>
                    <?php
                        $image_url = isset($lista_imagenes[$i]) ? $lista_imagenes[$i] : $imagen_por_defecto;
                    ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="service-item">
                            <a href="#" class="service-link"> 
                                <img src="<?php echo $image_url; ?>" alt="<?php echo htmlspecialchars($esp['nombre_especialidad']); ?>">
                                <h4><?php echo htmlspecialchars($esp['nombre_especialidad']); ?></h4>
                            </a>
                        </div>
                    </div>
                <?php
                    $i++; // Incrementamos contador
                    endwhile;
                ?>
            <?php else: ?>
                <p class="text-center">No hay servicios disponibles en este momento.</p>
            <?php endif; ?>
        </div>
    </section>
</div>

<section class="faq-section bg-light py-5">
    <div class="container">
        <h2 class="section-title">Preguntas Frecuentes</h2>
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="faq-card">
                    <h5>¿Cómo agendo una cita?</h5>
                    <p>Para agendar, elige una fecha y hora en nuestro formulario de citas en línea. Si no tienes una cuenta, serás guiado para llenar tus datos personales y tu historial médico.</p>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="faq-card">
                    <h5>¿Es necesario crear una cuenta?</h5>
                    <p>Sí, es necesario para agendar en línea. Con una cuenta, puedes ver tu historial de citas, agendar futuras visitas y llevar un seguimiento de tus tratamientos y recordatorios.</p>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="faq-card">
                    <h5>¿Qué tipo de pasta de dientes debo usar?</h5>
                    <p>Existen diferentes tipos de dentífricos y la elección ideal depende de tus necesidades y las de tu esmalte. Consulta a nuestros especialistas para una recomendación personalizada.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="emergency-contact-section text-center py-5">
    <div class="container">
        <div class="emergency-icons">
            <i class="bi bi-headset"></i>
            <i class="bi bi-telephone-fill"></i>
        </div>
        <p class="lead">¿Necesitas una revisión? ¡Llama para una consulta de emergencia!</p>
        <div class="emergency-phone-number">8579-9537</div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const usuarioLogueado = <?php echo json_encode($usuario_logueado); ?>;
    // ¡CAMBIO AQUÍ! La ruta directa a tu login.php
    const loginUrl = 'http://localhost/gestion_citas_medicas/auth/login.php'; 

    const serviceLinks = document.querySelectorAll('.service-link');

    serviceLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            event.preventDefault(); 

            if (!usuarioLogueado) {
                Swal.fire({
                    title: 'Inicia Sesión para Agendar',
                    text: 'Para agendar una cita o ver más detalles, por favor inicia sesión primero.',
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ir a Iniciar Sesión',
                    cancelButtonText: 'Cerrar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = loginUrl;
                    }
                });
            } else {
                window.location.href = '<?php echo BASE_URL; ?>paciente/agendar_cita.php';
            }
        });
    });

    const heroButton = document.querySelector('.hero-button');
    if (heroButton) {
        heroButton.addEventListener('click', function(event) {
            event.preventDefault(); 

            if (!usuarioLogueado) {
                Swal.fire({
                    title: 'Inicia Sesión para Agendar',
                    text: 'Para agendar una cita, por favor inicia sesión primero.',
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ir a Iniciar Sesión',
                    cancelButtonText: 'Cerrar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = loginUrl;
                    }
                });
            } else {
                window.location.href = this.href;
            }
        });
    }
});
</script>


<?php require_once __DIR__ . '/includes/footer.php'; ?>