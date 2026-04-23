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

if (!$conn) {
    die("<div class='alert alert-danger'>Error de conexión</div>");
}

// Obtener lista de clientes para el select
$sqlClientes = "SELECT ID_CLIENTE, NOMBRE FROM MUEBLERIA.CLIENTE ORDER BY NOMBRE";
$stmtClientes = oci_parse($conn, $sqlClientes);
oci_execute($stmtClientes);

// Obtener lista de productos para el select (incluyendo stock)
$sqlProductos = "SELECT p.ID_PRODUCTO, p.NOMBRE, p.PRECIO, NVL(i.STOCK_ACTUAL, 0) as STOCK 
                 FROM MUEBLERIA.PRODUCTO p
                 LEFT JOIN MUEBLERIA.INVENTARIO i ON p.ID_PRODUCTO = i.ID_PRODUCTO
                 WHERE p.ESTADO = 'ACTIVO' 
                 ORDER BY p.NOMBRE";
$stmtProductos = oci_parse($conn, $sqlProductos);
oci_execute($stmtProductos);

$mensaje_error = '';
$mensaje_exito = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cliente = $_POST['cliente'];
    $producto = $_POST['producto'];
    $cantidad = $_POST['cantidad'];
    $estado = $_POST['estado'];
    
    // ============================================
    // VALIDACIONES EXISTENTES
    // ============================================
    $errores = [];
    
    if (empty($cliente)) $errores[] = "Debe seleccionar un cliente";
    if (empty($producto)) $errores[] = "Debe seleccionar un producto";
    if (empty($cantidad) || $cantidad <= 0) $errores[] = "La cantidad debe ser mayor a 0";
    if (!is_numeric($cantidad)) $errores[] = "La cantidad debe ser un número";
    
    if (empty($errores)) {
        // Obtener precio y stock del producto
        $sqlDatos = "SELECT p.PRECIO, NVL(i.STOCK_ACTUAL, 0) as STOCK 
                     FROM MUEBLERIA.PRODUCTO p
                     LEFT JOIN MUEBLERIA.INVENTARIO i ON p.ID_PRODUCTO = i.ID_PRODUCTO
                     WHERE p.ID_PRODUCTO = :producto";
        $stmtDatos = oci_parse($conn, $sqlDatos);
        oci_bind_by_name($stmtDatos, ":producto", $producto);
        oci_execute($stmtDatos);
        $rowDatos = oci_fetch_assoc($stmtDatos);
        $precio = $rowDatos['PRECIO'];
        $stockActual = $rowDatos['STOCK'];
        $subtotal = $precio * $cantidad;
        
        // Validación adicional de stock antes de intentar insertar
        if ($cantidad > $stockActual) {
            $mensaje_error = "Stock disponible: $stockActual unidades. No puede pedir $cantidad unidades.";
        } else {
// ============================================
//PKG_PEDIDO.SP_CREAR_PEDIDO
//  p_id_cliente, p_id_producto, p_cantidad, p_estado, p_id_usuario, p_resultado OUT
// El SP calcula precio, subtotal y crea pedido + detalle en una transaccion
// ============================================
            $stid = oci_parse($conn,
                'BEGIN PKG_PEDIDO.SP_CREAR_PEDIDO(:id_cliente, :id_producto,
                                                   :cantidad, :estado, :id_usuario, :resultado); END;');

            $resultado = '';
            oci_bind_by_name($stid, ':id_cliente',  $cliente);
            oci_bind_by_name($stid, ':id_producto', $producto);
            oci_bind_by_name($stid, ':cantidad',    $cantidad);
            oci_bind_by_name($stid, ':estado',      $estado);
            oci_bind_by_name($stid, ':id_usuario',  $_SESSION['usuario_id']);
            oci_bind_by_name($stid, ':resultado',   $resultado, 500);
            oci_execute($stid);
            oci_free_statement($stid);

            if (str_starts_with($resultado, 'OK')) {
                // Confirmar transacción exitosa
                $mensaje_exito = $resultado;

                // Redirigir con mensaje de éxito usando JavaScript
                echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Pedido creado!',
                        text: '" . addslashes($mensaje_exito) . "',
                        confirmButtonColor: '#2c3e50',
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        window.location.href = 'pedidos.php';
                    });
                </script>";
                exit;
            } else {
                // Manejo de errores devueltos por el paquete
                if (strpos($resultado, 'ORA-20501') !== false) {
                    $mensaje_error = "Total del pedido inválido (debe ser mayor a 0)";
                } elseif (strpos($resultado, 'ORA-20502') !== false) {
                    $mensaje_error = "Cantidad invalida. La cantidad debe ser mayor a 0";
                } elseif (strpos($resultado, 'ORA-20503') !== false) {
                    $mensaje_error = "Subtotal invalido. Verifique que cantidad y precio sean válidos";
                } elseif (strpos($resultado, 'ORA-20504') !== false) {
                    $mensaje_error = "Stock insuficiente. No hay suficiente inventario para completar el pedido";
                } else {
                    $mensaje_error = "Error al registrar pedido: " . $resultado;
                }
            }
        }
    } else {
        $mensaje_error = implode("\n", $errores);
    }
    
    // Mostrar error si existe
    if (!empty($mensaje_error)) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '" . addslashes($mensaje_error) . "',
                confirmButtonColor: '#2c3e50'
            });
        </script>";
    }
}
?>

