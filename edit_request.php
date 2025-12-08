<?php

include('conect.php'); 

echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>'; 

// Simulamos el ID del usuario logueado (Asegúrate de obtener esto de la sesión real)
$id_usuario_logueado = 2; 

// 1. OBTENER ID DEL REQUEST Y PRECARGAR DATOS

//$id_request_a_editar = (int)($_GET['id_requests'] ?? 0); 
$id_request_a_editar = 1;
$request_data = null; 
$nombre_carrera_precargada = '';
$nombre_tipo_trabajo_precargada = '';
$nombre_materia_precargada = ''; // Nueva variable para precargar el nombre de la materia

if ($id_request_a_editar > 0) {
    // Consulta SEGURA para obtener los datos Y verificar que pertenezca al usuario logueado
    $sql_fetch_request = "SELECT * FROM requests WHERE id_requests = ? AND id_usuario = ?";
    $stmt_fetch = $mysqli->prepare($sql_fetch_request);

    if ($stmt_fetch) {
        $stmt_fetch->bind_param("ii", $id_request_a_editar, $id_usuario_logueado);
        $stmt_fetch->execute();
        $result_fetch = $stmt_fetch->get_result();

        if ($result_fetch->num_rows === 1) {
            $request_data = $result_fetch->fetch_assoc();

            // Consultas SEGURAS para obtener los NOMBRES de las categorías (para el input visual)
            
            // a) Obtener Nombre de Carrera
            $sql_carrera = "SELECT nombre_carrera FROM carreras WHERE id_carrera = ?";
            $stmt_carrera = $mysqli->prepare($sql_carrera);
            if ($stmt_carrera) {
                $stmt_carrera->bind_param("i", $request_data['id_carrera']);
                $stmt_carrera->execute();
                $result_carrera = $stmt_carrera->get_result();
                if ($result_carrera->num_rows === 1) {
                    $nombre_carrera_precargada = $result_carrera->fetch_assoc()['nombre_carrera'];
                }
                $stmt_carrera->close();
            }

            // b) Obtener Nombre de Tipo de Trabajo
            $sql_tipo_trabajo = "SELECT nombre FROM tipos_trabajos WHERE id_tipo_trabajo = ?";
            $stmt_tipo_trabajo = $mysqli->prepare($sql_tipo_trabajo);
            if ($stmt_tipo_trabajo) {
                $stmt_tipo_trabajo->bind_param("i", $request_data['id_tipo_trabajo']);
                $stmt_tipo_trabajo->execute();
                $result_tipo_trabajo = $stmt_tipo_trabajo->get_result();
                if ($result_tipo_trabajo->num_rows === 1) {
                    $nombre_tipo_trabajo_precargada = $result_tipo_trabajo->fetch_assoc()['nombre'];
                }
                $stmt_tipo_trabajo->close();
            }

            // c) Obtener Nombre de Materia
            $sql_materia = "SELECT nombre FROM materias WHERE id_materia = ?";
            $stmt_materia = $mysqli->prepare($sql_materia);
            if ($stmt_materia) {
                $stmt_materia->bind_param("i", $request_data['id_materia']);
                $stmt_materia->execute();
                $result_materia = $stmt_materia->get_result();
                if ($result_materia->num_rows === 1) {
                    $nombre_materia_precargada = $result_materia->fetch_assoc()['nombre'];
                }
                $stmt_materia->close();
            }


        } else {
            // Manejar caso: Request no encontrado o no pertenece al usuario
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire('Error', 'Request no encontrado o acceso denegado.', 'error').then(() => {
                        window.location.href = 'index.php';
                    });
                });
            </script>";
            exit; 
        }
        $stmt_fetch->close();
    } else {
         // Manejar error de preparación de consulta inicial
        error_log("Error al preparar la consulta de carga: " . $mysqli->error);
         // Mostrar un error genérico al usuario
    }
} else {
    // Si no hay ID, redirigir
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire('Error', 'ID de Request inválido.', 'error').then(() => {
                window.location.href = 'index.php';
            });
        });
    </script>";
    exit;
}

