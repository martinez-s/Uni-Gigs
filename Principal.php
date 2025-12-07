<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="stylesNav.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Uni-Gigs</title>
</head>
<body>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="main.js"></script>
    
    <?php include 'NavBar.php'; ?>

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
                            <h3 class="titulo">Ofrece un servicio</h3>
                            <p class="subtitulo">Estoy desesperado quiero chamba, pagame por favor, hago trabajos bonitos</p>
                        </div>
                    </button>
                    <button class="servicio-card flex-grow-1" type="button">
                        <div class="card-icono">
                            <span class="material-symbols-outlined">server_person</span>
                        </div>
                        <div class="card-contenido">
                            <h3 class="titulo">Publicar un request</h3>
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
                    <h3 class="Titulo">Explora diferentes requests</h1>
                    <hr>
                    <div class="card" style="width: 18rem;">
                        <div class="card-body">
                            <h5 class="card-title">Titulo coqueto</h5>
                            <div class="separator-line"></div>
                        
                            <h6 class="carrera">
                                <span class="material-symbols-outlined">license</span>
                                Carrera
                            </h6>
                        
                            <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card’s content.</p>
                            <div class="d-flex justify-content-between align-items-center mb-3"> 
                                <div class="star-rating-display" data-rating="3.5"></div>
                                <h4 class="Precio mb-0">$20</h6> 
                            </div>
                            <a href="#" class="btn btn-primary">Mas informacion</a>
                        e</div>
                    </div> 
                </div>
                
            </div>
        </div>
    </div>

    <div id="Inicio" class="banner-container">
        <div class="container-fluid px-5">
            <div class="row align-items-center mt-5">
                <div class="col-md-12 mb-4 mb-md-0">
                    <h3 class="Titulo">Explora diferentes requests</h1>
                    <hr>
                    <div class="card" style="width: 18rem;">
                        <div class="card-body">
                            <h5 class="card-title">Titulo coqueto</h5>
                            <hr>
                            <img src="img/si.png" class="card-img-top" alt="...">
                            <h6 class="carrera">
                                <span class="material-symbols-outlined">license</span>
                                Carrera
                            </h6>
                        
                            <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card’s content.</p>
                            <div class="d-flex justify-content-between align-items-center mb-3"> 
                                <div class="star-rating-display" data-rating="3.5"></div>
                                <h4 class="Precio mb-0">$20</h6> 
                            </div>
                            <a href="#" class="btn btn-primary">Mas informacion</a>
                        </div>
                    </div> 
                </div>
                
            </div>
        </div>
    </div>
     <?php include 'Footer.html.php'; ?>
    
</body>
</html>