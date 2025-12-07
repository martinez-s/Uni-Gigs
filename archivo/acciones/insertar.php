<?php 
#capturar los datos
$nombre = $_POST['nombreArchivo'];
$archivo = $_FILES['archivo'];

$tipo = $archivo['type'];
$categoria = pathinfo($archivo['name'], PATHINFO_EXTENSION);

#fecha
$fecha = date('Y-m-d H:i:s');

# Mover el archivo a la carpeta de uploads
$nombre_archivo = time() . '_' . basename($archivo['name']);
$ruta_destino = '../uploads/' . $nombre_archivo;

if(move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
    include '../config/bd.php';
    $conexion = conexion();
    $query = insertar($conexion, $nombre, $categoria, $fecha, $tipo, $ruta_destino);
    if($query){
        header('location:../index.php?insertar=success');
    } else {
        // Si falla la inserción, eliminar el archivo subido
        unlink($ruta_destino);
        header('location:../index.php?insertar=error');
    }
} else {
    header('location:../index.php?insertar=error');
}
?>