// 2. PROCESAR FORMULARIO (UPDATE) - Usa la lógica segura de tu ejemplo anterior

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Obtención de datos POST (sin sanitización aquí, mysqli::prepare lo hará)
    $id_request_post = (int)($_POST['id_requests'] ?? 0);
    $titulo = $_POST['titulo'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $precio = (float)($_POST['precio'] ?? 0); 
    $fecha_limite = $_POST['fecha-limit-req'] ?? '';
    $tipo_trabajo_id = (int)($_POST['tipo_trabajo_id'] ?? 0);
    $carrera_id = (int)($_POST['carrera_id'] ?? 0);
    $materia_id = (int)($_POST['materia_id'] ?? 0);

    // Validación de campos
    if (empty($titulo) || $precio <= 0 || $tipo_trabajo_id == 0 || $carrera_id == 0 || $materia_id == 0 || empty($fecha_limite)) {
        die('Se llegó al error');
        $error_msg = "Faltan campos obligatorios o los valores son inválidos.";

        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire('Error', '{$error_msg}', 'error');
            });
        </script>";
        
    } else if ($id_request_post != $id_request_a_editar) {
         // Validación de seguridad
        $error_msg = "Error de seguridad: ID de Request no coincide.";

        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire('Error', '{$error_msg}', 'error');
            });
        </script>";
    } else {
        
        // Ejecutar UPDATE (CONSULTA PREPARADA SEGURA)
        $sql_update_request = "UPDATE requests SET 
            titulo = ?, descripcion = ?, precio = ?, fecha_limite = ?, id_tipo_trabajo = ?, 
            id_carrera = ?, id_materia = ? 
            WHERE id_requests = ? AND id_usuario = ?";

        $stmt = $mysqli->prepare($sql_update_request);

        if ($stmt === false) {
            $error_msg = "Error al preparar la consulta de actualización: " . $mysqli->error;
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire('Error', '{$error_msg}', 'error');
                });
            </script>";
        } else {
            
            // s=string, d=double, i=integer
            $stmt->bind_param("ssdsiiiii", 
                $titulo, $descripcion, $precio, $fecha_limite, 
                $tipo_trabajo_id, $carrera_id, $materia_id,
                $id_request_post, $id_usuario_logueado 
            );

            if ($stmt->execute()) {
                $stmt->close();
                
                echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                title: '¡Éxito!',
                                text: 'Request actualizado correctamente.',
                                icon: 'success',
                                confirmButtonText: 'Ok'
                            }).then((result) => {
                                window.location.href = 'index.php'; 
                            });
                        });
                    </script>";

            } else {

                $error_msg = "Error al actualizar el servicio: " . $stmt->error;
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire('Error', '{$error_msg}', 'error');
                    });
                </script>";
                $stmt->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="public/styles/styles.css">
    <title>Editar Request</title>
