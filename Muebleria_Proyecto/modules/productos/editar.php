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

$id = $_GET['id'] ?? 0;

// Obtener categorías para el select
$query_cat = "SELECT ID_CATEGORIA, NOMBRE_CATEGORIA FROM MUEBLERIA.CATEGORIA ORDER BY NOMBRE_CATEGORIA";
$stmt_cat = oci_parse($conn, $query_cat);
oci_execute($stmt_cat);

// Obtener proveedores para el select
$query_prov = "SELECT ID_PROVEEDOR, NOMBRE FROM MUEBLERIA.PROVEEDOR ORDER BY NOMBRE";
$stmt_prov = oci_parse($conn, $query_prov);
oci_execute($stmt_prov);

// Procesar el formulario de edición
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $madera = $_POST['madera'];
    $medidas = $_POST['medidas'];
    $foto_url = $_POST['foto_url'];
    $id_categoria = $_POST['id_categoria'];
    $id_proveedor = $_POST['id_proveedor'];
    $estado = $_POST['estado'];
    $id = $_POST['id'];
    
    // Actualizar producto
    $query = "UPDATE MUEBLERIA.PRODUCTO 
              SET NOMBRE = :nombre, 
                  DESCRIPCION = :descripcion, 
                  PRECIO = :precio, 
                  MADERA = :madera, 
                  MEDIDAS = :medidas, 
                  FOTO_URL = :foto_url, 
                  ID_CATEGORIA = :id_categoria, 
                  ID_PROVEEDOR = :id_proveedor, 
                  ESTADO = :estado
              WHERE ID_PRODUCTO = :id";
    
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':id', $id);
    oci_bind_by_name($stmt, ':nombre', $nombre);
    oci_bind_by_name($stmt, ':descripcion', $descripcion);
    oci_bind_by_name($stmt, ':precio', $precio);
    oci_bind_by_name($stmt, ':madera', $madera);
    oci_bind_by_name($stmt, ':medidas', $medidas);
    oci_bind_by_name($stmt, ':foto_url', $foto_url);
    oci_bind_by_name($stmt, ':id_categoria', $id_categoria);
    oci_bind_by_name($stmt, ':id_proveedor', $id_proveedor);
    oci_bind_by_name($stmt, ':estado', $estado);
    
    if (oci_execute($stmt)) {
        // Alerta de éxito con SweetAlert2
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: '¡Excelente!',
                text: 'Producto actualizado exitosamente',
                showConfirmButton: true,
                confirmButtonColor: '#2c3e50'
            }).then((result) => {
                window.location.href = 'productos.php';
            });
        </script>";
    } else {
        $error = oci_error($stmt);
        // Alerta de error con SweetAlert2
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Error al actualizar: " . addslashes($error['message']) . "',
                confirmButtonColor: '#2c3e50'
            });
        </script>";
    }
}

// Obtener datos del producto
$query = "SELECT * FROM MUEBLERIA.PRODUCTO WHERE ID_PRODUCTO = :id";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':id', $id);
oci_execute($stmt);
$producto = oci_fetch_assoc($stmt);

if (!$producto) {
    echo "<script>
        Swal.fire({
            icon: 'warning',
            title: 'Producto no encontrado',
            text: 'El producto que intenta editar no existe',
            confirmButtonColor: '#2c3e50'
        }).then((result) => {
            window.location.href = 'productos.php';
        });
    </script>";
    exit;
}
?>

