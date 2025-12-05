<?php
#capturar los datos
$id = $_POST['id'];
$nombre = $_POST['nombreArchivo'];
$archivo = $_FILES['archivo'];

include '../config/bd.php';
$conexion = conexion();
$datos = datos($conexion, $id);
$nombreA = $datos['nombre'];
$ruta_actual = $datos['ruta'];

if(($archivo['size']==0 && $nombre=='') || ($archivo['size']==0 && $nombre==$nombreA) ){ #no modifico el archivo
    header("location:../editar.php?id=$id");
    exit;
}

if(($archivo['size']==0 && $nombre!='') || ($archivo['size']==0 && $nombre!=$nombreA)){
    #solo el nombre
    $query = editarNombre($conexion, $id, $nombre);
    header("location:../editar.php?id=$id&&editar=success");
    exit;
}

# Si se sube un archivo nuevo
if($archivo['size'] > 0){
    #categoria y tipo
    $tipo = $archivo['type'];
    $categoria = pathinfo($archivo['name'], PATHINFO_EXTENSION);

    #fecha
    $fecha = date('Y-m-d H:i:s');

    # Mover el archivo a la carpeta de uploads
    $nombre_archivo = time() . '_' . basename($archivo['name']);
    $nueva_ruta = '../uploads/' . $nombre_archivo;

    if(move_uploaded_file($archivo['tmp_name'], $nueva_ruta)){
        # Eliminar el archivo anterior si existe
        if(file_exists($ruta_actual)){
            unlink($ruta_actual);
        }

        # Actualizar en la base de datos
        if(($archivo['size']>0 && $nombre=='') || ($archivo['size']>0 && $nombre==$nombreA)){
            #modificar solo archivo
            $query = editarArchivo($conexion, $id, $categoria, $tipo, $fecha, $nueva_ruta);
        }
        if(($archivo['size']>0 && $nombre!='') || ($archivo['size']>0 && $nombre!=$nombreA)){
            #modificar todo
            $query = editar($conexion, $id, $nombre, $categoria, $tipo, $fecha, $nueva_ruta);
        }
        header("location:../editar.php?id=$id&&editar=success");
    } else {
        header("location:../editar.php?id=$id&&editar=error");
    }
}
?>