<?php
session_start();
include('../../conect.php'); 

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../../Index.php");
    exit();
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $idUsuarioVisita = $_GET['id'];
} else {
    header("Location: principal.php");
    exit();
}

if ($idUsuarioVisita == $_SESSION['id_usuario']) {
    header("Location: perfil.php"); 
    exit();
}

$idUsuario = $idUsuarioVisita; 

$sql = "SELECT u.url_foto_perfil, u.nombre, u.apellido, u.rating, u.porcentaje_completacion, u.descripcion, c.nombre_carrera FROM usuarios u
JOIN carreras c ON u.id_carrera = c.id_carrera
WHERE id_usuario = ?";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $idUsuario); 
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $datosUsuario = $resultado->fetch_assoc();
} else {
    echo "Usuario no encontrado.";
    exit();
}

$rutaFoto = !empty($datosUsuario['url_foto_perfil']) ? $datosUsuario['url_foto_perfil'] : "public/img/imgusuarios/default_avatar.jpg";

$nombreUsuario = $datosUsuario['nombre'] ?? 'Sin Nombre';
$apellidoUsuario = $datosUsuario['apellido'] ?? 'Sin Apellido';
$ratingUsuario = $datosUsuario['rating'] ?? 0;
$porcentajeUsuario = $datosUsuario['porcentaje_completacion'] ?? 0;
$carreraUsuario = $datosUsuario['nombre_carrera'] ?? 'Sin Carrera';
$descripcionUsuario = $datosUsuario['descripcion'] ?? 'Sin Descripción';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estructura de Perfil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="StylesNav.css">
</head>
<body>
   <?php include 'NavBar.php'; ?>
<div class="container pt-5">
    <div class="d-flex flex-column flex-md-row align-items-center gap-4">
        
        <div class="position-relative align-self-center-top">
            <img src="../../<?php echo htmlspecialchars($rutaFoto); ?>" alt="Foto de perfil" class="foto-perfil rounded-circle avatar-box" style="width: 150px; height: 150px; object-fit: cover;">
                <button type="button" class=" reportar position-absolute top-0 start-100 translate-middle rounded-circle badge-notification d-flex justify-content-center align-items-center border-0 p-0" title="Reportar Usuario" onclick="reportarUsuario(<?php echo $idUsuario; ?>)">
                    <span class="reportar-icon">!</span>
                </button>
        </div>

        <div class="flex-grow-1">
            <div class="row row-cols-5 g-2 mb-3">
                
                <div class="col-6 col-md-6 col-lg-4">
                    <div class="label-box">
                        <?php echo htmlspecialchars($nombreUsuario); ?>
                    </div>
                </div>
                
                <div class="col-6 col-md-6 col-lg-4">
                    <div class="label-box">
                        <?php echo htmlspecialchars($apellidoUsuario); ?>
                    </div>
                </div>
                
                <div class="col-6 col-md-6 col-lg-4">
                    <div class="label-box">
                        <strong>Rating:</strong> <?php echo htmlspecialchars($ratingUsuario); ?> 
                    </div>
                </div>
                
                <div class="col-6 col-md-6 col-lg-6">
                    <div class="label-box">
                        <strong>Completación:</strong> <?php echo htmlspecialchars($porcentajeUsuario); ?>%
                    </div>
                </div>
                
                <div class="col-12 col-md-12 col-lg-6">
                    <div class="label-box">
                        <?php echo htmlspecialchars($carreraUsuario); ?>
                    </div>
                </div>
            </div>    
            
            <div class="row">
                <div class="col-12">
                    <div class="description-box">
                        <?php echo htmlspecialchars($descripcionUsuario); ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="container mt-5 mb-5">
    <h3 class="mb-4">Servicios de <?php echo htmlspecialchars($nombreUsuario); ?></h3>
    <hr>

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
                    <div class="card h-100"> 
                        <div class="card-body d-flex flex-column">
                            
                            <h5 class="card-title"><?php echo htmlspecialchars($row['titulo']); ?></h5>
                            <div class="separator-line border-bottom mb-3"></div>
                            
                            <?php 
                            // Lógica de la imagen del servicio
                            if ($row['url_foto']) { 
                            ?>
                                <div class="img-wrapper mb-2" style="height: 200px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                                    <img class="imagen w-100 h-100 object-fit-cover" src="../../public/img/imgSer/<?php echo htmlspecialchars($row['url_foto']); ?>" alt="Foto del servicio" style="object-fit: cover;">
                                </div>
                            <?php } ?>

                            <h6 class="carrera text-muted">
                                <span class="material-symbols-outlined align-middle" style="font-size: 1.2rem;">license</span>
                                <?php echo htmlspecialchars($row['nombre_carrera']); ?>
                            </h6>
                        
                            <p class="card-text flex-grow-1 mt-2"><?php echo htmlspecialchars($row['descripcion']); ?></p>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3 mt-3"> 
                                <div class="star-rating-display" data-rating="<?php echo htmlspecialchars($ratingUsuario); ?>">
                                    <span class="text-warning">★</span> <?php echo htmlspecialchars($ratingUsuario); ?>
                                </div>
                                <h5 class="Precio mb-0 text-success">$<?php echo htmlspecialchars($row['precio']); ?></h5> 
                            </div>
                            
                            <a href="#" class="btn btn-primary mt-auto">Más información</a>
                        </div>
                    </div> 
                </div>
            <?php } ?>
        </div>
    <?php 
    } else { 
    ?>
        <div class="alert alert-info" role="alert">
            Este usuario no tiene servicios activos actualmente.
        </div>
    <?php 
    } 
    $stmt_ser->close();
    ?>
</div>
<div class="banner-container">
    <div class="container-fluid px-5">
        
        <div class="row align-items-center mt-5">
            <div class="col-md-12 mb-4 mb-md-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="Titulo mb-0">Requests de <?php echo htmlspecialchars($nombreUsuario); ?></h3> 
                    <a href="#" class="mas text-decoration-none">Ver más</a>
                </div>
                <hr>
            </div>
        </div>

        <?php

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
            <?php
            while ($row = $resultado->fetch_assoc()) {
            ?>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">
                <div class="card h-100"> <div class="card-body d-flex flex-column">
                    
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
            <?php
            } 
            ?>
        </div>
        <?php 
        } else {
            // Mensaje opcional si no tiene requests, manteniendo el estilo del contenedor
            echo '<div class="alert alert-light" role="alert">Este usuario no ha publicado requests todavía.</div>';
        }
        $stmt->close();
        ?>
    
    </div> 
</div>
<?php include '../../app/includes/footer.php'; ?>
<script>
function reportarUsuario(idReportado) {
    Swal.fire({
        title: 'Reportar Usuario',
        text: "¿Por qué deseas reportar a este usuario?",
        input: 'textarea',
        inputPlaceholder: 'Escribe la razón aquí...',
        inputAttributes: {
            'aria-label': 'Escribe la razón aquí'
        },
        showCancelButton: true,
        confirmButtonText: 'Enviar reporte',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d33', // Rojo para indicar acción de alerta
        cancelButtonColor: '#3085d6',
        preConfirm: (razon) => {
            if (!razon) {
                Swal.showValidationMessage('Debes escribir una razón');
            }
            return razon;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Enviar datos al servidor mediante FETCH
            fetch('reportarUsuario.php', { // Asegúrate de que la ruta sea correcta
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id_reportado: idReportado,
                    razon: result.value
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('¡Enviado!', 'El reporte ha sido enviado a los administradores.', 'success');
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Hubo un problema de conexión.', 'error');
            });
        }
    });
}
</script>
</body>
</html>
