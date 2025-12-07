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
                        step="0.01" 
                        min="0.00" 
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
                    
                    <input type="file" id="input-archivos-servicio" name="archivos-servicio[]" multiple hidden> 
                    <label for="input-archivos-servicio" class="btn btn-secondary" style="margin-bottom: 15px;">
                        Subir Archivos (Max 3)
                    </label>
                    
                    <div id="preview-archivos" style="
                        border: 1px solid #ccc;
                        padding: 15px;
                        min-height: 100px; /* Para que sea visible aunque no haya archivos */
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>

    </body>
</html>