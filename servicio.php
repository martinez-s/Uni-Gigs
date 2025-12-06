<?php

include('conect.php'); 

// 🔑 CORRECCIÓN: Incluir la librería de SweetAlert2 aquí, antes de cualquier posible llamada a Swal.fire
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
                
                // Éxito: Cerrar la sentencia y mostrar SweetAlert
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
                // Error en la ejecución de la consulta
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
  <title>Servicio</title>
</head>
<body>
  <?php include __DIR__ . '/app/includes/Navbar.php'; ?>
<div>
  <div class="cont-crear">
    <div class="div">
      <h3 class="Titulo titu_crear">PUBLICAR UN SERVICIO</h3>
    </div>
    <form action="servicio.php" method="POST" enctype="multipart/form-data">
    <div class="row">
      <div class="col-lg-12">
            <label for="titulo" class="lb_modal_des">TÍTULO</label>
            <br>
            <input type="text" id="titulo" name="titulo" class="inputs-publi">            
      </div>
          <div class="col-lg-6 col-md-12 espacio">
              <label for="carrera_id" class="lb_modal">CARRERA</label>
              <br>
              <select class="form-select dropdown_front" id="carrera_id" name="carrera_id">
                  
                  <option value="" class="text-dropdown">Seleccione la carrera</option> 
                  
                  <?php

                  $sql = "SELECT id_carrera, nombre_carrera FROM carreras ORDER BY nombre_carrera";
                  $result = $mysqli->query($sql);
            
                  if ($result->num_rows > 0) {
                      while($row = $result->fetch_assoc()) {
                          
                          // Genera el <option> usando los datos de la BDD
                          // El 'value' será el ID y el texto visible será el nombre.
                          echo '<option value="' . $row["id_carrera"] . '" class="text-dropdown">' . $row["nombre_carrera"] . '</option>';
                      }
                  } else {
                      // Opción de reserva si no hay datos
                      echo '<option value="" class="text-dropdown">(No hay carreras disponibles)</option>';
                  }
                  ?>
                  
              </select>
              <br>
              <label for="tipo_trabajo" class="lb_modal">TIPO DE TRABAJO</label>
              <select class="form-select dropdown_front" id="tipo_trabajo_id" name="tipo_trabajo_id" required>
                  
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
              <label for="tipo_materia" class="lb_modal">MATERIA</label>
              <select class="form-select dropdown_front" id="materia_id" name="materia_id" required>
                  
                  <option value="" class="text-dropdown">Seleccione la materia</option> 
                  
                  <?php

                  $sql = "SELECT id_materia, nombre FROM materias ORDER BY nombre";
                  $result = $mysqli->query($sql);
            
                  if ($result->num_rows > 0) {
                      while($row = $result->fetch_assoc()) {
                          
                          // Genera el <option> usando los datos de la BDD
                          // El 'value' será el ID y el texto visible será el nombre.
                          echo '<option value="' . $row["id_materia"] . '" class="text-dropdown">' . $row["nombre"] . '</option>';
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
              <input type="number" step="0.01" id="precio" name="precio" class="inputs" required>               
          </div>
          <div class="col-lg-12 espacio">
            <label for="descripcion" class="lb_modal_des">DESCRIPCIÓN</label>
            <br>
            <textarea id="descripcion_input" name="descripcion" class="inputs" rows="4" required></textarea>            
            <input type="file" id="input-archivos-request" name="archivos_servicio[]" multiple hidden> 
            
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
              <button type="submit" class="btn_crear_req btn_siguiente">CREAR</button>
            </div>
      </div>
  </div>
  </form>
</div>

  <script src="login_regis.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>