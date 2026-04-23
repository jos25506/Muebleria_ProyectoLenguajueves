<?php
// Incluir la clase Database
require_once __DIR__ . '/../../Conexion/conexion.php';
include __DIR__ . '/../../includes/header.php';

// Crear instancia de la base de datos
$db = new Database();
$conn = $db->getConnection();

// Verificar conexión
if (!$conn) {
    die("<div class='alert alert-danger'>Error de conexión a la base de datos</div>");
}

// Consultar productos
$query = "SELECT p.*, c.NOMBRE_CATEGORIA 
          FROM MUEBLERIA.PRODUCTO p
          LEFT JOIN MUEBLERIA.CATEGORIA c ON p.ID_CATEGORIA = c.ID_CATEGORIA
          ORDER BY p.ID_PRODUCTO";

$stmt = oci_parse($conn, $query);
oci_execute($stmt);
?>

<style>
/* Estilo para que todas las imágenes tengan el mismo tamaño */
.product-image {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 5px;
    border: 1px solid #ddd;
    padding: 3px;
    background-color: #f8f9fa;
}

.product-image:hover {
    transform: scale(1.1);
    transition: transform 0.3s ease;
    box-shadow: 0 0 10px rgba(0,0,0,0.2);
}
</style>

<h1>Lista de Productos</h1>
<a href="nuevo.php" class="btn btn-primary mb-3">Nuevo Producto</a>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>Imagen</th>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Precio</th>
                <th>Categoría</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = oci_fetch_assoc($stmt)): ?>
            <tr>
                <td>
                    <?php 
                    if (!empty($row['FOTO_URL'])): 
                    ?>
                        <img src="<?php echo htmlspecialchars($row['FOTO_URL']); ?>" 
                             alt="<?php echo htmlspecialchars($row['NOMBRE']); ?>"
                             class="product-image"
                             onerror="this.onerror=null; this.src='https://via.placeholder.com/80x80?text=Error';">
                    <?php else: ?>
                        <img src="https://via.placeholder.com/80x80?text=No+Image" 
                             alt="Sin imagen"
                             class="product-image">
                    <?php endif; ?>
                </td>
                <td><?php echo $row['ID_PRODUCTO']; ?></td>
                <td><?php echo htmlspecialchars($row['NOMBRE']); ?></td>
                <td><?php echo htmlspecialchars($row['DESCRIPCION']); ?></td>
                <td>₡<?php echo number_format($row['PRECIO'], 0, ',', '.'); ?></td>
                <td><?php echo htmlspecialchars($row['NOMBRE_CATEGORIA']); ?></td>
                <td>
                    <span class="badge bg-<?php echo $row['ESTADO'] == 'ACTIVO' ? 'success' : 'danger'; ?>">
                        <?php echo $row['ESTADO']; ?>
                    </span>
                </td>
                <td>
                    <a href="editar.php?id=<?php echo $row['ID_PRODUCTO']; ?>" 
                       class="btn btn-warning btn-sm" 
                       title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                    <!-- CAMBIO IMPORTANTE: Aquí quitamos el confirm nativo y llamamos a la función SweetAlert -->
                    <a href="javascript:void(0);" 
                       onclick="confirmarEliminacion(<?php echo $row['ID_PRODUCTO']; ?>, '<?php echo htmlspecialchars($row['NOMBRE']); ?>')" 
                       class="btn btn-danger btn-sm" 
                       title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Script para SweetAlert -->
<script>
function confirmarEliminacion(id, nombre) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: `¿Desea eliminar el producto "${nombre}"? Esta acción no se puede deshacer.`,
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

// También podemos agregar una función para mostrar mensajes de éxito/error si es necesario
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