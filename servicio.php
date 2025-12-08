<?php
session_start();
include('conect.php'); 
function hayMetodosDePagoRegistrados($mysqli, $id_usuario) {
    if (!$mysqli || $id_usuario == 0) return false;
    
    // Consulta directa a la tabla que registra si el usuario tiene métodos de pago.
    $sql = "
        SELECT 1 FROM usuario_metodos_pago WHERE id_usuario = ? LIMIT 1
    ";
    
    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $stmt->store_result();
        $count = $stmt->num_rows;
        $stmt->close();
        return $count > 0;
    }
    return false; 
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    header('Content-Type: application/json');
    
    $id_usuario_logueado = $_SESSION['id_usuario'] ?? 1;

    $titulo = $_POST['titulo'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $precio = (float)($_POST['precio'] ?? 0); 
    $tipo_trabajo_id = (int)($_POST['tipo_trabajo_id'] ?? 0);
    $carrera_id = (int)($_POST['carrera_id'] ?? 0);
    $materia_id = (int)($_POST['materia_id'] ?? 0);
    
    if (empty($titulo) || $precio <= 0 || $tipo_trabajo_id == 0 || $carrera_id == 0 || $materia_id == 0) {
        echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios']);
        exit;
    }

    if (!hayMetodosDePagoRegistrados($mysqli, $id_usuario_logueado)) {
        // Devolver señal al JavaScript para mostrar el modal y recargar
        echo json_encode([
            'success' => false, 
            'message' => 'Debe registrar al menos un método de pago antes de publicar un servicio.',
            'show_modal' => true // Señal clave para el JS
        ]);
        exit;
    }

    $sql_insert_servicio = "INSERT INTO servicios (titulo, descripcion, precio, fecha_creacion, id_tipo_trabajo, id_carrera, id_materia, id_usuario) VALUES (?, ?, ?, CURDATE(), ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql_insert_servicio);
    
    if ($stmt) {
        $stmt->bind_param("ssdiiii", $titulo, $descripcion, $precio, $tipo_trabajo_id, $carrera_id, $materia_id, $id_usuario_logueado);
        
        if ($stmt->execute()) {
            $id_servicio = $stmt->insert_id;
            $imagen_procesada = 0;

            if (isset($_FILES['imagen-servicio']) && $_FILES['imagen-servicio']['error'] === UPLOAD_ERR_OK) {
                $carpeta_destino = 'public/img/imgSer/';
                
                
                $tmp_file = $_FILES['imagen-servicio']['tmp_name'];
                $nombre_original = basename($_FILES['imagen-servicio']['name']);
                
                $nombre_imagen = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\.]/', '_', $nombre_original);
                $ruta_destino = $carpeta_destino . $nombre_imagen;
                
                // Validar que sea una imagen
                $valid_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                $file_extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
                
                if (in_array($file_extension, $valid_extensions)) {
                    if (move_uploaded_file($tmp_file, $ruta_destino)) {
                        // Guardar en la tabla fotos_servicios
                        $sql_foto = "INSERT INTO fotos_servicios (url_foto, id_servicio) VALUES (?, ?)";
                        $stmt_foto = $mysqli->prepare($sql_foto);
                        if ($stmt_foto) {
                            $stmt_foto->bind_param("si", $nombre_imagen, $id_servicio);
                            if ($stmt_foto->execute()) {
                                $imagen_procesada = 1;
                            }
                        }
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'Formato de imagen no válido. Use JPG, JPEG, PNG o GIF.']);
                    exit;
                }
            }
            
            echo json_encode([
                'success' => true, 
                'message' => 'Servicio creado correctamente' . ($imagen_procesada > 0 ? " con imagen adjunta" : ""),
                'imagen' => $imagen_procesada,
                'redirect' => 'public/pages/principal.php' // Redirige a la página principal
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Error preparando la consulta: ' . $mysqli->error]);
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
    
    <title>Crear Servicio</title>
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
    
<div class="cont-crear">
    <div class="div" style="padding-top: 30px;">
        <h3 class="Titulo titu_crear">PUBLICAR UN SERVICIO</h3>
    </div>
    
    <form id="formServicio" method="POST" enctype="multipart/form-data" action="servicio.php">
        <div class="row">
            <div class="col-lg-12">
                <label for="titulo" class="lb_modal">TÍTULO</label>
                <br>
                <input type="text" id="titulo" name="titulo" class="inputs-publi" required>          
            </div>
            
            <div class="col-lg-6 col-md-12 espacio">
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
                <label for="materia_visual_input" class="lb_modal">MATERIA</label>
                <br>
                <div class="custom-select-container">
                    <input type="text" id="materia_visual_input" class="form-control dropdown_front" required placeholder="Seleccione o busque una materia..." autocomplete="off">
                    <ul id="materia_custom_list" class="list-group" style="display: none; position: absolute; width: 100%; z-index: 1000; max-height: 200px; overflow-y: auto; border-top: none;"></ul>
                </div>
                <select id="materia_id" name="materia_id" required style="display: none;">
                    <option value="" selected disabled>Seleccione la materia</option> 
                </select>
                
                <br>
                <label for="precio" class="lb_modal">PRECIO</label>
                <div class="input-group mb-3">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.5" min="1.00" max="1000.00" id="precio" name="precio" class="form-control inputs" required>
                </div>
            </div>

            <div class="col-lg-12 espacio">
                <label for="descripcion_input" class="lb_modal_des">DESCRIPCIÓN</label>
                <br>
                <textarea id="descripcion_input" name="descripcion" class="inputs" rows="4" required></textarea>            
                
                <div class="mt-3">
                    <input type="file" id="input-archivos-servicio" name="imagen-servicio" accept="image/*" style="display: none;"> 
                    
                    <button type="button" class="btn btn-secondary" id="btn-trigger-image">
                        <i class="bi bi-cloud-arrow-up-fill"></i> Seleccionar Imagen (Opcional)
                    </button>
                            
                    <div id="preview-archivos" class="preview-container">
                        <p id="mensaje-vacio-imagen" class="text-muted w-100 text-center my-auto">
                            No hay imagen seleccionada.
                        </p>
                    </div> 
                </div>
            </div>

            <div class="d-flex justify-content-center mt-4">
                <button type="submit" id="btn-publicar-servicio" class="btn_siguiente">CREAR</button>
            </div>
        </div>
    </form>
</div>


<form id="formMetodosPago" action="modal_pagos.php" method="POST" enctype="multipart/form-data">
<div class="modal fade" id="modalConTabs" tabindex="-1" aria-labelledby="modalConTabsLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <ul class="nav nav-tabs" id="miTab" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-uno-tab" data-bs-toggle="tab" data-bs-target="#tab-uno" type="button" role="tab" aria-controls="tab-uno" aria-selected="true">OBLIGATORIO</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-dos-tab" data-bs-toggle="tab" data-bs-target="#tab-dos" type="button" role="tab" aria-controls="tab-dos" aria-selected="false">OPCIONAL</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-tres-tab" data-bs-toggle="tab" data-bs-target="#tab-tres" type="button" role="tab" aria-controls="tab-tres" aria-selected="false">OPCIONAL</button>
          </li>
            <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-cuatro-tab" data-bs-toggle="tab" data-bs-target="#tab-cuatro" type="button" role="tab" aria-controls="tab-cuatro" aria-selected="false">OPCIONAL</button>
          </li>
        </ul>
        <div class="tab-content" id="miTabContent">
          <div class="tab-pane fade show active" id="tab-uno" role="tabpanel" aria-labelledby="tab-uno-tab">
              <div class="container conte_pago">
                <h1 class="Titulo titu_modal">REGISTRA TU MÉTODO DE PAGO</h1>
                <h2 class="lb_subtitulo text-center">PAGO MÓVIL</h2>
                <div class="row">
                    <div class="col-lg-6 col-md-12">
                        <label for="documento_ident" class="lb_modal">DOCUMENTO DE IDENTIFICACIÓN</label><br>
                        <input type="text" name="documento_ident" class="form-control inputs">
                    </div>
                    <div class="col-lg-6 col-md-12">
                        <label for="telefono" class="lb_modal">TELÉFONO</label><br>
                        <input type="text" name="telefono" class="form-control inputs">
                    </div>
                  <div class="col-lg-12">
                      <label for="banco_visual_input" class="lb_modal">BANCO</label>
                      <br>
                  
                      <div class="custom-select-container">
                          <input 
                              type="text" 
                              id="banco_visual_input"  class="form-control dropdown_front" 
                              placeholder="Seleccione o busque el banco..."
                              autocomplete="off"
                          >
                          <ul id="banco_custom_list" class="list-group" style="display: none;">
                          </ul>
                      </div>
                  
                      <select id="banco_id" name="banco_id" style="display: none;"> 
                          <option value="" selected disabled>Seleccione EL BANCO</option> 
                          <?php
                          // Definición de la consulta SQL
                          $sql = "SELECT id, Concat(codigo, ' ', nombre) as Banco FROM bancos ORDER BY nombre";

                              $result = $mysqli->query($sql);
                          
                              if ($result && $result->num_rows > 0) {
                                  // Si hay resultados, genera las opciones
                                  while($row = $result->fetch_assoc()) {
                                      echo '<option value="' . $row["id"] . '" data-nombre="' . htmlspecialchars($row["Banco"]) . '">' . htmlspecialchars($row["Banco"]) . '</option>';
                                  }
                              } else {
                                  // Mensaje si no hay datos o la consulta falló
                                  echo '<option value="" class="text-dropdown">(No hay bancos disponibles)</option>';
                              }
                          ?>
                      </select>
                    </div>
                </div>
              </div>
          </div>
          <div class="tab-pane fade" id="tab-dos" role="tabpanel" aria-labelledby="tab-dos-tab">
              <div class="container conte_pago">
                <h1 class="Titulo titu_modal">REGISTRA TU MÉTODO DE PAGO</h1>
                <h2 class="lb_subtitulo text-center">TRANSFERENCIA BANCARIA</h2>
                <div class="row">
                    <div class="col-lg-6 col-md-12">
                        <label for="documento_identidad" class="lb_modal">DOCUMENTO DE IDENTIFICACIÓN</label><br>
                        <input type="text" name="documento_identidad" class="form-control inputs">
                    </div>
                    <div class="col-lg-6 col-md-12">
                        <label for="nro_cuenta" class="lb_modal">NUMERO DE CUENTA</label><br>
                        <input type="text" name="nro_cuenta" class="form-control inputs">
                    </div>
                  <div class="col-lg-12">
                      <label for="banco2_visual_input" class="lb_modal">BANCO</label>
                      <br>
                  
                      <div class="custom-select-container">
                          <input 
                              type="text" 
                              id="banco2_visual_input"  class="form-control dropdown_front" 
                              placeholder="Seleccione o busque el banco..."
                              autocomplete="off"
                          >
                          <ul id="banco2_custom_list" class="list-group" style="display: none;">
                          </ul>
                      </div>
                  
                      <select id="banco2_id" name="banco2_id"  style="display: none;"> 
                          <option value="" selected disabled>Seleccione EL BANCO</option> 
                          <?php
                          // Definición de la consulta SQL
                          $sql = "SELECT id, Concat(codigo, ' ', nombre) as Banco FROM bancos ORDER BY nombre";

                              $result = $mysqli->query($sql);
                          
                              if ($result && $result->num_rows > 0) {
                                  // Si hay resultados, genera las opciones
                                  while($row = $result->fetch_assoc()) {
                                      echo '<option value="' . $row["id"] . '" data-nombre="' . htmlspecialchars($row["Banco"]) . '">' . htmlspecialchars($row["Banco"]) . '</option>';
                                  }
                              } else {
                                  // Mensaje si no hay datos o la consulta falló
                                  echo '<option value="" class="text-dropdown">(No hay bancos disponibles)</option>';
                              }
                          ?>
                      </select>
                    </div>
                </div>
              </div>            
          </div>
          <div class="tab-pane fade" id="tab-tres" role="tabpanel" aria-labelledby="tab-tres-tab">
              <div class="container conte_pago">
                <h1 class="Titulo titu_modal">REGISTRA TU MÉTODO DE PAGO</h1>
                <h2 class="lb_subtitulo text-center">BINANCE</h2>
                <div class="row">
                    <div class="col-lg-12">
                        <label for="correo_binance" class="lb_modal">CORREO ASOCIADO</label><br>
                        <input type="text" name="correo_binance" class="form-control inputs">
                    </div>
                </div>
              </div>            
          </div>
          <div class="tab-pane fade" id="tab-cuatro" role="tabpanel" aria-labelledby="tab-cuatro-tab">
              <div class="container conte_pago">
                <h1 class="Titulo titu_modal">REGISTRA TU MÉTODO DE PAGO</h1>
                <h2 class="lb_subtitulo text-center">PAYPAL</h2>
                <div class="row">
                    <div class="col-lg-12">
                        <label for="correo_paypal" class="lb_modal">CORREO ASOCIADO</label><br>
                        <input type="text" name="correo_paypal" class="form-control inputs">
                    </div>
                </div>
              </div>            
          </div>
          
        </div>
        
        
      </div>
        <div class="modal-footer justify-content-center btn-regis">
            <button type="button" id="btnSubmitMetodosPago" class="btn_siguiente btn-secondary">REGISTRAR</button>
        </div>
    </div>
  </div>
</div>
</form>


    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="dropdown.js"></script>
    <script src="crearServicio.js"> </script>
  <script>

    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const showModalFlag = urlParams.get('show_modal');
        const reintentarPublicacion = urlParams.get('reintentar_publicacion');
        const pmStatus = urlParams.get('pm_status');
        const msg = urlParams.get('msg');
        
        const modalElement = document.getElementById('modalConTabs');
        const formServicio = document.getElementById('formServicio');


        if (showModalFlag === 'true' && modalElement) {
            history.replaceState(null, '', window.location.pathname); 
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const modal = new bootstrap.Modal(modalElement, {
                    backdrop: 'static', 
                    keyboard: false 
                });
                modal.show();
            }
        } 


if (pmStatus) {

    
    let title = '';
    let icon = '';
    let text = '';
    
    if (pmStatus === 'success') {
        title = '¡Métodos de Pago Registrados!';
        icon = 'success';

        text = 'Sus métodos de pago están listos. Ahora debe llenar los campos del servicio y presionar **CREAR** para publicar.';
    } else if (pmStatus === 'warning' && msg) {
        title = 'Registro con Advertencia';
        icon = 'warning';

        text = decodeURIComponent(msg) + "\n\nDebe llenar los campos del servicio y presionar **CREAR** para publicar.";
    } else if (pmStatus === 'error' && msg) {

        title = 'Error de Pago Obligatorio';
        icon = 'error';
        text = decodeURIComponent(msg);
        
        Swal.fire({
            title: title,
            text: text,
            icon: icon,
            confirmButtonText: 'Volver al Registro'
        }).then(() => {
            window.location.href = window.location.pathname + '?show_modal=true';
        });
        return; 
    }
    
    if (title) {

        Swal.fire({
            title: title,
            text: text,
            icon: icon,
            showConfirmButton: true, 
            confirmButtonText: 'Entendido, Publicaré Ahora'
        }).then(() => {

            history.replaceState(null, '', window.location.pathname); 
        });
    }
}

        document.getElementById('formServicio').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const fileInput = document.getElementById('input-archivos-servicio');
            
            if (fileInput.files.length > 0) {
                formData.append('imagen-servicio', fileInput.files[0]);
            }
            

            if (!document.querySelector('.swal2-loading')) {
                Swal.fire({
                    title: 'Publicando Servicio...',
                    text: 'Verificando datos y métodos de pago.',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
            }

            fetch('servicio.php', { 
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) { throw new Error('Network response was not ok'); }
                return response.json();
            })
            .then(data => {
                Swal.close(); 

                if (data.success) {

                    Swal.fire('¡Éxito!', data.message, 'success').then(() => {
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        }
                    });
                } else if (data.show_modal) {

                    Swal.fire({
                        title: 'Método de Pago Requerido',
                        text: data.message,
                        icon: 'warning',
                        confirmButtonText: 'Registrar Método',
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then(() => {
                        window.location.href = window.location.pathname + '?show_modal=true';
                    });

                } else {

                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.close();
                console.error('Error en la petición:', error);
                Swal.fire('Error', 'Ocurrió un error al intentar crear el servicio.', 'error');
            });
        });



        const formMetodosPago = document.getElementById('formMetodosPago');
        if (formMetodosPago) {
            formMetodosPago.addEventListener('submit', function(e) {

                const modalElement = document.getElementById('modalConTabs');
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const modalInstance = bootstrap.Modal.getInstance(modalElement);
                    if (modalInstance) {
                        modalInstance.hide(); 
                    }
                }

                Swal.fire({
                    title: 'Registrando Métodos de Pago...',
                    text: 'Será redirigido para continuar con la publicación.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            });
        }
    });
</script>

<script>

document.addEventListener('DOMContentLoaded', function() {
    
    // --- Función Auxiliar para Validar Email ---
    function isValidEmail(email) {
        const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9 сне1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    }

    const formMetodosPago = document.getElementById('formMetodosPago');
    const minDocLength = 8; // Mínimo de caracteres para documentos
    
    if (!formMetodosPago) {
        console.warn("El formulario 'formMetodosPago' no fue encontrado.");
        return;
    }

    // Referencias a campos y elementos
    const pm_documento = formMetodosPago.querySelector('[name="documento_ident"]');
    const pm_telefono = formMetodosPago.querySelector('[name="telefono"]');
    const pm_banco_id = formMetodosPago.querySelector('[name="banco_id"]');
    const pm_banco_visual_input = document.getElementById('banco_visual_input');

    const tr_documento = formMetodosPago.querySelector('[name="documento_identidad"]');
    const tr_nro_cuenta = formMetodosPago.querySelector('[name="nro_cuenta"]');
    const tr_banco_id = formMetodosPago.querySelector('[name="banco2_id"]');
    const tr_banco_visual_input = document.getElementById('banco2_visual_input');

    const correo_binance = formMetodosPago.querySelector('[name="correo_binance"]');
    const correo_paypal = formMetodosPago.querySelector('[name="correo_paypal"]');
    
    // Referencia al botón de registro (DEBE tener type="button" en el HTML)
    const btnSubmitMetodos = document.getElementById('btnSubmitMetodosPago') || formMetodosPago.querySelector('button[type="submit"]');
    const modalElement = document.getElementById('modalConTabs'); 

    
    // --- LÓGICA DE VALIDACIÓN PRINCIPAL (Al hacer click en el botón) ---
    // NOTA: Se escucha el 'click' del botón en lugar del 'submit' del formulario para mayor control.
    if (btnSubmitMetodos) {
        btnSubmitMetodos.addEventListener('click', function(e) {
            
            // Si el botón es type="submit", se previene el envío automático
            if (this.type === 'submit') {
                e.preventDefault(); 
            }

            const errors = [];
            
            // --- 1. PAGO MÓVIL (OBLIGATORIO) ---
            
            const isPmComplete = pm_documento.value.trim() && pm_telefono.value.trim() && pm_banco_id.value !== "";

            if (!isPmComplete) {
                errors.push('El método **Pago Móvil (OBLIGATORIO)** debe estar completo (Documento, Teléfono y Banco).');
                if (!pm_documento.value.trim()) pm_documento.classList.add('is-invalid');
                if (!pm_telefono.value.trim()) pm_telefono.classList.add('is-invalid');
                if (pm_banco_id.value === "") pm_banco_visual_input.classList.add('is-invalid');
            } else {
                if (pm_documento.value.trim().length < minDocLength) {
                    errors.push(`El Documento de Pago Móvil debe tener mínimo ${minDocLength} caracteres.`);
                    pm_documento.classList.add('is-invalid');
                } else {
                    pm_documento.classList.remove('is-invalid');
                }
                pm_telefono.classList.remove('is-invalid');
                pm_banco_visual_input.classList.remove('is-invalid');
            }


            // --- 2. TRANSFERENCIA BANCARIA (OPCIONAL PERO COMPLETO) ---
            const isTrPartiallyFilled = tr_documento.value.trim() || tr_nro_cuenta.value.trim() || tr_banco_id.value;
            const isTrComplete = tr_documento.value.trim() && tr_nro_cuenta.value.trim() && tr_banco_id.value;

            if (isTrPartiallyFilled) {
                if (!isTrComplete) {
                    errors.push('Si registra Transferencia Bancaria, debe llenar **todos** los campos (Documento, Cuenta y Banco).');
                    if (!tr_documento.value.trim()) tr_documento.classList.add('is-invalid');
                    if (!tr_nro_cuenta.value.trim()) tr_nro_cuenta.classList.add('is-invalid');
                    if (!tr_banco_id.value) tr_banco_visual_input.classList.add('is-invalid');
                } else {
                    if (tr_documento.value.trim().length < minDocLength) {
                        errors.push(`El Documento de Transferencia debe tener mínimo ${minDocLength} caracteres.`);
                        tr_documento.classList.add('is-invalid');
                    } else {
                        tr_documento.classList.remove('is-invalid');
                        tr_nro_cuenta.classList.remove('is-invalid');
                        tr_banco_visual_input.classList.remove('is-invalid');
                    }
                }
            } else {
                tr_documento.classList.remove('is-invalid');
                tr_nro_cuenta.classList.remove('is-invalid');
                tr_banco_visual_input.classList.remove('is-invalid');
            }


            // --- 3. BINANCE (OPCIONAL CON FORMATO DE EMAIL) ---
            if (correo_binance && correo_binance.value.trim()) {
                if (!isValidEmail(correo_binance.value.trim())) {
                    errors.push('El correo de Binance no tiene un formato válido.');
                    correo_binance.classList.add('is-invalid');
                } else {
                    correo_binance.classList.remove('is-invalid');
                }
            } else if (correo_binance) {
                correo_binance.classList.remove('is-invalid');
            }


            // --- 4. PAYPAL (OPCIONAL CON FORMATO DE EMAIL) ---
            if (correo_paypal && correo_paypal.value.trim()) {
                if (!isValidEmail(correo_paypal.value.trim())) {
                    errors.push('El correo de Paypal no tiene un formato válido.');
                    correo_paypal.classList.add('is-invalid');
                } else {
                    correo_paypal.classList.remove('is-invalid');
                }
            } else if (correo_paypal) {
                correo_paypal.classList.remove('is-invalid');
            }


            // --- MANEJO DE ERRORES Y ENVÍO ---
            if (errors.length > 0) {
                
                const errorHtml = '<ul>' + errors.map(err => `<li>${err}</li>`).join('') + '</ul>';
                
                // *** ESTO MUESTRA EL ERROR Y DEJA EL MODAL ABIERTO ***
                Swal.fire({
                    title: 'Campos Incompletos o Inválidos',
                    html: errorHtml,
                    icon: 'error',
                    confirmButtonText: 'Corregir'
                });
                
                return; // Detiene el envío
            }

            // Si la validación pasa: 
            
            // 1. Ocultar el modal manualmente (SOLO ÉXITO)
            if (modalElement && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const modalInstance = bootstrap.Modal.getInstance(modalElement);
                if (modalInstance) {
                    modalInstance.hide(); 
                }
            }

            // 2. Mostrar SweetAlert de carga antes de enviar
            Swal.fire({
                title: 'Registrando Métodos de Pago...',
                text: 'Será redirigido para continuar con la publicación.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // 3. Envía el formulario
            formMetodosPago.submit(); 
        });
    }


    // --- Función para limpiar la clase 'is-invalid' al escribir/seleccionar ---
    const fieldsToClean = [
        pm_documento, pm_telefono, pm_banco_visual_input,
        tr_documento, tr_nro_cuenta, tr_banco_visual_input,
        correo_binance, correo_paypal
    ].filter(el => el);

    fieldsToClean.forEach(input => {
        input.addEventListener('input', function() {
            this.classList.remove('is-invalid');
        });
        
        if (input === pm_banco_visual_input) {
            pm_banco_id && pm_banco_id.addEventListener('change', function() {
                pm_banco_visual_input.classList.remove('is-invalid');
            });
        }
        if (input === tr_banco_visual_input) {
            tr_banco_id && tr_banco_id.addEventListener('change', function() {
                tr_banco_visual_input.classList.remove('is-invalid');
            });
        }
    });

});


// ====================================================================
// ============ SEGUNDO BLOQUE: Validación de formServicio ============
// ====================================================================


document.addEventListener('DOMContentLoaded', function() {
    
    const formServicio = document.getElementById('formServicio');

    if (formServicio) {
        formServicio.addEventListener('submit', function(e) {
            e.preventDefault(); // Detener el envío por defecto para realizar la validación

            const form = this;
            const errors = [];
            
            // --- 1. Obtener y Limpiar Valores ---
            
            const tituloInput = form.querySelector('[name="titulo"]');
            const titulo = tituloInput ? tituloInput.value.trim() : '';

            const precioInput = form.querySelector('[name="precio"]');
            const precio = precioInput ? parseFloat(precioInput.value) : NaN;

            const descripcionInput = form.querySelector('[name="descripcion"]');
            const descripcion = descripcionInput ? descripcionInput.value.trim() : '';
            
            const carreraVisualInput = document.getElementById('carrera_visual_input');
            const tipoTrabajoVisualInput = document.getElementById('tipo_trabajo_visual_input');
            const materiaVisualInput = document.getElementById('materia_visual_input');
            
            const carreraIdSelect = form.querySelector('#carrera_id');
            const carrera_id = carreraIdSelect ? carreraIdSelect.value : '';

            const tipoTrabajoIdSelect = form.querySelector('#tipo_trabajo_id');
            const tipo_trabajo_id = tipoTrabajoIdSelect ? tipoTrabajoIdSelect.value : '';

            const materiaIdSelect = form.querySelector('#materia_id');
            const materia_id = materiaIdSelect ? materiaIdSelect.value : '';
            
            // Limpiamos las clases de error de todos los inputs antes de volver a validar
            document.querySelectorAll('.inputs-publi, .form-control, textarea').forEach(input => {
                input.classList.remove('is-invalid');
            });
            
            // --- 2. VALIDACIÓN DE CAMPOS DEL SERVICIO ---
            
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

            // d. Validación de campos Select
            
            if (!carrera_id) {
                errors.push('Debe seleccionar una **CARRERA** válida de la lista.');
                carreraVisualInput && carreraVisualInput.classList.add('is-invalid');
            } else if (carreraVisualInput && /\d/.test(carreraVisualInput.value)) { 
                 errors.push('El campo **CARRERA** no puede contener números.');
                 carreraVisualInput && carreraVisualInput.classList.add('is-invalid');
            }

            if (!tipo_trabajo_id) {
                errors.push('Debe seleccionar un **TIPO DE TRABAJO** válido de la lista.');
                tipoTrabajoVisualInput && tipoTrabajoVisualInput.classList.add('is-invalid');
            } else if (tipoTrabajoVisualInput && /\d/.test(tipoTrabajoVisualInput.value)) { 
                 errors.push('El campo **TIPO DE TRABAJO** no puede contener números.');
                 tipoTrabajoVisualInput && tipoTrabajoVisualInput.classList.add('is-invalid');
            }

            if (!materia_id) {
                errors.push('Debe seleccionar una **MATERIA** válida de la lista.');
                materiaVisualInput && materiaVisualInput.classList.add('is-invalid');
            } else if (materiaVisualInput && /\d/.test(materiaVisualInput.value)) { 
                 errors.push('El campo **MATERIA** no puede contener números.');
                 materiaVisualInput && materiaVisualInput.classList.add('is-invalid');
            }
            
            // --- 3. MOSTRAR ERRORES Y DETENER ENVÍO ---
            if (errors.length > 0) {
                const errorHtml = '<ul>' + errors.map(err => `<li>${err}</li>`).join('') + '</ul>';
                
                Swal.fire({
                    title: '🚨 Faltan Datos o son Inválidos',
                    html: errorHtml,
                    icon: 'error',
                    confirmButtonText: 'Corregir'
                });
                return; 
            }

            // --- 4. SI LA VALIDACIÓN PASA, CONTINUAR CON ENVÍO AJAX ---
            
            const formData = new FormData(form);
            const fileInput = document.getElementById('input-archivos-servicio');
            
            if (fileInput && fileInput.files.length > 0) {
                formData.append('imagen-servicio', fileInput.files[0]);
            }
            
            if (typeof Swal !== 'undefined') { 
                if (!document.querySelector('.swal2-loading')) {
                    Swal.fire({
                        title: 'Publicando Servicio...',
                        text: 'Verificando datos y métodos de pago.',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });
                }

                fetch(form.action, { 
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) { throw new Error('Network response was not ok'); }
                    return response.json();
                })
                .then(data => {
                    Swal.close(); 

                    if (data.success) {
                        Swal.fire('¡Éxito!', data.message, 'success').then(() => {
                            if (data.redirect) {
                                window.location.href = data.redirect;
                            }
                        });
                    } else if (data.show_modal) {
                        Swal.fire({
                            title: 'Método de Pago Requerido',
                            text: data.message,
                            icon: 'warning',
                            confirmButtonText: 'Registrar Método',
                            allowOutsideClick: false,
                            allowEscapeKey: false
                        }).then(() => {
                            // Se asume que este es el modal de métodos de pago
                            window.location.href = window.location.pathname + '?show_modal=true'; 
                        });

                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    Swal.close();
                    console.error('Error en la petición:', error);
                    Swal.fire('Error', 'Ocurrió un error al intentar crear el servicio.', 'error');
                });
            } else {
                form.submit();
            }

        });
    }

    // --- MEJORA UX: RESTRIJO ESCRITURA EN TIEMPO REAL ---
    const textOnlyInputs = ['carrera_visual_input', 'tipo_trabajo_visual_input', 'materia_visual_input'];

    textOnlyInputs.forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            input.addEventListener('keypress', function(e) {
                const charCode = (e.which) ? e.which : e.keyCode;
                if (charCode >= 48 && charCode <= 57) { // ASCII para 0-9
                    e.preventDefault();
                }
            });
            
            input.addEventListener('input', function() {
                this.value = this.value.replace(/[0-9]/g, ''); 
            });
        }
    });

    // --- LIMPIEZA DE ERRORES VISUALES AL INTERACTUAR ---
    
    document.querySelectorAll('.inputs-publi, .inputs, textarea, .form-control').forEach(input => {
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


// ... (Segundo bloque de código JS para formServicio - SIN CAMBIOS ya que no afecta) ...
document.addEventListener('DOMContentLoaded', function() {
    
    const formServicio = document.getElementById('formServicio');

    if (formServicio) {
        formServicio.addEventListener('submit', function(e) {
            e.preventDefault(); // Detener el envío por defecto para realizar la validación

            const form = this;
            const errors = [];
            
            // --- 1. Obtener y Limpiar Valores ---
            
            const tituloInput = form.querySelector('[name="titulo"]');
            const titulo = tituloInput ? tituloInput.value.trim() : '';

            const precioInput = form.querySelector('[name="precio"]');
            const precio = precioInput ? parseFloat(precioInput.value) : NaN;

            const descripcionInput = form.querySelector('[name="descripcion"]');
            const descripcion = descripcionInput ? descripcionInput.value.trim() : '';
            
            // Campos de Dropdown Visuales (Para validar contenido y marcar error visual)
            const carreraVisualInput = document.getElementById('carrera_visual_input');
            const tipoTrabajoVisualInput = document.getElementById('tipo_trabajo_visual_input');
            const materiaVisualInput = document.getElementById('materia_visual_input');
            
            // Campos de Dropdown Ocultos (los que contienen el ID real)
            const carreraIdSelect = form.querySelector('#carrera_id');
            const carrera_id = carreraIdSelect ? carreraIdSelect.value : '';

            const tipoTrabajoIdSelect = form.querySelector('#tipo_trabajo_id');
            const tipo_trabajo_id = tipoTrabajoIdSelect ? tipoTrabajoIdSelect.value : '';

            const materiaIdSelect = form.querySelector('#materia_id');
            const materia_id = materiaIdSelect ? materiaIdSelect.value : '';
            
            // Limpiamos las clases de error de todos los inputs antes de volver a validar
            document.querySelectorAll('.inputs-publi, .form-control, textarea').forEach(input => {
                input.classList.remove('is-invalid');
            });
            
            // --- 2. VALIDACIÓN DE CAMPOS DEL SERVICIO ---
            
            // a. Título (Obligatorio, longitud, y no debe contener solo números)
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

            // d. Validación de campos Select (Asegurando que el ID real no sea vacío)
            
            // Carrera (Validación de selección)
            if (!carrera_id) {
                errors.push('Debe seleccionar una **CARRERA** válida de la lista.');
                carreraVisualInput && carreraVisualInput.classList.add('is-invalid');
            } else if (/\d/.test(carreraVisualInput.value)) { // Validación: No debe tener números en el texto visual
                 errors.push('El campo **CARRERA** no puede contener números.');
                 carreraVisualInput && carreraVisualInput.classList.add('is-invalid');
            }

            // Tipo de Trabajo (Validación de selección y sin números)
            if (!tipo_trabajo_id) {
                errors.push('Debe seleccionar un **TIPO DE TRABAJO** válido de la lista.');
                tipoTrabajoVisualInput && tipoTrabajoVisualInput.classList.add('is-invalid');
            } else if (/\d/.test(tipoTrabajoVisualInput.value)) { // Validación: No debe tener números en el texto visual
                 errors.push('El campo **TIPO DE TRABAJO** no puede contener números.');
                 tipoTrabajoVisualInput && tipoTrabajoVisualInput.classList.add('is-invalid');
            }


            // Materia (Validación de selección y sin números)
            if (!materia_id) {
                errors.push('Debe seleccionar una **MATERIA** válida de la lista.');
                materiaVisualInput && materiaVisualInput.classList.add('is-invalid');
            } else if (/\d/.test(materiaVisualInput.value)) { // Validación: No debe tener números en el texto visual
                 errors.push('El campo **MATERIA** no puede contener números.');
                 materiaVisualInput && materiaVisualInput.classList.add('is-invalid');
            }
            
            // --- 3. MOSTRAR ERRORES Y DETENER ENVÍO ---
            if (errors.length > 0) {
                const errorHtml = '<ul>' + errors.map(err => `<li>${err}</li>`).join('') + '</ul>';
                
                Swal.fire({
                    title: '🚨 Faltan Datos o son Inválidos',
                    html: errorHtml,
                    icon: 'error',
                    confirmButtonText: 'Corregir'
                });
                return; 
            }

            // --- 4. SI LA VALIDACIÓN PASA, CONTINUAR CON ENVÍO AJAX (Tu código original) ---
            
            const formData = new FormData(form);
            const fileInput = document.getElementById('input-archivos-servicio');
            
            if (fileInput && fileInput.files.length > 0) {
                formData.append('imagen-servicio', fileInput.files[0]);
            }
            
            if (typeof Swal !== 'undefined') { 
                if (!document.querySelector('.swal2-loading')) {
                    Swal.fire({
                        title: 'Publicando Servicio...',
                        text: 'Verificando datos y métodos de pago.',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });
                }

                fetch(form.action, { 
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) { throw new Error('Network response was not ok'); }
                    return response.json();
                })
                .then(data => {
                    Swal.close(); 

                    if (data.success) {
                        Swal.fire('¡Éxito!', data.message, 'success').then(() => {
                            if (data.redirect) {
                                window.location.href = data.redirect;
                            }
                        });
                    } else if (data.show_modal) {
                        Swal.fire({
                            title: 'Método de Pago Requerido',
                            text: data.message,
                            icon: 'warning',
                            confirmButtonText: 'Registrar Método',
                            allowOutsideClick: false,
                            allowEscapeKey: false
                        }).then(() => {
                            window.location.href = window.location.pathname + '?show_modal=true'; 
                        });

                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    Swal.close();
                    console.error('Error en la petición:', error);
                    Swal.fire('Error', 'Ocurrió un error al intentar crear el servicio.', 'error');
                });
            } else {
                form.submit();
            }

        });
    }

    // --- MEJORA UX: RESTRIJO ESCRITURA EN TIEMPO REAL (on keypress) ---
    const textOnlyInputs = ['carrera_visual_input', 'tipo_trabajo_visual_input', 'materia_visual_input'];

    textOnlyInputs.forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            input.addEventListener('keypress', function(e) {
                // Permitir letras, espacios y algunos caracteres comunes
                // Si el carácter presionado es un dígito (0-9), prevenir la entrada.
                const charCode = (e.which) ? e.which : e.keyCode;
                if (charCode >= 48 && charCode <= 57) { // ASCII para 0-9
                    e.preventDefault();
                }
            });
            
            // También limpio números si se pegan (on paste) o se arrastran
            input.addEventListener('input', function() {
                // Elimina cualquier dígito que se haya colado
                this.value = this.value.replace(/[0-9]/g, ''); 
            });
        }
    });

    // --- LIMPIEZA DE ERRORES VISUALES AL INTERACTUAR ---
    
    document.querySelectorAll('.inputs-publi, .inputs, textarea, .form-control').forEach(input => {
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