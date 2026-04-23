<?php
session_start();
require_once __DIR__ . '/../../Conexion/conexion.php';
include __DIR__ . '/../../includes/header.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /Muebleria_Proyecto/login.php');
    exit;
}

$db = new Database();
$conn = $db->getConnection();

// Consultar usuarios con su rol
$query = "SELECT u.*, r.NOMBRE_ROL 
          FROM MUEBLERIA.USUARIO u
          LEFT JOIN MUEBLERIA.ROL r ON u.ID_ROL = r.ID_ROL
          ORDER BY u.ID_USUARIO";
$stmt = oci_parse($conn, $query);
oci_execute($stmt);
?>

<div class="card">
    <div class="card-header">
        <i class="fas fa-users-cog"></i> Lista de Usuarios
        <a href="nuevo.php" class="btn btn-primary btn-sm float-end">
            <i class="fas fa-plus"></i> Nuevo Usuario
        </a>
    </div>
    <div class="card-body">
        
        <!-- Buscador -->
        <div class="row mb-3">
            <div class="col-md-6">
                <input type="text" class="form-control" id="buscarUsuario" 
                       placeholder="Buscar usuario..." 
                       onkeyup="buscarTabla()">
            </div>
        </div>
        
        <!-- Tabla de usuarios -->
        <div class="table-responsive">
            <table class="table table-striped table-hover" id="tablaUsuarios">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $hay_usuarios = false;
                    while ($row = oci_fetch_assoc($stmt)): 
                        $hay_usuarios = true;
                    ?>
                    <tr>
                        <td><?php echo $row['ID_USUARIO']; ?></td>
                        <td><?php echo $row['NOMBRE']; ?></td>
                        <td><?php echo $row['CORREO']; ?></td>
                        <td><?php echo $row['NOMBRE_ROL']; ?></td>
                        <td>
                            <?php if ($row['ESTADO'] == 'ACTIVO'): ?>
                                <span class="badge bg-success">ACTIVO</span>
                            <?php else: ?>
                                <span class="badge bg-danger">INACTIVO</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="editar.php?id=<?php echo $row['ID_USUARIO']; ?>" 
                               class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if ($row['ID_USUARIO'] != $_SESSION['usuario_id']): ?>
                                <a href="eliminar.php?id=<?php echo $row['ID_USUARIO']; ?>" 
                                   class="btn btn-danger btn-sm" 
                                   onclick="return confirm('¿Está seguro de eliminar este usuario?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php 
                    endwhile; 
                    
                    if (!$hay_usuarios):
                    ?>
                    <tr>
                        <td colspan="6" class="text-center">
                            <div class="alert alert-info mb-0">
                                No hay usuarios registrados. 
                                <a href="nuevo.php">Agregar el primer usuario</a>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function buscarTabla() {
    var input, filter, table, tr, td, i, txtValue;
    input = document.getElementById("buscarUsuario");
    filter = input.value.toUpperCase();
    table = document.getElementById("tablaUsuarios");
    tr = table.getElementsByTagName("tr");
    
    for (i = 0; i < tr.length; i++) {
        var encontrado = false;
        for (j = 0; j < 3; j++) { // Buscar en nombre, correo y rol
            td = tr[i].getElementsByTagName("td")[j];
            if (td) {
                txtValue = td.textContent || td.innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    encontrado = true;
                    break;
                }
            }
        }
        
        if (encontrado) {
            tr[i].style.display = "";
        } else {
            tr[i].style.display = "none";
        }
    }
}
</script>

<?php
oci_free_statement($stmt);
$db->close();
include __DIR__ . '/../../includes/footer.php';
?>