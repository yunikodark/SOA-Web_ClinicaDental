<?php
require_once __DIR__ . '/../includes/config.php';
verificar_rol('doctor');

$id_usuario_doctor = $_SESSION['id_usuario'];

// Obtener ID Doctor (compatible)
$stmt_id = $mysqli->prepare("SELECT id_doctor FROM doctores WHERE id_usuario = ?");
$stmt_id->bind_param("i", $id_usuario_doctor);
$stmt_id->execute();
$result_id = $stmt_id->get_result();
if ($result_id->num_rows === 0) {
    die("Error fatal: El usuario actual no es un doctor registrado en la tabla 'doctores'.");
}
$id_doctor = $result_id->fetch_assoc()['id_doctor'];
$stmt_id->close();

// Obtener pacientes (Consulta corregida y más robusta con backticks ``)
$sql = "SELECT DISTINCT `p`.`id_paciente`, `u`.`nombre`, `u`.`apellido`, `u`.`correo` 
        FROM `pacientes` AS `p`
        JOIN `usuarios` AS `u` ON `p`.`id_usuario` = `u`.`id_usuario` 
        JOIN `citas` AS `c` ON `p`.`id_paciente` = `c`.`id_paciente` 
        WHERE `c`.`id_doctor` = ?";

$stmt_pacientes = $mysqli->prepare($sql);

// --- Punto de Depuración ---
// Si la línea de arriba ($mysqli->prepare) falla, $stmt_pacientes será `false`.
// Agregamos una verificación para ver el error exacto de MySQL.
if ($stmt_pacientes === false) {
    // Este mensaje te dirá exactamente qué está mal en la consulta SQL según tu base de datos.
    die("Error al preparar la consulta de pacientes: " . htmlspecialchars($mysqli->error));
}

$stmt_pacientes->bind_param("i", $id_doctor);
$stmt_pacientes->execute();
$pacientes = $stmt_pacientes->get_result();
?>

<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<h1>Mis Pacientes</h1>
<p>Lista de pacientes que han agendado citas contigo.</p>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Nombre Completo</th>
                        <th>Correo Electrónico</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($pacientes->num_rows > 0): ?>
                        <?php while($paciente = $pacientes->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido']); ?></td>
                                <td><?php echo htmlspecialchars($paciente['correo']); ?></td>
                                <td class="text-center">
                                    <a href="ver_historial_paciente.php?id_paciente=<?php echo $paciente['id_paciente']; ?>" class="btn btn-info btn-sm"><i class="bi bi-journal-text"></i> Ver Historial</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center">Aún no tienes pacientes registrados en tu historial de citas.</td></tr>
                    <?php endif; $stmt_pacientes->close(); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>