<?php
session_start();
include('conect.php'); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    header('Content-Type: application/json');
    
    $id_usuario_logueado = $_SESSION['id_usuario'] ?? 1;

    $titulo = $_POST['titulo'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $precio = (float)($_POST['precio'] ?? 0); 
    $tipo_trabajo_id = (int)($_POST['tipo_trabajo_id'] ?? 0);
    $carrera_id = (int)($_POST['carrera_id'] ?? 0);
    $materia_id = (int)($_POST['materia_id'] ?? 0);
    
    if (empty($titulo) || $precio <= 0 || $tipo_trabajo_id == 0 || $carrera_id == 0 || $materia_id == 0) {
        echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios']);
        exit;
    }

    $sql_insert_servicio = "INSERT INTO servicios (titulo, descripcion, precio, fecha_creacion, id_tipo_trabajo, id_carrera, id_materia, id_usuario) VALUES (?, ?, ?, CURDATE(), ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql_insert_servicio);
    
    if ($stmt) {
        $stmt->bind_param("ssdiiii", $titulo, $descripcion, $precio, $tipo_trabajo_id, $carrera_id, $materia_id, $id_usuario_logueado);
        
        if ($stmt->execute()) {
            $id_servicio = $stmt->insert_id;
            $imagen_procesada = 0;

            if (isset($_FILES['imagen-servicio']) && $_FILES['imagen-servicio']['error'] === UPLOAD_ERR_OK) {
                $carpeta_destino = 'public/img/imgSer/';
                
                
                $tmp_file = $_FILES['imagen-servicio']['tmp_name'];
                $nombre_original = basename($_FILES['imagen-servicio']['name']);
                
                $nombre_imagen = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\.]/', '_', $nombre_original);
                $ruta_destino = $carpeta_destino . $nombre_imagen;
                
                // Validar que sea una imagen
                $valid_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                $file_extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
                
                if (in_array($file_extension, $valid_extensions)) {
                    if (move_uploaded_file($tmp_file, $ruta_destino)) {
                        // Guardar en la tabla fotos_servicios
                        $sql_foto = "INSERT INTO fotos_servicios (url_foto, id_servicio) VALUES (?, ?)";
                        $stmt_foto = $mysqli->prepare($sql_foto);
                        if ($stmt_foto) {
                            $stmt_foto->bind_param("si", $nombre_imagen, $id_servicio);
                            if ($stmt_foto->execute()) {
                                $imagen_procesada = 1;
                            }
                        }
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'Formato de imagen no válido. Use JPG, JPEG, PNG o GIF.']);
                    exit;
                }
            }
            
            echo json_encode([
                'success' => true, 
                'message' => 'Servicio creado correctamente' . ($imagen_procesada > 0 ? " con imagen adjunta" : ""),
                'imagen' => $imagen_procesada,
                'redirect' => 'public/pages/principal.php' // Redirige a la página principal
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Error preparando la consulta: ' . $mysqli->error]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="public/styles/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Estilos específicos para el request -->
    <link rel="stylesheet" href="public/pages/StylesNav.css">
    <link rel="stylesheet" href="public/styles/crear_request.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <title>Crear Servicio</title>
</head>
<body>
    <style>
    .navbar-toggler:focus {
        outline: none !important;
        box-shadow: none !important;
        background-color: transparent !important; 
    }
    </style>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            
            <a class="navbar-brand" href="public/pages/principal.php">
                <img src="public/img/Logo_Navbar.png" alt="Logo" width="170" height="48" class="d-inline-block align-text-center">
            </a>
            
            <button class="navbar-toggler" style="border:none;" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="material-symbols-outlined">menu</span> 
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                
                <form class="search w-100" role="search" method="POST" action="public/pages/busqueda.php"> 
                    <div class="search-overlay w-100">
                        <input class="form-control" 
                            type="search" 
                            placeholder="Busqueda" 
                            aria-label="Search"
                            name="q_post"
                            value="<?php echo isset($_POST['q_post']) ? htmlspecialchars($_POST['q_post']) : ''; ?>"
                        />
                        
                        <button class="buscar btn btn-outline-success rounded-circle" type="submit" aria-label="Buscar">
                            <span class="material-symbols-outlined">search</span>
                        </button>
                    </div>
                </form>

                <ul class="navbar-nav mx-auto text-center mb-2 mb-lg-0 ms-3">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Carreras</a>
                        
                        <ul class="dropdown-menu">
                            
                            <li>
                                <form method="POST" action="principal.php" id="form-carrera-all" style="display:none;">
                                    </form>
                                <a class="dropdown-item" href="#" onclick="document.getElementById('form-carrera-all').submit(); return false;">
                                    <strong>Todas las Carreras</strong>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li> <?php
                        // ... (Tu código PHP de consulta y bucle a continuación)
                        $sql_carreras = "SELECT id_carrera, nombre_carrera FROM carreras ORDER BY nombre_carrera ASC";
                        $resultado_carreras = $mysqli->query($sql_carreras);

                        if ($resultado_carreras && $resultado_carreras->num_rows > 0) {
                            while ($carrera = $resultado_carreras->fetch_assoc()) {
                                $nombreCarrera = htmlspecialchars($carrera['nombre_carrera']);
                                $id = htmlspecialchars($carrera['id_carrera']);
                        ?>
                                    <li>
                                        <form method="POST" action="public/pages/principal.php" id="form-carrera-<?php echo $id; ?>" style="display:none;">
                                            <input type="hidden" name="id_carrera_filtro" value="<?php echo $id; ?>">
                                        </form>
                                        <a class="dropdown-item" href="#" onclick="document.getElementById('form-carrera-<?php echo $id; ?>').submit(); return false;">
                                            <?php echo $nombreCarrera; ?>
                                        </a>
                                    </li>
                        <?php
                            }
                            $resultado_carreras->free();
                        } else {
                        ?>
                            <li><a class="dropdown-item disabled" href="#">No hay carreras disponibles</a></li>
                        <?php
                        }
                        ?>
                        </ul>
                    </li>
                </ul>

                <div class="icon-group d-flex align-items-center mx-auto">
                    <a class="Icon fa-lg" href="Principal.html"><span class="material-symbols-outlined">notifications</span></a>
                    <a class="Icon" href="../../mensajeria.php"><span class="material-symbols-outlined">mail</span></a>
                    <a class="Icon"><span class="material-symbols-outlined">school</span></a>
                    <a class="Icon" href="public/pages/perfil.php"><span class="material-symbols-outlined">account_circle</span></a>
                </div>
                
            </div>
        </div>
    </nav>
    
<div class="cont-crear">
    <div class="div" style="padding-top: 30px;">
        <h3 class="Titulo titu_crear">PUBLICAR UN SERVICIO</h3>
    </div>
    
    <form id="formServicio">
        <div class="row">
            <div class="col-lg-12">
                <label for="titulo" class="lb_modal">TÍTULO</label>
                <br>
                <input type="text" id="titulo" name="titulo" class="inputs-publi" required>          
            </div>
            
            <div class="col-lg-6 col-md-12 espacio">
                <label for="carrera_visual_input" class="lb_modal">CARRERA</label>
                <br>
                <div class="custom-select-container">
                    <input type="text" id="carrera_visual_input" class="form-control dropdown_front" placeholder="Seleccione o busque la carrera..." autocomplete="off">
                    <ul id="carrera_custom_list" class="list-group" style="display: none;"></ul>
                </div>
                <select id="carrera_id" name="carrera_id" required style="display: none;">
                    <option value="" selected disabled>Seleccione la carrera</option> 
                    <?php
                    $sql = "SELECT id_carrera, nombre_carrera FROM carreras ORDER BY nombre_carrera";
                    $result = $mysqli->query($sql);
                    while($row = $result->fetch_assoc()) {
                        echo '<option value="' . $row["id_carrera"] . '">' . htmlspecialchars($row["nombre_carrera"]) . '</option>';
                    }
                    ?>
                </select>
                <br>
                
                <label for="tipo_trabajo_visual_input" class="lb_modal">TIPO DE TRABAJO</label>
                <div class="custom-select-container">
                    <input type="text" id="tipo_trabajo_visual_input" class="form-control dropdown_front" placeholder="Seleccione o busque el tipo de trabajo..." autocomplete="off">
                    <ul id="tipo_trabajo_custom_list" class="list-group" style="display: none;"></ul>
                </div>
                <select id="tipo_trabajo_id" name="tipo_trabajo_id" required style="display: none;">
                    <option value="" selected disabled>Seleccione el Tipo de Trabajo</option> 
                    <?php
                    $sql = "SELECT id_tipo_trabajo, nombre FROM tipos_trabajos ORDER BY nombre";
                    $result = $mysqli->query($sql);
                    while($row = $result->fetch_assoc()) {
                        echo '<option value="' . $row["id_tipo_trabajo"] . '">' . htmlspecialchars($row["nombre"]) . '</option>';
                    }
                    ?>
                </select>
            </div>

            <div class="col-lg-6 col-md-12 espacio">
                <label for="materia_visual_input" class="lb_modal">MATERIA</label>
                <br>
                <div class="custom-select-container">
                    <input type="text" id="materia_visual_input" class="form-control dropdown_front" placeholder="Seleccione o busque una materia..." autocomplete="off">
                    <ul id="materia_custom_list" class="list-group" style="display: none; position: absolute; width: 100%; z-index: 1000; max-height: 200px; overflow-y: auto; border-top: none;"></ul>
                </div>
                <select id="materia_id" name="materia_id" required style="display: none;">
                    <option value="" selected disabled>Seleccione la materia</option> 
                </select>
                
                <br>
                <label for="precio" class="lb_modal">PRECIO</label>
                <div class="input-group mb-3">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" min="0.00" id="precio" name="precio" class="form-control inputs" required>
                </div>
            </div>

            <div class="col-lg-12 espacio">
                <label for="descripcion_input" class="lb_modal_des">DESCRIPCIÓN</label>
                <br>
                <textarea id="descripcion_input" name="descripcion" class="inputs" rows="4" required></textarea>            
                
                <div class="mt-3">
                    <input type="file" id="input-archivos-servicio" accept="image/*" style="display: none;"> 
                    
                    <button type="button" class="btn btn-secondary" id="btn-trigger-image">
                        <i class="bi bi-cloud-arrow-up-fill"></i> Seleccionar Imagen (Opcional)
                    </button>
                            
                    <div id="preview-archivos" class="preview-container">
                        <p id="mensaje-vacio-imagen" class="text-muted w-100 text-center my-auto">
                            No hay imagen seleccionada.
                        </p>
                    </div> 
                </div>
            </div>

            <div class="d-flex justify-content-center mt-4">
                <button type="submit" class="btn_siguiente">CREAR</button>
            </div>
        </div>
    </form>
</div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="dropdown.js"></script>
    <script src="crearServicio.js"> </script>
</body>
</html>