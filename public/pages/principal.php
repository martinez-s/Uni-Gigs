
<?php 
session_start();

require_once __DIR__ . '/../../conect.php';

// Si el archivo de conexión define $mysqli, úsalo como $conn
if (!isset($conn) && isset($mysqli) && $mysqli instanceof mysqli) {
    $conn = $mysqli;
}

// Si sigue sin existir, abortar con mensaje corto (evita exponer credenciales)
if (!isset($conn) || !($conn instanceof mysqli)) {
    error_log('Error: conexión DB no encontrada en principal.php');
    header('HTTP/1.1 500 Internal Server Error');
    echo 'Error interno de servidor.';
    exit;
}

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../../login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

$stml_usuario = $conn->prepare("SELECT nombre FROM usuarios WHERE id_usuario = ?");
$stml_usuario->bind_param("i", $id_usuario);
$stml_usuario->execute();

$nombre_result = $stml_usuario->get_result();
if ($nombre_result->num_rows > 0) {
    $usuario = $nombre_result->fetch_assoc();
    $nombre = $usuario['nombre'];
} else {
    $nombre = "Usuario";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    
    <?php
    include('../../conect.php');
    include 'NavBar.php';
    ?>


    <div id="Inicio" class="banner-container">
        <div class="container-fluid px-5">
            <div class="row align-items-center mt-5">
                <div class="col-md-10 mb-4 mb-md-0">
                    <h1 class="Titulo">Hola, <?php echo $nombre?></h1>
                    <p class="texto mb-4 ">Comienza haciendo una publicación, descubre servicios o ayuda a otros a culminar sus tareas.</p> 
                </div>
                <div class="botones-agrupados d-flex flex-column flex-lg-row gap-3">
                        <button class="servicio-card flex-grow-1" type="button">
                            <div class="card-icono">
                                <span class="material-symbols-outlined">server_person</span>
                            </div>
                            <div class="card-contenido">
                                <a href="../../request.php">
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
                                <a href="../../request.php">
                                <h3 class="titulo">Publicar un request</h3>
                                </a>
                                <p class="subtitulo">Ayuda coy a raspar una materia, ofrezco a mi perro y jalobolas </p>
                            </div>
                        </button>
                    
                </div>
            </div>
        </div>
    </div>

    <div id="Servicios" class="banner-container">
    <div class="container-fluid px-5">
        
        <div class="row align-items-center mt-5">
            <div class="col-md-12 mb-4 mb-md-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="Titulo mb-0">Explora diferentes servicios</h3> 
                    <a href="VerMasServicio.php" class="mas text-decoration-none">Ver más</a>
                </div>
                <hr>
            </div>
        </div>

        <?php
        // Ya no se incluye "../../conect.php" aquí

        if (isset($_POST['id_carrera_filtro']) && !empty($_POST['id_carrera_filtro'])) {
            $id_carrera_seleccionada = (int)$_POST['id_carrera_filtro'];
        } else {
            $id_carrera_seleccionada = 0; 
        }

        // CORRECCIÓN: Agregar s.id_usuario y s.id_carrera al SELECT y GROUP BY para el formulario.
        $sql = "SELECT 
            s.id_servicio, 
            s.titulo, 
            s.descripcion, 
            s.precio,
            s.fecha_creacion, 
            s.id_usuario,        /* <--- AÑADIDO */
            s.id_carrera,        /* <--- AÑADIDO */
            -- Nombres de tablas relacionadas
            c.nombre_carrera, 
            m.nombre AS nombre_materia,
            tt.nombre AS tipo_trabajo_nombre,
            -- Datos del usuario
            u.nombre AS nombre_usuario,
            u.apellido AS apellido_usuario,
            u.rating, 
            u.porcentaje_completacion,
            -- Foto (solo la primera)
            MIN(f.url_foto) AS url_foto
        FROM 
            servicios s
        JOIN 
            carreras c ON s.id_carrera = c.id_carrera
        JOIN 
            usuarios u ON s.id_usuario = u.id_usuario
        JOIN 
            materias m ON s.id_materia = m.id_materia 
        JOIN 
            tipos_trabajos tt ON s.id_tipo_trabajo = tt.id_tipo_trabajo
        LEFT JOIN 
            fotos_servicios f ON s.id_servicio = f.id_servicio
        ";

        if ($id_carrera_seleccionada > 0) {
            $sql .= " WHERE s.id_carrera = " . $id_carrera_seleccionada;
        }

        // CORRECCIÓN: Agregar s.id_usuario y s.id_carrera al GROUP BY
        $sql .= " GROUP BY 
            s.id_servicio, s.titulo, s.descripcion, s.precio, s.fecha_creacion,
            s.id_usuario, s.id_carrera, /* <--- AÑADIDO */
            c.nombre_carrera, m.nombre, tt.nombre,
            u.nombre, u.apellido, u.rating, u.porcentaje_completacion
        LIMIT 8";

        // Usamos $conn o $mysqli, lo que esté definido. Usaré $conn por consistencia.
        $resultado = $conn->query($sql);

        if ($resultado && $resultado->num_rows > 0) {
        ?>
            <div class="masonry-container">
            <?php
                while ($row = $resultado->fetch_assoc()) {
                ?>
                    <div class="masonry-item">
                        <div class="card"> <div class="card-body d-flex flex-column">
                            
                            <h5 class="card-title"><?php echo htmlspecialchars($row['titulo']); ?></h5>
                            <div class="separator-line"></div>
                                <?php 
                                if ($row['url_foto']) { 
                                ?>
                                    <div class="img-wrapper">
                                    <img class="imagen" src="../../public/img/imgSer/<?php echo htmlspecialchars($row['url_foto']); ?>" alt="Foto del servicio">
                                    </div>
                                <?php 
                                } 
                                ?>
                            <h6 class="carrera">
                                <span class="material-symbols-outlined">license</span>
                                <?php echo htmlspecialchars($row['nombre_carrera']); ?>
                            </h6>
                        
                            <p class="card-text flex-grow-1"><?php echo htmlspecialchars($row['descripcion']); ?></p>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3 mt-3"> 
                                <div class="star-rating-display" data-rating="<?php echo htmlspecialchars($row['rating']); ?>"></div>
                                <h5 class="Precio mb-0">$<?php echo htmlspecialchars($row['precio']); ?></h5> 
                            </div>
                            
                            <form action="MasInfoSer.php" method="POST" class="mt-auto">
                                <input type="hidden" name="id_servicio" value="<?php echo htmlspecialchars($row['id_servicio']); ?>">
                                <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($row['id_usuario']); ?>">
                                <input type="hidden" name="id_carrera" value="<?php echo htmlspecialchars($row['id_carrera']); ?>">
                                
                                <button type="submit" class="btn btn-primary w-100">Más información</button>
                            </form>
                        </div>
                        </div> 
                    </div>
                <?php
                } 
                ?>
            </div>
        <?php 
        } else {
            echo '<div class="col-12"><p class="alert alert-info">No se encontraron servicios para la carrera seleccionada.</p></div>';
        }
        ?>
        </div>
    </div>


        

    <div id="Requests" class="banner-container">
    <div class="container-fluid px-5">
        
        <div class="row align-items-center mt-5">
            <div class="col-md-12 mb-4 mb-md-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="Titulo mb-0">Explora diferentes Requests</h3> 
                    <a href="VerMasRequest.php" class="mas text-decoration-none">Ver más</a>
                </div>
                <hr>
            </div>
        </div>

        <?php
        include('../../conect.php');

        if (isset($_POST['id_carrera_filtro']) && !empty($_POST['id_carrera_filtro'])) {
            $id_carrera_seleccionada = (int)$_POST['id_carrera_filtro'];
        } else {
            $id_carrera_seleccionada = 0; 
        }

        // --- CORRECCIÓN AQUÍ ---
        // Agregamos r.id_usuario y r.id_carrera al SELECT
        $sql = "SELECT 
            r.id_requests, r.titulo, r.descripcion, r.precio,
            r.id_usuario, r.id_carrera,  /* <--- ESTO FALTABA */
            c.nombre_carrera, u.rating, u.porcentaje_completacion,
            MIN(f.url_foto) AS url_foto
            FROM requests r
            JOIN carreras c ON r.id_carrera = c.id_carrera
            JOIN usuarios u ON r.id_usuario = u.id_usuario
            LEFT JOIN fotos_requests f ON r.id_requests = f.id_request";

        if ($id_carrera_seleccionada > 0) {
            $sql .= " WHERE r.id_carrera = " . $id_carrera_seleccionada;
        }

        // --- CORRECCIÓN AQUÍ ---
        // Agregamos r.id_usuario y r.id_carrera al GROUP BY
        $sql .= " GROUP BY 
            r.id_requests, r.titulo, r.descripcion, r.precio,
            r.id_usuario, r.id_carrera, /* <--- ESTO FALTABA */
            c.nombre_carrera, u.rating, u.porcentaje_completacion
            LIMIT 8";


        $resultado = $mysqli->query($sql);

        if ($resultado && $resultado->num_rows > 0) {
        ?>
            <div class="masonry-container">
            <?php
                while ($row = $resultado->fetch_assoc()) {
                ?>
                    <div class="masonry-item">
                        <div class="card"> <div class="card-body d-flex flex-column">
                            
                            <h5 class="card-title"><?php echo htmlspecialchars($row['titulo']); ?></h5>
                            <div class="separator-line"></div>
                                <?php 
                                if ($row['url_foto']) { 
                                ?>
                                    <div class="img-wrapper">
                                    <img class="imagen" src="../../public/img/imgSer/<?php echo htmlspecialchars($row['url_foto']); ?>" alt="Foto del servicio">
                                    </div>
                                <?php 
                                } 
                                ?>
                            <h6 class="carrera">
                                <span class="material-symbols-outlined">license</span>
                                <?php echo htmlspecialchars($row['nombre_carrera']); ?>
                            </h6>
                        
                            <p class="card-text flex-grow-1"><?php echo htmlspecialchars($row['descripcion']); ?></p>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3 mt-3"> 
                                <div class="star-rating-display" data-rating="<?php echo htmlspecialchars($row['rating']); ?>"></div>
                                <h5 class="Precio mb-0">$<?php echo htmlspecialchars($row['precio']); ?></h5> 
                            </div>
                            
                            <form action="MasInfoReq.php" method="POST" class="mt-auto">
                                <input type="hidden" name="id_request" value="<?php echo htmlspecialchars($row['id_requests']); ?>">
                                <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($row['id_usuario']); ?>">
                                <input type="hidden" name="id_carrera" value="<?php echo htmlspecialchars($row['id_carrera']); ?>">
                                
                                <button type="submit" class="btn btn-primary w-100">Más información</button>
                            </form>
                        </div>
                        </div> 
                    </div>
                <?php
                } 
                ?>
            </div>
        <?php 
        } else {
            echo '<div class="col-12"><p class="alert alert-info">No se encontraron servicios para la carrera seleccionada.</p></div>';
        }
        ?>
    </div>






<footer>
    <div id="Footer_Responsive" class="container-fluid bg-dark">
        <div class="row text-align-center p-5 d-md-none d-lg-none">
            <div class="accordion" id="accordionPanelsStayOpenExample" data-bs-theme="dark">
            <div class="accordion-item mb-2 border-0 border-bottom">
                <h2 class="accordion-header">
                <button class="accordion-button collapsed shadow-none bg-transparent text-white" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true" aria-controls="panelsStayOpen-collapseOne">
                    TEST
                </button>
                </h2>
                <div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse">
                <div class="accordion-body">
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                </div>
                </div>
            </div>
            <div class="accordion-item mb-2 border-0 border-bottom">
                <h2 class="accordion-header">
                <button class="accordion-button collapsed shadow-none bg-transparent text-white" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseTwo" aria-expanded="false" aria-controls="panelsStayOpen-collapseTwo">
                    TEST
                </button>
                </h2>
                <div id="panelsStayOpen-collapseTwo" class="accordion-collapse collapse">
                <div class="accordion-body">
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                </div>
                </div>
            </div>
            <div class="accordion-item mb-2 border-0 border-bottom">
                <h2 class="accordion-header">
                <button class="accordion-button collapsed shadow-none bg-transparent text-white" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseThree" aria-expanded="false" aria-controls="panelsStayOpen-collapseThree">
                    TEST
                </button>
                </h2>
                <div id="panelsStayOpen-collapseThree" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                    </div>
                </div>
            </div>
            <div class="mt-5">
                <p class="text-white text-center">&copy; 2025 Uni-Gigs. Todos los derechos reservados.</p>
                <div class="text-center">
                    <i class="bi bi-facebook fs-3 text-white p-3"></i>
                    <i class="bi bi-instagram fs-3 text-white p-3"></i>
                    <i class="bi bi-twitter-x fs-3 text-white p-3"></i>
                </div>
            </div>
        </div>
    </div>
    <div>
        <div id="Footer_Large" class="container-fluid">
            <div class="row bg-dark text-white text-center p-5 d-none d-md-flex d-lg-flex">
                <div class="col-lg-3 col-md-3">
                    <div class="Titulo d-flex justify-content-center align-items-center mb-3">
                        <img src="../../public/img/Isotipo_Blanco.png" alt="Logo" width="60" height="48" class="d-inline-block align-text-center me-2">
                        <span class="h3 mb-0">Uni-Gigs</span>                        
                    </div>
                    <div>
                        <i class="bi bi-facebook fs-2 text-white p-3"></i>
                        <i class="bi bi-instagram fs-2 text-white p-3"></i>
                        <i class="bi bi-twitter-x fs-2 text-white p-3"></i>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3">
                    <p class="h5">TEST</p>
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                    <div>
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3">
                    <p class="h5">TEST</p>
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                    <div>
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3">
                    <p class="h5">TEST</p>
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                </div>
                <div class="mt-3 border-top pt-3">
                    <p>&copy; 2025 Uni-Gigs. Todos los derechos reservados.</p>
                    <p>Av. Concepción Mariño, Sector El Toporo, El Valle del Espíritu Santo, Edo. Nueva Esparta, Venezuela.</p>
                </div>
                <div class="d-flex-center">
                    <a class="text-decoration-none text-white" href="#">Términos y condiciones</a>
                    <div class="vr mx-3 opacity-100"></div>
                    <a class="text-decoration-none text-white" href="#">Política de privacidad</a>
                    <div class="vr mx-3 opacity-100"></div>
                    <a class="text-decoration-none text-white" href="#">Cookies</a>
                </div>
            </div>
        </div>
    </div>
</footer>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Seleccionar todos los contenedores de estrellas
    const ratingContainers = document.querySelectorAll('.star-rating-display');

    ratingContainers.forEach(container => {
        // Obtener el valor del rating desde el atributo data-rating
        const rating = parseFloat(container.getAttribute('data-rating'));
        
        // Limpiar el contenido actual
        container.innerHTML = '';

        // Generar las 5 estrellas
        for (let i = 1; i <= 5; i++) {
            let iconName = 'star_border'; // Por defecto vacía
            let colorClass = 'text-secondary'; // Color gris por defecto

            if (rating >= i) {
                // Estrella completa
                iconName = 'star';
                colorClass = 'text-warning'; // Amarillo/Dorado (Bootstrap)
            } else if (rating >= i - 0.5) {
                // Media estrella
                iconName = 'star_half';
                colorClass = 'text-warning';
            }

            // Crear el elemento span para el icono
            const star = document.createElement('span');
            star.className = `material-symbols-outlined ${colorClass}`;
            star.textContent = iconName;
            
            // Ajustar tamaño si es necesario (opcional)
            star.style.fontSize = '20px'; 
            star.style.fontVariationSettings = "'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24";

            // Agregar al contenedor
            container.appendChild(star);
        }
    });
});
</script>
    
</body>
</html>