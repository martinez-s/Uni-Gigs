
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
    <script src="dropdown.js"></script>
    
    <?php
    include('../../conect.php');

    if (isset($mysqli) && !$mysqli->connect_errno) {
        $mysqli->set_charset("utf8mb4");
    } else {
        die("Error de conexión a la base de datos");
    }

    include 'NavBar.php';

    // CONFIGURACIÓN DE PAGINACIÓN Y FILTROS
    $registros_por_pagina = 12;
    // Usamos $_REQUEST para capturar tanto GET (paginación/filtros) como POST si fuera necesario
    $pagina_actual = isset($_REQUEST['pagina']) && is_numeric($_REQUEST['pagina']) ? (int)$_REQUEST['pagina'] : 1;
    if ($pagina_actual < 1) $pagina_actual = 1;

    $offset = ($pagina_actual - 1) * $registros_por_pagina;

    // Captura de Filtros (Usamos nombres de variables consistentes)
    $id_carrera_seleccionada = isset($_REQUEST['id_carrera_filtro']) && is_numeric($_REQUEST['id_carrera_filtro']) ? (int)$_REQUEST['id_carrera_filtro'] : 0;
    $id_tipo_seleccionado = isset($_REQUEST['id_tipo_trabajo_filtro']) && is_numeric($_REQUEST['id_tipo_trabajo_filtro']) ? (int)$_REQUEST['id_tipo_trabajo_filtro'] : 0;
    $id_materia_seleccionada = isset($_REQUEST['id_materia_filtro']) && is_numeric($_REQUEST['id_materia_filtro']) ? (int)$_REQUEST['id_materia_filtro'] : 0;

    // LÍMITES DE CARACTERES 
    $limite_titulo = 30;      
    $limite_descripcion = 79;

    // CONSTRUCCIÓN DEL WHERE (Para reutilizar en Count y Select)
    $where_clauses = [];
    $param_types = "";
    $params = [];

    if ($id_carrera_seleccionada > 0) {
        $where_clauses[] = "s.id_carrera = ?";
        $param_types .= "i";
        $params[] = $id_carrera_seleccionada;
    }
    if ($id_tipo_seleccionado > 0) {
        $where_clauses[] = "s.id_tipo_trabajo = ?";
        $param_types .= "i";
        $params[] = $id_tipo_seleccionado;
    }
    if ($id_materia_seleccionada > 0) {
        $where_clauses[] = "s.id_materia = ?";
        $param_types .= "i";
        $params[] = $id_materia_seleccionada;
    }

    // 1. CONSULTA DE CONTEO (TOTAL REGISTROS)
    $sql_count = "SELECT COUNT(s.id_servicio) as total_registros FROM servicios s";
    if (!empty($where_clauses)) {
        $sql_count .= " WHERE " . implode(" AND ", $where_clauses);
    }

    $stmt_count = $mysqli->prepare($sql_count);
    if (!empty($params)) {
        $stmt_count->bind_param($param_types, ...$params);
    }
    $stmt_count->execute();
    $resultado_count = $stmt_count->get_result();
    $fila_count = $resultado_count->fetch_assoc();
    $total_registros = (int)$fila_count['total_registros'];
    $stmt_count->close();

    $total_paginas = ceil($total_registros / $registros_por_pagina);
    if ($pagina_actual > $total_paginas && $total_paginas > 0) {
        $pagina_actual = $total_paginas;
        $offset = ($pagina_actual - 1) * $registros_por_pagina;
    }

    // 2. CONSULTA PRINCIPAL (DATA)
    $sql = "SELECT 
                s.id_servicio, s.titulo, s.descripcion, s.precio,
                c.nombre_carrera, u.rating, u.porcentaje_completacion,
                MIN(f.url_foto) AS url_foto
            FROM servicios s
            JOIN carreras c ON s.id_carrera = c.id_carrera
            JOIN usuarios u ON s.id_usuario = u.id_usuario
            LEFT JOIN fotos_servicios f ON s.id_servicio = f.id_servicio";

    if (!empty($where_clauses)) {
        $sql .= " WHERE " . implode(" AND ", $where_clauses);
    }

    $sql .= " GROUP BY s.id_servicio, s.titulo, s.descripcion, s.precio, c.nombre_carrera, u.rating, u.porcentaje_completacion";
    $sql .= " LIMIT ? OFFSET ?";
    
    // Añadimos limit y offset a los parámetros
    $param_types .= "ii";
    $params[] = $registros_por_pagina;
    $params[] = $offset;

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $stmt->close();
    ?>

    <?php if ($resultado) { // Quitamos la comprobación num_rows > 0 aquí para mostrar el filtro aunque no haya resultados ?>

        <div id="Requests" class="banner-container">
            <div class="container-fluid px-5">
                 <div class="row align-items-center mt-5">
                    <div class="col-md-12 mb-4 mb-md-0">
                        <h4>Filtros de Servicios</h4>
                        <hr>

                        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="GET" class="row g-3 align-items-end">
                            
                            <div class="col-md-4">
                                <label for="id_carrera_filtro" class="form-label">Carrera:</label>
                                <select class="form-select" id="id_carrera_filtro" name="id_carrera_filtro">
                                    <option value="0">Todas las Carreras</option>
                                    
                                    <?php 
                                    $sql_carreras = "SELECT id_carrera, nombre_carrera FROM carreras ORDER BY nombre_carrera ASC";
                                    $resultado_carreras = $mysqli->query($sql_carreras);

                                    if ($resultado_carreras && $resultado_carreras->num_rows > 0) {
                                        while ($carrera = $resultado_carreras->fetch_assoc()) {
                                            $nombreCarrera = htmlspecialchars($carrera['nombre_carrera']);
                                            $id = htmlspecialchars($carrera['id_carrera']);
                                            $selected = ($id == $id_carrera_seleccionada) ? 'selected' : '';
                                            echo '<option value="' . $id . '" ' . $selected . '>' . $nombreCarrera . '</option>';
                                        }
                                        $resultado_carreras->free();
                                    }
                                    ?>
                                </select>
                            </div>
                                                        
                            <div class="col-md-4">
                                <label for="id_tipo_trabajo_filtro" class="form-label">Tipo de Trabajo:</label>
                                <select class="form-select" id="id_tipo_trabajo_filtro" name="id_tipo_trabajo_filtro">
                                    <option value="0">Todos los Tipos</option>
                                    <?php 
                                    $sql_tipos = "SELECT id_tipo_trabajo, nombre FROM tipos_trabajos ORDER BY nombre ASC";
                                    $resultado_tipos = $mysqli->query($sql_tipos);

                                    if ($resultado_tipos && $resultado_tipos->num_rows > 0) {
                                        while ($tipo = $resultado_tipos->fetch_assoc()) {
                                            $nombre_tipo = htmlspecialchars($tipo['nombre']);
                                            $id_tipo = htmlspecialchars($tipo['id_tipo_trabajo']);
                                            $selected = ($id_tipo == $id_tipo_seleccionado) ? 'selected' : '';
                                            echo '<option value="' . $id_tipo . '" ' . $selected . '>' . $nombre_tipo . '</option>';
                                        }
                                        $resultado_tipos->free();
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="id_materia_filtro" class="form-label">Materia:</label>
                                <select class="form-select select2-enable" id="id_materia_filtro" name="id_materia_filtro">
                                    <option value="0" <?php if($id_materia_seleccionada == 0) echo 'selected'; ?>>Todas las Materias</option>
                                    
                                    <?php 
                                    // CARGA INICIAL DE MATERIAS (Server Side)
                                    // Si ya hay una carrera seleccionada al cargar la página, llenamos el select
                                    if ($id_carrera_seleccionada > 0) {
                                        $sql_materias = "
                                            SELECT m.id_materia, m.nombre 
                                            FROM materias m
                                            JOIN materias_carreras mc ON m.id_materia = mc.id_materia
                                            WHERE mc.id_carrera = ?
                                            ORDER BY m.nombre ASC";
                                        
                                        $stmt_m = $mysqli->prepare($sql_materias);
                                        $stmt_m->bind_param("i", $id_carrera_seleccionada);
                                        $stmt_m->execute();
                                        $res_m = $stmt_m->get_result();

                                        while ($materia = $res_m->fetch_assoc()) {
                                            $nom = htmlspecialchars($materia['nombre']);
                                            $id_m = htmlspecialchars($materia['id_materia']);
                                            $sel = ($id_m == $id_materia_seleccionada) ? 'selected' : '';
                                            echo '<option value="' . $id_m . '" ' . $sel . '>' . $nom . '</option>';
                                        }
                                        $stmt_m->close();
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="col-12 mt-3 d-flex gap-2"> 
                                <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">Limpiar Filtros
                                    
                                </a>
                                <button type="submit" class="btn btn-success">Aplicar Filtros</button>
                            </div>
                        </form>
                        <hr>
                    </div>
                </div>

                <div class="row">
                    <?php
                    if ($resultado->num_rows > 0) {
                        while ($row = $resultado->fetch_assoc()) {
                        ?>
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">
                                <div class="card">
                                    <div class="card-body d-flex flex-column">
                                        
                                        <h5 class="card-title" data-limite="<?php echo $limite_titulo; ?>"><?php echo htmlspecialchars($row['titulo']); ?></h5>
                                        
                                        <div class="separator-line"></div>
                                        <?php if (isset($row['url_foto']) && $row['url_foto']) { ?>
                                            <div class="img-wrapper">
                                                <img class="imagen" src="../../public/img/imgSer/<?php echo htmlspecialchars($row['url_foto']); ?>" alt="Foto del servicio">
                                            </div>
                                        <?php } ?>
                                        <h6 class="carrera">
                                            <span class="material-symbols-outlined">license</span>
                                            <?php echo htmlspecialchars($row['nombre_carrera']); ?>
                                        </h6>
                                        
                                        <p class="card-text flex-grow-1" data-limite="<?php echo $limite_descripcion; ?>"><?php echo htmlspecialchars($row['descripcion']); ?></p>
                                        
                                        <div class="d-flex justify-content-between align-items-center mb-3 mt-3"> 
                                            <div class="star-rating-display" data-rating="<?php echo htmlspecialchars($row['rating']); ?>"></div>
                                            <h5 class="Precio mb-0">$<?php echo htmlspecialchars($row['precio']); ?></h5> 
                                        </div>
                                        <a href="#" class="btn btn-primary mt-auto">Mas informacion</a>
                                    </div>
                                </div> 
                            </div>
                        <?php
                        } 
                    } else {
                        echo '<div class="col-12"><div class="alert alert-info">No se encontraron servicios con los filtros seleccionados.</div></div>';
                    }
                    ?>
                </div>
                
                <?php if ($total_paginas > 1) { ?>
                    
                        <div class="pag" style="display: flex; justify-content: center; width: 100%;">
                            <ul class="pagination" style="margin-bottom: 0;">
                                <?php
                                // Función auxiliar para mantener los filtros en los links
                                function crearEnlacePaginacion($pagina, $id_carrera, $id_tipo, $id_materia, $texto, $clase_li) {
                                    $url = htmlspecialchars($_SERVER["PHP_SELF"]) . "?pagina=" . $pagina;
                                    if ($id_carrera > 0) $url .= "&id_carrera_filtro=" . $id_carrera;
                                    if ($id_tipo > 0) $url .= "&id_tipo_trabajo_filtro=" . $id_tipo;
                                    if ($id_materia > 0) $url .= "&id_materia_filtro=" . $id_materia;

                                    echo '<li class="page-item ' . $clase_li . '">';
                                    echo '<a class="page-link" href="' . $url . '">' . $texto . '</a>';
                                    echo '</li>';
                                }

                                // Botón Anterior
                                $clase_anterior = ($pagina_actual <= 1) ? 'disabled' : '';
                                $pagina_anterior = $pagina_actual - 1;
                                if ($clase_anterior === 'disabled') {
                                    echo '<li class="page-item disabled"><span class="page-link" aria-hidden="true">&laquo;</span></li>';
                                } else {
                                    crearEnlacePaginacion($pagina_anterior, $id_carrera_seleccionada, $id_tipo_seleccionado, $id_materia_seleccionada, '<span aria-hidden="true">&laquo;</span>', $clase_anterior);
                                }
                                
                                // Números
                                for ($i = 1; $i <= $total_paginas; $i++) {
                                    $clase_li = ($i == $pagina_actual) ? 'active' : '';
                                    crearEnlacePaginacion($i, $id_carrera_seleccionada, $id_tipo_seleccionado, $id_materia_seleccionada, $i, $clase_li);
                                }

                                // Botón Siguiente
                                $clase_siguiente = ($pagina_actual >= $total_paginas) ? 'disabled' : '';
                                $pagina_siguiente = $pagina_actual + 1;
                                if ($clase_siguiente === 'disabled') {
                                    echo '<li class="page-item disabled"><span class="page-link" aria-hidden="true">&raquo;</span></li>';
                                } else {
                                    crearEnlacePaginacion($pagina_siguiente, $id_carrera_seleccionada, $id_tipo_seleccionado, $id_materia_seleccionada, '<span aria-hidden="true">&raquo;</span>', $clase_siguiente);
                                }
                                ?>
                            </ul>
                        </div>
                    
                <?php } ?>
            </div>
        </div>
    <?php } ?>

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

    <script>
        function aplicarLimiteDeTexto() {
            document.querySelectorAll('h5.card-title').forEach(element => {
                const limite = parseInt(element.dataset.limite);
                const textoCompleto = element.textContent.trim();
                if (limite && textoCompleto.length > limite) {
                    element.textContent = textoCompleto.substring(0, limite) + '...';
                }
            });

            document.querySelectorAll('p.card-text').forEach(element => {
                const limite = parseInt(element.dataset.limite);
                const textoCompleto = element.textContent.trim();
                if (limite && textoCompleto.length > limite) {
                    element.textContent = textoCompleto.substring(0, limite) + '...';
                }
            });
        }
        document.addEventListener('DOMContentLoaded', aplicarLimiteDeTexto);
    </script>

    <script>
        // AJAX PARA CARGAR MATERIAS DINÁMICAMENTE
        const selectCarrera = document.getElementById('id_carrera_filtro');
        const selectMateria = document.getElementById('id_materia_filtro');

        if(selectCarrera && selectMateria) {
            selectCarrera.addEventListener('change', function() {
                const idCarrera = this.value; 

                // Reiniciar select
                selectMateria.innerHTML = '<option value="0" selected>Todas las Materias</option>';

                if (idCarrera > 0) {
                    // Indicador de carga
                    selectMateria.innerHTML = '<option value="0" disabled selected>Cargando...</option>';

                    fetch('obtener_materias.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'id_carrera=' + idCarrera 
                    })
                    .then(response => response.json())
                    .then(data => {
                        selectMateria.innerHTML = '<option value="0" selected>Todas las Materias</option>';
                        data.forEach(materia => {
                            const option = document.createElement('option');
                            option.value = materia.id_materia;
                            option.textContent = materia.nombre;
                            selectMateria.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        selectMateria.innerHTML = '<option value="0" selected>Todas las Materias</option>';
                    });
                }
            });
        }
    </script>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>


    <script>
    $(document).ready(function() {
    
        const select2ConfigBase = {
            width: '100%',
            placeholder: "Seleccione una opción",
            allowClear: true,
            dropdownParent: $('body') 
        };

        $('#id_carrera_filtro').select2(select2ConfigBase);

        $('#id_tipo_trabajo_filtro').select2(select2ConfigBase);

        $('#id_materia_filtro').select2(select2ConfigBase);

        $('#id_carrera_filtro').on('change', function() {
            const id_carrera = $(this).val();
            
            if (id_carrera) {
                fetchMaterias(id_carrera);
            } else {
            
                actualizarMateriasDropdown([]); 
            }
        });

        function fetchMaterias(id_carrera) {
            $.ajax({
                url: 'obtener_materias.php', 
                method: 'GET',
                data: { id_carrera: id_carrera },
                dataType: 'json',
                success: function(materias) {
                    actualizarMateriasDropdown(materias);
                },
                error: function(xhr, status, error) {
                    console.error("Error al cargar materias:", error);
                    actualizarMateriasDropdown([]); 
                }
            });
        }

        function actualizarMateriasDropdown(materias) {
            const $materiaSelect = $('#id_materia_filtro');
            
            if ($materiaSelect.data('select2')) {
                $materiaSelect.select2('destroy'); 
            }

            $materiaSelect.empty(); 
            
            $materiaSelect.append($('<option></option>').val('').text('Selecciona la materia'));

            materias.forEach(materia => {
                $materiaSelect.append($('<option></option>').val(materia.id_materia).text(materia.nombre));
            });

            $materiaSelect.select2(select2ConfigBase);
        }

    });
    </script>
</body>
</html>
<?php $mysqli->close(); ?>