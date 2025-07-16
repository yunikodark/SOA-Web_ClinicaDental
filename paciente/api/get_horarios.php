<?php
// Habilitar la visualización de errores (QUITAR EN PRODUCCIÓN)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../includes/config.php'; // Asegúrate que esta ruta sea correcta

if (!isset($_GET['id_doctor'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Faltan parámetros.']);
    exit;
}

$id_doctor = (int)$_GET['id_doctor'];
$horarios_disponibles_formateados = [];

// Definir los nombres de los días y meses para el formato legible
$dias_semana_es = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
$meses_es = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

// Rango de días futuros a buscar (ej: los próximos 30 días)
$dias_a_buscar = 30; 
$intervalo_minutos = 30; // Intervalo de citas en minutos

for ($i = 0; $i < $dias_a_buscar; $i++) {
    $fecha_actual_timestamp = strtotime("+$i days");
    $fecha_actual_db = date('Y-m-d', $fecha_actual_timestamp); // Fecha en formato YYYY-MM-DD para la BD
    $dia_semana_actual = $dias_semana_es[date('w', $fecha_actual_timestamp)]; // Nombre del día de la semana (Lunes, Martes, etc.)

    // 1. Obtener el horario laboral del doctor para este día de la semana
    // Esta consulta asume que SOLO HAY UN RANGO DE HORARIO por doctor y día de la semana.
    $stmt_horario = $mysqli->prepare("SELECT hora_inicio, hora_fin FROM horarios WHERE id_doctor = ? AND dia_semana = ?");
    
    if (!$stmt_horario) {
        // Manejo de error si la preparación falla (ej. tabla horarios no existe)
        error_log("Error al preparar stmt_horario: " . $mysqli->error);
        continue; 
    }

    $stmt_horario->bind_param("is", $id_doctor, $dia_semana_actual);
    $stmt_horario->execute();
    $result_horario = $stmt_horario->get_result();

    if ($result_horario->num_rows === 0) {
        // El doctor no trabaja este día o no tiene horario definido, pasar al siguiente día
        $stmt_horario->close();
        continue; 
    }
    
    // Si hay resultados, obtenemos el primer (y se asume único) rango de horario
    $horario = $result_horario->fetch_assoc();
    $stmt_horario->close();

    $hora_inicio_timestamp = strtotime($horario['hora_inicio']);
    $hora_fin_timestamp = strtotime($horario['hora_fin']);

    // 2. Obtener las citas ya agendadas para este doctor en esta fecha
    $stmt_citas = $mysqli->prepare("SELECT hora_cita FROM citas WHERE id_doctor = ? AND fecha_cita = ? AND estado IN ('agendada', 'completada')");
    
    if (!$stmt_citas) {
        error_log("Error al preparar stmt_citas: " . $mysqli->error);
        continue;
    }

    $stmt_citas->bind_param("is", $id_doctor, $fecha_actual_db);
    $stmt_citas->execute();
    $result_citas = $stmt_citas->get_result();
    $citas_agendadas = [];
    while ($cita = $result_citas->fetch_assoc()) {
        $citas_agendadas[] = $cita['hora_cita']; // Formato HH:MM:SS
    }
    $stmt_citas->close();

    // 3. Generar todos los intervalos de tiempo y filtrar los disponibles para este día
    for ($tiempo_slot = $hora_inicio_timestamp; $tiempo_slot < $hora_fin_timestamp; $tiempo_slot += ($intervalo_minutos * 60)) {
        $hora_slot_full = date('H:i:s', $tiempo_slot); // Hora en formato HH:MM:SS para comparación
        
        // No mostrar horarios pasados del día actual (solo si es el día de hoy)
        if ($fecha_actual_db == date('Y-m-d') && $tiempo_slot < time()) {
            continue; 
        }

        // Si el slot no está agendado
        if (!in_array($hora_slot_full, $citas_agendadas)) {
            // Formatear la fecha y hora para la visualización del usuario
            $timestamp_fecha_para_formato = strtotime($fecha_actual_db);
            $dia_semana_num = date('w', $timestamp_fecha_para_formato);
            $dia_del_mes = date('j', $timestamp_fecha_para_formato);
            $mes_num = date('n', $timestamp_fecha_para_formato);
            $año = date('Y', $timestamp_fecha_para_formato);

            $fecha_formateada = $dias_semana_es[$dia_semana_num] . ', ' . $dia_del_mes . ' de ' . $meses_es[$mes_num] . ' de ' . $año;
            $hora_formateada = date('h:i A', $tiempo_slot); // Formato 12 horas con AM/PM

            $horarios_disponibles_formateados[] = [
                'fecha_cita' => $fecha_actual_db,       // YYYY-MM-DD
                'hora_cita' => $hora_slot_full,    // HH:MM:SS
                'fecha_formateada' => $fecha_formateada,
                'hora_formateada' => $hora_formateada,
                'fecha_hora_full' => $fecha_actual_db . ' ' . $hora_slot_full // Valor para el input oculto
            ];
        }
    }
}

header('Content-Type: application/json');
echo json_encode($horarios_disponibles_formateados);
?>