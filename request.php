<?php
session_start();
include('conect.php'); 

// ==================== DEBUG SUPER DETALLADO ====================
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Crear archivo de log
$log_file = 'debug_detallado.log';
$log = "====== " . date('Y-m-d H:i:s') . " ======\n";

function log_debug($message, $data = null) {
    global $log;
    $log .= "[DEBUG] $message\n";
    if ($data !== null) {
        $log .= print_r($data, true) . "\n";
    }
    $log .= "--------------------------------\n";
}

// Obtener ID del usuario desde sesión
$id_usuario_logueado = $_SESSION['id_usuario'] ?? 1;

// Variable para mensajes de alerta
$alert_script = '';

// === DEBUG DE FORMULARIO ===
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    log_debug("RECIBIENDO POST", [
        'POST' => $_POST,
        'FILES' => $_FILES,
        'CONTENT_TYPE' => $_SERVER['CONTENT_TYPE'],
        'CONTENT_LENGTH' => $_SERVER['CONTENT_LENGTH'] ?? 'N/A'
    ]);
    
    // DEBUG ESPECÍFICO DE ARCHIVOS
    if (isset($_FILES['archivos-request'])) {
        $file_count = count($_FILES['archivos-request']['name']);
        log_debug("Archivos recibidos", $file_count);
    }
    
    // === PROCESAMIENTO NORMAL ===
    $titulo = $_POST['titulo'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $precio = (float)($_POST['precio'] ?? 0); 
    $fecha_limite = $_POST['fecha-limit-req'] ?? '';
    $tipo_trabajo_id = (int)($_POST['tipo_trabajo_id'] ?? 0);
    $carrera_id = (int)($_POST['carrera_id'] ?? 0);
    $materia_id = (int)($_POST['materia_id'] ?? 0);
    
    // Validación básica
    if (empty($titulo) || $precio <= 0 || $tipo_trabajo_id == 0 || $carrera_id == 0 || $materia_id == 0 || empty($fecha_limite)) {
        $error_msg = "Faltan campos obligatorios";
        $alert_script = "Swal.fire('Error', '$error_msg', 'error');";
        
    } else {
        // Insertar request
        $sql_insert_request = "INSERT INTO requests (
            titulo, descripcion, precio, fecha_creacion, fecha_limite, id_tipo_trabajo, id_carrera, id_materia, id_usuario
        ) VALUES (
            ?, ?, ?, CURDATE(), ?, ?, ?, ?, ?
        )";
        
        $stmt = $mysqli->prepare($sql_insert_request);
        
        if ($stmt === false) {
            $error_msg = "Error al preparar la consulta: " . $mysqli->error;
            $alert_script = "Swal.fire('Error', '$error_msg', 'error');";
            
        } else {
            $stmt->bind_param("ssdsiiii", 
                $titulo, 
                $descripcion, 
                $precio, 
                $fecha_limite,
                $tipo_trabajo_id, 
                $carrera_id, 
                $materia_id, 
                $id_usuario_logueado
            );
            
            if ($stmt->execute()) {
                $id_request = $stmt->insert_id;
                
                // ============== PROCESAR ARCHIVOS ==============
                $archivos_procesados = 0;
                $errores_archivos = [];
                
                if (isset($_FILES['archivos-request']) && !empty($_FILES['archivos-request']['name'][0])) {
                    
                    // Crear carpeta si no existe
                    $carpeta_destino = 'uploads/requests/';
                    if (!file_exists($carpeta_destino)) {
                        mkdir($carpeta_destino, 0777, true);
                    }
                    
                    // Procesar cada archivo
                    $file_count = count($_FILES['archivos-request']['name']);
                    for ($i = 0; $i < $file_count && $i < 3; $i++) {
                        $archivos_procesados++;
                        
                        if ($_FILES['archivos-request']['error'][$i] === UPLOAD_ERR_OK) {
                            // Verificar que el archivo temporal exista
                            $tmp_file = $_FILES['archivos-request']['tmp_name'][$i];
                            if (!file_exists($tmp_file)) {
                                $errores_archivos[] = "Archivo temporal no encontrado";
                                continue;
                            }
                            
                            // Generar nombre único
                            $nombre_original = basename($_FILES['archivos-request']['name'][$i]);
                            $nombre_archivo = time() . '_' . $i . '_' . preg_replace('/[^a-zA-Z0-9\.]/', '_', $nombre_original);
                            $ruta_destino = $carpeta_destino . $nombre_archivo;
                            
                            // Mover archivo
                            if (move_uploaded_file($tmp_file, $ruta_destino)) {
                                
                                // Insertar en BD
                                $sql_archivo = "INSERT INTO archivos_request (nombre_archivo, url_archivo, tipo_archivo, id_requests) 
                                                VALUES (?, ?, ?, ?)";
                                
                                $stmt_archivo = $mysqli->prepare($sql_archivo);
                                if ($stmt_archivo) {
                                    $tipo_archivo = $_FILES['archivos-request']['type'][$i];
                                    $stmt_archivo->bind_param(
                                        "sssi",
                                        $nombre_original,
                                        $ruta_destino,
                                        $tipo_archivo,
                                        $id_request
                                    );
                                    
                                    if (!$stmt_archivo->execute()) {
                                        $errores_archivos[] = "Error BD: " . $stmt_archivo->error;
                                        unlink($ruta_destino);
                                    }
                                    $stmt_archivo->close();
                                } else {
                                    $errores_archivos[] = "Error preparando consulta";
                                    unlink($ruta_destino);
                                }
                            } else {
                                $errores_archivos[] = "Error moviendo archivo: $nombre_original";
                            }
                        } else {
                            $errores_archivos[] = "Error en subida del archivo";
                        }
                    }
                }
                
                $stmt->close();
                
                // Crear mensaje de resultado
                if (empty($errores_archivos)) {
                    if ($archivos_procesados > 0) {
                        $alert_script = "
                            Swal.fire({
                                title: '¡Éxito!',
                                text: 'Request publicado con $archivos_procesados archivo(s) adjunto(s).',
                                icon: 'success'
                            }).then(() => {
                                window.location.href = 'index.php';
                            });
                        ";
                    } else {
                        $alert_script = "
                            Swal.fire({
                                title: '¡Éxito!',
                                text: 'Request publicado sin archivos adjuntos.',
                                icon: 'success'
                            }).then(() => {
                                window.location.href = 'index.php';
                            });
                        ";
                    }
                } else {
                    $alert_script = "
                        Swal.fire({
                            title: 'Atención',
                            html: 'Request publicado, pero con errores en algunos archivos.<br><br>Archivos procesados: $archivos_procesados<br>Errores: " . count($errores_archivos) . "',
                            icon: 'warning'
                        }).then(() => {
                            window.location.href = 'index.php';
                        });
                    ";
                }
                
            } else {
                $error_msg = "Error: " . $stmt->error;
                $alert_script = "Swal.fire('Error', '$error_msg', 'error');";
                $stmt->close();
            }
        }
    }
    
    // Guardar log
    file_put_contents($log_file, $log, FILE_APPEND);
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
    
    <!-- SweetAlert2 CSS & JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <title>Request</title>
    <style>
        .file-input-container {
            margin: 15px 0;
            padding: 20px;
            border: 2px dashed #ccc;
            border-radius: 8px;
            background-color: #f9f9f9;
            transition: all 0.3s;
        }
        
        .file-input-container:hover {
            border-color: #007bff;
            background-color: #e9f7fe;
        }
        
        #input-archivos-request {
            display: block !important;
            width: 100% !important;
            padding: 10px !important;
            margin: 10px 0 !important;
        }
        
        #file-list .badge {
            font-size: 0.9em;
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/app/includes/Navbar.php'; ?>
    
    <!-- Script para mostrar alertas después de cargar la página -->
    <?php if (!empty($alert_script)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php echo $alert_script; ?>
        });
    </script>
    <?php endif; ?>
    
    <div class="cont-crear">
    <div class="div">
        <h3 class="Titulo titu_crear">CREAR UN REQUEST</h3>
    </div>
        <form action="request.php" method="POST" enctype="multipart/form-data" id="formRequest">
        <div class="row">
            <div class="col-lg-6 col-md-12 espacio">
                <label for="titu_req" class="lb_modal">TÍTULO</label>
                <br>
                <input type="text" id="titulo" name="titulo" class="inputs" required>
                <br>
                <label for="carrera_visual_input" class="lb_modal">CARRERA</label>
                <br>
                <div class="custom-select-container">
                    <input type="text" id="carrera_visual_input" class="form-control dropdown_front" placeholder="Seleccione o busque la carrera...">
                    <ul id="carrera_custom_list" class="list-group" style="display: none;"></ul>
                </div>
                <select id="carrera_id" name="carrera_id" required style="display: none;">
                    <option value="" selected disabled>Seleccione la carrera</option> 
                    <?php
                    $sql = "SELECT id_carrera, nombre_carrera FROM carreras ORDER BY nombre_carrera";
                    $result = $mysqli->query($sql);
                    while($row = $result->fetch_assoc()) {
                        echo '<option value="' . $row["id_carrera"] . '">' . $row["nombre_carrera"] . '</option>';
                    }
                    ?>
                </select>
                <br>
                <label for="tipo_trabajo_visual_input" class="lb_modal">TIPO DE TRABAJO</label>
                <div class="custom-select-container">
                    <input type="text" id="tipo_trabajo_visual_input" class="form-control dropdown_front" placeholder="Seleccione o busque el tipo de trabajo...">
                    <ul id="tipo_trabajo_custom_list" class="list-group" style="display: none;"></ul>
                </div>
                <select id="tipo_trabajo_id" name="tipo_trabajo_id" required style="display: none;">
                    <option value="" selected disabled>Seleccione el Tipo de Trabajo</option> 
                    <?php
                    $sql = "SELECT id_tipo_trabajo, nombre FROM tipos_trabajos ORDER BY nombre";
                    $result = $mysqli->query($sql);
                    while($row = $result->fetch_assoc()) {
                        echo '<option value="' . $row["id_tipo_trabajo"] . '">' . $row["nombre"] . '</option>';
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
                    <input type="text" id="materia_visual_input" class="form-control dropdown_front" placeholder="Seleccione o busque una materia...">
                    <ul id="materia_custom_list" class="list-group" style="display: none;"></ul>
                </div>
                <select id="materia_id" name="materia_id" required style="display: none;">
                    <option value="" selected disabled>Seleccione la materia</option> 
                </select>
                <br>
                <label for="precio" class="lb_modal">PRECIO</label>
                <br>
                <input type="number" step="0.01" min="0.00" id="precio" name="precio" class="inputs" required>               
            </div>
            <div class="col-lg-12 espacio">
                <label for="descripcion" class="lb_modal_des">DESCRIPCIÓN</label>
                <br>
                <textarea id="descripcion_input" name="descripcion" class="inputs" rows="4" required></textarea>            
                
                <!-- INPUT DE ARCHIVOS VISIBLE Y FUNCIONAL -->
                <div class="file-input-container">
                    <label for="input-archivos-request" class="form-label fw-bold">ARCHIVOS ADJUNTOS (Máx. 3)</label>
                    <input type="file" 
                           id="input-archivos-request" 
                           name="archivos-request[]" 
                           class="form-control" 
                           multiple 
                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.txt,.xls,.xlsx,.zip,.rar"
                           onchange="showFileCount(this)">
                    <small class="text-muted">Selecciona hasta 3 archivos para adjuntar al request</small>
                    <div id="file-count-display" class="mt-2 text-primary"></div>
                </div>
                
                <!-- Preview simplificado -->
                <div id="preview-archivos" style="display: none; margin-top: 15px;">
                    <h6>Archivos seleccionados:</h6>
                    <div id="file-list"></div>
                </div>
            </div>
            <div class="d-flex justify-content-center">
                <button type="submit" class="btn_crear_req btn_siguiente">CREAR REQUEST</button>
            </div>
        </div>
        </form>
    </div>

    <!-- Scripts -->
    <script>
        // Función para mostrar cantidad de archivos
        function showFileCount(input) {
            const fileCount = input.files.length;
            const display = document.getElementById('file-count-display');
            const preview = document.getElementById('preview-archivos');
            const fileList = document.getElementById('file-list');
            
            if (fileCount > 0) {
                display.innerHTML = `<strong>${fileCount} archivo(s) seleccionado(s)</strong>`;
                preview.style.display = 'block';
                
                // Mostrar lista de archivos
                fileList.innerHTML = '';
                for (let i = 0; i < Math.min(fileCount, 3); i++) {
                    const file = input.files[i];
                    const fileItem = document.createElement('div');
                    fileItem.className = 'mb-1';
                    fileItem.innerHTML = `
                        <span class="badge bg-secondary">${file.name}</span>
                        <small class="text-muted">(${(file.size / 1024).toFixed(2)} KB)</small>
                    `;
                    fileList.appendChild(fileItem);
                }
                
                // Limitar a 3 archivos
                if (fileCount > 3) {
                    alert('Solo puedes subir un máximo de 3 archivos. Se seleccionarán solo los primeros 3.');
                    // Cortar la lista a 3 archivos
                    const dataTransfer = new DataTransfer();
                    for (let i = 0; i < 3; i++) {
                        dataTransfer.items.add(input.files[i]);
                    }
                    input.files = dataTransfer.files;
                }
            } else {
                display.innerHTML = '';
                preview.style.display = 'none';
            }
        }
        
        // Debug del formulario
        document.getElementById('formRequest').addEventListener('submit', function(e) {
            console.log('=== DEBUG FORMULARIO REQUEST ===');
            console.log('Formulario enviado');
            
            const fileInput = document.getElementById('input-archivos-request');
            console.log('Archivos seleccionados:', fileInput.files.length);
            
            for (let i = 0; i < fileInput.files.length; i++) {
                console.log(`Archivo ${i+1}:`, fileInput.files[i].name, 'Tamaño:', fileInput.files[i].size, 'Tipo:', fileInput.files[i].type);
            }
        });
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="dropdown.js"></script>

</body>
</html>