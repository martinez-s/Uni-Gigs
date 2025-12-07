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

    // Insertar el servicio en la base de datos (sin fecha límite según tu estructura)
    $sql_insert_servicio = "INSERT INTO servicios (titulo, descripcion, precio, fecha_creacion, id_tipo_trabajo, id_carrera, id_materia, id_usuario) VALUES (?, ?, ?, CURDATE(), ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql_insert_servicio);
    
    if ($stmt) {
        $stmt->bind_param("ssdiiii", $titulo, $descripcion, $precio, $tipo_trabajo_id, $carrera_id, $materia_id, $id_usuario_logueado);
        
        if ($stmt->execute()) {
            $id_servicio = $stmt->insert_id;
            $imagen_procesada = 0;

            // Procesar imagen (solo una)
            if (isset($_FILES['imagen-servicio']) && $_FILES['imagen-servicio']['error'] === UPLOAD_ERR_OK) {
                $carpeta_destino = 'public/img/imgSer/';
                
                // Crear carpeta si no existe
                if (!file_exists($carpeta_destino)) {
                    mkdir($carpeta_destino, 0777, true);
                }
                
                $tmp_file = $_FILES['imagen-servicio']['tmp_name'];
                $nombre_original = basename($_FILES['imagen-servicio']['name']);
                
                // Generar nombre único para la imagen
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
    <link rel="stylesheet" href="public/styles/styles.css">
    <!-- Estilos específicos para el servicio -->
    <link rel="stylesheet" href="public/styles/crear_request.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <title>Crear Servicio</title>
</head>
<body>
    <?php include __DIR__ . '/app/includes/Navbar.php'; ?>
    
<div class="cont-crear">
    <div class="div" style="padding-top: 100px;">
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
                <br>
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
                    <input type="file" id="input-imagen-servicio" accept="image/*" style="display: none;"> 
                    
                    <button type="button" class="btn btn-secondary" id="btn-trigger-image">
                        <i class="bi bi-image-fill"></i> Seleccionar Imagen (Solo 1)
                    </button>
                            
                    <div id="preview-imagen" class="preview-container">
                        <p id="mensaje-vacio-imagen" class="text-muted w-100 text-center my-auto">No hay imagen seleccionada.</p>
                    </div> 
                </div>
            </div>

            <div class="d-flex justify-content-center mt-4">
                <button type="submit" class="btn_crear_req btn_siguiente">CREAR</button>
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