<style>
/* Estilos para mensajes de error en tiempo real */
.error-message {
    color: #e74c3c;
    font-size: 12px;
    margin-top: 5px;
    display: none;
}

.error-message.show {
    display: block;
}

.input-error {
    border-color: #e74c3c !important;
}

.input-success {
    border-color: #27ae60 !important;
}
</style>

<div class="card">
    <div class="card-header">
        <i class="fas fa-cart-plus"></i> Nuevo Pedido
    </div>
    <div class="card-body">
        <form method="POST" id="formPedido" onsubmit="return validarFormulario(event)">
            <!-- Cliente -->
            <div class="mb-3">
                <label for="cliente" class="form-label">
                    <i class="fas fa-user"></i> Cliente *
                </label>
                <select name="cliente" id="cliente" class="form-control" 
                        onchange="validarCliente()" required>
                    <option value="">Seleccione un cliente...</option>
                    <?php
                    oci_execute($stmtClientes);
                    while ($row = oci_fetch_assoc($stmtClientes)) {
                        echo "<option value='{$row['ID_CLIENTE']}'>{$row['NOMBRE']}</option>";
                    }
                    ?>
                </select>
                <div id="error-cliente" class="error-message">
                    <i class="fas fa-times-circle"></i> Debe seleccionar un cliente
                </div>
            </div>

            <!-- Producto -->
            <div class="mb-3">
                <label for="producto" class="form-label">
                    <i class="fas fa-couch"></i> Producto *
                </label>
                <select name="producto" id="producto" class="form-control" 
                        onchange="validarProducto()" required>
                    <option value="">Seleccione un producto...</option>
                    <?php
                    oci_execute($stmtProductos);
                    while ($row = oci_fetch_assoc($stmtProductos)) {
                        $stock = $row['STOCK'];
                        $stockText = ($stock <= 5) ? " (Stock: $stock)" : "";
                        echo "<option value='{$row['ID_PRODUCTO']}' 
                                    data-precio='{$row['PRECIO']}'
                                    data-stock='{$row['STOCK']}'>
                                    {$row['NOMBRE']} - ₡" . number_format($row['PRECIO'], 0, ',', '.') . "$stockText
                                </option>";
                    }
                    ?>
                </select>
                <div id="error-producto" class="error-message">
                    <i class="fas fa-times-circle"></i> Debe seleccionar un producto
                </div>
                <small id="stock-info" class="text-muted"></small>
            </div>

            <!-- Cantidad -->
            <div class="mb-3">
                <label for="cantidad" class="form-label">
                    <i class="fas fa-hashtag"></i> Cantidad *
                    <small class="text-muted">(solo números enteros positivos)</small>
                </label>
                <input type="number" name="cantidad" id="cantidad" class="form-control" 
                       placeholder="Ej: 1, 2, 3..."
                       min="1"
                       step="1"
                       onkeyup="validarCantidad()"
                       onblur="validarCantidad()"
                       required>
                <div id="error-cantidad" class="error-message">
                    <i class="fas fa-times-circle"></i> La cantidad debe ser un número entero positivo
                </div>
            </div>

            <!-- Estado -->
            <div class="mb-3">
                <label for="estado" class="form-label">
                    <i class="fas fa-circle"></i> Estado *
                </label>
                <select name="estado" id="estado" class="form-control" required>
                    <option value="PENDIENTE">PENDIENTE</option>
                    <option value="ENVIADO">ENVIADO</option>
                    <option value="ENTREGADO">ENTREGADO</option>
                    <option value="CANCELADO">CANCELADO</option>
                </select>
            </div>

            <!-- Resumen del pedido -->
            <div class="alert alert-info mt-3" id="resumen-pedido" style="display: none;">
                <strong><i class="fas fa-receipt"></i> Resumen del pedido:</strong><br>
                <span id="resumen-producto"></span><br>
                <span id="resumen-cantidad"></span><br>
                <span id="resumen-precio"></span><br>
                <strong><span id="resumen-total"></span></strong>
            </div>

            <hr>

            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save"></i> Guardar Pedido
            </button>
            <a href="pedidos.php" class="btn btn-secondary btn-lg">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </form>
    </div>
