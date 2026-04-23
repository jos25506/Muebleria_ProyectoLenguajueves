<?php
require_once __DIR__ . '/../../Conexion/conexion.php';
include __DIR__ . '/../../includes/header.php';

$db   = new Database();
$conn = $db->getConnection();

$id = $_GET['id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $id = $_POST['id'];
    $stock_actual = $_POST['stock_actual'];
    $stock_minimo = $_POST['stock_minimo'];

    //PKG_INVENTARIO.SP_ACTUALIZAR_STOCK
    // El trigger trg_inv_stock_actual y trg_inv_stock_minimo se activa en el update
    $resultado = '';
    $stmt = oci_parse($conn,
        "BEGIN PKG_INVENTARIO.SP_ACTUALIZAR_STOCK(:id,:actual, :minimo,:res); END;");
    oci_bind_by_name($stmt,':id', $id,32);
    oci_bind_by_name($stmt,':actual', $stock_actual,32);
    oci_bind_by_name($stmt,':minimo', $stock_minimo,32);
    oci_bind_by_name($stmt,':res', $resultado,500);
    oci_execute($stmt);

    if (strpos($resultado,'OK') === 0) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Inventario actualizado',
                text: " . json_encode($resultado) . "
            }).then(() => window.location = 'inventario.php');
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error al actualizar',
                text: " . json_encode($resultado) . "
            });
        </script>";
    }
}

//FN_OBTENER_INVENTARIO
$cursor = oci_new_cursor($conn);
$stmtGet = oci_parse($conn,
    "BEGIN :cur := PKG_INVENTARIO.FN_OBTENER_INVENTARIO(:id); END;");
oci_bind_by_name($stmtGet,':cur',$cursor, -1, OCI_B_CURSOR);
oci_bind_by_name($stmtGet,':id', $id,32);
oci_execute($stmtGet);
oci_execute($cursor);
$row = oci_fetch_assoc($cursor);
oci_free_statement($cursor);

if (!$row) {
    echo "<div class='alert alert-danger'>Inventario no encontrado</div>";
    include __DIR__ . '/../../includes/footer.php';
    exit;
}
?>

<h1>Editar Inventario</h1>

<p class="text-muted">
    Usa PKG_INVENTARIO.SP_ACTUALIZAR_STOCK.
    Valores negativos activaran los triggers de validacion
</p>

<form method="POST">
    <input type="hidden" name="id" value="<?= $row['ID_INVENTARIO'] ?>">

    <div class="mb-3">
        <label>Producto</label>
        <input type="text" class="form-control"
               value="<?= htmlspecialchars($row['PRODUCTO']) ?>" readonly>
    </div>

    <div class="mb-3">
        <label>Stock Actual</label>
        <input type="number" name="stock_actual" class="form-control"
               value="<?= $row['STOCK_ACTUAL'] ?>">
    </div>

    <div class="mb-3">
        <label>Stock Mínimo</label>
        <input type="number" name="stock_minimo" class="form-control"
               value="<?= $row['STOCK_MINIMO'] ?>">
    </div>

    <button class="btn btn-warning">Actualizar</button>
    <a href="inventario.php" class="btn btn-secondary">Volver</a>
</form>

<?php
$db->close();
include __DIR__ . '/../../includes/footer.php';
?>