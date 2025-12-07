<?php
session_start();
include('../../conect.php'); 

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../../Index.php");
    exit();
}

$idUsuario = $_SESSION['id_usuario'];

$sql = "SELECT u.url_foto_perfil, u.nombre, u.apellido, u.rating, u.porcentaje_completacion, u.descripcion, c.nombre_carrera FROM usuarios u
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
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="StylesNav.css">
</head>
<body>
   <?php include 'NavBar.php'; ?>
<div class="container pt-5">
    <div class="d-flex flex-column flex-md-row align-items-center gap-4">
        
        <div class="position-relative align-self-center-top">
            <img src="../../<?php echo htmlspecialchars($rutaFoto); ?>" alt="Foto de perfil" class="foto-perfil rounded-circle avatar-box" style="width: 150px; height: 150px; object-fit: cover;">
            <button type="button" class=" reportar position-absolute top-0 start-100 translate-middle rounded-circle badge-notification d-flex justify-content-center align-items-center border-0 p-0" title="Reportar Usuario" onclick="reportarUsuario()">
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
<?php include '../../app/includes/footer.php'; ?>
</body>
</html>