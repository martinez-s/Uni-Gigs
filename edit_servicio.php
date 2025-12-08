<?php
session_start();
include('conect.php'); 

echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>'; 

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../../login.php"); 
    exit();
}

$id_usuario_logueado = $_SESSION['id_usuario'];

$id_servicio_a_editar = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_servicio'])) {

    $id_servicio_a_editar = (int)$_POST['id_servicio'];
} elseif (isset($_GET['id']) && is_numeric($_GET['id'])) {

    $id_servicio_a_editar = (int)$_GET['id'];
} else {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire('Error', 'No se especificó un Servicio para editar.', 'error').then(() => {
                window.location.href = '../../servicio.php';
            });
        });
    </script>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

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
        
    } else {
        $sql_update_servicio = "UPDATE servicios SET 
            titulo = ?, descripcion = ?, precio = ?, id_tipo_trabajo = ?, 
            id_carrera = ?, id_materia = ? 
            WHERE id_servicio = ? AND id_usuario = ?";

        $stmt = $mysqli->prepare($sql_update_servicio);

        if ($stmt) {
            // Bind params: s=string, d=double, i=int
            // titulo(s), desc(s), precio(d), tipo(i), carrera(i), materia(i), id_serv(i), id_user(i)
            $stmt->bind_param("ssdiiiii", 
                $titulo, $descripcion, $precio,
                $tipo_trabajo_id, $carrera_id, $materia_id,
                $id_servicio_a_editar, $id_usuario_logueado 
            );

            if ($stmt->execute()) {
                echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                title: '¡Éxito!',
                                text: 'Servicio actualizado correctamente.',
                                icon: 'success',
                                confirmButtonText: 'Ok'
                            }).then((result) => {
                                window.location.href = 'public/pages/perfil.php'; 
                            });
                        });
                    </script>";
            } else {
                $error_msg = "Error al actualizar: " . $stmt->error;
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire('Error', '{$error_msg}', 'error');
                    });
                </script>";
            }
            $stmt->close();
        } else {
            error_log("Error prepare update: " . $mysqli->error);
        }
    }
}

// ---------------------------------------------------------
// BLOQUE 2: OBTENER DATOS ACTUALES
// ---------------------------------------------------------
$servicio_data = null; 
$nombre_carrera_precargada = '';
$nombre_tipo_trabajo_precargada = '';
$nombre_materia_precargada = ''; 

$sql_fetch_servicio = "SELECT * FROM servicios WHERE id_servicio = ? AND id_usuario = ?";
$stmt_fetch = $mysqli->prepare($sql_fetch_servicio);

if ($stmt_fetch) {
    $stmt_fetch->bind_param("ii", $id_servicio_a_editar, $id_usuario_logueado);
    $stmt_fetch->execute();
    $result_fetch = $stmt_fetch->get_result();

    if ($result_fetch->num_rows === 1) {
        $servicio_data = $result_fetch->fetch_assoc();

        // Obtener nombres auxiliares (igual que en request)
        if ($servicio_data['id_carrera']) {
            $stmt_c = $mysqli->prepare("SELECT nombre_carrera FROM carreras WHERE id_carrera = ?");
            $stmt_c->bind_param("i", $servicio_data['id_carrera']);
            $stmt_c->execute();
            if ($row_c = $stmt_c->get_result()->fetch_assoc()) { $nombre_carrera_precargada = $row_c['nombre_carrera']; }
            $stmt_c->close();
        }
        if ($servicio_data['id_tipo_trabajo']) {
            $stmt_t = $mysqli->prepare("SELECT nombre FROM tipos_trabajos WHERE id_tipo_trabajo = ?");
            $stmt_t->bind_param("i", $servicio_data['id_tipo_trabajo']);
            $stmt_t->execute();
            if ($row_t = $stmt_t->get_result()->fetch_assoc()) { $nombre_tipo_trabajo_precargada = $row_t['nombre']; }
            $stmt_t->close();
        }
        if ($servicio_data['id_materia']) {
            $stmt_m = $mysqli->prepare("SELECT nombre FROM materias WHERE id_materia = ?");
            $stmt_m->bind_param("i", $servicio_data['id_materia']);
            $stmt_m->execute();
            if ($row_m = $stmt_m->get_result()->fetch_assoc()) { $nombre_materia_precargada = $row_m['nombre']; }
            $stmt_m->close();
        }

    } else {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire('Acceso Denegado', 'No puedes editar este servicio.', 'error').then(() => {
                    window.location.href = 'public/pages/perfil.php';
                });
            });
        </script>";
        if ($_SERVER["REQUEST_METHOD"] != "POST") exit; 
    }
    $stmt_fetch->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="public/styles/styles.css">
    <title>Editar Servicio</title>
