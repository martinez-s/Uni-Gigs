<?php
session_start();
include('conect.php');

$sql = "SELECT 
            s.id_servicio, 
            s.titulo, 
            s.descripcion, 
            s.precio,
            c.nombre_carrera, 
            u.rating, 
            u.porcentaje_completacion
        FROM servicios s
        JOIN carreras c ON s.id_carrera = c.id_carrera
        JOIN usuarios u ON s.id_usuario = u.id_usuario";

$resultado = $mysqli->query($sql);

// Verificar si la consulta fue exitosa
if ($resultado === false) {
    die("Error en la consulta: " . $mysqli->error);
}

// Obtener todos los resultados como un array asociativo
$servicios = $resultado->fetch_all(MYSQLI_ASSOC);

// Ahora puedes usar $servicios en tu bucle foreach
?>
?>