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
                            Calle Falsa 123, Ciudad, País
                        </div>
                    </div>

                    <div class="contact-item">
                        <i class="bi bi-telephone-fill"></i>
                        <div>
                            <strong>Teléfono:</strong><br>
                            +1 (234) 567-890
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
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d387191.0361093121!2d-74.30934422784534!3d40.69753995874427!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c24fa5d33f083b%3A0xc80b8f06e177fe62!2sNew%20York%2C%20NY%2C%20USA!5e0!3m2!1sen!2scr!4v1687802873193!5m2!1sen!2scr" width="100%" height="200" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>