</div>

<script>
// ============================================
// VALIDACIONES EN TIEMPO REAL PARA PEDIDOS
// ============================================

// 1. Validar selección de cliente
function validarCliente() {
    var input = document.getElementById('cliente');
    var errorDiv = document.getElementById('error-cliente');
    var valor = input.value;
    
    if (valor === '') {
        errorDiv.classList.add('show');
        input.classList.add('input-error');
        input.classList.remove('input-success');
        return false;
    } else {
        errorDiv.classList.remove('show');
        input.classList.remove('input-error');
        input.classList.add('input-success');
        return true;
    }
}

// 2. Validar selección de producto
function validarProducto() {
    var input = document.getElementById('producto');
    var errorDiv = document.getElementById('error-producto');
    var valor = input.value;
    
    if (valor === '') {
        errorDiv.classList.add('show');
        input.classList.add('input-error');
        input.classList.remove('input-success');
        document.getElementById('stock-info').innerHTML = '';
        return false;
    } else {
        errorDiv.classList.remove('show');
        input.classList.remove('input-error');
        input.classList.add('input-success');
        
        // Mostrar stock disponible
        var selectedOption = input.options[input.selectedIndex];
        var stock = selectedOption.getAttribute('data-stock');
        var stockInfo = document.getElementById('stock-info');
        
        if (stock <= 0) {
            stockInfo.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> Producto sin stock disponible</span>';
        } else if (stock <= 5) {
            stockInfo.innerHTML = '<span class="text-warning"><i class="fas fa-clock"></i> Stock bajo: solo ' + stock + ' unidades disponibles</span>';
        } else {
            stockInfo.innerHTML = '<span class="text-success"><i class="fas fa-check-circle"></i> Stock disponible: ' + stock + ' unidades</span>';
        }
        
        actualizarResumen();
        return true;
    }
}

// 3. Validar CANTIDAD
function validarCantidad() {
    var input = document.getElementById('cantidad');
    var errorDiv = document.getElementById('error-cantidad');
    var valor = input.value.trim();
    
    if (valor === '') {
        errorDiv.classList.remove('show');
        input.classList.remove('input-error', 'input-success');
        actualizarResumen();
        return true;
    }
    
    if (parseInt(valor) <= 0) {
        errorDiv.innerHTML = '<i class="fas fa-times-circle"></i> La cantidad debe ser mayor a 0';
        errorDiv.classList.add('show');
        input.classList.add('input-error');
        input.classList.remove('input-success');
        actualizarResumen();
        return false;
    } else {
        // Validar contra stock
        var productoSelect = document.getElementById('producto');
        var selectedOption = productoSelect.options[productoSelect.selectedIndex];
        var stock = parseInt(selectedOption.getAttribute('data-stock')) || 0;
        var cantidad = parseInt(valor);
        
        if (stock > 0 && cantidad > stock) {
            errorDiv.innerHTML = '<i class="fas fa-times-circle"></i> La cantidad (' + cantidad + ') supera el stock disponible (' + stock + ')';
            errorDiv.classList.add('show');
            input.classList.add('input-error');
            input.classList.remove('input-success');
            actualizarResumen();
            return false;
        }
        
        errorDiv.innerHTML = '<i class="fas fa-times-circle"></i> La cantidad debe ser un número entero positivo';
        errorDiv.classList.remove('show');
        input.classList.remove('input-error');
        input.classList.add('input-success');
        actualizarResumen();
        return true;
    }
}

