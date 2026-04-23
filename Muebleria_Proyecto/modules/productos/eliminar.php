<?php
session_start();
require_once __DIR__ . '/../../Conexion/conexion.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /Muebleria_Proyecto/login.php');
    exit;
}

$db = new Database();
$conn = $db->getConnection();

$id = $_GET['id'] ?? 0;

// Verificar si el producto existe antes de eliminar
$query_check = "SELECT COUNT(*) as existe, NOMBRE FROM MUEBLERIA.PRODUCTO WHERE ID_PRODUCTO = :id GROUP BY NOMBRE";
$stmt_check = oci_parse($conn, $query_check);
oci_bind_by_name($stmt_check, ':id', $id);
oci_execute($stmt_check);
$row_check = oci_fetch_assoc($stmt_check);

if (!$row_check || $row_check['EXISTE'] == 0) {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css'>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
    <script>
        Swal.fire({
            icon: 'warning',
            title: 'Producto no encontrado',
            text: 'El producto que intenta eliminar no existe',
            confirmButtonColor: '#2c3e50'
        }).then((result) => {
            window.location.href = 'productos.php';
        });
    </script>
    </body>
    </html>";
    exit;
}

$nombre_producto = $row_check['NOMBRE'];

// Verificar si el producto tiene dependencias - CORREGIDO
$query_deps = "SELECT 
                    (SELECT COUNT(*) FROM MUEBLERIA.INVENTARIO WHERE ID_PRODUCTO = :id1) as inventario,
                    (SELECT COUNT(*) FROM MUEBLERIA.DETALLE_PEDIDO WHERE ID_PRODUCTO = :id2) as pedidos,
                    (SELECT COUNT(*) FROM MUEBLERIA.DETALLE_COMPRA WHERE ID_PRODUCTO = :id3) as compras
                FROM DUAL";
$stmt_deps = oci_parse($conn, $query_deps);
oci_bind_by_name($stmt_deps, ':id1', $id);
oci_bind_by_name($stmt_deps, ':id2', $id);
oci_bind_by_name($stmt_deps, ':id3', $id);
oci_execute($stmt_deps);
$row_deps = oci_fetch_assoc($stmt_deps);

if ($row_deps['INVENTARIO'] > 0 || $row_deps['PEDIDOS'] > 0 || $row_deps['COMPRAS'] > 0) {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css'>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'No se puede eliminar',
            text: 'El producto \"$nombre_producto\" tiene registros asociados en inventario, pedidos o compras.',
            confirmButtonColor: '#2c3e50'
        }).then((result) => {
            window.location.href = 'productos.php';
        });
    </script>
    </body>
    </html>";
    exit;
}

// Si pasó todas las validaciones, mostrar confirmación antes de eliminar
if (!isset($_GET['confirm'])) {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css'>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
    <script>
        Swal.fire({
            title: '¿Estás seguro?',
            text: '¿Desea eliminar el producto \"$nombre_producto\"? Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            cancelButtonColor: '#2c3e50',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'eliminar.php?id=$id&confirm=1';
            } else {
                window.location.href = 'productos.php';
            }
        });
    </script>
    </body>
    </html>";
    exit;
}

// Eliminar producto
$query_delete = "DELETE FROM MUEBLERIA.PRODUCTO WHERE ID_PRODUCTO = :id";
$stmt_delete = oci_parse($conn, $query_delete);
oci_bind_by_name($stmt_delete, ':id', $id);

if (oci_execute($stmt_delete)) {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css'>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
    <script>
        Swal.fire({
            icon: 'success',
            title: '¡Eliminado!',
            text: 'El producto \"$nombre_producto\" ha sido eliminado exitosamente.',
            confirmButtonColor: '#2c3e50'
        }).then((result) => {
            window.location.href = 'productos.php';
        });
    </script>
    </body>
    </html>";
} else {
    $error = oci_error($stmt_delete);
    echo "<!DOCTYPE html>
    <html>
    <head>
        <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css'>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al eliminar: " . addslashes($error['message']) . "',
            confirmButtonColor: '#2c3e50'
        }).then((result) => {
            window.location.href = 'productos.php';
        });
    </script>
    </body>
    </html>";
}

$db->close();
?>