</head>
<body>
    <?php include __DIR__ . '/app/includes/Navbar.php'; ?>
    
    <div class="cont-crear">
        <div class="div">
            <h3 class="Titulo titu_crear">EDITAR SERVICIO</h3>
        </div>
        
        <form id="formEditarServicio" action="" method="POST" enctype="multipart/form-data">
            
            <input type="hidden" name="id_servicio" value="<?php echo $id_servicio_a_editar; ?>">

            <div class="row">
                <div class="col-lg-12">
                    <label for="titulo" class="lb_modal">TÍTULO</label>
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
                        <input type="text" id="carrera_visual_input" class="form-control dropdown_front" placeholder="Seleccione..." autocomplete="off" value="<?php echo htmlspecialchars($nombre_carrera_precargada); ?>">
                        <ul id="carrera_custom_list" class="list-group" style="display: none;"></ul>
                    </div>
                    <select id="carrera_id" name="carrera_id" required style="display: none;">
                        <option value="" disabled>Seleccione...</option> 
                        <?php
                        $sql = "SELECT id_carrera, nombre_carrera FROM carreras ORDER BY nombre_carrera";
                        $res = $mysqli->query($sql);
                        while($row = $res->fetch_assoc()) {
                            $sel = ($servicio_data['id_carrera'] ?? 0) == $row["id_carrera"] ? 'selected' : '';
                            echo '<option value="' . $row["id_carrera"] . '" data-nombre="' . htmlspecialchars($row["nombre_carrera"]) . '" ' . $sel . '>' . htmlspecialchars($row["nombre_carrera"]) . '</option>';
                        }
                        ?>
                    </select>
                    <br>
                    
                    <label for="tipo_trabajo_visual_input" class="lb_modal">TIPO DE TRABAJO</label>
                    <div class="custom-select-container">
                        <input type="text" id="tipo_trabajo_visual_input" class="form-control dropdown_front" placeholder="Seleccione..." autocomplete="off" value="<?php echo htmlspecialchars($nombre_tipo_trabajo_precargada); ?>">
                        <ul id="tipo_trabajo_custom_list" class="list-group" style="display: none;"></ul>
                    </div>
                    <select id="tipo_trabajo_id" name="tipo_trabajo_id" required style="display: none;">
                        <option value="" disabled>Seleccione...</option> 
                        <?php
                        $sql = "SELECT id_tipo_trabajo, nombre FROM tipos_trabajos ORDER BY nombre";
                        $res = $mysqli->query($sql);
                        while($row = $res->fetch_assoc()) {
                            $sel = ($servicio_data['id_tipo_trabajo'] ?? 0) == $row["id_tipo_trabajo"] ? 'selected' : '';
                            echo '<option value="' . $row["id_tipo_trabajo"] . '" data-nombre="' . htmlspecialchars($row["nombre"]) . '" ' . $sel . '>' . htmlspecialchars($row["nombre"]) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                
                <div class="col-lg-6 col-md-12 espacio">
                    <label for="materia_visual_input" class="lb_modal">MATERIA</label>
                    <br>
                    <div class="custom-select-container">
                        <input type="text" id="materia_visual_input" class="form-control dropdown_front" placeholder="Seleccione..." autocomplete="off" value="<?php echo htmlspecialchars($nombre_materia_precargada); ?>">
                        <ul id="materia_custom_list" class="list-group" style="display: none; position: absolute; width: 100%; z-index: 1000; max-height: 200px; overflow-y: auto; border-top: none;"></ul>
                    </div>
                    <select id="materia_id" name="materia_id" required style="display: none;">
                        <option value="" disabled>Seleccione...</option> 
                        <?php
                            if (!empty($servicio_data['id_materia']) && !empty($nombre_materia_precargada)) {
                                echo '<option value="' . $servicio_data['id_materia'] . '" data-nombre="' . htmlspecialchars($nombre_materia_precargada) . '" selected>' . htmlspecialchars($nombre_materia_precargada) . '</option>';
                            }
                        ?>
                    </select>
                    <br>
                    
                    <label for="precio" class="lb_modal">PRECIO</label>
                    <br>
                    <input type="number" step="0.01" min="0.00" id="precio" name="precio" class="inputs" required value="<?php echo htmlspecialchars($servicio_data['precio'] ?? '0.00'); ?>">        
                </div>
                
                <div class="col-lg-12 espacio">
                    <label for="descripcion_input" class="lb_modal_des">DESCRIPCIÓN</label>
                    <br>
                    <textarea id="descripcion_input" name="descripcion" class="inputs" rows="4" required><?php echo htmlspecialchars($servicio_data['descripcion'] ?? ''); ?></textarea> 
                    
                    <input type="file" id="input-archivos-servicio" name="archivos-servicio[]" multiple hidden> 
                    <label for="input-archivos-servicio" class="btn btn-secondary" style="margin-bottom: 15px;">
                        Subir Archivos (Max 3)
                    </label>
                    <div id="preview-archivos" style="border: 1px solid #ccc; padding: 15px; min-height: 100px; border-radius: 5px; background-color: #f9f9f9; display: flex; gap: 15px; flex-wrap: wrap;">
                        <p id="mensaje-vacio" style="color: #888;">No hay archivos seleccionados.</p>
                    </div> 
                </div>
                
                <div class="d-flex justify-content-center">
                    <button type="submit" class="btn_crear_req btn_siguiente">ACTUALIZAR</button>
                </div>
            </div>
        </form>
    </div>

    <script src="login_regis.js"></script>
    <script src="dropdown.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // Obtenemos el formulario por ID
        const form = document.getElementById('formEditarServicio'); 

        // Auxiliar para limpiar números
        const cleanNumbers = (value) => value.replace(/[0-9]/g, '');

        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault(); // Detener envío

                const errors = [];
                
                // Obtener Valores
                const titulo = form.querySelector('[name="titulo"]').value.trim();
                const precio = parseFloat(form.querySelector('[name="precio"]').value);
                const descripcion = document.getElementById('descripcion_input').value.trim();
                
                // IDs Ocultos
                const carrera_id = form.querySelector('#carrera_id').value;
                const tipo_trabajo_id = form.querySelector('#tipo_trabajo_id').value;
                const materia_id = form.querySelector('#materia_id').value;
                
                // Validaciones
                if (!titulo || titulo.length < 5) errors.push('Título muy corto.');
                if (!descripcion || descripcion.length < 20) errors.push('Descripción muy corta.');
                if (isNaN(precio) || precio <= 0) errors.push('Precio inválido.');
                
                if (!carrera_id) errors.push('Seleccione una Carrera.');
                if (!tipo_trabajo_id) errors.push('Seleccione un Tipo de Trabajo.');
                if (!materia_id) errors.push('Seleccione una Materia.');

                // Mostrar errores o enviar
                if (errors.length > 0) {
                    Swal.fire({ title: 'Error', html: errors.join('<br>'), icon: 'warning' });
                } else {
                    form.submit(); // Enviar si todo está bien
                }
            });
        }

        const inputFile = document.getElementById('input-archivos-servicio');
        const previewContainer = document.getElementById('preview-archivos');
        
        if (inputFile && previewContainer) {
            inputFile.addEventListener('change', function() {
                previewContainer.innerHTML = '';
                const files = Array.from(this.files);
                if (files.length === 0) {
                    previewContainer.innerHTML = '<p style="color: #888;">No hay archivos seleccionados.</p>';
                    return;
                }
                files.forEach(file => {
                    const item = document.createElement('div');
                    item.className = 'd-flex align-items-center me-3 mb-2 p-2 border rounded bg-white';
                    item.innerHTML = `<span>${file.name}</span>`; // Simplificado para brevedad
                    previewContainer.appendChild(item);
                });
            });
        }
    });
    </script>
</body>
</html>