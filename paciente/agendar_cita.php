<?php
require_once __DIR__ . '/../includes/config.php';
verificar_rol('paciente');

// Obtener el id_paciente a partir del id_usuario en la sesión
$id_usuario = $_SESSION['id_usuario'];
$stmt_paciente = $mysqli->prepare("SELECT id_paciente FROM pacientes WHERE id_usuario = ?");
$stmt_paciente->bind_param("i", $id_usuario);
$stmt_paciente->execute();
$result_paciente = $stmt_paciente->get_result();
if ($result_paciente->num_rows === 0) {
    die("Error: No se encontró el perfil del paciente.");
}
$id_paciente = $result_paciente->fetch_assoc()['id_paciente'];
$stmt_paciente->close();

$mensaje = '';
$tipo_mensaje = '';
$cita_agendada_exito = false; // Nueva variable para controlar el SweetAlert

// --- LÓGICA DE GUARDADO ACTUALIZADA ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_doctor = $_POST['id_doctor'] ?? null;
    $fecha_hora_cita_full = $_POST['fecha_hora_cita_full'] ?? null; 

    if ($fecha_hora_cita_full) {
        list($fecha_cita, $hora_cita_temp) = explode(' ', $fecha_hora_cita_full, 2);
        $hora_cita = substr($hora_cita_temp, 0, 8); // Aseguramos HH:MM:SS
    } else {
        $fecha_cita = null;
        $hora_cita = null;
    }

    if (empty($id_doctor) || empty($fecha_cita) || empty($hora_cita)) {
        $mensaje = 'Debes seleccionar una especialidad, doctor y un horario disponible.';
        $tipo_mensaje = 'danger';
    } else {
        $mysqli->begin_transaction();
        try {
            // Verificar que el horario sigue disponible (doble chequeo)
            $sql_verif = "SELECT id_cita FROM citas WHERE id_doctor = ? AND fecha_cita = ? AND hora_cita = ? AND estado IN ('agendada', 'completada')";
            $stmt_verif = $mysqli->prepare($sql_verif);
            $stmt_verif->bind_param("iss", $id_doctor, $fecha_cita, $hora_cita);
            $stmt_verif->execute();
            if ($stmt_verif->get_result()->num_rows > 0) {
                throw new Exception('El horario seleccionado ya no está disponible. Por favor, elige otro.');
            }
            $stmt_verif->close();

            // Insertar la nueva cita
            $sql_insert = "INSERT INTO citas (id_paciente, id_doctor, fecha_cita, hora_cita, estado) VALUES (?, ?, ?, ?, 'agendada')";
            $stmt_insert = $mysqli->prepare($sql_insert);
            $stmt_insert->bind_param("iiss", $id_paciente, $id_doctor, $fecha_cita, $hora_cita);
            $stmt_insert->execute();
            
            $id_cita_generada = $mysqli->insert_id; 
            
            $stmt_insert->close();

            // Manejar la subida del historial médico en PDF
            if (isset($_FILES['historial_pdf']) && $_FILES['historial_pdf']['error'] == 0) {
                $upload_dir = ROOT_PATH . '/uploads/historiales_pacientes/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $file_name = time() . '_' . basename($_FILES['historial_pdf']['name']);
                $target_file = $upload_dir . $file_name;
                $db_path = 'uploads/historiales_pacientes/' . $file_name;

                // Mover el archivo y guardar la ruta en la BD
                if (move_uploaded_file($_FILES['historial_pdf']['tmp_name'], $target_file)) {
                    $stmt_historial = $mysqli->prepare("INSERT INTO historial_medico (id_paciente, id_cita, archivo_historial) VALUES (?, ?, ?)");
                    $stmt_historial->bind_param("iis", $id_paciente, $id_cita_generada, $db_path);
                    $stmt_historial->execute();
                    $stmt_historial->close();
                } else {
                    throw new Exception('Hubo un error al subir tu historial médico.');
                }
            }

            $mysqli->commit();
            // ¡CAMBIO CLAVE AQUÍ! No establecer el mensaje directamente, sino la bandera
            $cita_agendada_exito = true; 

        } catch (Exception $e) {
            $mysqli->rollback();
            $mensaje = $e->getMessage();
            $tipo_mensaje = 'danger';
        }
    }
}

