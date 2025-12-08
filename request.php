<?php
session_start();
include('conect.php'); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    header('Content-Type: application/json');
    
    $id_usuario_logueado = $_SESSION['id_usuario'] ?? 1;

    $titulo = $_POST['titulo'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $precio = (float)($_POST['precio'] ?? 0); 
    $fecha_limite = $_POST['fecha-limit-req'] ?? '';
    $tipo_trabajo_id = (int)($_POST['tipo_trabajo_id'] ?? 0);
    $carrera_id = (int)($_POST['carrera_id'] ?? 0);
    $materia_id = (int)($_POST['materia_id'] ?? 0);
    
    if (empty($titulo) || $precio <= 0 || $tipo_trabajo_id == 0 || $carrera_id == 0 || $materia_id == 0 || empty($fecha_limite)) {
        echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios']);
        exit;
    }

    $sql_insert_request = "INSERT INTO requests (titulo, descripcion, precio, fecha_creacion, fecha_limite, id_tipo_trabajo, id_carrera, id_materia, id_usuario) VALUES (?, ?, ?, CURDATE(), ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql_insert_request);
    
    if ($stmt) {
        $stmt->bind_param("ssdsiiii", $titulo, $descripcion, $precio, $fecha_limite, $tipo_trabajo_id, $carrera_id, $materia_id, $id_usuario_logueado);
        
        if ($stmt->execute()) {
            $id_request = $stmt->insert_id;
            $archivos_procesados = 0;

            // Procesar archivos
            if (isset($_FILES['archivos-request']) && !empty($_FILES['archivos-request']['name'][0])) {
                $carpeta_destino = 'uploads/requests/';
                $file_count = count($_FILES['archivos-request']['name']);
                
                for ($i = 0; $i < $file_count && $i < 3; $i++) {
                    if ($_FILES['archivos-request']['error'][$i] === UPLOAD_ERR_OK) {
                        $tmp_file = $_FILES['archivos-request']['tmp_name'][$i];
                        $nombre_original = basename($_FILES['archivos-request']['name'][$i]);
                        $nombre_archivo = time() . '_' . $i . '_' . preg_replace('/[^a-zA-Z0-9\.]/', '_', $nombre_original);
                        $ruta_destino = $carpeta_destino . $nombre_archivo;
                        
                        if (move_uploaded_file($tmp_file, $ruta_destino)) {
                            $sql_archivo = "INSERT INTO archivos_request (nombre_archivo, url_archivo, tipo_archivo, id_requests) VALUES (?, ?, ?, ?)";
                            $stmt_archivo = $mysqli->prepare($sql_archivo);
                            if ($stmt_archivo) {
                                $tipo_archivo = $_FILES['archivos-request']['type'][$i];
                                $stmt_archivo->bind_param("sssi", $nombre_original, $ruta_destino, $tipo_archivo, $id_request);
                                $stmt_archivo->execute();
                                $archivos_procesados++;
                            }
                        }
                    }
                }
            }
            
            echo json_encode([
                'success' => true, 
                'message' => 'Request creado correctamente' . ($archivos_procesados > 0 ? " con $archivos_procesados archivo(s)" : ""),
                'archivos' => $archivos_procesados
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error en la base de datos']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Error preparando la consulta']);
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
    <!-- Estilos específicos para el request -->
    <link rel="stylesheet" href="public/styles/crear_request.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <title>Crear Request</title>
</head>
<body>
    <?php include __DIR__ . '/app/includes/Navbar.php'; ?>
    
    <div class="cont-crear ">
        <div class="div" style="padding-top: 100px;">
            <h3 class="Titulo titu_crear">CREAR UN REQUEST</h3>
        </div>
        
        <form id="formRequest">
            <div class="row">
                <div class="col-lg-6 col-md-12 espacio">
                    <label for="titulo" class="lb_modal">TÍTULO</label>
                    <br>
                    <input type="text" id="titulo" name="titulo" class="inputs" required>
                    <br>
                    
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
                    <label for="fecha-limit-req" class="lb_modal">FECHA LÍMITE</label>
                    <br>
                    <input type="date" id="fecha-limit-req" name="fecha-limit-req" class="inputs" required>   
                    <br>
                    
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
                        <span class="input-group-text" style="height: 16px">$</span>
                        <input type="number" step="0.01" min="0.00" id="precio" name="precio" class="form-control inputs" style="height: 16px"required>
                    </div>
                </div>

                <div class="col-lg-12 espacio">
                    <label for="descripcion_input" class="lb_modal_des">DESCRIPCIÓN</label>
                    <br>
                    <textarea id="descripcion_input" name="descripcion" class="inputs" rows="4" required></textarea>            
                    
                    <div class="mt-3">
                        <input type="file" id="input-archivos-request" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.txt,.zip,.rar"> 
                        
                        <button type="button" class="btn btn-secondary" id="btn-trigger-file">
                            <i class="bi bi-cloud-arrow-up-fill"></i> Seleccionar Archivos (Máx 3)
                        </button>
                                
                        <div id="preview-archivos" class="preview-container">
                            <p id="mensaje-vacio" class="text-muted w-100 text-center my-auto">No hay archivos seleccionados.</p>
                        </div> 
                    </div>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    <button type="submit" style="width: 100%;" class=" btn_siguiente">CREAR REQUEST</button>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="dropdown.js"></script>

    <script src="crearRequest.js"> </script>
</body>
</html>