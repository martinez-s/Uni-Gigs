<?php
session_start();

$id_request_seleccionado = null;
$data = null; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_request'])) {
    
    $id_request = intval($_POST['id_request']);
    $id_usuario = intval($_POST['id_usuario']);
    $id_carrera = intval($_POST['id_carrera']);
    

    $_SESSION['current_request_id'] = $id_request;
    $_SESSION['request_user_id'] = $id_usuario;
    $_SESSION['request_carrera_id'] = $id_carrera;
    

    $id_request_seleccionado = $id_request;

} elseif (isset($_SESSION['current_request_id'])) {

    $id_request_seleccionado = $_SESSION['current_request_id'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="stylesNav.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Uni-Gigs - Detalle</title>
</head>
<body>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="main.js"></script>
    
    <?php

include('../../conect.php');
include 'NavBar.php'; 

if ($id_request_seleccionado) {
    
    $sql = "SELECT 
        r.id_requests, 
        r.titulo, 
        r.descripcion, 
        r.precio, 
        r.fecha_creacion, 
        r.fecha_limite, 

        m.nombre AS nombre_materia, 
        tt.nombre AS tipo_trabajo_nombre,
        c.nombre_carrera, 

        u.rating, 
        u.porcentaje_completacion, 
        u.nombre AS nombre_usuario, 
        u.apellido AS apellido_usuario,
        u.url_foto_perfil, 

        MIN(fr.url_foto) AS url_foto
    FROM 
        requests r
    JOIN 
        carreras c ON r.id_carrera = c.id_carrera
    JOIN 
        usuarios u ON r.id_usuario = u.id_usuario
    JOIN 
        materias m ON r.id_materia = m.id_materia
    JOIN 
        tipos_trabajos tt ON r.id_tipo_trabajo = tt.id_tipo_trabajo
    LEFT JOIN 
        fotos_requests fr ON r.id_requests = fr.id_request
    WHERE 
        r.id_requests = ? 
    GROUP BY 
        r.id_requests, r.titulo, r.descripcion, r.precio, r.fecha_creacion, r.fecha_limite, 
        m.nombre, tt.nombre, c.nombre_carrera, 
        u.rating, u.porcentaje_completacion, u.nombre, u.apellido, u.url_foto_perfil"
        ;

    if ($stmt = $mysqli->prepare($sql)) {

        $stmt->bind_param("i", $id_request_seleccionado);
        $stmt->execute();
        $resultado = $stmt->get_result();
        

        $data = $resultado->fetch_assoc();
        $stmt->close();
    } else {

        echo "<p class='alert alert-danger'>Error al preparar la consulta: " . $mysqli->error . "</p>";
    }

    $archivos_request = [];
    $sql_archivos = "SELECT nombre_archivo, url_archivo, tipo_archivo FROM archivos_request WHERE id_requests = ?";
    if (isset($mysqli) && $stmt_archivos = $mysqli->prepare($sql_archivos)) {
        $stmt_archivos->bind_param("i", $id_request_seleccionado);
        $stmt_archivos->execute();
        $resultado_archivos = $stmt_archivos->get_result();

        while ($archivo = $resultado_archivos->fetch_assoc()) {
            $archivos_request[] = $archivo;
        }
        $stmt_archivos->close();
    }
}
?>

<div class="container my-5">
    
    <?php if ($data): ?>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <span class="badge bg-primary bg-opacity-10 text-primary mb-2">Request #<?php echo $data['id_requests']; ?></span>
            <h2 class="fw-bold text-dark mb-0">
                <?php echo htmlspecialchars($data['titulo']); ?>
            </h2>
        </div>
        </div>

    <div class="row g-4">
        
        <div class="col-lg-9">
            
            <h5 class="fw-bold mb-3 text-secondary" style="letter-spacing: 1px; font-size: 0.8rem;">DETALLES Y PRESUPUESTO</h5>
            
            <div class="row g-3 mb-4">
                
                <div class="col-md-4 col-sm-6">
                    <div class="detail-card">
                        <span class="detail-label">Carrera</span>
                        <div class="detail-value">
                            <span class="material-symbols-outlined text-primary" style="font-size: 18px;">license</span>
                            <span class="text-truncate" style="font-size: 0.9rem;"><?php echo htmlspecialchars($data['nombre_carrera']); ?></span>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-sm-6">
                    <div class="detail-card">
                        <span class="detail-label">Materia</span>
                        <div class="detail-value">
                            <span class="material-symbols-outlined text-info" style="font-size: 18px;">book_2</span>
                            <span class="text-truncate" style="font-size: 0.9rem;"><?php echo htmlspecialchars($data['nombre_materia']); ?></span>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-sm-6">
                    <div class="detail-card">
                        <span class="detail-label">Tipo</span>
                        <div class="detail-value">
                            <span class="material-symbols-outlined text-secondary" style="font-size: 18px;">type_specimen</span>
                            <span style="font-size: 0.9rem;"><?php echo htmlspecialchars($data['tipo_trabajo_nombre']); ?></span>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-sm-6">
                    <div class="detail-card">
                        <span class="detail-label">Publicado</span>
                        <div class="detail-value">
                            <span class="material-symbols-outlined text-muted" style="font-size: 18px;">calendar_today</span>
                            <span style="font-size: 0.9rem;">
                                <?php echo isset($data['fecha_creacion']) ? date('d/m/Y', strtotime($data['fecha_creacion'])) : 'N/A'; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-sm-6">
                    <div class="detail-card">
                        <span class="detail-label">Límite Entrega</span>
                        <div class="detail-value">
                            <span class="material-symbols-outlined text-danger" style="font-size: 18px;">event_busy</span>
                            <span style="font-size: 0.9rem;">
                                <?php echo isset($data['fecha_limite']) ? date('d/m/Y', strtotime($data['fecha_limite'])) : 'Sin fecha'; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-sm-6">
                    <div class="detail-card price-card">
                        <span class="detail-label text-success">Presupuesto</span>
                        <div class="detail-value text-success">
                            <span class="material-symbols-outlined" style="font-size: 18px;">payments</span>
                            <span style="font-size: 1.1rem; font-weight: 800;">
                                <?php echo '$' . number_format($data['precio'], 2, '.', ','); ?>
                            </span>
                        </div>
                    </div>
                </div>

            </div>

            <h5 class="fw-bold mb-3 text-secondary" style="letter-spacing: 1px; font-size: 0.8rem;">DESCRIPCIÓN DEL PROYECTO</h5>
            <div class="desc-box shadow-sm mb-4" style="min-height: 2.75rem;">
                <?php echo nl2br(htmlspecialchars($data['descripcion'])); ?>
            </div>

            <h5 class="fw-bold mb-3 text-secondary" style="letter-spacing: 1px; font-size: 0.8rem;">ARCHIVOS ADJUNTOS</h5>
            <div class="desc-box shadow-sm p-3">
                <?php if (!empty($archivos_request)): ?>
                    <?php 
                    $base_path = '../../'; 
                    ?>
                    <div class="row g-3">
                        <?php foreach ($archivos_request as $archivo): 
                            $nombre = htmlspecialchars($archivo['nombre_archivo']);
                            $url_db = htmlspecialchars($archivo['url_archivo']);
                            $tipo = $archivo['tipo_archivo'];
                            $ruta_completa = $base_path . $url_db; 

                            $icono_html = '';
                            if (strpos($tipo, 'image/') !== false) {
                                $icono_html = '<img src="' . $ruta_completa . '" alt="' . $nombre . '" class="img-fluid rounded" style="max-height: 100px; width: 100%; object-fit: cover;">';
                            } elseif (strpos($tipo, 'pdf') !== false) {
                                $icono_html = '<span class="material-symbols-outlined fs-2 text-danger">picture_as_pdf</span>';
                            } else {
                                $icono_html = '<span class="material-symbols-outlined fs-2 text-primary">attach_file</span>';
                            }
                        ?>
                            <div class="col-6 col-md-3">
                                <div class="card h-100 p-2 text-center border-0 shadow-sm">
                                    <div class="d-flex justify-content-center mb-1">
                                        <?php echo $icono_html; ?>
                                    </div>
                                    <p class="card-text small text-truncate mb-1" title="<?php echo $nombre; ?>"><?php echo $nombre; ?></p>
                                    <a href="<?php echo $ruta_completa; ?>" target="_blank" class="btn btn-sm btn-outline-secondary btn-block" style="font-size: 0.8rem;">Ver/Descargar</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">No se adjuntaron archivos a esta solicitud.</p>
                <?php endif; ?>
            </div>
            </div>

        <div class="col-lg-3">
            <div class="user-card-modern">
                <div class="report-btn" title="Reportar solicitud">
                    <span class="material-symbols-outlined" style="font-size: 20px;">flag</span>
                </div>

                <?php 
                $user_photo_url = (isset($data['url_foto_perfil']) && $data['url_foto_perfil'] && $data['url_foto_perfil'] !== 'public/img/default_avatar.jpg')
                                  ? '../../' . htmlspecialchars($data['url_foto_perfil'])
                                  : null;
                ?>
                <?php if ($user_photo_url): ?>
                    <img src="<?php echo $user_photo_url; ?>" alt="Foto de perfil de usuario" class="avatar-image shadow-sm">
                <?php else: ?>
                    <div class="avatar-placeholder shadow-sm">
                        <span class="fw-bold"><?php echo strtoupper(substr($data['nombre_usuario'], 0, 1)); ?></span>
                    </div>
                <?php endif; ?>
                <h6 class="fw-bold text-dark mb-1">
                    <?php echo htmlspecialchars($data['nombre_usuario']) . ' ' . htmlspecialchars($data['apellido_usuario']); ?>
                </h6>
                <p class="text-muted small mb-3">Solicitante</p>
                
                <div class="d-flex justify-content-center mb-4">
                    <?php
                    $rating = isset($data['rating']) ? floatval($data['rating']) : 0;
                    $estrellas_llenas = floor($rating);
                    $max_estrellas = 5;
                    ?>
                    <div class="star-rating d-flex align-items-center" style="font-size: 1.5rem; color: #adb5bd;"> 
                        <?php for ($i = 1; $i <= $max_estrellas; $i++): ?>
                            <?php if ($i <= $estrellas_llenas): ?>
                                <i class="bi bi-star-fill mx-1" style="color: #ffc107;"></i> 
                            <?php else: ?>
                                <i class="bi bi-star mx-1"></i>
                            <?php endif; ?>
                        <?php endfor; ?>
                        <span class="ms-3 fw-bold fs-5 text-dark"><?php echo number_format($rating, 1); ?> / 5</span>
                    </div>
                </div>
                <div class="d-flex justify-content-center mb-4">
                    <h6 style="font-size: 0.8rem;">Gigs Completados: <?php echo htmlspecialchars($data['porcentaje_completacion']); ?>%</h6>
                </div>

                <button class="btn btn-primary w-100 py-2 fw-bold shadow-sm rounded-pill">
                    ACEPTAR TRABAJO
                </button>
                
                <div class="mt-3 text-center">
                    <small class="text-muted" style="font-size: 0.75rem;">
                        <i class="bi bi-shield-check me-1"></i>Pago protegido
                    </small>
                </div>
            </div>
        </div>

    </div>
    
    <?php elseif ($id_request_seleccionado === null): ?>
        <div class="text-center py-5">
            <div class="mb-3">
                <span class="material-symbols-outlined text-muted" style="font-size: 64px;">search_off</span>
            </div>
            <h4 class="fw-light">No has seleccionado ninguna solicitud.</h4>
            <a href="index.php" class="btn btn-outline-primary mt-3 px-4 rounded-pill">Volver al inicio</a>
        </div>
        
    <?php else: ?>
        <div class="text-center py-5">
               <div class="mb-3">
                <span class="material-symbols-outlined text-danger" style="font-size: 64px;">error_outline</span>
            </div>
            <h4 class="text-danger">Solicitud no encontrada</h4>
            <p class="text-muted">El ID solicitado no existe o fue eliminado.</p>
            <a href="index.php" class="btn btn-secondary mt-3 px-4 rounded-pill">Volver</a>
        </div>
    <?php endif; ?>
</div>


<footer>
    <div id="Footer_Responsive" class="container-fluid bg-dark">
        <div class="row text-align-center p-5 d-md-none d-lg-none">
            <div class="accordion" id="accordionPanelsStayOpenExample" data-bs-theme="dark">
            <div class="accordion-item mb-2 border-0 border-bottom">
                <h2 class="accordion-header">
                <button class="accordion-button collapsed shadow-none bg-transparent text-white" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true" aria-controls="panelsStayOpen-collapseOne">
                    TEST
                </button>
                </h2>
                <div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse">
                <div class="accordion-body">
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                </div>
                </div>
            </div>
            <div class="accordion-item mb-2 border-0 border-bottom">
                <h2 class="accordion-header">
                <button class="accordion-button collapsed shadow-none bg-transparent text-white" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseTwo" aria-expanded="false" aria-controls="panelsStayOpen-collapseTwo">
                    TEST
                </button>
                </h2>
                <div id="panelsStayOpen-collapseTwo" class="accordion-collapse collapse">
                <div class="accordion-body">
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                </div>
                </div>
            </div>
            <div class="accordion-item mb-2 border-0 border-bottom">
                <h2 class="accordion-header">
                <button class="accordion-button collapsed shadow-none bg-transparent text-white" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseThree" aria-expanded="false" aria-controls="panelsStayOpen-collapseThree">
                    TEST
                </button>
                </h2>
                <div id="panelsStayOpen-collapseThree" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                    </div>
                </div>
            </div>
            <div class="mt-5">
                <p class="text-white text-center">&copy; 2025 Uni-Gigs. Todos los derechos reservados.</p>
                <div class="text-center">
                    <i class="bi bi-facebook fs-3 text-white p-3"></i>
                    <i class="bi bi-instagram fs-3 text-white p-3"></i>
                    <i class="bi bi-twitter-x fs-3 text-white p-3"></i>
                </div>
            </div>
        </div>
    </div>
    <div>
        <div id="Footer_Large" class="container-fluid">
            <div class="row bg-dark text-white text-center p-5 d-none d-md-flex d-lg-flex">
                <div class="col-lg-3 col-md-3">
                    <div class="Titulo d-flex justify-content-center align-items-center mb-3">
                        <img src="../../public/img/Isotipo_Blanco.png" alt="Logo" width="60" height="48" class="d-inline-block align-text-center me-2">
                        <span class="h3 mb-0">Uni-Gigs</span>                        
                    </div>
                    <div>
                        <i class="bi bi-facebook fs-2 text-white p-3"></i>
                        <i class="bi bi-instagram fs-2 text-white p-3"></i>
                        <i class="bi bi-twitter-x fs-2 text-white p-3"></i>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3">
                    <p class="h5">TEST</p>
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                    <div>
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3">
                    <p class="h5">TEST</p>
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                    <div>
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3">
                    <p class="h5">TEST</p>
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                    <div class="mb-2">
                        <a class="text-secondary text-decoration-none" href="#">TEST</a>
                    </div>
                </div>
                <div class="mt-3 border-top pt-3">
                    <p>&copy; 2025 Uni-Gigs. Todos los derechos reservados.</p>
                    <p>Av. Concepción Mariño, Sector El Toporo, El Valle del Espíritu Santo, Edo. Nueva Esparta, Venezuela.</p>
                </div>
                <div class="d-flex-center">
                    <a class="text-decoration-none text-white" href="#">Términos y condiciones</a>
                    <div class="vr mx-3 opacity-100"></div>
                    <a class="text-decoration-none text-white" href="#">Política de privacidad</a>
                    <div class="vr mx-3 opacity-100"></div>
                    <a class="text-decoration-none text-white" href="#">Cookies</a>
                </div>
            </div>
        </div>
    </div>
</footer>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Seleccionar todos los contenedores de estrellas
    const ratingContainers = document.querySelectorAll('.star-rating-display');

    ratingContainers.forEach(container => {
        // Obtener el valor del rating desde el atributo data-rating
        const rating = parseFloat(container.getAttribute('data-rating'));
        
        // Limpiar el contenido actual
        container.innerHTML = '';

        // Generar las 5 estrellas
        for (let i = 1; i <= 5; i++) {
            let iconName = 'star_border'; // Por defecto vacía
            let colorClass = 'text-secondary'; // Color gris por defecto

            if (rating >= i) {
                // Estrella completa
                iconName = 'star';
                colorClass = 'text-warning'; // Amarillo/Dorado (Bootstrap)
            } else if (rating >= i - 0.5) {
                // Media estrella
                iconName = 'star_half';
                colorClass = 'text-warning';
            }

            // Crear el elemento span para el icono
            const star = document.createElement('span');
            star.className = `material-symbols-outlined ${colorClass}`;
            star.textContent = iconName;
            
            // Ajustar tamaño si es necesario (opcional)
            star.style.fontSize = '28px'; 
            star.style.fontVariationSettings = "'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24";

            // Agregar al contenedor
            container.appendChild(star);
        }
    });
});
</script>
    
</body>
</html>