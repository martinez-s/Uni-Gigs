
<?php

include('conect.php'); 

echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>'; 

$id_usuario_logueado = 2; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Recibir y tipificar datos del formulario
    $titulo = $_POST['titulo'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $precio = (float)($_POST['precio'] ?? 0); 
    
    $tipo_trabajo_id = (int)($_POST['tipo_trabajo_id'] ?? 0);
    $carrera_id = (int)($_POST['carrera_id'] ?? 0);
    $materia_id = (int)($_POST['materia_id'] ?? 0);
    
    // Validar datos mínimos
    if (empty($titulo) || $precio <= 0 || $tipo_trabajo_id == 0 || $carrera_id == 0 || $materia_id == 0) {
        
        $error_msg = "Faltan campos obligatorios o los valores son inválidos.";
        echo "<script>Swal.fire('Error', '{$error_msg}', 'error');</script>";
        
    } else {
        
        $fecha_creacion = date("Y-m-d");

        // 2. Sentencia Preparada para la inserción en `servicios`
        $sql_insert_servicio = "INSERT INTO servicios (
            titulo, descripcion, precio, fecha_creacion, id_tipo_trabajo, id_carrera, id_materia, id_usuario
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?
        )";

        $stmt = $mysqli->prepare($sql_insert_servicio);

        if ($stmt === false) {
            $error_msg = "Error al preparar la consulta: " . $mysqli->error;
            echo "<script>Swal.fire('Error', '{$error_msg}', 'error');</script>";
        } else {
            
            // 3. Vincular los parámetros
            $stmt->bind_param("ssdsiiii", 
                $titulo, $descripcion, $precio, $fecha_creacion, 
                $tipo_trabajo_id, $carrera_id, $materia_id, $id_usuario_logueado
            );

            if ($stmt->execute()) {

                $stmt->close();
                
                echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                title: '¡Éxito!',
                                text: 'Servicio publicado correctamente.',
                                icon: 'success',
                                confirmButtonText: 'Ok'
                            }).then((result) => {
                                window.location.href = 'index.php'; // Cambia a la página deseada
                            });
                        });
                    </script>";

            } else {
                $error_msg = "Error al publicar el servicio: " . $stmt->error;
                echo "<script>Swal.fire('Error', '{$error_msg}', 'error');</script>";
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
  <title>Request</title>
</head>
<body>
  <?php include __DIR__ . '/app/includes/Navbar.php'; ?>
<div>
  <div class="cont-crear">
    <div class="div">
      <h3 class="Titulo titu_crear">CREAR UN REQUEST</h3>
    </div>
      <div class="row">
          <div class="col-lg-6 col-md-12 espacio">
              <label for="titu_req" class="lb_modal">TÍTULO</label>
              <br>
              <input type="text" id="titureq" name="titulo" class="inputs">
              <br>
              <label for="nombre_carrera" class="lb_modal">CARRERA</label>
              <br>
              <select class="form-select dropdown_front" id="tipo_trabajo" name="tipo_trabajo">
                  
                  <option value="" class="text-dropdown">Seleccione la carrera</option> 
                  
                  <?php

                  $sql = "SELECT id_tipo_trabajo, nombre FROM tipos_trabajos ORDER BY nombre";
                  $result = $mysqli->query($sql);
            
                  if ($result->num_rows > 0) {
                      while($row = $result->fetch_assoc()) {
                          
                          // Genera el <option> usando los datos de la BDD
                          // El 'value' será el ID y el texto visible será el nombre.
                          echo '<option value="' . $row["id_tipo_trabajo"] . '" class="text-dropdown">' . $row["nombre"] . '</option>';
                      }
                  } else {
                      // Opción de reserva si no hay datos
                      echo '<option value="" class="text-dropdown">(No hay carreras disponibles)</option>';
                  }
                  ?>
                  
              </select>
              <br>
              <label for="tipo_trabajo" class="lb_modal">TIPO DE TRABAJO</label>
              <select class="form-select dropdown_front" id="tipo_trabajo" name="tipo_trabajo">
                  
                  <option value="" class="text-dropdown">Seleccione el Tipo de Trabajo</option> 
                  
                  <?php

                  $sql = "SELECT id_tipo_trabajo, nombre FROM tipos_trabajos ORDER BY nombre";
                  $result = $mysqli->query($sql);
            
                  if ($result->num_rows > 0) {
                      while($row = $result->fetch_assoc()) {
                          
                          // Genera el <option> usando los datos de la BDD
                          // El 'value' será el ID y el texto visible será el nombre.
                          echo '<option value="' . $row["id_tipo_trabajo"] . '" class="text-dropdown">' . $row["nombre"] . '</option>';
                      }
                  } else {
                      // Opción de reserva si no hay datos
                      echo '<option value="" class="text-dropdown">(No hay tipos de trabajo disponibles)</option>';
                  }
                  ?>
                  
              </select>
          </div>
          <div class="col-lg-6 col-md-12 espacio">
              <label for="fecha-limit-req" class="lb_modal">FECHA LÍMITE</label>
              <br>
              <input type="date" id="fecha-limit-req" name="fecha-limit-req" class="inputs">   
              <br>
              <label for="tipo_materia" class="lb_modal">MATERIA</label>
              <select class="form-select dropdown_front" id="tipo_materia" name="tipo_materia" required>
                  
                  <option value="" class="text-dropdown">Seleccione la materia</option> 
                  
                  <?php

                  $sql = "SELECT id_tipo_trabajo, nombre FROM tipos_trabajos ORDER BY nombre";
                  $result = $mysqli->query($sql);
            
                  if ($result->num_rows > 0) {
                      while($row = $result->fetch_assoc()) {
                          
                          // Genera el <option> usando los datos de la BDD
                          // El 'value' será el ID y el texto visible será el nombre.
                          echo '<option value="' . $row["id_estudiante"] . '" class="text-dropdown">' . $row["nombre"] . '</option>';
                      }
                  } else {
                      // Opción de reserva si no hay datos
                      echo '<option value="" class="text-dropdown">(No hay materias disponibles)</option>';
                  }
                  ?>
                  
              </select> 
              <br>
              <label for="precio" class="lb_modal">PRECIO</label>
              <br>
              <input type="text" id="precio" name="precio" class="inputs">               
          </div>
          <div class="col-lg-12 espacio">
            <label for="descripcion" class="lb_modal_des">DESCRIPCIÓN</label>
            <br>
            <input type="text" id="descripcion" name="descripcion" class="inputs">            
            <input type="file" id="input-archivos-request" name="archivos-request[]" multiple hidden> 
            
            <label for="input-archivos-request" class="btn btn-secondary" style="margin-bottom: 15px;">
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
                flex-wrap: wrap; /* Permite que las miniaturas se envuelvan si hay muchas */
            ">
                <p id="mensaje-vacio" style="color: #888;">No hay archivos seleccionados.</p>
            </div> 
          </div>
            <div class="d-flex justify-content-center">
              <button type="button" class="btn_crear_req btn_siguiente" data-bs-toggle="modal" data-bs-target="#exampleModal">CREAR</button>
            </div>
      </div>
  </div>
  <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="exampleModalLabel">Modal title</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <ul class="nav nav-tabs" id="miTab" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="inicio-tab" data-bs-toggle="tab" data-bs-target="#inicio" type="button" role="tab" aria-controls="inicio" aria-selected="true">Inicio</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="perfil-tab" data-bs-toggle="tab" data-bs-target="#perfil" type="button" role="tab" aria-controls="perfil" aria-selected="false">Perfil</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="config-tab" data-bs-toggle="tab" data-bs-target="#config" type="button" role="tab" aria-controls="config" aria-selected="false">Configuración</button>
              </li>
            <ul>

            <div class="tab-content" id="miContenidoTab">
              <div class="tab-pane fade show active" id="inicio" role="tabpanel" aria-labelledby="inicio-tab">
                <h2>Contenido de Inicio</h2>
                <p>Aquí va toda la información de la pestaña de inicio.</p>
              </div>
              <div class="tab-pane fade" id="perfil" role="tabpanel" aria-labelledby="perfil-tab">
                <h2>Contenido de Perfil</h2>
                <p>Aquí va la información del perfil del usuario.</p>
              </div>
              <div class="tab-pane fade" id="config" role="tabpanel" aria-labelledby="config-tab">
                <h2>Contenido de Configuración</h2>
                <p>Opciones de configuración.</p>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary">Save changes</button>
          </div>
        </div>
      </div>
  </div>
</div>

  <script src="login_regis.js"></script>
  <script src="dropdown.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>