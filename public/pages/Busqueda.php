<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    
 
<?php
include('../../conect.php');
include 'NavBar.php'; 

// 3. Inicializar variables de búsqueda
$sql_query_completa = ''; 
$termino_actual = ''; 
$termino_para_sql = null; 
$resultado = false;

// Determinar si hay término de búsqueda POST
if (isset($_POST['q_post']) && !empty($_POST['q_post'])) {
    $termino_actual = $_POST['q_post']; 
    $termino_para_sql = '%' . $termino_actual . '%';
}

// ===================================================================
// 4. Consultas Base (SELECT 1 y SELECT 2) - Se incluye id_usuario y id_carrera
// ===================================================================

$sql_servicios = "
    SELECT 
        s.id_servicio AS id_item, 
        'servicio' AS tipo, 
        s.id_usuario, 
        s.id_carrera, 
        s.titulo, 
        s.descripcion, 
        s.precio,
        s.fecha_creacion,                               /* <-- CAMPO AÑADIDO */
        
        c.nombre_carrera, 
        m.nombre AS nombre_materia,                     /* <-- CAMPO AÑADIDO */
        tt.nombre AS tipo_trabajo_nombre,               /* <-- CAMPO AÑADIDO */
        
        u.nombre AS nombre_usuario,                     /* <-- CAMPO AÑADIDO */
        u.apellido AS apellido_usuario,                 /* <-- CAMPO AÑADIDO */
        u.rating, 
        u.porcentaje_completacion,
        
        MIN(f.url_foto) AS url_foto
    FROM servicios s
    JOIN carreras c ON s.id_carrera = c.id_carrera
    JOIN usuarios u ON s.id_usuario = u.id_usuario
    JOIN materias m ON s.id_materia = m.id_materia        /* <-- JOIN AÑADIDO */
    JOIN tipos_trabajos tt ON s.id_tipo_trabajo = tt.id_tipo_trabajo /* <-- JOIN AÑADIDO */
    LEFT JOIN fotos_servicios f ON s.id_servicio = f.id_servicio
    {CONDICION_BUSQUEDA_S}
    GROUP BY 
        s.id_servicio, s.id_usuario, s.id_carrera, s.titulo, s.descripcion, s.precio, 
        s.fecha_creacion, c.nombre_carrera, m.nombre, tt.nombre, 
        u.nombre, u.apellido, u.rating, u.porcentaje_completacion
";

// Consulta para REQUESTS (Solicitudes)
$sql_requests = "
    SELECT 
        r.id_requests AS id_item, 
        'request' AS tipo, 
        r.id_usuario, 
        r.id_carrera, 
        r.titulo, 
        r.descripcion, 
        r.precio,
        r.fecha_creacion,                               /* <-- CAMPO AÑADIDO */
        
        c.nombre_carrera, 
        m.nombre AS nombre_materia,                     /* <-- CAMPO AÑADIDO */
        tt.nombre AS tipo_trabajo_nombre,               /* <-- CAMPO AÑADIDO */
        
        u.nombre AS nombre_usuario,                     /* <-- CAMPO AÑADIDO */
        u.apellido AS apellido_usuario,                 /* <-- CAMPO AÑADIDO */
        u.rating, 
        u.porcentaje_completacion,
        
        MIN(f.url_foto) AS url_foto
    FROM requests r
    JOIN carreras c ON r.id_carrera = c.id_carrera
    JOIN usuarios u ON r.id_usuario = u.id_usuario
    JOIN materias m ON r.id_materia = m.id_materia        /* <-- JOIN AÑADIDO */
    JOIN tipos_trabajos tt ON r.id_tipo_trabajo = tt.id_tipo_trabajo /* <-- JOIN AÑADIDO */
    LEFT JOIN fotos_requests f ON r.id_requests = f.id_request
    {CONDICION_BUSQUEDA_R}
    GROUP BY 
        r.id_requests, r.id_usuario, r.id_carrera, r.titulo, r.descripcion, r.precio, 
        r.fecha_creacion, c.nombre_carrera, m.nombre, tt.nombre, 
        u.nombre, u.apellido, u.rating, u.porcentaje_completacion
