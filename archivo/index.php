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
        <form class="m-auto w-50 mt-2 mb-2" method="POST" enctype="multipart/form-data" action="acciones/insertar.php">
            <div class="mb-2">
                <label class="form-label">Nombre del archivo</label>
                <input class='form-control form-control-sm' type="text" name="nombreArchivo">
            </div>
            <div class="mb-2">
                <label class="form-label">Selecciona un archivo</label>
                <input class='form-control form-control-sm' type="file" name="archivo">
            </div>
            <button class="btn btn-primary btn-sm">Subir archivo</button>
        </form>
        <table class="table table-sm table-striped">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>categoria</th>
                    <th>Archivo</th>
                    <th>fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    include 'config/bd.php';
                    $conexion=conexion();
                    $query=listar($conexion);
                    $contador=0;
                    while($datos=mysqli_fetch_assoc($query)){
                        $contador++;
                        $id=$datos['id'];
                        $nombre=$datos['nombre'];
                        $categoria=$datos['categoria'];
                        $fecha=$datos['fecha'];
                        $ruta=$datos['ruta'];
                        $tipo=$datos['tipo'];

                    $valor="";
                    if($categoria=='jpg' || $categoria=='png'){
                        // Mostrar la imagen desde la ruta
                        $valor="<img width='50' src='$ruta'>";
                    }

                    if($categoria=='pdf'){
                        $valor="<img  width='50' src='img/pdf.png'>";
                    }

                    if($categoria=='xls' || $categoria=='xlsm' ){
                        $valor="<img  width='50' src='img/exel.png'>";
                    }

                    if($categoria=='doc' || $categoria=='docx'){
                        $valor="<img  width='50' src='img/word.png'>";
                    }
                    if($categoria=='mp4'){
                        $valor="<img  width='50' src='img/desconocido.png'>";
                    }

                    if($categoria=='mp3'){
                        $valor="<img  width='50' src='img/desconocido.png'>";
                    }

                    if($valor==''){
                        $valor="<img  width='50' src='img/desconocido.png'>";
                    }

                    
                ?>
                <tr>
                    <td><?php echo $contador;?></td>
                    <td><?php echo $nombre ;?></td>
                    <td><?php echo $categoria;?></td>
                    <td><a href="archivo/<?php echo $ruta; ?>" download>descargar</a></td>
                    <td><?php echo $fecha ;?></td>
                    <td><a class='btn btn-secondary' href="editar.php?id=<?php echo $id?>">Editar</a> <a class='btn btn-danger' href="archivo/acciones/eliminar.php?id=<?php echo $id?>">Eliminar</a></td>
                </tr>
                <?php 
                    }
                ?>
            </tbody>
        </table>


    </div>
    

<script src="bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>