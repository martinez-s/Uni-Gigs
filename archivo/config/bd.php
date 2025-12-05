<?php
function conexion(){
    $conexion = mysqli_connect("localhost", "root", "", "archivoDebug");
    return $conexion;
}

// Nota: Ahora la función insertar recibe la ruta en lugar del BLOB
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
    // Primero obtenemos la ruta del archivo para eliminarlo del servidor
    $datos = datos($conexion, $id);
    if($datos){
        $ruta = $datos['ruta'];
        if(file_exists($ruta)){
            unlink($ruta); // Elimina el archivo físico
        }
    }
    $sql = "DELETE FROM archivo WHERE id=$id";
    $query = mysqli_query($conexion, $sql);
    return $query;
}

// Función para editar solo el nombre
function editarNombre($conexion, $id, $nombre){
    $sql = "UPDATE archivo SET nombre='$nombre' WHERE id=$id";
    $query = mysqli_query($conexion, $sql);
    return $query;
}

// Función para editar solo el archivo (y por lo tanto la ruta, categoría, tipo, fecha)
function editarArchivo($conexion, $id, $categoria, $tipo, $fecha, $ruta){
    $sql = "UPDATE archivo SET categoria='$categoria', tipo='$tipo', fecha='$fecha', ruta='$ruta' WHERE id=$id";
    $query = mysqli_query($conexion, $sql);
    return $query;
}

// Función para editar todo
function editar($conexion, $id, $nombre, $categoria, $tipo, $fecha, $ruta){
    $sql = "UPDATE archivo SET nombre='$nombre', categoria='$categoria', tipo='$tipo', fecha='$fecha', ruta='$ruta' WHERE id=$id";
    $query = mysqli_query($conexion, $sql);
    return $query;
}
?>