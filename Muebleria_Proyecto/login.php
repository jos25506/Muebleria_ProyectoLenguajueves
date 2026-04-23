<?php
session_start();
require_once __DIR__ . '/Conexion/conexion.php';

// Si ya está logueado, redirigir al inicio
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = trim($_POST['correo']);
    $clave = trim($_POST['clave']);
    
    $db = new Database();
    $conn = $db->getConnection();
    
    // Buscar usuario por correo
    $query = "SELECT * FROM MUEBLERIA.USUARIO WHERE CORREO = :correo AND ESTADO = 'ACTIVO'";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':correo', $correo);
    oci_execute($stmt);
    $usuario = oci_fetch_assoc($stmt);
    
    if ($usuario && $clave == $usuario['CLAVE']) {
        $_SESSION['usuario_id'] = $usuario['ID_USUARIO'];
        $_SESSION['usuario_nombre'] = $usuario['NOMBRE'];
        $_SESSION['usuario_correo'] = $usuario['CORREO'];
        $_SESSION['usuario_rol'] = $usuario['ID_ROL'];
        
        header('Location: index.php');
        exit;
    } else {
        $error = 'Correo o contraseña incorrectos';
    }
    
    $db->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Mueblería</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: url('/Muebleria_Proyecto/assets/fondo/imagenmuebleria.png') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        
        /* Capa oscura para que el texto se lea mejor */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.55);
            z-index: 0;
        }
        
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 30px rgba(0,0,0,0.3);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            position: relative;
            z-index: 1;
        }
        
        .login-card h2 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-card h2 i {
            color: #e67e22;
            margin-right: 10px;
        }
        
        .btn-login {
            background-color: #2c3e50;
            color: white;
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        
        .btn-login:hover {
            background-color: #34495e;
        }
        
        .error {
            color: #e74c3c;
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f8d7da;
            border-radius: 5px;
        }
        
        .info {
            background-color: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .info ul {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>
            <i class="fas fa-chair"></i> Mueblería
        </h2>
        
        <?php if ($error): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="info">
            <strong>Usuarios disponibles:</strong>
            <ul class="mb-0 mt-2">
                <li>carlos.ramirez@gmail.com / cram123</li>
                <li>maria.fernandez@gmail.com / mfer234</li>
                <li>luis.gonzalez@gmail.com / lgon345</li>
            </ul>
        </div>
        
        <form method="POST">
            <div class="mb-3">
                <label for="correo" class="form-label">
                    <i class="fas fa-envelope"></i> Correo electrónico
                </label>
                <input type="email" class="form-control" id="correo" name="correo" 
                       value="carlos.ramirez@gmail.com" required>
            </div>
            
            <div class="mb-3">
                <label for="clave" class="form-label">
                    <i class="fas fa-lock"></i> Contraseña
                </label>
                <input type="password" class="form-control" id="clave" name="clave" 
                       value="cram123" required>
            </div>
            
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
            </button>
        </form>
    </div>
</body>
</html>