</head>
<body>
    <?php include __DIR__ . '/app/includes/Navbar.php'; ?>
    <div class="cont-crear">
        <div class="div">
            <h3 class="Titulo titu_crear">EDITAR REQUEST</h3>
        </div>
        
        <form action="edit_request.php" method="POST" enctype="multipart/form-data">
            
            <input type="hidden" name="id_requests" value="<?php echo $id_request_a_editar; ?>">

            <div class="row">
                
                <div class="col-lg-6 col-md-12 espacio">
                    <label for="titu_req" class="lb_modal">TÍTULO</label>
                    <br>
                    <input 
                        type="text" 
                        id="titulo" 
                        name="titulo" 
                        class="inputs" 
                        required 
                        value="<?php echo htmlspecialchars($request_data['titulo'] ?? ''); ?>"
                    >
                    <br>
                    
                    <label for="carrera_visual_input" class="lb_modal">CARRERA</label>
                    <br>

                    <div class="custom-select-container">
                        <input 
                            type="text" 
                            id="carrera_visual_input" 
                            class="form-control dropdown_front" 
                            placeholder="Seleccione o busque la carrera..."
                            autocomplete="off"
                            value="<?php echo htmlspecialchars($nombre_carrera_precargada); ?>"
                        >
                        <ul id="carrera_custom_list" class="list-group" style="display: none;"></ul>
                    </div>

                    <select id="carrera_id" name="carrera_id" required style="display: none;">
                        <option value="" disabled>Seleccione la carrera</option> 
                        <?php

                        $sql = "SELECT id_carrera, nombre_carrera FROM carreras ORDER BY nombre_carrera";
                        $result = $mysqli->query($sql);

                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                $selected = ($request_data['id_carrera'] ?? 0) == $row["id_carrera"] ? 'selected' : '';
                                echo '<option value="' . $row["id_carrera"] . '" data-nombre="' . htmlspecialchars($row["nombre_carrera"]) . '" ' . $selected . '>' . htmlspecialchars($row["nombre_carrera"]) . '</option>';
                            }
                        } else {
                            echo '<option value="" class="text-dropdown">(No hay carreras disponibles)</option>';
                        }
                        ?>
                    </select>
                    <br>
                    
                    <label for="tipo_trabajo_visual_input" class="lb_modal">TIPO DE TRABAJO</label>

                    <div class="custom-select-container">
                        <input 
                            type="text" 
                            id="tipo_trabajo_visual_input" 
                            class="form-control dropdown_front" 
                            placeholder="Seleccione o busque el tipo de trabajo..."
                            autocomplete="off"
                            value="<?php echo htmlspecialchars($nombre_tipo_trabajo_precargada); ?>"
                        >
                        <ul id="tipo_trabajo_custom_list" class="list-group" style="display: none;"></ul>
                    </div>

                    <select id="tipo_trabajo_id" name="tipo_trabajo_id" required style="display: none;">
                        <option value="" disabled>Seleccione el Tipo de Trabajo</option> 
                        <?php

                        $sql = "SELECT id_tipo_trabajo, nombre FROM tipos_trabajos ORDER BY nombre";
                        $result = $mysqli->query($sql);

                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                $selected = ($request_data['id_tipo_trabajo'] ?? 0) == $row["id_tipo_trabajo"] ? 'selected' : '';
                                echo '<option value="' . $row["id_tipo_trabajo"] . '" data-nombre="' . htmlspecialchars($row["nombre"]) . '" ' . $selected . '>' . htmlspecialchars($row["nombre"]) . '</option>';
                            }
                        } else {
                            echo '<option value="" class="text-dropdown">(No hay tipos de trabajo disponibles)</option>';
                        }
                        ?>
                    </select>
                </div>
                
                <div class="col-lg-6 col-md-12 espacio">
                    <label for="fecha-limit-req" class="lb_modal">FECHA LÍMITE</label>
                    <br>
                    <input 
                        type="date" 
                        id="fecha-limit-req" 
                        name="fecha-limit-req" 
                        class="inputs"
                        value="<?php echo htmlspecialchars($request_data['fecha_limite'] ?? ''); ?>"
                    > 
                    <br>
                    
                    <label for="materia_visual_input" class="lb_modal">MATERIA</label>
                    <br>

                    <div class="custom-select-container">
                        <input 
                            type="text" 
                            id="materia_visual_input" 
                            class="form-control dropdown_front" 
                            placeholder="Seleccione o busque una materia..."
                            autocomplete="off"
                            value="<?php echo htmlspecialchars($nombre_materia_precargada); ?>"
                        >

                        <ul id="materia_custom_list" class="list-group" style="display: none; position: absolute; width: 100%; z-index: 1000; max-height: 200px; overflow-y: auto; border-top: none;">
                        </ul>
                    </div>

                    <select id="materia_id" name="materia_id" required style="display: none;">
                        <option value="" disabled>Seleccione la materia</option> 
                        <?php
                            if (!empty($request_data['id_materia']) && !empty($nombre_materia_precargada)) {
                                echo '<option value="' . $request_data['id_materia'] . '" data-nombre="' . htmlspecialchars($nombre_materia_precargada) . '" selected>' . htmlspecialchars($nombre_materia_precargada) . '</option>';
                            }
                        ?>
                    </select>
                    <br>
                    
                    <label for="precio" class="lb_modal">PRECIO</label>
                    <br>
                    <input 
                        type="number" 
                        step="0.5" 
                        min="1.00" 
                        max="1000.00"
                        id="precio" 
                        name="precio" 
                        class="inputs" 
                        required
                        value="<?php echo htmlspecialchars($request_data['precio'] ?? '0.00'); ?>"
                    >        
                </div>
                
                <div class="col-lg-12 espacio">
                    <label for="descripcion" class="lb_modal_des">DESCRIPCIÓN</label>
                    <br>
                    <textarea 
                        id="descripcion_input" 
                        name="descripcion" 
                        class="inputs" 
                        rows="4" 
                        required
                    ><?php echo htmlspecialchars($request_data['descripcion'] ?? ''); ?></textarea> 
                    
                </div>
                
                <div class="d-flex justify-content-center">
                    <button type="submit" class="btn_crear_req btn_siguiente">ACTUALIZAR</button>
                </div>
            </div>
        </form>
    </div>

    <script src="login_regis.js"></script>
    <script src="dropdown.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>


    <script>
        document.addEventListener('DOMContentLoaded', function() {

    // Función auxiliar para obtener la fecha de hoy en formato YYYY-MM-DD
    const getTodayString = () => {
        const today = new Date();
        const year = today.getFullYear();
        // getMonth() es 0-indexado, así que sumamos 1
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };

    // Función auxiliar para limpiar números de un string (para los inputs de texto de dropdown)
    const cleanNumbers = (value) => value.replace(/[0-9]/g, '');
    
    // Obtener el formulario principal
    const formEditRequest = document.querySelector('form[action="edit_request.php"]'); 

    // --- MANEJADOR PRINCIPAL DEL FORMULARIO ---
    if (formEditRequest) {
        formEditRequest.addEventListener('submit', function(e) {
            e.preventDefault(); 

            const form = this;
            const errors = [];
            
            // --- 1. Obtener Valores e Inputs ---
            
            const tituloInput = form.querySelector('[name="titulo"]');
            const titulo = tituloInput ? tituloInput.value.trim() : '';

            const precioInput = form.querySelector('[name="precio"]');
            const precio = precioInput ? parseFloat(precioInput.value) : NaN; 
            
            const descripcionInput = document.getElementById('descripcion_input');
            const descripcion = descripcionInput ? descripcionInput.value.trim() : '';
            
            const fechaLimiteInput = document.getElementById('fecha-limit-req');
            const fechaLimite = fechaLimiteInput ? fechaLimiteInput.value : ''; // Formato YYYY-MM-DD
            const todayString = getTodayString(); // Formato YYYY-MM-DD
            
            // Campos de Dropdown Visuales y Select Ocultos
            const carreraVisualInput = document.getElementById('carrera_visual_input');
            const tipoTrabajoVisualInput = document.getElementById('tipo_trabajo_visual_input');
            const materiaVisualInput = document.getElementById('materia_visual_input');
            
            const carrera_id = form.querySelector('#carrera_id') ? form.querySelector('#carrera_id').value : '';
            const tipo_trabajo_id = form.querySelector('#tipo_trabajo_id') ? form.querySelector('#tipo_trabajo_id').value : '';
            const materia_id = form.querySelector('#materia_id') ? form.querySelector('#materia_id').value : '';
            
            // Limpiamos las clases de error antes de volver a validar
            document.querySelectorAll('.is-invalid').forEach(input => input.classList.remove('is-invalid'));
            
            // --- 2. VALIDACIÓN DE CAMPOS ---
            
            // a. Título
            if (!titulo) {
                errors.push('El campo **TÍTULO** es obligatorio.');
                tituloInput && tituloInput.classList.add('is-invalid');
            } else if (titulo.length < 5) {
                 errors.push('El **TÍTULO** debe tener al menos 5 caracteres.');
                 tituloInput && tituloInput.classList.add('is-invalid');
            }

            // b. Descripción
            if (!descripcion) {
                errors.push('El campo **DESCRIPCIÓN** es obligatorio.');
                descripcionInput && descripcionInput.classList.add('is-invalid');
            } else if (descripcion.length < 20) {
                 errors.push('La **DESCRIPCIÓN** debe ser más detallada (mínimo 20 caracteres).');
                 descripcionInput && descripcionInput.classList.add('is-invalid');
            }

            // c. Precio
            if (isNaN(precio) || precio <= 0) {
                errors.push('El **PRECIO** debe ser un número válido y mayor que cero.');
                precioInput && precioInput.classList.add('is-invalid');
            }
            
            // d. Fecha Límite
            if (!fechaLimite) {
                errors.push('La **FECHA LÍMITE** es obligatoria.');
                fechaLimiteInput && fechaLimiteInput.classList.add('is-invalid');
            } else if (fechaLimite < todayString) {
                // Validación estricta: la fecha límite no puede ser anterior a hoy
                errors.push('La **FECHA LÍMITE** no puede ser una fecha pasada.');
                fechaLimiteInput && fechaLimiteInput.classList.add('is-invalid');
            }


            // e. Dropdown Fields (Validación de selección basada en ID oculto)
            
            // Carrera
            if (!carrera_id || carrera_id == 0) {
                errors.push('Debe seleccionar una **CARRERA** válida de la lista.');
                carreraVisualInput && carreraVisualInput.classList.add('is-invalid');
            } else if (/\d/.test(carreraVisualInput.value)) { 
                 errors.push('El campo **CARRERA** no debe contener números, seleccione de la lista.');
                 carreraVisualInput && carreraVisualInput.classList.add('is-invalid');
            }

            // Tipo de Trabajo
            if (!tipo_trabajo_id || tipo_trabajo_id == 0) {
                errors.push('Debe seleccionar un **TIPO DE TRABAJO** válido de la lista.');
                tipoTrabajoVisualInput && tipoTrabajoVisualInput.classList.add('is-invalid');
            } else if (/\d/.test(tipoTrabajoVisualInput.value)) { 
                 errors.push('El campo **TIPO DE TRABAJO** no debe contener números, seleccione de la lista.');
                 tipoTrabajoVisualInput && tipoTrabajoVisualInput.classList.add('is-invalid');
            }

            // Materia
            if (!materia_id || materia_id == 0) {
                errors.push('Debe seleccionar una **MATERIA** válida de la lista.');
                materiaVisualInput && materiaVisualInput.classList.add('is-invalid');
            } else if (/\d/.test(materiaVisualInput.value)) { 
                 errors.push('El campo **MATERIA** no debe contener números, seleccione de la lista.');
                 materiaVisualInput && materiaVisualInput.classList.add('is-invalid');
            }
            
            // --- 3. MOSTRAR ERRORES Y DETENER ENVÍO ---
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

            // --- 4. SI LA VALIDACIÓN PASA, CONTINUAR CON EL ENVÍO ---
            console.log("Validación de Edición exitosa. Procediendo con el envío al servidor.");
            form.submit(); // Envía el formulario PHP para la actualización
        });
    }

    // --- MEJORA UX: RESTRICCIÓN DE ENTRADA EN TIEMPO REAL (Inputs de solo texto para dropdowns) ---
    
    const textOnlyInputs = ['carrera_visual_input', 'tipo_trabajo_visual_input', 'materia_visual_input'];

    textOnlyInputs.forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            // Evitar que el usuario escriba números al presionar tecla
            input.addEventListener('keypress', function(e) {
                const charCode = (e.which) ? e.which : e.keyCode;
                if (charCode >= 48 && charCode <= 57) { // Bloquea números (0-9)
                    e.preventDefault();
                }
            });
            
            // Limpiar números si se pegan o se arrastran
            input.addEventListener('input', function() {
                this.value = cleanNumbers(this.value);
            });
        }
    });

    // --- LIMPIEZA DE ERRORES VISUALES AL INTERACTUAR ---
    
    // Limpia la clase 'is-invalid' cuando el usuario empieza a escribir o seleccionar
    document.querySelectorAll('.inputs, .form-control, textarea').forEach(input => {
        input.addEventListener('input', function() {
            this.classList.remove('is-invalid');
        });
        // Para los selects visuales, el click en la lista también debería limpiar el error.
        // Asumo que la lógica en 'dropdown.js' ya maneja esto al seleccionar un ítem de la lista.
    });

    // --- VALIDACIÓN Y PREVIEW DE ARCHIVOS ---
    const inputFile = document.getElementById('input-archivos-request');
    const previewContainer = document.getElementById('preview-archivos');
    const maxFiles = 3;

    if (inputFile && previewContainer) {
        inputFile.addEventListener('change', function() {
            let files = Array.from(this.files);
            
            // 1. Límite de archivos
            if (files.length > maxFiles) {
                Swal.fire('Advertencia', `Solo se permiten subir hasta ${maxFiles} archivos. Los archivos excedentes serán ignorados.`, 'warning');
                // Ajusta el FileList del input a solo los primeros 3
                const dt = new DataTransfer();
                files.slice(0, maxFiles).forEach(file => dt.items.add(file));
                this.files = dt.files;
                files = Array.from(this.files);
            }
            
            previewContainer.innerHTML = ''; // Limpiar el contenido anterior
            
            if (files.length === 0) {
                 previewContainer.innerHTML = '<p id="mensaje-vacio" style="color: #888;">No hay archivos seleccionados.</p>';
                 return;
            }

            // 2. Generar vista previa
            files.forEach((file, index) => {
                const item = document.createElement('div');
                item.className = 'preview-item d-flex align-items-center me-3 mb-2';
                item.style.border = '1px solid #ddd';
                item.style.padding = '5px';
                item.style.borderRadius = '3px';
                item.style.backgroundColor = '#fff';

                // Determinar ícono (usando Bootstrap Icons)
                const icon = document.createElement('i');
                if (file.type.startsWith('image/')) {
                    icon.className = 'bi bi-image-fill text-success me-2';
                } else if (file.type === 'application/pdf') {
                    icon.className = 'bi bi-file-pdf-fill text-danger me-2';
                } else if (file.type.includes('word')) {
                     icon.className = 'bi bi-file-earmark-word-fill text-primary me-2';
                } else {
                    icon.className = 'bi bi-file-earmark text-secondary me-2';
                }
                
                const nameSpan = document.createElement('span');
                nameSpan.textContent = file.name;
                nameSpan.style.fontSize = '0.9em';
                
                const removeButton = document.createElement('button');
                removeButton.innerHTML = '&times;'; 
                removeButton.className = 'btn-close ms-2';
                removeButton.type = 'button';
                removeButton.style.fontSize = '0.8em';
                
                removeButton.addEventListener('click', function() {
                    // Lógica para eliminar el archivo del FileList
                    const dt = new DataTransfer();
                    let currentFiles = Array.from(inputFile.files);
                    // Removemos el archivo por su índice actual
                    currentFiles.splice(index, 1);
                    currentFiles.forEach(f => dt.items.add(f));
                    inputFile.files = dt.files;
                    
                    // Actualizar la vista previa
                    inputFile.dispatchEvent(new Event('change')); 
                });

                item.appendChild(icon);
                item.appendChild(nameSpan);
                item.appendChild(removeButton);
                previewContainer.appendChild(item);
            });
        });
    }

});
    </script>
    </body>
</html>