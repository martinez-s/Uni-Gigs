<?php 
$id=$_GET['id'];
include "config/bd.php";
$conexion=conexion();
$datos=datos($conexion,$id);

if($datos){
    $ruta = $datos['ruta'];
    $tipo = $datos['tipo'];
    $nombre = $datos['nombre'];
    $categoria = $datos['categoria'];

    // Verificar que el archivo exista
    if(file_exists($ruta)){
        header("Content-type: $tipo");
        header("Content-Disposition: inline; filename=$nombre.$categoria");
        readfile($ruta);
    } else {
        echo "El archivo no existe.";
    }
} else {
    echo "No se encontró el registro.";
}
?>