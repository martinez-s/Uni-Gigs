<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="styles.css">
    <title>Document</title>
</head>
<body>
<?php include 'Navbar.html.php'; ?>
    <div id="Inicio" class="banner-container">
        <div class="container">
            <div class="row align-items-center mt-5">
                <div class="col-md-6 mb-4 mb-md-0 mt-md-3 mt-5">
                    <h1 class="Titulo">Bienvenido a Uni-Gigs</h1>
                    <p class="lead text-justify">Uni-Gigs es una plataforma para estudiantes, donde hacemos los trabajos de los demás porque no nos queremos. 
                        Pero hey, al menos pagan por hacerlos.</p>
                    <a href="#" class="btn btn-light btn-lg">REGISTRATE</a>
                </div>
                <div class="col-md-6 text-center">
                    <img src="img/img_banner.png" class="banner-img img-fluid" alt="Descripción de la imagen">
                </div>
            </div>
        </div>
    </div>
    <div id="Sobre_Nosotros" class="container mb-5">
        <h2 class="Titulo text-center mt-5">Sobre nosotros</h2>
        <div class="row mt-5 mb-5">
            <div class="col-md-6 ">
                <h3>¿Qué es Uni-Gigs?</h3>
                <p class="text-justify">Uni-Gigs es una plataforma diseñada para conectar a estudiantes que necesitan ayuda con sus tareas académicas con otros estudiantes 
                    dispuestos a ofrecer sus servicios. Nuestra misión es facilitar el intercambio de conocimientos y habilidades entre estudiantes, 
                    promoviendo un ambiente colaborativo y de apoyo mutuo.
                </p>
            </div>
            <div class="col-md-6">
                <h3>¿Cómo funciona?</h3>
                <p class="text-justify">Los usuarios pueden registrarse como "solicitantes" o "proveedores". Los solicitantes pueden publicar sus necesidades académicas, 
                    mientras que los proveedores pueden ofrecer sus servicios en diversas áreas de estudio. La plataforma permite la comunicación directa 
                    entre ambas partes, facilitando la negociación y el acuerdo sobre los términos del servicio.
                </p>
            </div>
        </div>
    </div>
    <div id="Funciones" class="container mb-5 mt-5">
        <h2 class="Titulo text-center mt-5">Funciones</h2>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mt-3">
            <div class="col">
                <div class="card h-100 shadow-sm border-0"> <img src="img/Relleno.jpg" class="card-img-top" alt="...">
                    <div class="card-body">
                        <h5 class="card-title">Card title</h5>
                        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card’s content.</p>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card h-100 shadow-sm border-0">
                    <img src="img/Relleno.jpg" class="card-img-top" alt="...">
                    <div class="card-body">
                        <h5 class="card-title">Card title</h5>
                        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card’s content.</p>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card h-100 shadow-sm border-0">
                    <img src="img/Relleno.jpg" class="card-img-top" alt="...">
                    <div class="card-body">
                        <h5 class="card-title">Card title</h5>
                        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card’s content.</p>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card h-100 shadow-sm border-0">
                    <img src="img/Relleno.jpg" class="card-img-top" alt="...">
                    <div class="card-body">
                        <h5 class="card-title">Card title</h5>
                        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card’s content.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modal_login" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container conte_log">
                        <form action="login.php" method="post">
                            <h1 class="Titulo titu_modal">INICIA SESIÓN</h1>
                            <label for="correo" class="lb_modal">CORREO</label>
                            <br>
                            <input type="text" id="correo" name="correo" class="inputs">
                            <br>
                            <label for="clave" class="lb_modal">CONTRASEÑA</label>
                            <br>
                            <input type="text" id="clave" name="clave" class="inputs">
                            <a href="#" class="texto_log_cont"><p >¿OLVIDASTE LA CONTRASEÑA?</p></a>
                            <button type="submit" class="btn_inicio" >INICIAR</button>
                            <a href="#" class="texto_log_regis"><p>¿NO TIENES CUENTA? REGISTRATE</p></a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    

    <div id="Registro" class="container-fluid p-5 bg-light">
        <div class="row">
            <div class="col">
                <div class="banner-footer text-center text-dark d-flex flex-column justify-content-center align-items-center">
                    <h2 class="Titulo">¿Listo para comenzar?</h2>
                    <p class="lead">Únete a Uni-Gigs hoy mismo y descubre cómo podemos ayudarte a alcanzar tus metas académicas.</p>
                    <a href="#" class="btn btn-dark btn-lg">REGISTRATE</a>
                </div>
            </div>
        </div>
    </div>
<?php include 'Footer.html.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    // 1. Verificar si la variable 'error' está presente en la URL usando PHP
        <?php if(isset($_GET['error'])): ?>
        
            // Capturamos el mensaje de error de PHP en una variable JavaScript
            const errorMessage = "<?php echo htmlspecialchars($_GET['error']); ?>";

            // 2. Usamos SweetAlert2 para mostrar el error
            Swal.fire({
                icon: "error",
                title: "Error de inicio de sesión",
                text: errorMessage, 
            });
            var loginModal = new bootstrap.Modal(document.getElementById('modal_login'));
            loginModal.show();

        <?php endif; ?>
    </script>
    <script>
        <?php if(isset($_GET['success'])): ?>
        
            // Capturamos el mensaje de error de PHP en una variable JavaScript
            const successMessage = "<?php echo htmlspecialchars($_GET['success']); ?>";

            // 2. Usamos SweetAlert2 para mostrar el error
            Swal.fire({
                icon: "success",
                title: "Inicio de sesión exitoso",
                text: successMessage, // Usamos el mensaje dinámico capturado
                // Puedes mantener o eliminar la línea del footer si no la necesitas
                // footer: '<a href="#">¿Por qué tengo este problema?</a>' 
            });

        <?php endif; ?>
    </script>
    
</body>
</html> 