<?php

include('conect.php'); 

echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>'; 

// Simulamos el ID del usuario logueado (Asegúrate de obtener esto de la sesión real)
$id_usuario_logueado = 2; 

// 1. OBTENER ID DEL servicio Y PRECARGAR DATOS

//$id_servicio_a_editar = (int)($_GET['id_servicio'] ?? 0); 
$id_servicio_a_editar = 1;
$servicio_data = null; 
$nombre_carrera_precargada = '';
$nombre_tipo_trabajo_precargada = '';
$nombre_materia_precargada = ''; // Nueva variable para precargar el nombre de la materia

if ($id_servicio_a_editar > 0) {

    $sql_fetch_servicio = "SELECT * FROM servicios WHERE id_servicio = ? AND id_usuario = ?";
    $stmt_fetch = $mysqli->prepare($sql_fetch_servicio);

    if ($stmt_fetch) {
        $stmt_fetch->bind_param("ii", $id_servicio_a_editar, $id_usuario_logueado);
        $stmt_fetch->execute();
        $result_fetch = $stmt_fetch->get_result();

        if ($result_fetch->num_rows === 1) {
            $servicio_data = $result_fetch->fetch_assoc();


            $sql_carrera = "SELECT nombre_carrera FROM carreras WHERE id_carrera = ?";
            $stmt_carrera = $mysqli->prepare($sql_carrera);
            if ($stmt_carrera) {
                $stmt_carrera->bind_param("i", $servicio_data['id_carrera']);
                $stmt_carrera->execute();
                $result_carrera = $stmt_carrera->get_result();
                if ($result_carrera->num_rows === 1) {
                    $nombre_carrera_precargada = $result_carrera->fetch_assoc()['nombre_carrera'];
                }
                $stmt_carrera->close();
            }


            $sql_tipo_trabajo = "SELECT nombre FROM tipos_trabajos WHERE id_tipo_trabajo = ?";
            $stmt_tipo_trabajo = $mysqli->prepare($sql_tipo_trabajo);
            if ($stmt_tipo_trabajo) {
                $stmt_tipo_trabajo->bind_param("i", $servicio_data['id_tipo_trabajo']);
                $stmt_tipo_trabajo->execute();
                $result_tipo_trabajo = $stmt_tipo_trabajo->get_result();
                if ($result_tipo_trabajo->num_rows === 1) {
                    $nombre_tipo_trabajo_precargada = $result_tipo_trabajo->fetch_assoc()['nombre'];
                }
                $stmt_tipo_trabajo->close();
            }


            $sql_materia = "SELECT nombre FROM materias WHERE id_materia = ?";
            $stmt_materia = $mysqli->prepare($sql_materia);
            if ($stmt_materia) {
                $stmt_materia->bind_param("i", $servicio_data['id_materia']);
                $stmt_materia->execute();
                $result_materia = $stmt_materia->get_result();
                if ($result_materia->num_rows === 1) {
                    $nombre_materia_precargada = $result_materia->fetch_assoc()['nombre'];
                }
                $stmt_materia->close();
            }


        } else {

            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire('Error', 'servicio no encontrado o acceso denegado.', 'error').then(() => {
                        window.location.href = 'index.php';
                    });
                });
            </script>";
            exit; 
        }
        $stmt_fetch->close();
    } else {

        error_log("Error al preparar la consulta de carga: " . $mysqli->error);

    }
} else {

    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire('Error', 'ID de servicio inválido.', 'error').then(() => {
                window.location.href = 'index.php';
            });
        });
    </script>";
    exit;
}



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    

    $id_servicio_post = (int)($_POST['id_servicio'] ?? 0);
    $titulo = $_POST['titulo'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $precio = (float)($_POST['precio'] ?? 0); 
    $tipo_trabajo_id = (int)($_POST['tipo_trabajo_id'] ?? 0);
    $carrera_id = (int)($_POST['carrera_id'] ?? 0);
    $materia_id = (int)($_POST['materia_id'] ?? 0);


    if (empty($titulo) || $precio <= 0 || $tipo_trabajo_id == 0 || $carrera_id == 0 || $materia_id == 0 ) {
        
        $error_msg = "Faltan campos obligatorios o los valores son inválidos.";

        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire('Error', '{$error_msg}', 'error');
            });
        </script>";
        
    } else if ($id_servicio_post != $id_servicio_a_editar) {

        $error_msg = "Error de seguridad: ID de servicio no coincide.";

        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire('Error', '{$error_msg}', 'error');
            });
        </script>";
    } else {
        

        $sql_update_servicio = "UPDATE servicios SET 
            titulo = ?, descripcion = ?, precio = ?, id_tipo_trabajo = ?, 
            id_carrera = ?, id_materia = ? 
            WHERE id_servicio = ? AND id_usuario = ?";

        $stmt = $mysqli->prepare($sql_update_servicio);

        if ($stmt === false) {
            $error_msg = "Error al preparar la consulta de actualización: " . $mysqli->error;
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire('Error', '{$error_msg}', 'error');
                });
            </script>";
        } else {
            

            $stmt->bind_param("ssdiiiii", 
                $titulo, $descripcion, $precio,
                $tipo_trabajo_id, $carrera_id, $materia_id,
                $id_servicio_post, $id_usuario_logueado 
            );

            if ($stmt->execute()) {
                $stmt->close();
                
                echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                title: '¡Éxito!',
                                text: 'servicio actualizado correctamente.',
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
    <title>Editar servicio</title>
</head>
<body>
    <?php include __DIR__ . '/app/includes/Navbar.php'; ?>
    <div class="cont-crear">
        <div class="div">
            <h3 class="Titulo titu_crear">EDITAR SERVICIO</h3>
        </div>
        
        <form action="edit_servicio.php" method="POST" enctype="multipart/form-data">
            
            <input type="hidden" name="id_servicio" value="<?php echo $id_servicio_a_editar; ?>">

            <div class="row">
                <div class="col-lg-12">
                    <label for="titu_req" class="lb_modal">TÍTULO</label>
                    <br>
                    <input 
                        type="text" 
                        id="titulo" 
                        name="titulo" 
                        class="inputs" 
                        required 
                        value="<?php echo htmlspecialchars($servicio_data['titulo'] ?? ''); ?>"
                    >
                    <br>
                </div>
                <div class="col-lg-6 col-md-12 espacio">
                    
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
                        // Consulta SEGURA para obtener las carreras
                        $sql = "SELECT id_carrera, nombre_carrera FROM carreras ORDER BY nombre_carrera";
                        $result = $mysqli->query($sql);

                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                $selected = ($servicio_data['id_carrera'] ?? 0) == $row["id_carrera"] ? 'selected' : '';
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
                                $selected = ($servicio_data['id_tipo_trabajo'] ?? 0) == $row["id_tipo_trabajo"] ? 'selected' : '';
                                echo '<option value="' . $row["id_tipo_trabajo"] . '" data-nombre="' . htmlspecialchars($row["nombre"]) . '" ' . $selected . '>' . htmlspecialchars($row["nombre"]) . '</option>';
                            }
                        } else {
                            echo '<option value="" class="text-dropdown">(No hay tipos de trabajo disponibles)</option>';
                        }
                        ?>
                    </select>
                </div>
                
                <div class="col-lg-6 col-md-12 espacio">
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

                        $sql = "SELECT id_materia, nombre FROM materias ORDER BY nombre";
                        $result = $mysqli->query($sql);

                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                $selected = ($servicio_data['id_materia'] ?? 0) == $row["id_materia"] ? 'selected' : '';
                                echo '<option value="' . $row["id_materia"] . '" data-nombre="' . htmlspecialchars($row["nombre_materia"]) . '" ' . $selected . '>' . htmlspecialchars($row["nombre_materia"]) . '</option>';
                            }
                        } else {
                            echo '<option value="" class="text-dropdown">(No hay materias disponibles)</option>';
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
                        value="<?php echo htmlspecialchars($servicio_data['precio'] ?? '0.00'); ?>"
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
                    ><?php echo htmlspecialchars($servicio_data['descripcion'] ?? ''); ?></textarea> 
                    
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
    
    // Asumo que el formulario es el único en la página, pero lo apuntamos por su acción (POST)
    const formEditServicio = document.querySelector('form[action="edit_servicio.php"]'); 

    // Función auxiliar para limpiar números de un string
    const cleanNumbers = (value) => value.replace(/[0-9]/g, '');

    // --- MANEJADOR PRINCIPAL DEL FORMULARIO ---
    if (formEditServicio) {
        formEditServicio.addEventListener('submit', function(e) {
            e.preventDefault(); 

            const form = this;
            const errors = [];
            
            // --- 1. Obtener Valores e Inputs ---
            
            const tituloInput = form.querySelector('[name="titulo"]');
            const titulo = tituloInput ? tituloInput.value.trim() : '';

            const precioInput = form.querySelector('[name="precio"]');
            // Obtener el valor directamente del input para validación
            const precio = precioInput ? parseFloat(precioInput.value) : NaN; 
            
            const descripcionInput = document.getElementById('descripcion_input');
            const descripcion = descripcionInput ? descripcionInput.value.trim() : '';

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
            // Usamos isNaN y la comparación con 0, ya que PHP también exige precio > 0.
            if (isNaN(precio) || precio <= 0) {
                errors.push('El **PRECIO** debe ser un número válido y mayor que cero.');
                precioInput && precioInput.classList.add('is-invalid');
            }


            // d. Dropdown Fields (Validación de selección basada en ID oculto)
            
            // Carrera
            if (!carrera_id || carrera_id == 0) {
                errors.push('Debe seleccionar una **CARRERA** válida de la lista.');
                carreraVisualInput && carreraVisualInput.classList.add('is-invalid');
            } else if (/\d/.test(carreraVisualInput.value)) { 
                 // Validamos que no se hayan escrito números en el input visual
                 errors.push('El campo **CARRERA** no puede contener números.');
                 carreraVisualInput && carreraVisualInput.classList.add('is-invalid');
            }

            // Tipo de Trabajo
            if (!tipo_trabajo_id || tipo_trabajo_id == 0) {
                errors.push('Debe seleccionar un **TIPO DE TRABAJO** válido de la lista.');
                tipoTrabajoVisualInput && tipoTrabajoVisualInput.classList.add('is-invalid');
            } else if (/\d/.test(tipoTrabajoVisualInput.value)) { 
                 errors.push('El campo **TIPO DE TRABAJO** no puede contener números.');
                 tipoTrabajoVisualInput && tipoTrabajoVisualInput.classList.add('is-invalid');
            }

            // Materia
            if (!materia_id || materia_id == 0) {
                errors.push('Debe seleccionar una **MATERIA** válida de la lista.');
                materiaVisualInput && materiaVisualInput.classList.add('is-invalid');
            } else if (/\d/.test(materiaVisualInput.value)) { 
                 errors.push('El campo **MATERIA** no puede contener números.');
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
            // Si llega aquí, significa que las validaciones de Front-end pasaron.
            console.log("Validación de Edición exitosa. Procediendo con el envío al servidor.");
            form.submit(); // Envía el formulario PHP para la actualización
        });
    }

    // --- MEJORA UX: RESTRICCIÓN DE ENTRADA EN TIEMPO REAL (Inputs de solo texto) ---
    // Esto es muy importante ya que estás usando inputs de texto para simular dropdowns.
    const textOnlyInputs = ['carrera_visual_input', 'tipo_trabajo_visual_input', 'materia_visual_input'];

    textOnlyInputs.forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            // Evitar que el usuario escriba números
            input.addEventListener('keypress', function(e) {
                const charCode = (e.which) ? e.which : e.keyCode;
                // Bloquea números (48 a 57)
                if (charCode >= 48 && charCode <= 57) { 
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
    });

    // Limpia la clase de error del input visual cuando se selecciona un valor en el select oculto
    const dropdownSelects = ['carrera_id', 'tipo_trabajo_id', 'materia_id'];
    dropdownSelects.forEach(id => {
        const select = document.getElementById(id);
        if (select) {
            select.addEventListener('change', function() {
                // Mapear el ID del select oculto ('carrera_id') al input visual ('carrera_visual_input')
                const visualId = this.id.replace('_id', '_visual_input');
                const visualInput = document.getElementById(visualId);
                if (visualInput) {
                    visualInput.classList.remove('is-invalid');
                }
            });
        }
    });

    // --- VALIDACIÓN Y PREVIEW DE ARCHIVOS ---
    const inputFile = document.getElementById('input-archivos-servicio');
    const previewContainer = document.getElementById('preview-archivos');
    const maxFiles = 3;

    if (inputFile && previewContainer) {
        inputFile.addEventListener('change', function() {
            let files = Array.from(this.files);
            
            // Eliminar archivos excedentes si el usuario seleccionó más de 3
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

            files.forEach((file, index) => {
                const item = document.createElement('div');
                item.className = 'preview-item d-flex align-items-center me-3 mb-2';
                item.style.border = '1px solid #ddd';
                item.style.padding = '5px';
                item.style.borderRadius = '3px';
                item.style.backgroundColor = '#fff';

                const icon = document.createElement('i');
                // Asumo que tienes Font Awesome o Bootstrap Icons para los íconos
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