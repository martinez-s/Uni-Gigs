<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="StylesNav.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Uni-Gigs</title>
</head>
<body>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="main.js"></script>
    
    <?php include 'NavbarBusqueda.php'; ?>

    <div id="Inicio" class="banner-container">
        <div class="container-fluid px-5">
            <div class="row align-items-center mt-5">
                <div class="col-md-10 mb-4 mb-md-0">
                    <h1 class="Titulo">Hola, nombre</h1>
                    <p class="texto mb-4 ">Comienza haciendo una publicación, descubre servicios o ayuda a otros a culminar sus tareas.</p> 
                </div>
                <div class="botones-agrupados d-flex flex-column flex-lg-row gap-3">
                        <button class="servicio-card flex-grow-1" type="button">
                            <div class="card-icono">
                                <span class="material-symbols-outlined">server_person</span>
                            </div>
                            <div class="card-contenido">
                                <a href="request.php">
                                <h3 class="titulo">Ofrece un servicio</h3>
                                </a>
                                <p class="subtitulo">Estoy desesperado quiero chamba, pagame por favor, hago trabajos bonitos</p>
                            </div>
                        </button>
                        <button class="servicio-card flex-grow-1" type="button">
                            <div class="card-icono">
                                <span class="material-symbols-outlined">server_person</span>
                            </div>
                            <div class="card-contenido">
                                <a href="request.php">
                                <h3 class="titulo">Publicar un request</h3>
                                </a>
                                <p class="subtitulo">Ayuda coy a raspar una materia, ofrezco a mi perro y jalobolas </p>
                            </div>
                        </button>
                    
                </div>
            </div>
        </div>
    </div>

    <div id="Inicio" class="banner-container">
    <div class="container-fluid px-5">
        
        <div class="row align-items-center mt-5">
            <div class="col-md-12 mb-4 mb-md-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="Titulo mb-0">Explora diferentes servicios</h3> 
                    <a href="#" class="mas text-decoration-none">Ver más</a>
                </div>
                <hr>
            </div>
        </div>

        <?php
        $base_url = '/uni-gigs/';
        include('conect.php');
        $sql = "SELECT 
            s.id_servicio, s.titulo, s.descripcion, s.precio,
            c.nombre_carrera, u.rating, u.porcentaje_completacion,
            MIN(f.url_foto) AS url_foto
            FROM servicios s
            JOIN carreras c ON s.id_carrera = c.id_carrera
            JOIN usuarios u ON s.id_usuario = u.id_usuario
            JOIN fotos_servicios f ON s.id_servicio = f.id_servicio
            GROUP BY s.id_servicio
            ";

        $resultado = $mysqli->query($sql);

        if ($resultado && $resultado->num_rows > 0) {
        ?><div class="row">
            <?php
            while ($row = $resultado->fetch_assoc()) {
            ?><div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">
                <div class="card"> <div class="card-body d-flex flex-column">
                    
                    <h5 class="card-title"><?php echo htmlspecialchars($row['titulo']); ?></h5>
                    <div class="separator-line"></div>
                    <div class="img-wrapper">
                    <img class="imagen" src="public/img/imgSer/<?php echo htmlspecialchars($row['url_foto']); ?>">
                    </div>
                    <h6 class="carrera">
                        <span class="material-symbols-outlined">license</span>
                        <?php echo htmlspecialchars($row['nombre_carrera']); ?>
                    </h6>
                
                    <p class="card-text flex-grow-1"><?php echo htmlspecialchars($row['descripcion']); ?></p>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3 mt-3"> 
                        <div class="star-rating-display" data-rating="<?php echo htmlspecialchars($row['rating']); ?>"></div>
                        <h5 class="Precio mb-0">$<?php echo htmlspecialchars($row['precio']); ?></h5> 
                    </div>
                    
                    <a href="#" class="btn btn-primary mt-auto">Mas informacion</a>
                </div>
                </div> 
            </div><?php
            } 
            ?>
        </div><?php 
        } 
        ?>
    
    </div> 
</div>
    




    

    <div id="Inicio" class="banner-container">
    <div class="container-fluid px-5">
        
        <div class="row align-items-center mt-5">
            <div class="col-md-12 mb-4 mb-md-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="Titulo mb-0">Explora diferentes Requests</h3> 
                    <a href="#" class="mas text-decoration-none">Ver más</a>
                </div>
                <hr>
            </div>
        </div>

        <?php
        include('conect.php');
        $sql = "SELECT 
                    s.id_servicio, s.titulo, s.descripcion, s.precio,
                    c.nombre_carrera, u.rating, u.porcentaje_completacion
                FROM servicios s
                JOIN carreras c ON s.id_carrera = c.id_carrera
                JOIN usuarios u ON s.id_usuario = u.id_usuario
                ";

        $resultado = $mysqli->query($sql);

        if ($resultado && $resultado->num_rows > 0) {
        ?><div class="row">
            <?php
            while ($row = $resultado->fetch_assoc()) {
            ?><div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">
                <div class="card"> <div class="card-body d-flex flex-column">
                    
                    <h5 class="card-title"><?php echo htmlspecialchars($row['titulo']); ?></h5>
                    <div class="separator-line"></div>
                
                    <h6 class="carrera">
                        <span class="material-symbols-outlined">license</span>
                        <?php echo htmlspecialchars($row['nombre_carrera']); ?>
                    </h6>
                
                    <p class="card-text flex-grow-1"><?php echo htmlspecialchars($row['descripcion']); ?></p>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3 mt-3"> 
                        <div class="star-rating-display" data-rating="<?php echo htmlspecialchars($row['rating']); ?>"></div>
                        <h5 class="Precio mb-0">$<?php echo htmlspecialchars($row['precio']); ?></h5> 
                    </div>
                    
                    <a href="#" class="btn btn-primary mt-auto">Mas informacion</a>
                </div>
                </div> 
            </div><?php
            } 
            ?>
        </div><?php 
        } 
        ?>
    
    </div> 
</div>


    <?php include 'Footer.html.php'; ?>
    
</body>
</html>