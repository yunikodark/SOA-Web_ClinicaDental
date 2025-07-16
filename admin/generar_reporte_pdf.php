<?php
require_once __DIR__ . '/../includes/config.php';
require_once ROOT_PATH . '/includes/fpdf186/fpdf.php';

verificar_rol('administrador');

if ($_SERVER['REQUEST_METHOD'] != 'POST' || empty($_POST['fecha_inicio']) || empty($_POST['fecha_fin'])) {
    die("Parámetros inválidos para generar el reporte.");
}

$fecha_inicio = $_POST['fecha_inicio'];
$fecha_fin = $_POST['fecha_fin'];
$id_especialidad = !empty($_POST['id_especialidad']) ? intval($_POST['id_especialidad']) : null;

// --- Lógica de la consulta a la BD (igual que en el generador de CSV) ---
$sql = "SELECT c.fecha_cita, c.hora_cita, c.estado,
            CONCAT(up.nombre, ' ', up.apellido) as paciente,
            CONCAT(ud.nombre, ' ', ud.apellido) as doctor,
            e.nombre_especialidad
        FROM citas c
        JOIN pacientes p ON c.id_paciente = p.id_paciente
        JOIN usuarios up ON p.id_usuario = up.id_usuario
        JOIN doctores d ON c.id_doctor = d.id_doctor
        JOIN usuarios ud ON d.id_usuario = ud.id_usuario
        JOIN especialidades e ON d.id_especialidad = e.id_especialidad
        WHERE c.fecha_cita BETWEEN ? AND ?";
$params = [$fecha_inicio, $fecha_fin];
$types = "ss";

if ($id_especialidad) {
    $sql .= " AND e.id_especialidad = ?";
    $params[] = $id_especialidad;
    $types .= "i";
}
$sql .= " ORDER BY c.fecha_cita, c.hora_cita";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$resultado = $stmt->get_result();


// --- Lógica para la creación del PDF ---
class PDF_Report extends FPDF
{
    // Cabecera de página
    function Header()
    {
        $this->Image(ROOT_PATH . '/assets/img/logo.png', 10, 8, 25);
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'Reporte de Citas', 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 10, 'Generado el: ' . date('d/m/Y'), 0, 1, 'C');
        $this->Ln(10);
    }

    // Pie de página
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo(), 0, 0, 'C');
    }

    // Tabla de datos
    function FancyTable($header, $data)
    {
        $this->SetFillColor(220, 220, 220);
        $this->SetTextColor(0);
        $this->SetDrawColor(128, 128, 128);
        $this->SetLineWidth(.3);
        $this->SetFont('', 'B');
        // Cabecera
        $w = array(30, 25, 30, 70, 70, 40); // Ancho de las columnas
        for($i = 0; $i < count($header); $i++) {
            $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C', true);
        }
        $this->Ln();
        // Restauración de colores y fuentes
        $this->SetFillColor(245, 245, 245);
        $this->SetTextColor(0);
        $this->SetFont('');
        // Datos
        $fill = false;
        foreach($data as $row) {
            $this->Cell($w[0], 6, date('d/m/Y', strtotime($row['fecha_cita'])), 'LR', 0, 'L', $fill);
            $this->Cell($w[1], 6, date('h:i A', strtotime($row['hora_cita'])), 'LR', 0, 'L', $fill);
            $this->Cell($w[2], 6, ucfirst($row['estado']), 'LR', 0, 'L', $fill);
            $this->Cell($w[3], 6, utf8_decode($row['paciente']), 'LR', 0, 'L', $fill);
            $this->Cell($w[4], 6, utf8_decode($row['doctor']), 'LR', 0, 'L', $fill);
            $this->Cell($w[5], 6, utf8_decode($row['nombre_especialidad']), 'LR', 0, 'L', $fill);
            $this->Ln();
            $fill = !$fill;
        }
        // Línea de cierre
        $this->Cell(array_sum($w), 0, '', 'T');
    }
}

$pdf = new PDF_Report('L', 'mm', 'A4'); // L para Landscape (horizontal)
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

// Títulos de la tabla
$header = array('Fecha', 'Hora', 'Estado', 'Paciente', 'Doctor', 'Especialidad');

// Cargar datos
$data = [];
while ($row = $resultado->fetch_assoc()) {
    $data[] = $row;
}
$stmt->close();

$pdf->FancyTable($header, $data);

$nombre_archivo = 'Reporte_Citas_' . date('Y-m-d') . '.pdf';
$pdf->Output('D', $nombre_archivo);