<div class="card">
    <div class="card-header">
        <i class="fas fa-edit"></i> Editar Producto
    </div>
    <div class="card-body">
        <form method="POST" onsubmit="return validarFormulario(event)">
            <input type="hidden" name="id" value="<?php echo $producto['ID_PRODUCTO']; ?>">
            
            <div class="row">
                <div class="col-md-4 text-center mb-3">
                    <div class="card">
                        <div class="card-header">
                            Imagen Actual
                        </div>
                        <div class="card-body">
                            <?php if (!empty($producto['FOTO_URL'])): ?>
                                <img src="<?php echo htmlspecialchars($producto['FOTO_URL']); ?>" 
                                     alt="Producto" 
                                     class="img-fluid mb-3" 
                                     style="max-height: 150px; object-fit: contain; border: 1px solid #ddd; padding: 5px;"
                                     onerror="this.onerror=null; this.src='https://via.placeholder.com/150x150?text=Error';">
                                <p>
                                    <a href="<?php echo htmlspecialchars($producto['FOTO_URL']); ?>" 
                                       target="_blank" 
                                       class="btn btn-sm btn-info">
                                        <i class="fas fa-external-link-alt"></i> Ver original
                                    </a>
                                </p>
                            <?php else: ?>
                                <img src="https://via.placeholder.com/150x150?text=Sin+Imagen" 
                                     alt="Sin imagen" 
                                     class="img-fluid mb-3">
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombre" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required 
                                   value="<?php echo htmlspecialchars($producto['NOMBRE']); ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="precio" class="form-label">Precio *</label>
                            <input type="number" class="form-control" id="precio" name="precio" step="0.01" required 
                                   value="<?php echo $producto['PRECIO']; ?>">
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?php echo htmlspecialchars($producto['DESCRIPCION']); ?></textarea>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="madera" class="form-label">Madera</label>
                            <input type="text" class="form-control" id="madera" name="madera" 
                                   value="<?php echo htmlspecialchars($producto['MADERA']); ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="medidas" class="form-label">Medidas</label>
                            <input type="text" class="form-control" id="medidas" name="medidas" 
                                   value="<?php echo htmlspecialchars($producto['MEDIDAS']); ?>">
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label for="foto_url" class="form-label">URL de la imagen</label>
                            <input type="url" class="form-control" id="foto_url" name="foto_url" 
                                   value="<?php echo htmlspecialchars($producto['FOTO_URL']); ?>"
                                   placeholder="https://ejemplo.com/imagen.jpg">
                            <small class="text-muted">Ingrese la URL completa de la imagen (de Freepik o similar)</small>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="id_categoria" class="form-label">Categoría *</label>
                            <select class="form-control" id="id_categoria" name="id_categoria" required>
                                <option value="">Seleccione...</option>
                                <?php 
                                oci_execute($stmt_cat);
                                while ($cat = oci_fetch_assoc($stmt_cat)): 
                                ?>
                                <option value="<?php echo $cat['ID_CATEGORIA']; ?>" 
                                    <?php echo $cat['ID_CATEGORIA'] == $producto['ID_CATEGORIA'] ? 'selected' : ''; ?>>
                                    <?php echo $cat['NOMBRE_CATEGORIA']; ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="id_proveedor" class="form-label">Proveedor *</label>
                            <select class="form-control" id="id_proveedor" name="id_proveedor" required>
                                <option value="">Seleccione...</option>
                                <?php 
                                oci_execute($stmt_prov);
                                while ($prov = oci_fetch_assoc($stmt_prov)): 
                                ?>
                                <option value="<?php echo $prov['ID_PROVEEDOR']; ?>" 
                                    <?php echo $prov['ID_PROVEEDOR'] == $producto['ID_PROVEEDOR'] ? 'selected' : ''; ?>>
                                    <?php echo $prov['NOMBRE']; ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-control" id="estado" name="estado">
                                <option value="ACTIVO" <?php echo $producto['ESTADO'] == 'ACTIVO' ? 'selected' : ''; ?>>ACTIVO</option>
                                <option value="INACTIVO" <?php echo $producto['ESTADO'] == 'INACTIVO' ? 'selected' : ''; ?>>INACTIVO</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Actualizar Producto
                </button>
                <a href="productos.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function validarFormulario(event) {
    event.preventDefault(); // Prevenir envío automático
    
    var nombre = document.getElementById('nombre').value;
    var precio = document.getElementById('precio').value;
    var id_categoria = document.getElementById('id_categoria').value;
    var id_proveedor = document.getElementById('id_proveedor').value;
    
    // Validaciones
    if (nombre.trim() == '') {
        Swal.fire({
            icon: 'warning',
            title: 'Campo requerido',
            text: 'Por favor ingrese el nombre del producto',
            confirmButtonColor: '#2c3e50'
        });
        return false;
    }
    
    if (precio.trim() == '' || parseFloat(precio) <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Campo requerido',
            text: 'Por favor ingrese un precio válido',
            confirmButtonColor: '#2c3e50'
        });
        return false;
    }
    
    if (id_categoria == '') {
        Swal.fire({
            icon: 'warning',
            title: 'Campo requerido',
            text: 'Por favor seleccione una categoría',
            confirmButtonColor: '#2c3e50'
        });
        return false;
    }
    
    if (id_proveedor == '') {
        Swal.fire({
            icon: 'warning',
            title: 'Campo requerido',
            text: 'Por favor seleccione un proveedor',
            confirmButtonColor: '#2c3e50'
        });
        return false;
    }
    
    // Si todo está bien, enviar el formulario
    event.target.submit();
    return true;
}
</script>

<?php
$db->close();
include __DIR__ . '/../../includes/footer.php';
?>