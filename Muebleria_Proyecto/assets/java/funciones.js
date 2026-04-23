// Confirmar eliminación
function confirmarEliminacion(id, tipo) {
    if (confirm('¿Está seguro de eliminar este registro?')) {
        window.location.href = `eliminar.php?id=${id}&tipo=${tipo}`;
    }
}

// Formatear moneda
function formatearMoneda(valor) {
    return '₡' + new Intl.NumberFormat('es-CR').format(valor);
}

// Validar formulario
function validarFormulario(formId) {
    let formulario = document.getElementById(formId);
    let inputs = formulario.querySelectorAll('input[required], select[required]');
    
    for (let input of inputs) {
        if (!input.value.trim()) {
            alert('Por favor complete todos los campos requeridos');
            input.focus();
            return false;
        }
    }
    return true;
}

// Buscar en tiempo real
function buscarTabla(inputId, tablaId) {
    let input = document.getElementById(inputId);
    let filter = input.value.toUpperCase();
    let table = document.getElementById(tablaId);
    let tr = table.getElementsByTagName('tr');
    
    for (let i = 1; i < tr.length; i++) {
        let td = tr[i].getElementsByTagName('td');
        let encontrado = false;
        
        for (let j = 0; j < td.length; j++) {
            if (td[j]) {
                let txtValue = td[j].textContent || td[j].innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    encontrado = true;
                    break;
                }
            }
        }
        
        if (encontrado) {
            tr[i].style.display = '';
        } else {
            tr[i].style.display = 'none';
        }
    }
}

// Actualizar total en detalles
function actualizarTotal() {
    let cantidades = document.querySelectorAll('.cantidad');
    let precios = document.querySelectorAll('.precio');
    let subtotales = document.querySelectorAll('.subtotal');
    let total = 0;
    
    for (let i = 0; i < cantidades.length; i++) {
        let cantidad = parseFloat(cantidades[i].value) || 0;
        let precio = parseFloat(precios[i].value) || 0;
        let subtotal = cantidad * precio;
        
        if (subtotales[i]) {
            subtotales[i].value = subtotal;
        }
        
        total += subtotal;
    }
    
    document.getElementById('total').value = total;
}