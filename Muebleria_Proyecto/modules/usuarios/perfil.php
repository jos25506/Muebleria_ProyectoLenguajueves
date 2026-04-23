<?php
session_start();
require_once __DIR__ . '/../../Conexion/conexion.php';
include __DIR__ . '/../../includes/header.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /Muebleria_Proyecto/login.php');
    exit;
}

$db = new Database();
$conn = $db->getConnection();

$id = $_SESSION['usuario_id'];

// Obtener datos del usuario
$query = "SELECT u.*, r.NOMBRE_ROL 
          FROM MUEBLERIA.USUARIO u
          LEFT JOIN MUEBLERIA.ROL r ON u.ID_ROL = r.ID_ROL
          WHERE u.ID_USUARIO = :id";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':id', $id);
oci_execute($stmt);
$usuario = oci_fetch_assoc($stmt);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    
    // Verificar si el correo ya existe (excepto para este usuario)
    $query_check = "SELECT COUNT(*) as total FROM MUEBLERIA.USUARIO 
                    WHERE CORREO = :correo AND ID_USUARIO != :id";
    $stmt_check = oci_parse($conn, $query_check);
    oci_bind_by_name($stmt_check, ':correo', $correo);
    oci_bind_by_name($stmt_check, ':id', $id);
    oci_execute($stmt_check);
    $row_check = oci_fetch_assoc($stmt_check);
    
    if ($row_check['TOTAL'] > 0) {
        echo "<div class='alert alert-danger'>El correo ya está registrado por otro usuario</div>";
    } else {
        if (!empty($_POST['clave_actual']) && !empty($_POST['clave_nueva'])) {
            // Verificar contraseña actual
            $clave_actual = $_POST['clave_actual'];
            $clave_nueva = $_POST['clave_nueva'];
            
            if ($clave_actual != $usuario['CLAVE']) { 
                echo "<div class='alert alert-danger'>La contraseña actual es incorrecta</div>";
            } else {
                // Actualizar con nueva contraseña
                $query = "UPDATE MUEBLERIA.USUARIO 
                          SET NOMBRE = :nombre, CORREO = :correo, CLAVE = :clave
                          WHERE ID_USUARIO = :id";
                $stmt = oci_parse($conn, $query);
                oci_bind_by_name($stmt, ':clave', $clave_nueva);
            }
        } else {
            // Actualizar sin cambiar contraseña
            $query = "UPDATE MUEBLERIA.USUARIO 
                      SET NOMBRE = :nombre, CORREO = :correo
                      WHERE ID_USUARIO = :id";
            $stmt = oci_parse($conn, $query);
        }
        
        oci_bind_by_name($stmt, ':id', $id);
        oci_bind_by_name($stmt, ':nombre', $nombre);
        oci_bind_by_name($stmt, ':correo', $correo);
        
        if (oci_execute($stmt)) {
            $_SESSION['usuario_nombre'] = $nombre;
            $_SESSION['usuario_correo'] = $correo;
            
            echo "<script>
                alert('Perfil actualizado exitosamente');
                window.location.href = 'perfil.php';
            </script>";
        } else {
            $error = oci_error($stmt);
            echo "<div class='alert alert-danger'>Error: " . $error['message'] . "</div>";
        }
    }
}
?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-user-circle"></i> Información del Usuario
            </div>
            <div class="card-body text-center">
                <i class="fas fa-user-circle" style="font-size: 100px; color: #2c3e50;"></i>
                <h3 class="mt-3"><?php echo $usuario['NOMBRE']; ?></h3>
                <p class="text-muted">
                    <i class="fas fa-envelope"></i> <?php echo $usuario['CORREO']; ?>
                </p>
                <p>
                    <span class="badge bg-info"><?php echo $usuario['NOMBRE_ROL']; ?></span>
                    <span class="badge bg-success"><?php echo $usuario['ESTADO']; ?></span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-edit"></i> Editar Perfil
            </div>
            <div class="card-body">
                <form method="POST" onsubmit="return validarFormulario()">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="nombre" class="form-label">Nombre completo *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required 
                                   value="<?php echo htmlspecialchars($usuario['NOMBRE']); ?>">
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label for="correo" class="form-label">Correo electrónico *</label>
                            <input type="email" class="form-control" id="correo" name="correo" required 
                                   value="<?php echo htmlspecialchars($usuario['CORREO']); ?>">
                        </div>
                        
                        <div class="col-md-12">
                            <h5 class="mt-3">Cambiar Contraseña</h5>
                            <p class="text-muted">(Dejar vacío para no cambiar)</p>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="clave_actual" class="form-label">Contraseña actual</label>
                            <input type="password" class="form-control" id="clave_actual" name="clave_actual">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="clave_nueva" class="form-label">Nueva contraseña</label>
                            <input type="password" class="form-control" id="clave_nueva" name="clave_nueva">
                        </div