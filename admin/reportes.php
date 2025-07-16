<?php
require_once __DIR__ . '/../includes/config.php';
verificar_rol('administrador');

// Usamos el método compatible para obtener las especialidades
$especialidades = $mysqli->query("SELECT * FROM especialidades ORDER BY nombre_especialidad");
?>
<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<h1>Reportes de Citas</h1>
<p>Genera y descarga un reporte de citas filtrado por fechas y, opcionalmente, por especialidad.</p>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" target="_blank">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="fecha_inicio">Desde la Fecha</label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="fecha_fin">Hasta la Fecha</label>
                    <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="id_especialidad">Especialidad (Opcional)</label>
                    <select name="id_especialidad" id="id_especialidad" class="form-select">
                        <option value="">-- Todas --</option>
                        <?php if ($especialidades && $especialidades->num_rows > 0): ?>
                            <?php while($e = $especialidades->fetch_assoc()): ?>
                            <option value="<?php echo $e['id_especialidad']; ?>"><?php echo htmlspecialchars($e['nombre_especialidad']); ?></option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
            
            <button type="submit" formaction="generar_reporte.php" class="btn btn-success">
                <i class="bi bi-file-earmark-spreadsheet"></i> Exportar a CSV
            </button>
            
            <button type="submit" formaction="generar_reporte_pdf.php" class="btn btn-danger">
                <i class="bi bi-file-earmark-pdf"></i> Exportar a PDF
            </button>
        </form>
    </div>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>