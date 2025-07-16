
<?php
require_once __DIR__ . '/../includes/config.php';
verificar_rol('administrador');

// Obtener todos los usuarios de la base de datos
$sql = "SELECT id_usuario, nombre, apellido, correo, rol, fecha_registro FROM usuarios ORDER BY rol, fecha_registro DESC";
$resultado = $mysqli->query($sql);
?>
<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<h1>Ver Todos los Usuarios</h1>
<p>Lista completa de todos los usuarios registrados en el sistema.</p>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre Completo</th>
                        <th>Correo Electrónico</th>
                        <th>Rol</th>
                        <th>Fecha de Registro</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado && $resultado->num_rows > 0): ?>
                        <?php while($usuario = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $usuario['id_usuario']; ?></td>
                                <td><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['correo']); ?></td>
                                <td>
                                    <?php
                                        $rol = $usuario['rol'];
                                        $badge_class = 'bg-secondary';
                                        if ($rol == 'administrador') $badge_class = 'bg-danger';
                                        if ($rol == 'doctor') $badge_class = 'bg-info';
                                        if ($rol == 'paciente') $badge_class = 'bg-primary';
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($rol); ?></span>
                                </td>
                                <td><?php echo date("d/m/Y H:i", strtotime($usuario['fecha_registro'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">No se encontraron usuarios.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>