<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "bd_calculadora");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Verificar si el formulario ha sido enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los datos del formulario
    $nombre = $conexion->real_escape_string($_POST['nombre']);
    $telefono = $conexion->real_escape_string($_POST['telefono']);
    $correo = $conexion->real_escape_string($_POST['correo']);
    $dpi = $conexion->real_escape_string($_POST['dpi']);

    // Preparar la consulta SQL para insertar los datos
    $sql = "INSERT INTO creditos (nombre_cliente, telefono_cliente, correo_cliente, dpi_cliente)
            VALUES ('$nombre', '$telefono', '$correo', '$dpi')";

    if ($conexion->query($sql) === TRUE) {
        echo "Datos guardados correctamente";
    } else {
        echo "Error: " . $sql . "<br>" . $conexion->error;
    }

    // Cerrar la conexión
    $conexion->close();
}
?>
