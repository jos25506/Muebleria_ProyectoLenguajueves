<?php
require_once __DIR__ . '/../../Conexion/conexion.php';
include __DIR__ . '/../../includes/header.php';

$db = new Database();
$conn = $db->getConnection();

// Consultar todos los clientes
$query = "SELECT * FROM MUEBLERIA.CLIENTE ORDER BY ID_CLIENTE";
$stmt = oci_parse($conn, $query);
oci_execute($stmt);
?>
<div class="card">
    <div class="card-header">
        <i class="fas fa-users"></i> Lista de Clientes
        <a href="nuevo.php" class="btn btn-primary btn-sm float-end">
            <i class="fas fa-plus"></i> Nuevo Cliente
        </a>
    </div>
    <div class="card-body">
        
        <!-- Buscador -->
        <div class="row mb-3">
            <div class="col-md-6">
                <input type="text" class="form-control" id="buscarCliente" 
                       placeholder="Buscar cliente por nombre o teléfono..." 
                       onkeyup="buscarTabla()">
            </div>
        </div>
        
        <!-- Tabla de clientes -->
        <div class="table-responsive">
            <table class="table table-striped table-hover" id="tablaClientes">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th>Dirección</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $hay_clientes = false;
                    while ($row = oci_fetch_assoc($stmt)): 
                        $hay_clientes = true;
                    ?>
                    <tr>
                        <td><?php echo $row['ID_CLIENTE']; ?></td>
                        <td><?php echo htmlspecialchars($row['NOMBRE']); ?></td>
                        <td><?php echo htmlspecialchars($row['TELEFONO']); ?></td>
                        <td><?php echo htmlspecialchars($row['CORREO']); ?></td>
                        <td><?php echo htmlspecialchars($row['DIRECCION']); ?></td>
                        <td>
                            <a href="editar.php?id=<?php echo $row['ID_CLIENTE']; ?>" 
                               class="btn btn-warning btn-sm" 
                               title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="javascript:void(0);" 
                               onclick="confirmarEliminacion(<?php echo $row['ID_CLIENTE']; ?>, '<?php echo htmlspecialchars($row['NOMBRE']); ?>')" 
                               class="btn btn-danger btn-sm" 
                               title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php 
                    endwhile; 
                    
                    if (!$hay_clientes):
                    ?>
                    <tr>
                        <td colspan="6" class="text-center">
                            <div class="alert alert-info mb-0">
                                No hay clientes registrados. 
                                <a href="nuevo.php">Agregar el primer cliente</a>
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
// Función para buscar en la tabla
function buscarTabla() {
    var input, filter, table, tr, td, i, txtValue;
    input = document.getElementById("buscarCliente");
    filter = input.value.toUpperCase();
    table = document.getElementById("tablaClientes");
    tr = table.getElementsByTagName("tr");
    
    for (i = 0; i < tr.length; i++) {
        var encontrado = false;
        for (j = 0; j < 2; j++) {
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

// Función para confirmar eliminación con SweetAlert
function confirmarEliminacion(id, nombre) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: `¿Desea eliminar el cliente "${nombre}"? Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74c3c',
        cancelButtonColor: '#2c3e50',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `eliminar.php?id=${id}&confirm=1`;
        }
    });
}

// Función para mostrar mensajes de éxito/error 
function mostrarMensaje(tipo, titulo, texto) {
    Swal.fire({
        icon: tipo,
        title: titulo,
        text: texto,
        confirmButtonColor: '#2c3e50'
    });
}
</script>

<?php
oci_free_statement($stmt);
$db->close();
include __DIR__ . '/../../includes/footer.php';
?>