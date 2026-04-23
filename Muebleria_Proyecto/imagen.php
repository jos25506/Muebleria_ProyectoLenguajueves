<?php
echo "<h2>🔍 Verificando imagen de fondo</h2>";

// Ruta donde debería estar la imagen
$ruta_imagen = $_SERVER['DOCUMENT_ROOT'] . '/Muebleria_Proyecto/assets/fondo/';

echo "<p><strong>Buscando en:</strong> " . $ruta_imagen . "</p>";

// Verificar si la carpeta existe
if (is_dir($ruta_imagen)) {
    echo "<p style='color:green'>✅ La carpeta 'fondo' existe</p>";
    
    // Listar archivos en la carpeta
    $archivos = scandir($ruta_imagen);
    echo "<p><strong>Archivos encontrados:</strong></p>";
    echo "<ul>";
    foreach ($archivos as $archivo) {
        if ($archivo != '.' && $archivo != '..') {
            echo "<li>" . $archivo . " - <strong>Ruta para usar:</strong> /Muebleria_Proyecto/assets/fondo/" . $archivo . "</li>";
        }
    }
    echo "</ul>";
} else {
    echo "<p style='color:red'>❌ La carpeta 'fondo' NO existe en assets/</p>";
    echo "<p>Debes crearla en: C:\\xampp\\htdocs\\Muebleria_Proyecto\\assets\\fondo\\</p>";
}
?>