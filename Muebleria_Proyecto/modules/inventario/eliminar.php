<?php
require_once __DIR__ . '/../../Conexion/conexion.php';

$db   = new Database();
$conn = $db->getConnection();

$id = $_GET['id'] ?? 0;

//PKG_INVENTARIO.SP_ELIMINAR_INVENTARIO
$resultado = '';
$stmt = oci_parse($conn,
    "BEGIN PKG_INVENTARIO.SP_ELIMINAR_INVENTARIO(:id, :res); END;");
oci_bind_by_name($stmt,':id', $id,32);
oci_bind_by_name($stmt,':res',$resultado, 500);
oci_execute($stmt);

$db->close();

if (strpos($resultado,'OK') === 0) {
    header("Location: inventario.php");
} else {
    // Error redirige con mensaje en query string
    header("Location: inventario.php?error=" . urlencode($resultado));
}
exit;
?>