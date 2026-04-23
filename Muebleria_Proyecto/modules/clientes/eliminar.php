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
$confirm = $_GET['confirm'] ?? 0;

// Verificar si el cliente existe
$query_check = "SELECT COUNT(*) as existe, NOMBRE FROM MUEBLERIA.CLIENTE WHERE ID_CLIENTE = :id GROUP BY NOMBRE";
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
            title: 'Cliente no encontrado',
            text: 'El cliente que intenta eliminar no existe',
            confirmButtonColor: '#2c3e50'
        }).then((result) => {
            window.location.href = 'clientes.php';
        });
    </script>
    </body>
    </html>";
    exit;
}

$nombre_cliente = $row_check['NOMBRE'];

// Verificar si el cliente tiene pedidos
$query_pedidos = "SELECT COUNT(*) as total FROM MUEBLERIA.PEDIDO WHERE ID_CLIENTE = :id";
$stmt_pedidos = oci_parse($conn, $query_pedidos);
oci_bind_by_name($stmt_pedidos, ':id', $id);
oci_execute($stmt_pedidos);
$row_pedidos = oci_fetch_assoc($stmt_pedidos);

if ($row_pedidos['TOTAL'] > 0) {
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
            text: 'El cliente \"$nombre_cliente\" tiene pedidos asociados. No se puede eliminar.',
            confirmButtonColor: '#2c3e50'
        }).then((result) => {
            window.location.href = 'clientes.php';
        });
    </script>
    </body>
    </html>";
    exit;
}

// Si no viene confirmación, mostrar SweetAlert de confirmación
if ($confirm != 1) {
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
            text: '¿Desea eliminar el cliente \"$nombre_cliente\"? Esta acción no se puede deshacer.',
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
                window.location.href = 'clientes.php';
            }
        });
    </script>
    </body>
    </html>";
    exit;
}

// Eliminar cliente
$query_delete = "DELETE FROM MUEBLERIA.CLIENTE WHERE ID_CLIENTE = :id";
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
            text: 'El cliente \"$nombre_cliente\" ha sido eliminado exitosamente.',
            confirmButtonColor: '#2c3e50'
        }).then((result) => {
            window.location.href = 'clientes.php';
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
            window.location.href = 'clientes.php';
        });
    </script>
    </body>
    </html>";
}

$db->close();
?>