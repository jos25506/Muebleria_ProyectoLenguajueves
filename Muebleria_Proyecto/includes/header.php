<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Mueblería</title>
    <link rel="stylesheet" href="/Muebleria_Proyecto/assets/Css/estilo.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/Muebleria_Proyecto/index.php">
                <i class="fas fa-chair"></i> Mueblería
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/Muebleria_Proyecto/modules/productos/productos.php">
                            <i class="fas fa-couch"></i> Productos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/Muebleria_Proyecto/modules/clientes/clientes.php">
                            <i class="fas fa-users"></i> Clientes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/Muebleria_Proyecto/modules/pedidos/pedidos.php">
                            <i class="fas fa-shopping-cart"></i> Pedidos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/Muebleria_Proyecto/modules/proveedores/proveedores.php">
                            <i class="fas fa-truck"></i> Proveedores
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/Muebleria_Proyecto/modules/inventario/inventario.php">
                            <i class="fas fa-boxes"></i> Inventario
                        </a>
                    </li>
                    <!-- NUEVOS MÓDULOS -->
                    <li class="nav-item">
                        <a class="nav-link" href="/Muebleria_Proyecto/modules/compra/compras.php">
                            <i class="fas fa-cart-arrow-down"></i> Compras
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/Muebleria_Proyecto/modules/pagos/pagos.php">
                            <i class="fas fa-credit-card"></i> Pagos
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> Usuario
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/Muebleria_Proyecto/modules/usuarios/perfil.php">Mi Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Cerrar Sesión</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">