// Obtener todas las especialidades activas para el formulario
$especialidades = $mysqli->query("SELECT id_especialidad, nombre_especialidad FROM especialidades WHERE estado = 'activo' ORDER BY nombre_especialidad ASC");
?>

<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* Estilos para el contenedor de horarios */
    #horarios-disponibles {
        display: flex;
        flex-wrap: wrap;
        gap: 10px; /* Espacio entre los elementos de horario */
        padding: 15px; /* Más padding para que no se vea tan apretado */
    }

    /* Estilos para cada opción de horario (radio button y label) */
    .form-check-inline {
        margin-right: 0; /* Eliminar el margen predeterminado de Bootstrap si es muy grande */
    }

    .form-check-label {
        background-color: #e9ecef; /* Un color de fondo suave */
        border: 1px solid #ced4da;
        border-radius: .25rem;
        padding: .5rem .75rem; /* Más padding interno */
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        white-space: nowrap; /* Evita que el texto se rompa en varias líneas */
        font-size: 0.95rem; /* Ajustar tamaño de fuente */
    }

    .form-check-input:checked + .form-check-label {
        background-color: #007bff; /* Color primario de Bootstrap */
        color: white;
        border-color: #007bff;
        box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, .25); /* Sombra al seleccionar */
    }

    .form-check-input:hover + .form-check-label {
        background-color: #dbe2e7; /* Color de fondo al pasar el ratón */
    }

    /* Ocultar el radio button nativo */
    .form-check-input[type="radio"] {
        display: none;
    }
</style>

<h1>Agendar Nueva Cita 🗓️</h1>
<p>Sigue los pasos para encontrar un horario disponible con el especialista que necesitas.</p>

