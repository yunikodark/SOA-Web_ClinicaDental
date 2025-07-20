<style>
    /*
=================================
Estilos para el Nuevo Footer (Versión con Fondo Uniforme)
=================================
*/

.site-footer {
    /* Color de fondo principal para TODO el footer */
    background-color: #E0F7FA; /* Este es un tono menta muy claro, como el de tu Figma */
    color: #004D40; /* Color de texto oscuro para buen contraste */
    padding: 60px 0 20px 0; /* Espaciado interno: 60px arriba, 20px abajo */
}

/* El footer-top ya no necesita su propio color de fondo */
.footer-top {
    padding-bottom: 40px;
}

.footer-logo .clinic-name {
    font-weight: 700;
    letter-spacing: 1px;
    color: #004D40;
}

.footer-title {
    font-size: 16px;
    font-weight: 700;
    text-transform: uppercase;
    color: #004D40; /* Un verde azulado oscuro */
    margin-bottom: 20px;
    letter-spacing: 0.5px;
    border-bottom: 1px solid rgba(0, 77, 64, 0.2); /* Línea divisoria sutil */
    padding-bottom: 8px;
}

.footer-links li {
    margin-bottom: 10px;
}

.footer-links a {
    text-decoration: none;
    color: #004D40; /* Mismo color de texto oscuro */
    transition: color 0.3s ease;
}

.footer-links a:hover {
    color: #00796B;
    text-decoration: underline;
}

.social-icons .social-icon {
    display: inline-block;
    width: 40px;
    height: 40px;
    line-height: 40px;
    text-align: center;
    border-radius: 50%;
    background-color: rgba(0, 137, 123, 0.1); /* Fondo translúcido */
    color: #004D40;
    margin-right: 10px;
    font-size: 20px;
    transition: background-color 0.3s, color 0.3s;
}

.social-icons .social-icon:hover {
    background-color: #004D40;
    color: #fff;
}

/* El footer-bottom ya no necesita su propio color de fondo */
.footer-bottom {
    padding-top: 20px;
    border-top: 1px solid rgba(0, 77, 64, 0.2); /* Línea divisoria sutil */
    font-size: 14px;
    color: #004D40; /* Texto oscuro para que sea legible */
}
</style>

</div> <footer class="site-footer mt-auto">
    <br/>
    <br/>
    <div class="footer-top">
        <div class="container">
            <div class="row">

                <div class="col-lg-4 col-md-6 col-12 mb-4">
                    <div class="footer-logo">
                        <img src="<?php echo BASE_URL; ?>assets/img/logo.png" alt="Logo Clínica Dental" class="img-fluid mb-2" style="max-width: 150px;">
                        <p class="clinic-name">DENTAL CLINIC</p>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6 col-12 mb-4">
                    <h5 class="footer-title">Servicios</h5>
                    <ul class="list-unstyled footer-links">
                        <li><a href="#">Profilaxis oral</a></li>
                        <li><a href="#">Restauración dental</a></li>
                        <li><a href="#">Blanqueo</a></li>
                        <li><a href="#">Tratamiento con flúor</a></li>
                        <li><a href="#">Ortodoncia</a></li>
                        <li><a href="#">Extracción dental</a></li>
                        <li><a href="#">Dentadura completa</a></li>
                        <li><a href="#">Prótesis parcial</a></li>
                        <li><a href="#">Corona de chaqueta</a></li>
                        <li><a href="#">Fixed Bridge</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6 col-12 mb-4">
                    <h5 class="footer-title">Enlaces rapidos</h5>
                    <ul class="list-unstyled footer-links">
                        <li><a href="<?php echo BASE_URL; ?>index.php">Inicio</a></li>
                        <li><a href="#">Dental Servicios</a></li>
                        <li><a href="<?php echo BASE_URL; ?>paciente/agendar_cita.php">Cita</a></li>
                        <li><a href="<?php echo BASE_URL; ?>nosotros.php">Quienes somos</a></li>
                        <li><a href="<?php echo BASE_URL; ?>contacto.php">Contactanos</a></li>
                        <li><a href="<?php echo BASE_URL; ?>auth/login.php">Mi cuenta</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6 col-12 mb-4">
                    <h5 class="footer-title">Redes sociales</h5>
                    <div class="social-icons">
                        <a href="https://www.facebook.com/profile.php?id=100006773834772" class="social-icon"><i class="bi bi-facebook"></i></a>
                        <a href="https://x.com/auronplay?lang=es" class="social-icon"><i class="bi bi-twitter"></i></a>
                        <a href="https://www.instagram.com/davidguetta/" class="social-icon"><i class="bi bi-instagram"></i></a>
                        <a href="https://www.linkedin.com/in/dafne-cuvas-alvarez-dca/" class="social-icon"><i class="bi bi-linkedin"></i></a>
                    </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container text-center">
            <p class="mb-0">© <?php echo date("Y"); ?> Clínica Dental Sonrisas. Todos los derechos reservados.</p>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>
</body>
</html>