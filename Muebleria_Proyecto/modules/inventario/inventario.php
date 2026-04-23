<?php
require_once __DIR__ . '/../../Conexion/conexion.php';
include __DIR__ . '/../../includes/header.php';

$db  = new Database();
$conn = $db->getConnection();

// ── Llamar SP_LISTAR_INVENTARIO (cursor explicito #1 del paquete) ──────────
$cursor = oci_new_cursor($conn);
$stmt= oci_parse($conn, "BEGIN PKG_INVENTARIO.SP_LISTAR_INVENTARIO(:cur); END;");
oci_bind_by_name($stmt, ':cur', $cursor, -1, OCI_B_CURSOR);
oci_execute($stmt);
oci_execute($cursor);

// ── Llamar SP_ALERTA_STOCK_BAJO (cursor explicito #2 del paquete) ──────────
$cursorAlerta= oci_new_cursor($conn);
$stmtAlerta= oci_parse($conn, "BEGIN PKG_INVENTARIO.SP_ALERTA_STOCK_BAJO(:cur); END;");
oci_bind_by_name($stmtAlerta, ':cur', $cursorAlerta, -1, OCI_B_CURSOR);
oci_execute($stmtAlerta);
oci_execute($cursorAlerta);

$alertas = [];
while ($a = oci_fetch_assoc($cursorAlerta)) {
    $alertas[]= $a;
}
oci_free_statement($cursorAlerta);
?>

<h1>Inventario</h1>

<?php if (!empty($alertas)): ?>
<div class="alert alert-warning">
    <strong><i class="fas fa-exclamation-triangle"></i> Alerta de Stock Bajo!!</strong>
    — Los siguientes productos estan por debajo del stock minimo(se activa por PKG_INVENTARIO.SP_ALERTA_STOCK_BAJO):
    <ul class="mb-0 mt-1">
    <?php foreach ($alertas as $a): ?>
        <li>
            <strong><?= htmlspecialchars($a['PRODUCTO']) ?></strong>:
            stock actual <?= $a['STOCK_ACTUAL'] ?>, minimo <?= $a['STOCK_MINIMO'] ?>,
            faltan <?= $a['FALTAN'] ?> unidades
        </li>
    <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<a href="nuevo.php" class="btn btn-primary mb-3">Nuevo Registro</a>

<table class="table table-striped">
<thead class="table-dark">
<tr>
    <th>ID</th>
    <th>Producto</th>
    <th>Stock Actual</th>
    <th>Stock Mínimo</th>
    <th>Estado Stock</th>
    <th>Acciones</th>
</tr>
</thead>
<tbody>
<?php while ($row = oci_fetch_assoc($cursor)): ?>
<tr>
    <td><?= $row['ID_INVENTARIO'] ?></td>
    <td><?= htmlspecialchars($row['PRODUCTO']) ?></td>
    <td><?= $row['STOCK_ACTUAL'] ?></td>
    <td><?= $row['STOCK_MINIMO'] ?></td>
    <td>
        <?php if ($row['STOCK_ACTUAL'] <= $row['STOCK_MINIMO']): ?>
            <span class="badge bg-danger">BAJO STOCK</span>
        <?php else: ?>
            <span class="badge bg-success">STOCK SUFICIENTE</span>
        <?php endif; ?>
    </td>
    <td>
        <a href="editar.php?id=<?= $row['ID_INVENTARIO'] ?>"
           class="btn btn-warning btn-sm">Editar</a>
        <a href="javascript:void(0);"
           onclick="confirmarEliminacion(<?= $row['ID_INVENTARIO'] ?>)"
           class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></a>
    </td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

<script>
function confirmarEliminacion(id) {
    Swal.fire({
        title: '¿Eliminar inventario?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74c3c',
        cancelButtonColor: '#2c3e50',
        confirmButtonText: 'Sí eliminar',
        cancelButtonText: 'Cancelar'
    }).then(result => {
        if (result.isConfirmed) window.location.href = `eliminar.php?id=${id}`;
    });
}
</script>

<?php
oci_free_statement($cursor);
$db->close();
include __DIR__ . '/../../includes/footer.php';
?>