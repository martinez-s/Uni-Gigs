<?php
// 1. INICIO DE SESIÓN
session_start();

// Cambiamos el nombre de la variable para ser más explícitos
$id_servicio_seleccionado = null;

// 2. LÓGICA DE OBTENCIÓN DE DATOS (POST vs SESSION)
// Ahora busca 'id_servicio' en lugar de 'id_request'
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_servicio'])) {
    // CASO A: Venimos del formulario POST (Click en "Más información")
    
    // Cambiamos 'id_request' por 'id_servicio'
    $id_servicio = intval($_POST['id_servicio']);
    $id_usuario = intval($_POST['id_usuario']);
    $id_carrera = intval($_POST['id_carrera']);
    
    // Guardamos en sesión con nuevos nombres para persistencia
    $_SESSION['current_service_id'] = $id_servicio;
    $_SESSION['service_user_id'] = $id_usuario;
    $_SESSION['service_carrera_id'] = $id_carrera;
    
    // Definimos el ID que usaremos para la consulta
    $id_servicio_seleccionado = $id_servicio;

} elseif (isset($_SESSION['current_service_id'])) {
    // CASO B: Recarga de página (GET) o navegación interna, usamos la sesión.
    $id_servicio_seleccionado = $_SESSION['current_service_id'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="stylesNav.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Uni-Gigs - Detalle Servicio</title>
</head>
<body>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="main.js"></script>
    
    <?php
    // Inclusión de archivos necesarios
    include('../../conect.php'); // Asegúrate de que esta ruta sea correcta y que defina $mysqli
    include 'NavBar.php'; // Asegúrate de que este archivo no tenga session_start()
    
    $data = null; // Inicializamos la variable que contendrá el resultado del servicio

    // 3. CONSULTA SQL SEGURA PARA SERVICIOS
    // Usamos $id_servicio_seleccionado
    if ($id_servicio_seleccionado) {
        
        // Consulta adaptada a la tabla 'servicios' (s)
        $sql = "SELECT 
            s.id_servicio, s.titulo, s.descripcion, s.precio, s.fecha_creacion,
            c.nombre_carrera, 
            m.nombre AS nombre_materia, 
            tt.nombre AS tipo_trabajo_nombre,
            u.rating, u.porcentaje_completacion, 
            u.nombre AS nombre_usuario, 
            u.apellido AS apellido_usuario,
            u.id_usuario AS id_usuario,
            MIN(fs.url_foto) AS url_foto
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
            fotos_servicios fs ON s.id_servicio = fs.id_servicio /* CAMBIADO: tabla fotos_servicios */
        WHERE 
            s.id_servicio = ? /* CAMBIADO: filtro por s.id_servicio */
        GROUP BY 
            s.id_servicio, s.titulo, s.descripcion, s.precio, s.fecha_creacion, 
            c.nombre_carrera, m.nombre, tt.nombre, 
            u.rating, u.porcentaje_completacion, u.nombre, u.apellido";

        // Preparamos la consulta
        if ($stmt = $mysqli->prepare($sql)) {
            // Vinculamos el ID (i = integer). Usamos $id_servicio_seleccionado
            $stmt->bind_param("i", $id_servicio_seleccionado); 
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            // Obtenemos el único registro
            $data = $resultado->fetch_assoc();
            $stmt->close();
        } else {
            // Error de conexión o de sintaxis en la consulta
            echo "<p class='alert alert-danger'>Error al preparar la consulta: " . $mysqli->error . "</p>";
        }
    }
    ?>

    <div class="container my-5">
        
        <?php if ($data): // Muestra los detalles si se encontró el registro ?>

        <div class="row align-items-center mb-4">
            <div class="col-md-8 mb-2 mb-md-0">
                <h2 class="fw-normal mb-0">
                    <?php echo htmlspecialchars($data['titulo']); ?>
                </h2>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="gray-box py-2 px-3 d-inline-block w-auto">
                    <?php 
                        if (isset($data['fecha_creacion']) && $data['fecha_creacion']) {
                            echo date('d/m/Y', strtotime($data['fecha_creacion']));
                        } else {
                            echo "FECHA NO DISP.";
                        }
                    ?>
                </div>
            </div>
        </div>

        <div class="row g-4">
            
            <div class="col-lg-4 col-md-12">
                <div class="img-placeholder">
                    <?php if (!empty($data['url_foto'])): ?>
                        <img src="../../public/img/imgSer/<?php echo htmlspecialchars($data['url_foto']); ?>" alt="Imagen servicio" style="width: 100%; height: auto; display: block; object-fit: cover;">
                    <?php else: ?>
                        <div class="text-center py-5 bg-light">IMAGEN NO DISPONIBLE</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-5 col-md-8">
                <div class="mb-3">
                    <span class="h6">INFORMACION</span>
                </div>

                <div class="row g-2 mb-2">
                    <div class="col-12"> <div class="gray-box py-2">
                            <?php echo htmlspecialchars($data['nombre_carrera']); ?>
                        </div>
                    </div>
                </div>
                
                <div class="row g-2 mb-3">
                    <div class="col-4">
                        <div class="gray-box py-2">
                            <?php echo isset($data['nombre_materia']) ? htmlspecialchars($data['nombre_materia']) : 'General'; ?>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="gray-box py-2 px-1" style="font-size: 0.8rem;">
                            <?php echo isset($data['tipo_trabajo_nombre']) ? htmlspecialchars($data['tipo_trabajo_nombre']) : 'Varios'; ?>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="gray-box py-2">
                            <?php echo '$' . number_format($data['precio'], 2, '.', ','); ?>
                        </div>
                    </div>
                </div>

                <hr class="border-dark my-4">

                <div class="desc-box">
                    <?php echo nl2br(htmlspecialchars($data['descripcion'])); ?>
                </div>
            </div>

            <div class="col-lg-3 col-md-4">
                <div class="user-card">
                    <div class="alert-btn">!</div>

                    <div class="d-flex flex-column align-items-center mt-4">
                        <div class="avatar-circle mb-2" style="background-color: #ccc; width: 60px; height: 60px; border-radius: 50%;"></div>
                        
                        <h6 class="mb-3">
                            <?php echo htmlspecialchars($data['nombre_usuario']) . ' ' . htmlspecialchars($data['apellido_usuario']); ?>
                        </h6>
                        
                        <div class="bg-white py-1 px-4 mb-3 w-100 text-center border">
                            <?php echo htmlspecialchars($data['rating']); ?> / 5
                        </div>
                    </div>
                    <?php 
                        if($data['id_usuario'] != $_SESSION['id_usuario']) {
                            $id_usuario_req = $_SESSION['id_usuario'];
                            $id_usuario_ser = $data['id_usuario'];
                            $sql_chatViejo= "SELECT id_chat FROM chats WHERE ((id_usuario1 = ? AND id_usuario2 = ?) OR (id_usuario1 = ? AND id_usuario2 = ?)) AND (estado = TRUE)";
                            $stmt_chatViejo = $mysqli->prepare($sql_chatViejo);
                            $stmt_chatViejo->bind_param("iiii", $id_usuario_req, $id_usuario_ser, $id_usuario_ser, $id_usuario_req);
                            $stmt_chatViejo->execute();
                            $result_chatViejo = $stmt_chatViejo->get_result();

                            if($result_chatViejo->num_rows > 0){
                                $chatViejo = $result_chatViejo->fetch_assoc();
                                $id_chatViejo = $chatViejo['id_chat'];
                                echo '<div class="alert alert-info text-center mt-3">Ya tienes un chat activo con este usuario.</div>';
                                exit();
                            }else{
                                echo '<a href="../../public/pages/asociarChatSer.php?id_usuario=' . urlencode($data['id_usuario']) . '"><button class="btn btn-secondary mt-auto w-100">ACEPTAR</button></a>';
                            }
                        }else {
                            echo '<div class="alert alert-info text-center mt-3">Este es tu servicio.</div>';
                        }

                    ?>
                </div>
            </div>

        </div>
        
        <?php elseif ($id_servicio_seleccionado === null): ?>
            <div class="alert alert-info text-center mt-5">
                <h4>No has seleccionado ningún servicio.</h4>
                <a href="index.php" class="btn btn-primary mt-3">Volver a la lista</a>
            </div>
            
        <?php else: ?>
            <div class="alert alert-danger text-center mt-5">
                <h4>Error: El servicio ID (<?php echo $id_servicio_seleccionado; ?>) solicitado no existe o fue eliminado.</h4>
                <a href="index.php" class="btn btn-primary mt-3">Volver</a>
            </div>
        <?php endif; ?>
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

    
</body>
</html>