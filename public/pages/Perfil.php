<?php
session_start();
include('../../conect.php'); 

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../../Index.php");
    exit();
}
$idUsuario = $_SESSION['id_usuario'];

$sql = "SELECT u.url_foto_perfil, u.nombre, u.apellido, u.rating, u.porcentaje_completacion, u.descripcion, u.estado, c.nombre_carrera
        FROM usuarios u
        JOIN carreras c ON u.id_carrera = c.id_carrera
        WHERE id_usuario = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$resultado = $stmt->get_result();
$datosUsuario = $resultado->fetch_assoc();

$rutaFoto = !empty($datosUsuario['url_foto_perfil']) ? $datosUsuario['url_foto_perfil'] : "public/img/imgusuarios/default_avatar.jpg";
$nombreUsuario = $datosUsuario['nombre'] ?? 'Sin Nombre';
$apellidoUsuario = $datosUsuario['apellido'] ?? 'Sin Apellido';
$ratingUsuario = $datosUsuario['rating'] ?? 0;
$porcentajeUsuario = $datosUsuario['porcentaje_completacion'] ?? 0;
$carreraUsuario = $datosUsuario['nombre_carrera'] ?? 'Sin Carrera';
$descripcionUsuario = $datosUsuario['descripcion'] ?? 'Agrega una descripción sobre ti.';
$estadoCuenta = (isset($datosUsuario['estado']) && $datosUsuario['estado'] == 1) ? 'Activo' : 'Inactivo';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="StylesNav.css">
</head>
<body>
   <?php include 'NavBar.php'; ?>

<div class="container-fluid p-0 mb-5"> 
    <div class="row m-0">
        <div class="col-12 p-0">
            
            <div class="profile-card text-center" style="border-radius: 0; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                
                <div class="profile-banner">
                    <button type="button" class="btn-report-banner" title="Cerrar Sesión" onclick="cerrarSesion()">
                        <span class="material-symbols-outlined" style="font-size: 1.4rem; padding:0;">logout</span>
                    </button>
                </div>

                <div class="profile-avatar-container">
                    <img src="../../<?php echo htmlspecialchars($rutaFoto); ?>" alt="Foto de perfil" class="profile-avatar">
                </div>

                <div class="card-body px-4 pb-4 d-flex flex-column align-items-center">
                    
                    <h1 class="profile-name mb-2 text-center">
                        <?php echo htmlspecialchars($nombreUsuario . ' ' . $apellidoUsuario); ?>
                    </h1>
                    
                    <div class="d-flex align-items-center mb-3" style="color: #198754; font-weight: 600; gap: 6px;">
                        <span>Estado: <?php echo htmlspecialchars($estadoCuenta); ?></span>
                    </div>
                    
                    <div class="profile-bio mb-4">
                        <span><?php echo htmlspecialchars($carreraUsuario); ?></span>
                    </div>

                    <div class="profile-career d-flex flex-column align-items-center mb-4" style="max-width: 700px; width: 100%;">
                        
                        <p class=" m-0 text-center">
                            <span id="textoDescripcion">"<?php echo htmlspecialchars($descripcionUsuario); ?>"</span>
                        </p>
                        
                        <button onclick="editarDescripcion()" class="btneditar btn-link p-0 text-decoration-none mt-2" title="Editar descripción">
                            <span class="material-symbols-outlined" style="font-size: 1.2rem;">edit</span>
                        </button>

                    </div>

                    <div class="profile-stats-row w-100">
                        <div class="stat-item">
                            <div class="stat-number justify-content-center">
                                <span class="text-warning material-symbols-outlined">star</span> 
                                <?php echo htmlspecialchars($ratingUsuario); ?>
                            </div>
                            <div class="stat-label">Rating</div>
                        </div>

                        <div class="stat-item">
                            <div class="stat-number justify-content-center">
                                <span class="text-success material-symbols-outlined">check_circle</span>
                                <?php echo htmlspecialchars($porcentajeUsuario); ?>%
                            </div>
                            <div class="stat-label">Completado</div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
<div id="Servicios" class="banner-container pb-5">
    <div class="container-fluid px-5">
        <div class="row align-items-center mt-5">
            <div class="col-md-12 mb-4 mb-md-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="Titulo mb-0">Mis Servicios Publicados</h3> 
                    <a href="VerMasServicio.php" class="mas text-decoration-none">Ver más</a>
                </div>
                <hr>
            </div>
        </div>

        <?php
        $sql_servicios = "SELECT 
            s.id_servicio, s.titulo, s.descripcion, s.precio,
            c.nombre_carrera,
            MIN(f.url_foto) AS url_foto
            FROM servicios s
            JOIN carreras c ON s.id_carrera = c.id_carrera
            LEFT JOIN fotos_servicios f ON s.id_servicio = f.id_servicio
            WHERE s.id_usuario = ?
            GROUP BY
                s.id_servicio, s.titulo, s.descripcion, s.precio,
                c.nombre_carrera";

        $stmt_ser = $mysqli->prepare($sql_servicios);
        $stmt_ser->bind_param("i", $idUsuario);
        $stmt_ser->execute();
        $resultado_ser = $stmt_ser->get_result();

        if ($resultado_ser && $resultado_ser->num_rows > 0) {
        ?>
            <div class="row">
                <?php while ($row = $resultado_ser->fetch_assoc()) { ?>
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">
                        <div class="card"> <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($row['titulo']); ?></h5>
                            <div class="separator-line"></div>
                                <?php if ($row['url_foto']) { ?>
                                    <div class="img-wrapper">
                                    <img class="imagen" src="../../public/img/imgSer/<?php echo htmlspecialchars($row['url_foto']); ?>" alt="Foto del servicio">
                                    </div>
                                <?php } ?>
                            <h6 class="carrera">
                                <span class="material-symbols-outlined">license</span>
                                <?php echo htmlspecialchars($row['nombre_carrera']); ?>
                            </h6>
                            <p class="card-text flex-grow-1"><?php echo htmlspecialchars($row['descripcion']); ?></p>
                            <div class="d-flex justify-content-between align-items-center mb-3 mt-3"> 
                                <div class="star-rating-display" data-rating="<?php echo htmlspecialchars($ratingUsuario); ?>"></div>
                                <h5 class="Precio mb-0">$<?php echo htmlspecialchars($row['precio']); ?></h5> 
                            </div>
                            <a href="#" class="btn btn-primary mt-auto">Más información</a>
                        </div>
                        </div> 
                    </div>
                <?php } ?>
            </div>
        <?php } else { echo '<div class="alert alert-light" role="alert">Aún no has publicado ningún servicio.</div>'; } ?>
    </div>
