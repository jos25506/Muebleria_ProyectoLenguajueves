<?php
require_once __DIR__ . '/../../Conexion/conexion.php';
include __DIR__ . '/../../includes/header.php';

$db = new Database();
$conn = $db->getConnection();

if (!$conn) {
    die("<div class='alert alert-danger'>Error de conexión</div>");
}

if (!isset($_GET['id'])) {
    die("<div class='alert alert-danger'>ID no especificado</div>");
}

$id = (int)$_GET['id'];

/* -------------------------------------------------------
   PROCESAR FORMULARIO
   PKG_PEDIDO.SP_ACTUALIZAR_ESTADO(p_id_pedido, p_estado, p_resultado OUT)
   El paquete valida con REGEXP que el estado sea uno de:
   PENDIENTE ENVIADO ENTREGADO CANCELADO
   ------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $estado = $_POST['estado'] ?? '';

    $stid = oci_parse($conn,
        'BEGIN PKG_PEDIDO.SP_ACTUALIZAR_ESTADO(:id_pedido, :estado, :resultado); END;');

    $resultado = '';
    oci_bind_by_name($stid, ':id_pedido', $id);
    oci_bind_by_name($stid, ':estado',    $estado);
    oci_bind_by_name($stid, ':resultado', $resultado, 500);
    oci_execute($stid);
    oci_free_statement($stid);

    if (str_starts_with($resultado, 'OK')) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Estado actualizado',
                text: '" . addslashes($resultado) . "',
                confirmButtonColor: '#2c3e50'
            }).then(() => window.location.href = 'pedidos.php');
        </script>";
    } else {
        echo "<script>
            Swal.fire({ icon:'error', title:'Error',
                        text:'" . addslashes($resultado) . "',
                        confirmButtonColor:'#2c3e50' });
        </script>";
    }
}

/* -------------------------------------------------------
CARGAR DATOS ACTUALES DEL PEDIDO
PKG_PEDIDO.FN_OBTENER_PEDIDO(p_id) RETURN SYS_REFCURSOR
Cursor: ID_PEDIDO, FECHA, ESTADO, TOTAL, CLIENTE,
CANTIDAD, PRECIO_UNITARIO, SUB_TOTAL, PRODUCTO
------------------------------------------------------- */
$stid = oci_parse($conn, 'BEGIN :cursor := PKG_PEDIDO.FN_OBTENER_PEDIDO(:id); END;');
$cursor = oci_new_cursor($conn);
oci_bind_by_name($stid, ':cursor', $cursor, -1, OCI_B_CURSOR);
oci_bind_by_name($stid, ':id',     $id);
oci_execute($stid);
oci_execute($cursor);

$pedido = oci_fetch_assoc($cursor);
oci_free_statement($stid);
oci_free_statement($cursor);

if (!$pedido) {
    echo "<div class='alert alert-danger'>Pedido #$id no encontrado.</div>";
    $db->close();
    include __DIR__ . '/../../includes/footer.php';
    exit;
}
?>

<div class="card">
    <div class="card-header">
        <i class="fas fa-edit"></i> Editar Estado — Pedido #<?php echo $pedido['ID_PEDIDO']; ?>
        <a href="pedidos.php" class="btn btn-secondary btn-sm float-end">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
    <div class="card-body">

        <!-- Resumen informativo del pedido (solo lectura) -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="fw-bold text-secondary">Cliente</div>
                <div><?php echo htmlspecialchars($pedido['CLIENTE']); ?></div>
            </div>
            <div class="col-md-3">
                <div class="fw-bold text-secondary">Producto</div>
                <div><?php echo htmlspecialchars($pedido['PRODUCTO']); ?></div>
            </div>
            <div class="col-md-3">
                <div class="fw-bold text-secondary">Cantidad</div>
                <div><?php echo $pedido['CANTIDAD']; ?></div>
            </div>
            <div class="col-md-3">
                <div class="fw-bold text-secondary">Total</div>
                <div class="text-success fw-bold">₡<?php echo number_format($pedido['TOTAL'], 2); ?></div>
            </div>
        </div>

        <hr>

        <!-- Formulario: solo cambia el estado lo que el SP soporta -->
        <form method="POST" id="formEditar" onsubmit="return confirmarCambio(event)">
            <div class="mb-3">
                <label for="estado" class="form-label">
                    <i class="fas fa-circle"></i> Estado del Pedido *
                </label>
                <select name="estado" id="estado" class="form-select form-control" required>
                    <?php
                    $estados = ['PENDIENTE', 'ENVIADO', 'ENTREGADO', 'CANCELADO'];
                    foreach ($estados as $e):
                        $sel = ($pedido['ESTADO'] === $e) ? 'selected' : '';
                    ?>
                        <option value="<?php echo $e; ?>" <?php echo $sel; ?>>
                            <?php echo $e; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-warning btn-lg">
                <i class="fas fa-save"></i> Actualizar Estado
            </button>
            <a href="pedidos.php" class="btn btn-secondary btn-lg">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </form>
    </div>
</div>

<script>
function confirmarCambio(event) {
    event.preventDefault();
    const nuevoEstado = document.getElementById('estado').value;
    const estadoActual = '<?php echo $pedido['ESTADO']; ?>';

    if (nuevoEstado === estadoActual) {
        Swal.fire({ icon:'info', title:'Sin cambios',
                    text:'El estado seleccionado es igual al actual.',
                    confirmButtonColor:'#2c3e50' });
        return false;
    }

    Swal.fire({
        title: '¿Confirmar cambio?',
        html: `Estado actual: <strong>${estadoActual}</strong><br>
               Nuevo estado: <strong>${nuevoEstado}</strong>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#e67e22',
        cancelButtonColor: '#2c3e50',
        confirmButtonText: 'Sí, actualizar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('formEditar').submit();
        }
    });
    return false;
}
</script>

<?php
$db->close();
include __DIR__ . '/../../includes/footer.php';
?>
