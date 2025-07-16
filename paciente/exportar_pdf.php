<?php
// Requerimos los archivos de configuración y la librería FPDF
require_once __DIR__ . '/../includes/config.php';
// POR ESTA OTRA:
require_once ROOT_PATH . '/includes/fpdf186/fpdf.php';

verificar_rol('paciente');

// Validar que se recibió un ID de cita numérico
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de cita no válido.");
}

$id_cita = intval($_GET['id']);
$id_usuario_paciente = $_SESSION['id_usuario'];

// Consulta para obtener TODOS los detalles de la cita
$sql = "SELECT 
            c.fecha_cita, c.hora_cita,
            p_user.nombre AS paciente_nombre, p_user.apellido AS paciente_apellido,
            d_user.nombre AS doctor_nombre, d_user.apellido AS doctor_apellido,
            e.nombre_especialidad,
            rc.anotaciones_doctor, rc.recomendaciones, rc.tratamiento
        FROM citas c
        JOIN pacientes p ON c.id_paciente = p.id_paciente
        JOIN usuarios p_user ON p.id_usuario = p_user.id_usuario
        JOIN doctores d ON c.id_doctor = d.id_doctor
        JOIN usuarios d_user ON d.id_usuario = d_user.id_usuario
        JOIN especialidades e ON d.id_especialidad = e.id_especialidad
        LEFT JOIN registros_citas rc ON c.id_cita = rc.id_cita
        WHERE c.id_cita = ? AND p.id_usuario = ?";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ii", $id_cita, $id_usuario_paciente);
$stmt->execute();
$cita = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$cita) {
    die("Cita no encontrada o no tienes permiso para verla.");
}

// --- INICIA LA CREACIÓN DEL PDF ---

class PDF extends FPDF
{
    // Cabecera de página
    function Header()
    {
        // Logo (asegúrate que la ruta sea correcta)
        $this->Image(ROOT_PATH . '/assets/img/logo.png', 10, 8, 33);
        // Arial bold 15
        $this->SetFont('Arial', 'B', 15);
        // Movernos a la derecha
        $this->Cell(80);
        // Título
        $this->Cell(30, 10, 'Historial de Consulta', 0, 0, 'C');
        // Salto de línea
        $this->Ln(20);
    }

    // Pie de página
    function Footer()
    {
        // Posición: a 1,5 cm del final
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Número de página
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    // Un método para crear secciones de forma sencilla
    function ChapterTitle($label)
    {
        $this->SetFont('Arial', 'B', 12);
        $this->SetFillColor(220, 220, 220);
        $this->Cell(0, 6, $label, 0, 1, 'L', true);
        $this->Ln(4);
    }

    function ChapterBody($body)
    {
        $this->SetFont('Arial', '', 12);
        // Usamos MultiCell para que el texto se ajuste automáticamente
        $this->MultiCell(0, 5, $body);
        $this->Ln();
    }
}

// Creación del objeto PDF
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// --- Título y Fecha ---
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Detalles de la Cita Medica', 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Fecha de emision: ' . date('d/m/Y'), 0, 1, 'C');
$pdf->Ln(10);

// --- Datos del Paciente y Doctor ---
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(95, 7, 'Paciente:', 1, 0, 'L');
$pdf->Cell(95, 7, 'Atendido por:', 1, 1, 'L');

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(95, 7, utf8_decode(htmlspecialchars($cita['paciente_nombre'] . ' ' . $cita['paciente_apellido'])), 1, 0, 'L');
$pdf->Cell(95, 7, utf8_decode('Dr(a). ' . htmlspecialchars($cita['doctor_nombre'] . ' ' . $cita['doctor_apellido'])), 1, 1, 'L');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(95, 7, 'Fecha de la Cita:', 1, 0, 'L');
$pdf->Cell(95, 7, 'Especialidad:', 1, 1, 'L');

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(95, 7, date("d/m/Y", strtotime($cita['fecha_cita'])) . ' a las ' . date("h:i A", strtotime($cita['hora_cita'])), 1, 0, 'L');
$pdf->Cell(95, 7, utf8_decode(htmlspecialchars($cita['nombre_especialidad'])), 1, 1, 'L');
$pdf->Ln(10);

// --- Contenido de la consulta ---
$pdf->ChapterTitle('Anotaciones Clinicas');
$pdf->ChapterBody(utf8_decode(htmlspecialchars($cita['anotaciones_doctor'] ?? 'Sin anotaciones.')));

$pdf->ChapterTitle('Recomendaciones');
$pdf->ChapterBody(utf8_decode(htmlspecialchars($cita['recomendaciones'] ?? 'Sin recomendaciones.')));

$pdf->ChapterTitle('Tratamiento Indicado');
$pdf->ChapterBody(utf8_decode(htmlspecialchars($cita['tratamiento'] ?? 'Sin tratamiento especifico.')));


// --- Salida del PDF ---
// 'D' fuerza la descarga del archivo con el nombre especificado.
$nombre_archivo = 'Cita-' . $cita['fecha_cita'] . '.pdf';
$pdf->Output('D', $nombre_archivo);

?>