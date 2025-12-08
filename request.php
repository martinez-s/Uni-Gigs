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
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="public/styles/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Estilos específicos para el request -->
    <link rel="stylesheet" href="public/pages/StylesNav.css">
    <link rel="stylesheet" href="public/styles/crear_request.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <title>Crear Request</title>
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
                        <input type="text" id="carrera_visual_input" required class="form-control dropdown_front" placeholder="Seleccione o busque la carrera..." autocomplete="off">
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
                        <input type="text" id="tipo_trabajo_visual_input" required class="form-control dropdown_front" placeholder="Seleccione o busque el tipo de trabajo..." autocomplete="off">
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
                    <input type="date" id="fecha-limit-req" required name="fecha-limit-req" class="inputs" required>   
                    <br>
                    
                    <label for="materia_visual_input" class="lb_modal">MATERIA</label>
                    <br>
                    <div class="custom-select-container">
                        <input type="text" id="materia_visual_input" required class="form-control dropdown_front" placeholder="Seleccione o busque una materia..." autocomplete="off">
                        <ul id="materia_custom_list" class="list-group" style="display: none; position: absolute; width: 100%; z-index: 1000; max-height: 200px; overflow-y: auto; border-top: none;"></ul>
                    </div>
                    <select id="materia_id" name="materia_id" required style="display: none;">
                        <option value="" selected disabled>Seleccione la materia</option> 
                    </select>
                    <br>
                    
                    <label for="precio" class="lb_modal">PRECIO</label>
                    <div class="input-group mb-3">
                        <span class="input-group-text" style="height: 16px">$</span>
                        <input type="number" step="0.50" min="1.00" max="1000.00" id="precio" name="precio" class="form-control inputs" style="height: 16px"required>
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
                    <button type="submit" class=" btn_siguiente">CREAR REQUEST</button>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="dropdown.js"></script>

    <script src="crearRequest.js"> </script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    const formRequest = document.getElementById('formRequest');
    const fechaInput = document.getElementById('fecha-limit-req');


    function getTodayString() {
        const hoy = new Date();
        

        const dia = String(hoy.getDate()).padStart(2, '0');
        const mes = String(hoy.getMonth() + 1).padStart(2, '0'); 
        const anio = hoy.getFullYear();

        return anio + '-' + mes + '-' + dia;
    }
    

    const cleanNumbers = (value) => value.replace(/[0-9]/g, '');


    if (fechaInput) {
        const fechaMinima = getTodayString();

        fechaInput.setAttribute('min', fechaMinima);
    }

    if (formRequest) {
        formRequest.addEventListener('submit', function(e) {
            e.preventDefault(); 

            const form = this;
            const errors = [];
            

            
            const tituloInput = form.querySelector('[name="titulo"]');
            const titulo = tituloInput ? tituloInput.value.trim() : '';

            const precioInput = form.querySelector('[name="precio"]');
            const precio = precioInput ? parseFloat(precioInput.value) : NaN;

            const descripcionInput = form.querySelector('[name="descripcion"]');
            const descripcion = descripcionInput ? descripcionInput.value.trim() : '';
            
 
            const fechaLimite = fechaInput ? fechaInput.value : ''; 


            const carreraVisualInput = document.getElementById('carrera_visual_input');
            const tipoTrabajoVisualInput = document.getElementById('tipo_trabajo_visual_input');
            const materiaVisualInput = document.getElementById('materia_visual_input');
            
            const carrera_id = form.querySelector('#carrera_id') ? form.querySelector('#carrera_id').value : '';
            const tipo_trabajo_id = form.querySelector('#tipo_trabajo_id') ? form.querySelector('#tipo_trabajo_id').value : '';
            const materia_id = form.querySelector('#materia_id') ? form.querySelector('#materia_id').value : '';
            

            document.querySelectorAll('.is-invalid').forEach(input => input.classList.remove('is-invalid'));
            

            if (!titulo) {
                errors.push('El campo **TÍTULO** es obligatorio.');
                tituloInput && tituloInput.classList.add('is-invalid');
            } else if (titulo.length < 5) {
                 errors.push('El **TÍTULO** debe tener al menos 5 caracteres.');
                 tituloInput && tituloInput.classList.add('is-invalid');
            }

  
            if (!descripcion) {
                errors.push('El campo **DESCRIPCIÓN** es obligatorio.');
                descripcionInput && descripcionInput.classList.add('is-invalid');
            } else if (descripcion.length < 20) {
                 errors.push('La **DESCRIPCIÓN** debe ser más detallada (mínimo 20 caracteres).');
                 descripcionInput && descripcionInput.classList.add('is-invalid');
            }


            if (isNaN(precio) || precio <= 1) {
                errors.push('El **PRECIO** debe ser un número válido y mayor que cero.');
                precioInput && precioInput.classList.add('is-invalid');
            }


            if (!fechaLimite) {
                errors.push('El campo **FECHA LÍMITE** es obligatorio.');
                fechaInput && fechaInput.classList.add('is-invalid');
            } else {
                const todayString = getTodayString();
                
                if (fechaLimite < todayString) {
                    errors.push('La **FECHA LÍMITE** no puede ser un día anterior al día actual.');
                    fechaInput && fechaInput.classList.add('is-invalid');
                }
            }


            if (!carrera_id) {
                errors.push('Debe seleccionar una **CARRERA** válida de la lista.');
                carreraVisualInput && carreraVisualInput.classList.add('is-invalid');
            } else if (/\d/.test(carreraVisualInput.value)) { 
                 errors.push('El campo **CARRERA** no puede contener números.');
                carreraVisualInput && carreraVisualInput.classList.add('is-invalid');
            }


            if (!tipo_trabajo_id) {
                errors.push('Debe seleccionar un **TIPO DE TRABAJO** válido de la lista.');
                tipoTrabajoVisualInput && tipoTrabajoVisualInput.classList.add('is-invalid');
            } else if (/\d/.test(tipoTrabajoVisualInput.value)) { 
                 errors.push('El campo **TIPO DE TRABAJO** no puede contener números.');
                tipoTrabajoVisualInput && tipoTrabajoVisualInput.classList.add('is-invalid');
            }


            if (!materia_id) {
                errors.push('Debe seleccionar una **MATERIA** válida de la lista.');
                materiaVisualInput && materiaVisualInput.classList.add('is-invalid');
            } else if (/\d/.test(materiaVisualInput.value)) { 
                 errors.push('El campo **MATERIA** no puede contener números.');
                materiaVisualInput && materiaVisualInput.classList.add('is-invalid');
            }
            

            if (errors.length > 0) {
                const errorHtml = '<ul>' + errors.map(err => `<li>${err}</li>`).join('') + '</ul>';
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '🚨 Faltan Datos o son Inválidos',
                        html: errorHtml,
                        icon: 'error',
                        confirmButtonText: 'Corregir'
                    });
                } else {
                    alert('Errores de Validación:\n\n' + errors.join('\n'));
                }
                return; 
            }
            
            console.log("Validación de Solicitud exitosa. Procediendo con el envío.");

        });
    }


    const textOnlyInputs = ['carrera_visual_input', 'tipo_trabajo_visual_input', 'materia_visual_input'];

    textOnlyInputs.forEach(id => {
        const input = document.getElementById(id);
        if (input) {

            input.addEventListener('keypress', function(e) {
                const charCode = (e.which) ? e.which : e.keyCode;
                if (charCode >= 48 && charCode <= 57) {
                    e.preventDefault();
                }
            });
            

            input.addEventListener('input', function() {
                this.value = cleanNumbers(this.value);
            });
        }
    });


    
    document.querySelectorAll('.inputs, .form-control, textarea').forEach(input => {
        input.addEventListener('input', function() {
            this.classList.remove('is-invalid');
        });
    });


    const dropdownSelects = ['carrera_id', 'tipo_trabajo_id', 'materia_id'];
    dropdownSelects.forEach(id => {
        const select = document.getElementById(id);
        if (select) {
            select.addEventListener('change', function() {
                const visualId = this.id.replace('_id', '_visual_input');
                const visualInput = document.getElementById(visualId);
                if (visualInput) {
                    visualInput.classList.remove('is-invalid');
                }
            });
        }
    });
});
    </script>
</body>
</html>