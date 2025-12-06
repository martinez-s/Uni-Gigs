<?php 
$id=$_GET['id'];
include 'config/bd.php';
$conexion=conexion();
$datos=datos($conexion,$id);
$nombre=$datos['nombre'];
$categoria=$datos['categoria'];
$titulo=$nombre.'.'.$categoria;
$tipo=$datos['tipo'];
$ruta=$datos['ruta'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="bootstrap/bootstrap.min.css">
</head>
<body>
    <div class="container">
            <form class="m-auto w-50 mt-2 mb-2" method="POST" enctype="multipart/form-data" action="acciones/editar.php">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <h3 class="text-center"><?php echo $titulo; ?></h3>
                <div class="mb-2">
                    <label class="form-label">Nombre del archivo</label>
                    <input class='form-control form-control-sm' type="text" name="nombreArchivo" value="<?php echo $nombre ;?>">
                </div>
                <div class="mb-2">
                    <label class="form-label">Selecciona un archivo</label>
                    <input class='form-control form-control-sm' type="file" name="archivo">
                </div>
                <button class="btn btn-primary btn-sm">Actualizar archivo</button>
                <a class="btn btn-warning btn-sm" href="index.php">Regresar</a>
            </form>
            <div class="m-auto w-75 mt-2 text-center">
                <?php 
                if(file_exists("archivo/$ruta")){
                    $valor="";
                    if($categoria=='pdf'){
                        $valor="<iframe class='w-100' height='500' src='archivo/$ruta' frameborder='0'></iframe>";
                    }
                    if($categoria=='png' || $categoria=='jpg'){
                        $valor="<img src='archivo/$ruta' style='max-width: 100%;' >";
                    }
                    if($categoria=='mp4' || $categoria=='mp3'){
                        $valor="<video class='m-auto' controls='true' src='archivo/$ruta' style='max-width: 100%;'></video>";
                    }
                    
                    echo $valor;
                } else {
                    echo "El archivo no existe en la ruta: $ruta";
                }
                ?>
            </div>

    </div>
    <script src="bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>