<?php if ($mensaje && !$cita_agendada_exito): // Mostrar solo si hay error, ya que el éxito se maneja con SA ?>
    <div class="alert alert-<?php echo $tipo_mensaje; ?>" role="alert">
        <?php echo htmlspecialchars($mensaje); ?>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="id_especialidad" class="form-label fw-bold">1. Selecciona la Especialidad</label>
                    <select id="id_especialidad" class="form-select" required>
                        <option value="">-- Elige una especialidad --</option>
                        <?php while ($esp = $especialidades->fetch_assoc()): ?>
                            <option value="<?php echo $esp['id_especialidad']; ?>"><?php echo htmlspecialchars($esp['nombre_especialidad']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
            </div>
            <div class="col-md-6 mb-3">
                    <label for="id_doctor" class="form-label fw-bold">2. Selecciona el Doctor</label>
                    <select name="id_doctor" id="id_doctor" class="form-select" required disabled>
                        <option value="">-- Primero elige una especialidad --</option>
                    </select>
                </div>

            <div class="row align-items-end">
                <div class="col-md-12 mb-3">
                    <label class="form-label fw-bold">3. Elige un Horario Disponible</label>
                    <div id="horarios-disponibles" class="p-2 border rounded bg-light" style="min-height: 58px;">
                        <span class="text-muted">-- Esperando doctor --</span>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                   <label for="historial_pdf" class="form-label fw-bold">4. Adjuntar Historial Médico (Opcional)</label>
                   <input class="form-control" type="file" name="historial_pdf" id="historial_pdf" accept=".pdf">
                   <small class="form-text text-muted">Sube tu historial médico en formato PDF si lo tienes.</small>
            </div>
            
            <input type="hidden" name="fecha_hora_cita_full" id="fecha_hora_cita_seleccionada">

            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Confirmar Cita</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const especialidadSelect = document.getElementById('id_especialidad');
    const doctorSelect = document.getElementById('id_doctor');
    const horariosContainer = document.getElementById('horarios-disponibles');
    const fechaHoraCitaHiddenInput = document.getElementById('fecha_hora_cita_seleccionada');

    // Variable PHP para detectar éxito de agendamiento
    const citaAgendadaConExito = <?php echo json_encode($cita_agendada_exito); ?>;
    const citasPacienteUrl = '<?php echo BASE_URL; ?>paciente/mis_citas.php'; // Ruta a la página de "Mis Citas"
    const agendarCitaUrl = '<?php echo BASE_URL; ?>paciente/agendar_cita.php'; // Ruta para volver a agendar

    // Si la cita fue agendada con éxito, mostrar SweetAlert
    if (citaAgendadaConExito) {
        Swal.fire({
            title: '¡Cita Agendada con Éxito!',
            text: 'Tu cita ha sido confirmada.',
            icon: 'success',
            showCancelButton: true,
            confirmButtonColor: '#28a745', // Verde para "Ver mis citas"
            cancelButtonColor: '#007bff', // Azul para "Agendar otra cita"
            confirmButtonText: 'Ver mis Citas',
            cancelButtonText: 'Agendar otra Cita',
            reverseButtons: true // Invierte el orden para que el botón "Confirmar" esté a la derecha
        }).then((result) => {
            if (result.isConfirmed) {
                // Si hace clic en "Ver mis Citas"
                window.location.href = citasPacienteUrl;
            } else {
                // Si hace clic en "Agendar otra Cita" o cierra el modal
                window.location.href = agendarCitaUrl;
            }
        });
    }

    especialidadSelect.addEventListener('change', function() {
        const especialidadId = this.value;
        doctorSelect.innerHTML = '<option value="">Cargando doctores...</option>';
        doctorSelect.disabled = true;
        horariosContainer.innerHTML = '<span class="text-muted">-- Primero elige una especialidad --</span>';
        fechaHoraCitaHiddenInput.value = '';

        if (!especialidadId) return;

        fetch(`api/get_doctores.php?id_especialidad=${especialidadId}`)
            .then(response => response.json())
            .then(data => {
                doctorSelect.innerHTML = '<option value="">-- Selecciona un doctor --</option>';
                data.forEach(doctor => {
                    doctorSelect.innerHTML += `<option value="${doctor.id_doctor}">${doctor.nombre_completo}</option>`;
                });
                doctorSelect.disabled = false;
            })
            .catch(error => {
                console.error('Error al cargar doctores:', error);
                doctorSelect.innerHTML = '<option value="">Error al cargar doctores</option>';
            });
    });

    function cargarHorariosDisponibles() {
        const doctorId = doctorSelect.value;

        if (!doctorId) {
            horariosContainer.innerHTML = '<span class="text-muted">-- Esperando doctor --</span>';
            return;
        }

        horariosContainer.innerHTML = '<span class="text-muted">Buscando horarios disponibles...</span>';
        fechaHoraCitaHiddenInput.value = '';

        fetch(`api/get_horarios.php?id_doctor=${doctorId}`) 
            .then(response => response.json())
            .then(data => {
                horariosContainer.innerHTML = '';
                if (data.length > 0) {
                    data.forEach(cita => {
                        const div = document.createElement('div');
                        div.className = 'form-check form-check-inline';
                        const uniqueId = `cita_${cita.fecha_hora_full.replace(/[\s:-]/g, '').replace(/\./g, '')}`;
                        div.innerHTML = `
                            <input class="form-check-input" type="radio" name="fecha_hora_cita_full" 
                                id="${uniqueId}" 
                                value="${cita.fecha_hora_full}" required>
                            <label class="form-check-label" 
                                for="${uniqueId}">
                                ${cita.fecha_formateada} ${cita.hora_formateada}
                            </label>
                        `;
                        horariosContainer.appendChild(div);
                    });
                } else {
                    horariosContainer.innerHTML = '<span class="text-danger">No hay horarios disponibles para este doctor.</span>';
                }
            })
            .catch(error => {
                console.error('Error al cargar horarios:', error);
                horariosContainer.innerHTML = '<span class="text-danger">Error al cargar horarios. Intenta de nuevo.</span>';
            });
    }

    doctorSelect.addEventListener('change', cargarHorariosDisponibles);
    
    horariosContainer.addEventListener('change', function(e) {
        if (e.target.name === 'fecha_hora_cita_full') {
            fechaHoraCitaHiddenInput.value = e.target.value;
        }
    });
});
</script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>