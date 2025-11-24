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
                    <a class="btn btn-light btn-lg" data-bs-toggle="modal" data-bs-target="#modal_registro">
                    REGISTRATE
                    </a>
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

    // Modales de Login y Registro

    //Modal Login
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
                            <input type="password" id="clave" name="clave" class="inputs">
                            <a href="#" class="texto_log_cont"><p >¿OLVIDASTE LA CONTRASEÑA?</p></a>
                            <button type="submit" class="btn_inicio">INICIAR</button>
                            <a class="texto_log_regis" data-bs-toggle="modal" data-bs-target="#modal_registro"><p>¿NO TIENES CUENTA? REGISTRATE</p></a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    //Modal Registro 1
    <div class="modal fade" id="modal_registro" aria-hidden="true" aria-labelledby="modal_registro" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container conte_regis">
                        <form action="login.php" method="post">
                            <h1 class="Titulo titu_modal">REGISTRATE</h1>
                            <h2 class="lb_subtitulo text-center">CUENTANOS SOBRE TI, INGRESA TUS DATOS BÁSICOS</h2>
                                <div class="row">
                                    <div class="col-lg-6 col-md-12">
                                        <label for="nombre" class="lb_modal">NOMBRE</label>
                                        <br>
                                        <input type="text" id="nombre" name="nombre" class="inputs">
                                        <br>
                                        <label for="correo" class="lb_modal">CORREO</label>
                                        <br>
                                        <input type="text" id="correo" name="correo" class="inputs">
                                        <br>
                                        <label for="clave" class="lb_modal">CONTRASEÑA</label>
                                        <br>
                                        <input type="password" id="clave" name="clave" class="inputs">   
                                    </div>
                                    <div class="col-lg-6 col-md-12">
                                        <label for="apellido" class="lb_modal">APELLIDO</label>
                                        <br>
                                        <input type="text" id="nombre" name="nombre" class="inputs">
                                        <br>
                                        <label for="fecha_nacimiento" class="lb_modal">FECHA DE NACIMIENTO</label>
                                        <br>
                                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="inputs">  
                                        <br>
                                        <label for="clave" class="lb_modal">CONFIRMAR CONTRASEÑA</label>
                                        <br>
                                        <input type="password" id="clave" name="clave" class="inputs">       
                                    </div>
                                </div>
                                <div class="d-flex justify-content-center">
                                    <button type="button" class="btn_siguiente" data-bs-target="#modal_registro2" data-bs-toggle="modal" data-bs-dismiss="modal">SIGUIENTE</button>
                                </div>
                                <a class="texto_log_regis" data-bs-toggle="modal" data-bs-target="#modal_login"><p>¿YA TIENES CUENTA? INICIA SESION</p></a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    //Modal Registro 2
    <div class="modal fade" id="modal_registro2" aria-hidden="true" aria-labelledby="modal_registro2" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container conte_regis">
                        <h1 class="Titulo titu_modal">REGISTRATE</h1>
                        <h2 class="lb_subtitulo text-center">PERSONALIZA TU CUENTA, ESCOGE UNA IMAGEN DE PERFIL</h2>
                            <div class="row">
                                <div class="col">
                                    <div class="d-flex justify-content-center my-4">
                                        <label for="input-imagen-perfil" class="circulo-imagen-perfil">
                                            <span class="texto-placeholder">+</span>
                                        </label>
                                        <input type="file" id="input-imagen-perfil" name="imagen_perfil" accept="image/*">
                                    </div>
                                    <a><p class="texto_log_regis" data-bs-target="#modal_registro3" data-bs-toggle="modal" data-bs-dismiss="modal">PREFIERO SALTARME ESTE PASO</p></a>
                                </div>
                            </div>
                        <div class="d-flex justify-content-center">
                            <button type="button" class="btn_siguiente" data-bs-target="#modal_registro3" data-bs-toggle="modal" data-bs-dismiss="modal">SIGUIENTE</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> 
    //Modal Registro 3
    <div class="modal fade" id="modal_registro3" aria-hidden="true" aria-labelledby="modal_registro3" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container conte_regis">
                        <h1 class="Titulo titu_modal">REGISTRATE</h1>
                        <h2 class="lb_subtitulo text-center">AHORA ES MOMENTO DE LA VERIFICACION DE ESTUDIANTE</h2>
                        <div class="row">
                            <div class="col-lg-4 col-md-12 flex-column justify-content-center">
                                <p class="p_carnet" >SUBE UNA FOTO DE TU CARNET ESTUDIANTIL</p>
                                <div class="d-flex justify-content-center">
                                    <label for="input-imagen-carnet" class="imagen_carnet">
                                        <span class="texto-placeholder">+</span>
                                    </label>
                                    <input type="file" id="input-imagen-carnet" name="imagen_carnet" accept="image/*">
                                </div>
                            </div>
                            <div class="col-lg-8 col-md-12 flex-column justify-content-center columna_regis3">
                                <label for="universidad" class="lb_modal">UNIVERSIDAD</label>
                                <br>
                                <input type="text" id="universidad" name="universidad" class="inputs">
                                <br>
                                <label for="carrera" class="lb_modal">CARRERA</label>
                                <br>
                                <input type="text" id="carrera" name="carrera" class="inputs">
                                <div class="d-flex justify-content-center">
                                    <button type="submit" class="btn_siguiente">FINALIZAR REGISTRO</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


            

    //Hasta aqui modales

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

    <script src="login_regis.js"></script>

    //Scripts que deberia poner en otro archivo
    <script>

        <?php if(isset($_GET['error'])): ?>
        

            const errorMessage = "<?php echo htmlspecialchars($_GET['error']); ?>";

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
        

            const successMessage = "<?php echo htmlspecialchars($_GET['success']); ?>";

            Swal.fire({
                icon: "success",
                title: "Inicio de sesión exitoso",
                text: successMessage, 
            });

        <?php endif; ?>
    </script>
    
</body>
</html> 