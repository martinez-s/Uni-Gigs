<?php
session_start();
include('conect.php'); 

echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>'; 

if (!isset($_SESSION['id_usuario'])) {

    header("Location: ../../login.php"); 
    exit();
}

$id_usuario_logueado = $_SESSION['id_usuario'];

$id_request_a_editar = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_requests'])) {

    $id_request_a_editar = (int)$_POST['id_requests'];
} elseif (isset($_GET['id']) && is_numeric($_GET['id'])) {

    $id_request_a_editar = (int)$_GET['id'];
} else {

    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire('Error', 'No se especificó un Request para editar.', 'error').then(() => {
                window.location.href = 'index.php'; // O tu página de listado
            });
        });
    </script>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $titulo = $_POST['titulo'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $precio = (float)($_POST['precio'] ?? 0); 
    $fecha_limite = $_POST['fecha-limit-req'] ?? '';
    $tipo_trabajo_id = (int)($_POST['tipo_trabajo_id'] ?? 0);
    $carrera_id = (int)($_POST['carrera_id'] ?? 0);
    $materia_id = (int)($_POST['materia_id'] ?? 0);

    if (empty($titulo) || $precio <= 0 || $tipo_trabajo_id == 0 || $carrera_id == 0 || $materia_id == 0 || empty($fecha_limite)) {
        $error_msg = "Faltan campos obligatorios o los valores son inválidos.";
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire('Error', '{$error_msg}', 'error');
            });
        </script>";
        
    } else {
        $sql_update_request = "UPDATE requests SET 
            titulo = ?, descripcion = ?, precio = ?, fecha_limite = ?, id_tipo_trabajo = ?, 
            id_carrera = ?, id_materia = ? 
            WHERE id_requests = ? AND id_usuario = ?";

        $stmt = $mysqli->prepare($sql_update_request);

        if ($stmt) {
            $stmt->bind_param("ssdsiiiii", 
                $titulo, $descripcion, $precio, $fecha_limite, 
                $tipo_trabajo_id, $carrera_id, $materia_id,
                $id_request_a_editar, $id_usuario_logueado 
            );

            if ($stmt->execute()) {

                echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                title: '¡Éxito!',
                                text: 'Request actualizado correctamente.',
                                icon: 'success',
                                confirmButtonText: 'Ok'
                            }).then((result) => {
                                // Redirigir a la lista de mis requests o al index
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

$request_data = null; 
$nombre_carrera_precargada = '';
$nombre_tipo_trabajo_precargada = '';
$nombre_materia_precargada = ''; 

$sql_fetch_request = "SELECT * FROM requests WHERE id_requests = ? AND id_usuario = ?";
$stmt_fetch = $mysqli->prepare($sql_fetch_request);

if ($stmt_fetch) {
    $stmt_fetch->bind_param("ii", $id_request_a_editar, $id_usuario_logueado);
    $stmt_fetch->execute();
    $result_fetch = $stmt_fetch->get_result();

    if ($result_fetch->num_rows === 1) {
        $request_data = $result_fetch->fetch_assoc();

        if ($request_data['id_carrera']) {
            $stmt_c = $mysqli->prepare("SELECT nombre_carrera FROM carreras WHERE id_carrera = ?");
            $stmt_c->bind_param("i", $request_data['id_carrera']);
            $stmt_c->execute();
            $res_c = $stmt_c->get_result();
            if ($row_c = $res_c->fetch_assoc()) { $nombre_carrera_precargada = $row_c['nombre_carrera']; }
            $stmt_c->close();
        }

        if ($request_data['id_tipo_trabajo']) {
            $stmt_t = $mysqli->prepare("SELECT nombre FROM tipos_trabajos WHERE id_tipo_trabajo = ?");
            $stmt_t->bind_param("i", $request_data['id_tipo_trabajo']);
            $stmt_t->execute();
            $res_t = $stmt_t->get_result();
            if ($row_t = $res_t->fetch_assoc()) { $nombre_tipo_trabajo_precargada = $row_t['nombre']; }
            $stmt_t->close();
        }

        if ($request_data['id_materia']) {
            $stmt_m = $mysqli->prepare("SELECT nombre FROM materias WHERE id_materia = ?");
            $stmt_m->bind_param("i", $request_data['id_materia']);
            $stmt_m->execute();
            $res_m = $stmt_m->get_result();
            if ($row_m = $res_m->fetch_assoc()) { $nombre_materia_precargada = $row_m['nombre']; }
            $stmt_m->close();
        }

    } else {

        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire('Acceso Denegado', 'No puedes editar este request o no existe.', 'error').then(() => {
                    window.location.href = '../../request.php';
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
    <title>Editar Request</title>
</head>
<body>
    <?php include __DIR__ . '/app/includes/Navbar.php'; ?>
    
    <div class="cont-crear">
        <div class="div">
            <h3 class="Titulo titu_crear">EDITAR REQUEST</h3>
        </div>
        
        <form id="formEditarRequest" action="" method="POST" enctype="multipart/form-data">
            
            <input type="hidden" name="id_requests" value="<?php echo $id_request_a_editar; ?>">

            <div class="row">
                
                <div class="col-lg-6 col-md-12 espacio">
                    <label for="titulo" class="lb_modal">TÍTULO</label>
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
                        if ($result && $result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                $selected = ($request_data['id_carrera'] ?? 0) == $row["id_carrera"] ? 'selected' : '';
                                echo '<option value="' . $row["id_carrera"] . '" data-nombre="' . htmlspecialchars($row["nombre_carrera"]) . '" ' . $selected . '>' . htmlspecialchars($row["nombre_carrera"]) . '</option>';
                            }
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
                        if ($result && $result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                $selected = ($request_data['id_tipo_trabajo'] ?? 0) == $row["id_tipo_trabajo"] ? 'selected' : '';
                                echo '<option value="' . $row["id_tipo_trabajo"] . '" data-nombre="' . htmlspecialchars($row["nombre"]) . '" ' . $selected . '>' . htmlspecialchars($row["nombre"]) . '</option>';
                            }
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
                        <ul id="materia_custom_list" class="list-group" style="display: none; position: absolute; width: 100%; z-index: 1000; max-height: 200px; overflow-y: auto; border-top: none;"></ul>
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
                    <label for="descripcion_input" class="lb_modal_des">DESCRIPCIÓN</label>
                    <br>
                    <textarea 
                        id="descripcion_input" 
                        name="descripcion" 
                        class="inputs" 
                        rows="4" 
                        required
                    ><?php echo htmlspecialchars($request_data['descripcion'] ?? ''); ?></textarea> 
                    
                    <input type="file" id="input-archivos-request" name="archivos-request[]" multiple hidden> 
                    <label for="input-archivos-request" class="btn btn-secondary" style="margin-bottom: 15px;">
                        Subir Archivos (Max 3)
                    </label>
                    
                    <div id="preview-archivos" style="
                        border: 1px solid #ccc;
                        padding: 15px;
                        min-height: 100px;
                        border-radius: 5px;
                        background-color: #f9f9f9;
                        display: flex; 
                        gap: 15px; 
                        flex-wrap: wrap; 
                    ">
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

            const formEditRequest = document.querySelector('form[action="edit_request.php"]'); 
            if (formEditRequest) {

            }
        });
    </script>
</body>
</html>