";
// ===================================================================
// 5. Lógica de Ejecución (Búsqueda o Todos)
// ===================================================================
if ($termino_para_sql) {
    // A) Búsqueda Activa (Usa consultas preparadas)

    // Definición de las cláusulas WHERE para la búsqueda
    $where_s = "WHERE s.titulo LIKE ? OR s.descripcion LIKE ? OR c.nombre_carrera LIKE ?";
    $where_r = "WHERE r.titulo LIKE ? OR r.descripcion LIKE ? OR c.nombre_carrera LIKE ?";

    $sql_servicios = str_replace('{CONDICION_BUSQUEDA_S}', $where_s, $sql_servicios);
    $sql_requests = str_replace('{CONDICION_BUSQUEDA_R}', $where_r, $sql_requests);

    // Consulta final combinada (UNION ALL)
    $sql_query_completa = "
        ($sql_servicios)
        UNION ALL
        ($sql_requests)
        ORDER BY tipo DESC, id_item DESC
    ";

    $stmt = $mysqli->prepare($sql_query_completa);
    
    // Vincula 6 parámetros de cadena (s*6)
    if ($stmt) {
        $stmt->bind_param("ssssss", 
            $termino_para_sql, $termino_para_sql, $termino_para_sql, // 3 para Servicios
            $termino_para_sql, $termino_para_sql, $termino_para_sql  // 3 para Requests
        );
        $stmt->execute();
        $resultado = $stmt->get_result();
        $stmt->close();
    } else {
        error_log("Error al preparar la consulta con búsqueda UNION: " . $mysqli->error);
    }

} else {
    // B) Sin Búsqueda (Muestra todos los resultados)
    
    // Elimina los marcadores de condición (no hay WHERE)
    $sql_servicios = str_replace('{CONDICION_BUSQUEDA_S}', '', $sql_servicios);
    $sql_requests = str_replace('{CONDICION_BUSQUEDA_R}', '', $sql_requests);
    
    // Consulta final combinada (UNION ALL)
    $sql_query_completa = "
        ($sql_servicios)
        UNION ALL
        ($sql_requests)
        ORDER BY tipo DESC, id_item DESC
    ";

    $resultado = $mysqli->query($sql_query_completa);

    if (!$resultado) {
        error_log("Error al ejecutar la consulta UNION sin búsqueda: " . $mysqli->error);
    }
}
?>

<div id="Resultados" class="banner-container">
    <div class="container-fluid px-5">
        <div class="row align-items-center mt-5">
            <div class="col-md-12 mb-4 mb-md-0">
                <h4 class="Titulo mb-0">Resultados de Búsqueda</h4> 
                <hr>
            </div>
        </div>

        <?php
        if ($resultado && $resultado->num_rows > 0) {
        ?>
            <div class="row">
                <?php
                while ($row = $resultado->fetch_assoc()) {
                    $es_servicio = ($row['tipo'] === 'servicio');
                    $es_request = ($row['tipo'] === 'request');
                    $tiene_foto = !empty($row['url_foto']);
                ?>
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">
                        <div class="card">
                            <div class="card-body d-flex flex-column">
                                
                                <h5 class="card-title"><?php echo htmlspecialchars($row['titulo']); ?></h5>
                                
                                <div class="separator-line"></div>
                                
                                <?php 
                                if ($tiene_foto) { 
                                    $folder = $es_servicio ? 'imgSer' : 'imgReq';
                                    $ruta_imagen = "../../public/img/{$folder}/" . htmlspecialchars($row['url_foto']);
                                ?>
                                    <div class="img-wrapper">
                                    <img class="imagen" src="<?php echo $ruta_imagen; ?>" alt="Foto del item">
                                    </div>
                                <?php 
                                } elseif ($es_request) {
                                    // Placeholder visual si es un request y NO tiene foto
                                ?>
                                <?php 
                                } 
                                // CIERRE DEL BLOQUE IF/ELSEIF DE IMÁGENES
                                ?>

                                   <?php if ($es_request): ?>
                                        <h6 class="EsReq">
                                            Request
                                        </h6>
                                    <?php elseif ($es_servicio): ?>
                                        <h6 class="EsSer">
                                            Servicio
                                        </h6>
                                    <?php endif; ?>
                                    
                                    
                                    <h6 class="carrera">
                                        <span class="material-symbols-outlined">license</span>
                                        <?php echo htmlspecialchars($row['nombre_carrera']); ?>
                                    </h6>
                                
                                    <p class="card-text flex-grow-1"><?php echo htmlspecialchars($row['descripcion']); ?></p>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-3 mt-3"> 
                                        
                                        <?php if (!empty($row['rating'])): ?>
                                            <div class="star-rating-display" data-rating="<?php echo htmlspecialchars($row['rating']); ?>"></div>
                                        <?php else: ?>
                                            <div class="star-rating-display" data-rating="0"></div>
                                        <?php endif; ?>

                                        <?php if (!empty($row['precio'])): ?>
                                            <h5 class="Precio mb-0">$<?php echo htmlspecialchars($row['precio']); ?></h5> 
                                        <?php else: ?>
                                            <h5 class="Precio mb-0">Precio no listado</h5>  
                                        <?php endif; ?>
                                    </div>
                                    
                                    <form action="<?php echo $es_servicio ? 'MasInfoSer.php' : 'MasInfoReq.php'; ?>" method="POST" class="mt-auto">
                                        <input type="hidden" name="<?php echo $es_servicio ? 'id_servicio' : 'id_request'; ?>" value="<?php echo htmlspecialchars($row['id_item']); ?>">
                                        
                                        <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($row['id_usuario']); ?>">
                                        <input type="hidden" name="id_carrera" value="<?php echo htmlspecialchars($row['id_carrera']); ?>">
                                        
                                        <button type="submit" class="btn btn-primary w-100">Más información</button>
                                    </form>
                                    </div>
                            </div> 
                        </div>
                <?php
                } // FIN DEL WHILE
                ?>
            </div>
        <?php 
        } else { 
        ?>
            <div class="row">
                <div class="col-12 mt-4">
                    <div class="alert alert-warning" role="alert">
                        No se encontraron servicios ni solicitudes que coincidan con tu búsqueda.
                    </div>
                </div>
            </div>
        <?php
        } // FIN DEL IF ($resultado)
        ?>
    
    </div> 
</div>

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

    
</body>
</html>