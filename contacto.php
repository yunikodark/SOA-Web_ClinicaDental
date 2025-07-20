<?php require_once __DIR__ . '/includes/header.php'; ?>

<div class="contact-page-container">
    <div class="container">
        <h1 class="mb-4">Contacto</h1>
        <div class="row">
            <div class="col-lg-7 mb-4 mb-lg-0">
                <div class="contact-card">
                    <h3>Envíanos un Mensaje</h3>
                    <p class="text-muted mb-4">¿Tienes alguna pregunta? Llena el formulario y te responderemos a la brevedad.</p>
                    <form action="#" method="POST">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre Completo</label>
                            <input type="text" class="form-control" id="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="asunto" class="form-label">Asunto</label>
                            <input type="text" class="form-control" id="asunto" required>
                        </div>
                        <div class="mb-3">
                            <label for="mensaje" class="form-label">Mensaje</label>
                            <textarea class="form-control" id="mensaje" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Enviar Mensaje</button>
                    </form>
                </div>
            </div>

            <div class="col-lg-5">
                 <div class="contact-card contact-info-container">
                    <h3>Información de Contacto</h3>
                    
                    <div class="contact-item">
                        <i class="bi bi-geo-alt-fill"></i>
                        <div>
                            <strong>Dirección:</strong><br>
                            Panamericana Norte, Av. Alfredo Mendiola 6377, Los Olivos 15306
                        </div>
                    </div>

                    <div class="contact-item">
                        <i class="bi bi-telephone-fill"></i>
                        <div>
                            <strong>Teléfono:</strong><br>
                            +51 902 267 106
                        </div>
                    </div>

                    <div class="contact-item">
                        <i class="bi bi-envelope-fill"></i>
                        <div>
                            <strong>Email:</strong><br>
                            contacto@clinica-sonrisas.com
                        </div>
                    </div>

                    <h4 class="mt-5">Horario de Atención</h4>

                    <div class="contact-item">
                        <i class="bi bi-clock-fill"></i>
                        <div>
                            Lunes a Viernes: 9:00 AM - 7:00 PM<br>
                            Sábados: 10:00 AM - 2:00 PM
                        </div>
                    </div>
                    
                    <div class="mt-4 contact-map">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3903.312035313837!2d-77.0727669253681!3d-11.95288464015903!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x9105d1d877f532d7%3A0x8db19fe8e1f40feb!2sUniversidad%20Tecnol%C3%B3gica%20del%20Per%C3%BA!5e0!3m2!1ses-419!2spe!4v1752817713705!5m2!1ses-419!2spe" width="100%" height="200" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>