// 4. Actualizar resumen del pedido en tiempo real
function actualizarResumen() {
    var productoSelect = document.getElementById('producto');
    var cantidadInput = document.getElementById('cantidad');
    var resumenDiv = document.getElementById('resumen-pedido');
    
    var productoId = productoSelect.value;
    var cantidad = cantidadInput.value.trim();
    
    if (productoId !== '' && cantidad !== '' && parseInt(cantidad) > 0) {
        // Obtener nombre y precio del producto seleccionado
        var selectedOption = productoSelect.options[productoSelect.selectedIndex];
        var nombreProducto = selectedOption.text.split(' - ')[0];
        var precio = parseFloat(selectedOption.getAttribute('data-precio'));
        var stock = parseInt(selectedOption.getAttribute('data-stock')) || 0;
        var cantidadInt = parseInt(cantidad);
        var subtotal = precio * cantidadInt;
        
        document.getElementById('resumen-producto').innerHTML = '<i class="fas fa-couch"></i> <strong>Producto:</strong> ' + nombreProducto;
        document.getElementById('resumen-cantidad').innerHTML = '<i class="fas fa-hashtag"></i> <strong>Cantidad:</strong> ' + cantidad;
        document.getElementById('resumen-precio').innerHTML = '<i class="fas fa-dollar-sign"></i> <strong>Precio unitario:</strong> ₡' + precio.toLocaleString('es-CR');
        document.getElementById('resumen-total').innerHTML = '<i class="fas fa-receipt"></i> <strong>Total:</strong> ₡' + subtotal.toLocaleString('es-CR');
        
        // Advertencia si excede stock
        if (cantidadInt > stock && stock > 0) {
            document.getElementById('resumen-cantidad').innerHTML += '<br><span class="text-danger"><i class="fas fa-exclamation-triangle"></i> ¡La cantidad excede el stock disponible (' + stock + ')!</span>';
        }
        
        resumenDiv.style.display = 'block';
    } else {
        resumenDiv.style.display = 'none';
    }
}

// 5. Validar TODO el formulario antes de enviar
function validarFormulario(event) {
    event.preventDefault();
    
    var cliente = document.getElementById('cliente').value;
    var producto = document.getElementById('producto').value;
    var cantidad = document.getElementById('cantidad').value.trim();
    
    if (cliente === '') {
        Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'Por favor seleccione un cliente', confirmButtonColor: '#2c3e50' });
        return false;
    }
    
    if (producto === '') {
        Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'Por favor seleccionar un producto', confirmButtonColor: '#2c3e50' });
        return false;
    }
    
    if (cantidad === '') {
        Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'Por favor ingrese la cantidad', confirmButtonColor: '#2c3e50' });
        return false;
    }
    
    if (parseInt(cantidad) <= 0) {
        Swal.fire({ icon: 'warning', title: 'Cantidad inválida', text: 'La cantidad debe ser un número entero positivo', confirmButtonColor: '#2c3e50' });
        return false;
    }
    
    // Validar stock antes de enviar
    var productoSelect = document.getElementById('producto');
    var selectedOption = productoSelect.options[productoSelect.selectedIndex];
    if (selectedOption && selectedOption.value !== '') {
        var stock = parseInt(selectedOption.getAttribute('data-stock')) || 0;
        var cantidadInt = parseInt(cantidad);
        
        if (cantidadInt > stock) {
            Swal.fire({ 
                icon: 'warning', 
                title: 'Stock insuficiente', 
                text: 'Stock disponible: ' + stock + ' unidades. No puede pedir ' + cantidadInt + ' unidades.',
                confirmButtonColor: '#2c3e50' 
            });
            return false;
        }
    }
    
    // Enviar el formulario
    document.getElementById('formPedido').submit();
    return true;
}

// Agregar event listeners
document.getElementById('producto').addEventListener('change', actualizarResumen);
document.getElementById('cantidad').addEventListener('input', actualizarResumen);
document.getElementById('cliente').addEventListener('change', validarCliente);
document.getElementById('producto').addEventListener('change', validarProducto);
</script>

<?php
$db->close();
include __DIR__ . '/../../includes/footer.php';
?>