</div>

<div id="Requests" class="banner- pb-5">
    <div class="container-fluid px-5">
        <div class="row align-items-center mt-5">
            <div class="col-md-12 mb-4 mb-md-0">
                <div class="d-flex justify-content-between align-items-center">
                     <h3 class="Titulo mb-0">Mis Requests Publicados</h3> 
                    <a href="VerMasRequest.php" class="mas text-decoration-none">Ver más</a>
                </div>
                <hr>
            </div>
        </div>

        <?php
        // Reutilizamos la misma consulta, filtrando por el ID de sesión
        $sql = "SELECT 
                    r.id_requests, r.titulo, r.descripcion, r.precio,
                    c.nombre_carrera, u.rating, u.porcentaje_completacion
                FROM requests r
                JOIN carreras c ON r.id_carrera = c.id_carrera
                JOIN usuarios u ON r.id_usuario = u.id_usuario
                WHERE r.id_usuario = ?";

        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado && $resultado->num_rows > 0) {
        ?>
            <div class="row">
            <?php while ($row = $resultado->fetch_assoc()) { ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">
                    <div class="card"> <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo htmlspecialchars($row['titulo']); ?></h5>
                        <div class="separator-line"></div>
                        <h6 class="carrera">
                            <span class="material-symbols-outlined">license</span>
                            <?php echo htmlspecialchars($row['nombre_carrera']); ?>
                        </h6>
                        <p class="card-text flex-grow-1"><?php echo htmlspecialchars($row['descripcion']); ?></p>
                        <div class="d-flex justify-content-between align-items-center mb-3 mt-3"> 
                            <div class="star-rating-display" data-rating="<?php echo htmlspecialchars($row['rating']); ?>"></div>
                            <h5 class="Precio mb-0">$<?php echo htmlspecialchars($row['precio']); ?></h5> 
                        </div>
                         <a href="#" class="btn btn-primary mt-auto">Más información</a>
                    </div>
                    </div> 
                </div>
            <?php } ?>
            </div>
        <?php } else { echo '<div class="alert alert-light" role="alert">Aún no has publicado ningún request.</div>'; } ?>
    </div>
</div>


<form id="formMetodosPago" action="modal_pagos.php" method="POST" enctype="multipart/form-data">
<div class="modal fade" id="modalConTabs" tabindex="-1" aria-labelledby="modalConTabsLabel" aria-hidden="true">
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
        <button type="submit" class="btn btn-secondary"">REGISTRAR</button>
      </div>
    </div>
  </div>
</div>
</form>

<?php include '../../app/includes/footer.php'; ?>

<script>
function cerrarSesion() {
    Swal.fire({
        title: '¿Cerrar sesión?',
        text: "¿Estás seguro de que quieres salir?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, cerrar sesión',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '../../logout.php'; 
        }
    });
}

function editarDescripcion() {
    let textoActual = document.getElementById('textoDescripcion').innerText;
    textoActual = textoActual.replace(/^"|"$/g, '');

    Swal.fire({
        title: 'Editar mi descripción',
        input: 'textarea',
        inputLabel: 'Escribe algo sobre ti',
        inputValue: textoActual,
        showCancelButton: true,
        confirmButtonText: 'Guardar cambios',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#203864',
        showLoaderOnConfirm: true,
        preConfirm: (nuevaDescripcion) => {
            return fetch('actualizarDescripcion.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ descripcion: nuevaDescripcion })
            })
            .then(response => {
                if (!response.ok) { throw new Error(response.statusText) }
                return response.json();
            })
            .catch(error => {
                Swal.showValidationMessage(`Error: ${error}`);
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed && result.value.success) {
            document.getElementById('textoDescripcion').innerText = `"${result.value.nueva_descripcion}"`;
            Swal.fire({
                icon: 'success',
                title: '¡Actualizado!',
                text: 'Tu descripción ha sido guardada correctamente.',
                confirmButtonColor: '#203864'
            });
        } else if (result.isConfirmed && !result.value.success) {
             Swal.fire('Error', result.value.message || 'No se pudo guardar.', 'error');
        }
    });
}
</script>
</body>
</html>