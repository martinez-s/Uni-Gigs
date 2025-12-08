<?php
function conexion(){
    $conexion = mysqli_connect("localhost", "root", "", "archivoDebug");
    return $conexion;
}

function insertar($conexion, $nombre, $categoria, $fecha, $tipo, $ruta){
    $sql = "INSERT INTO archivo (nombre, categoria, fecha, tipo, ruta) VALUES ('$nombre', '$categoria', '$fecha', '$tipo', '$ruta')";
    $query = mysqli_query($conexion, $sql);
    return $query;
}

function listar($conexion){
    $sql = "SELECT * FROM archivo";
    $query = mysqli_query($conexion, $sql);
    return $query;
}

function datos($conexion, $id){
    $sql = "SELECT * FROM archivo WHERE id=$id";
    $query = mysqli_query($conexion, $sql);
    $datos = mysqli_fetch_assoc($query);
    return $datos;
}

function eliminar($conexion, $id){
    $datos = datos($conexion, $id);
    if($datos){
        $ruta = $datos['ruta'];
        if(file_exists($ruta)){
            unlink($ruta); 
        }
    }
    $sql = "DELETE FROM archivo WHERE id=$id";
    $query = mysqli_query($conexion, $sql);
    return $query;
}

function editarNombre($conexion, $id, $nombre){
    $sql = "UPDATE archivo SET nombre='$nombre' WHERE id=$id";
    $query = mysqli_query($conexion, $sql);
    return $query;
}

function editarArchivo($conexion, $id, $categoria, $tipo, $fecha, $ruta){
    $sql = "UPDATE archivo SET categoria='$categoria', tipo='$tipo', fecha='$fecha', ruta='$ruta' WHERE id=$id";
    $query = mysqli_query($conexion, $sql);
    return $query;
}

function editar($conexion, $id, $nombre, $categoria, $tipo, $fecha, $ruta){
    $sql = "UPDATE archivo SET nombre='$nombre', categoria='$categoria', tipo='$tipo', fecha='$fecha', ruta='$ruta' WHERE id=$id";
    $query = mysqli_query($conexion, $sql);
    return $query;
}
?>