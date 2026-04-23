<?php
require_once __DIR__ . '/../../Conexion/conexion.php';

$db = new Database();
$conn = $db->getConnection();

if (!$conn) {
    die("Error de conexión");
}

$resultado = '';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

/* -------------------------------------------------------
PKG_PEDIDO.SP_ELIMINAR_PEDIDO(p_id_pedido, p_resultado OUT)
El paquete borra DETALLE_PEDIDO y PEDIDO en una sola transacción respetando las fk
------------------------------------------------------- */
    $stid = oci_parse($conn,
        'BEGIN PKG_PEDIDO.SP_ELIMINAR_PEDIDO(:id_pedido, :resultado); END;');

    oci_bind_by_name($stid, ':id_pedido', $id);
    oci_bind_by_name($stid, ':resultado', $resultado, 500);
    oci_execute($stid);
    oci_free_statement($stid);
} else {
    $resultado = 'Error: ID no especificado';
}

$exitoso = str_starts_with($resultado, 'OK');
$icono   = $exitoso ? 'success' : 'error';
$titulo  = $exitoso ? 'Eliminado' : 'Error';
$db->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<script>
    Swal.fire({
        icon: '<?php echo $icono; ?>',
        title: '<?php echo $titulo; ?>',
        text: '<?php echo addslashes($resultado); ?>',
        confirmButtonColor: '#2c3e50'
    }).then(() => {
        window.location.href = 'pedidos.php';
    });
</script>
</body>
</html>
