<?php
require_once __DIR__ . '/../../Conexion/conexion.php';

$db = new Database();
$conn = $db->getConnection();

if(!$conn){
    die("Error de conexión");
}

$mensaje = '';
$tipo_mensaje = '';

if(isset($_GET['id'])){
    $id = $_GET['id'];
    
    try {
        oci_commit($conn);
        
        // Eliminar detalles
        $sqlDetalle = "DELETE FROM MUEBLERIA.DETALLE_PEDIDO WHERE ID_PEDIDO = :id";
        $stmtDetalle = oci_parse($conn, $sqlDetalle);
        oci_bind_by_name($stmtDetalle, ":id", $id);
        oci_execute($stmtDetalle);
        
        // Eliminar pedido
        $sqlPedido = "DELETE FROM MUEBLERIA.PEDIDO WHERE ID_PEDIDO = :id";
        $stmtPedido = oci_parse($conn, $sqlPedido);
        oci_bind_by_name($stmtPedido, ":id", $id);
        oci_execute($stmtPedido);
        
        oci_commit($conn);
        $mensaje = "Pedido eliminado correctamente";
        $tipo_mensaje = "success";
        
    } catch (Exception $e) {
        oci_rollback($conn);
        $mensaje = "Error al eliminar: " . $e->getMessage();
        $tipo_mensaje = "error";
    }
} else {
    $mensaje = "ID no especificado";
    $tipo_mensaje = "warning";
}
?>
<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <script>
        Swal.fire({
            icon: '<?php echo $tipo_mensaje; ?>',
            title: '<?php echo ($tipo_mensaje == 'success') ? '¡Eliminado!' : 'Error'; ?>',
            text: '<?php echo addslashes($mensaje); ?>',
            confirmButtonColor: '#2c3e50'
        }).then(() => {
            window.location.href = 'pedidos.php';
        });
    </script>
</body>
</html>
<?php
$db->close();
?>