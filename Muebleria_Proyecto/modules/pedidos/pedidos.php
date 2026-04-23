<?php
require_once __DIR__ . '/../../Conexion/conexion.php';
include __DIR__ . '/../../includes/header.php';

$db = new Database();
$conn = $db->getConnection();

if (!$conn) {
    die("<div class='alert alert-danger'>Error de conexión</div>");
}

/* BUSCADOR */
$buscar = "";
if (isset($_GET['buscar'])) {
    $buscar = trim($_GET['buscar']);
}

/* PKG_PEDIDO.SP_LISTAR_PEDIDOS
   Cursor 3: ID_PEDIDO, FECHA, ESTADO, TOTAL, CLIENTE */
$stid = oci_parse($conn, 'BEGIN PKG_PEDIDO.SP_LISTAR_PEDIDOS(:cursor); END;');
$cursor = oci_new_cursor($conn);
oci_bind_by_name($stid, ':cursor', $cursor, -1, OCI_B_CURSOR);
oci_execute($stid);
oci_execute($cursor);

$pedidos = [];
while ($row = oci_fetch_assoc($cursor)) {
    $pedidos[] = $row;
}

oci_free_statement($stid);
oci_free_statement($cursor);

/* Filtro por buscador en PHP */
if ($buscar !== '') {
    $pedidos = array_filter($pedidos, function ($p) use ($buscar) {
        return stripos($p['CLIENTE'], $buscar) !== false
            || stripos((string)$p['ID_PEDIDO'], $buscar) !== false;
    });
}
?>

<h1><i class="fas fa-shopping-cart"></i> Pedidos</h1>

<a href="nuevo.php" class="btn btn-primary mb-3">
    <i class="fas fa-cart-plus"></i> Nuevo Pedido
</a>

<form method="GET" class="mb-3">
    <div class="input-group">
        <input type="text" name="buscar" class="form-control"
               placeholder="Buscar por cliente o ID de pedido"
               value="<?php echo htmlspecialchars($buscar); ?>">
        <button class="btn btn-dark"><i class="fas fa-search"></i> Buscar</button>
        <?php if ($buscar !== ''): ?>
            <a href="pedidos.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Limpiar
            </a>
        <?php endif; ?>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Total</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($pedidos)): ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                        No hay pedidos registrados.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($pedidos as $row): ?>
                <tr>
                    <td><?php echo $row['ID_PEDIDO']; ?></td>
                    <td><?php echo htmlspecialchars($row['FECHA']); ?></td>
                    <td><?php echo htmlspecialchars($row['CLIENTE']); ?></td>
                    <td>₡<?php echo number_format($row['TOTAL'], 2); ?></td>
                    <td>
                        <?php
                        switch ($row['ESTADO']) {
                            case 'PENDIENTE': echo "<span class='badge bg-warning text-dark'>PENDIENTE</span>"; break;
                            case 'ENVIADO':   echo "<span class='badge bg-primary'>ENVIADO</span>";             break;
                            case 'ENTREGADO': echo "<span class='badge bg-success'>ENTREGADO</span>";           break;
                            case 'CANCELADO': echo "<span class='badge bg-danger'>CANCELADO</span>";            break;
                            default: echo "<span class='badge bg-secondary'>" . htmlspecialchars($row['ESTADO']) . "</span>";
                        }
                        ?>
                    </td>
                    <td>
                        <a href="detalle.php?id=<?php echo $row['ID_PEDIDO']; ?>"
                           class="btn btn-info btn-sm" title="Ver detalle">
                            <i class="fas fa-eye"></i> Ver
                        </a>
                        <a href="editar.php?id=<?php echo $row['ID_PEDIDO']; ?>"
                           class="btn btn-warning btn-sm" title="Editar estado">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="javascript:void(0);"
                           onclick="confirmarEliminacion(<?php echo $row['ID_PEDIDO']; ?>)"
                           class="btn btn-danger btn-sm" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function confirmarEliminacion(id) {
    Swal.fire({
        title: '¿Eliminar pedido?',
        text: 'Esta acción eliminará el pedido y su detalle. No se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74c3c',
        cancelButtonColor: '#2c3e50',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `eliminar.php?id=${id}`;
        }
    });
}
</script>

<?php
$db->close();
include __DIR__ . '/../../includes/footer.php';
?>
