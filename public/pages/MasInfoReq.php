<?php
session_start();

// Incluir conexión a la base de datos al principio
include('../../conect.php');

$id_request_seleccionado = null;
$data = null; 
$id_usuario_req = null;
$id_usuario_ser = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_request'])) {
    
    $id_request = intval($_POST['id_request']);
    $id_usuario = intval($_POST['id_usuario']);
    $id_carrera = intval($_POST['id_carrera']);

    // ID del usuario que hizo el request (el que publicó)
    $id_usuario_ser = $id_usuario;
    // ID del usuario actual (logueado)
    $id_usuario_req = isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : null;

    $_SESSION['current_request_id'] = $id_request;
    $_SESSION['request_user_id'] = $id_usuario;
    $_SESSION['request_carrera_id'] = $id_carrera;
    
    $id_request_seleccionado = $id_request;

} elseif (isset($_SESSION['current_request_id'])) {
    // Recarga de página
    $id_request_seleccionado = $_SESSION['current_request_id'];
    $id_usuario_ser = isset($_SESSION['request_user_id']) ? $_SESSION['request_user_id'] : null;
    $id_usuario_req = isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : null;
} else {
    // No hay datos, redirigir
    header("Location: index.php");
    exit();
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
    
    <style>
        /* Estilos para la foto de perfil */
        .user-card-modern {
            background-color: #fff;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            position: sticky;
            top: 20px;
            text-align: center;
            border: 1px solid #f0f0f0;
        }
        
        .avatar-placeholder {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background-color: #e9ecef;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #adb5bd;
            font-size: 2.5rem;
            font-weight: bold;
        }
        
        .avatar-image {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 15px;
            border: 3px solid #fff;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .detail-card {
            background-color: #fff;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            height: 100%;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .detail-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            border-color: #dee2e6;
        }

        .detail-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            margin-bottom: 4px;
            display: block;
        }

        .detail-value {
            font-weight: 600;
            color: #212529;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .price-card {
            background-color: #f0fdf4;
            border-color: #bbf7d0;
        }
        
        .desc-box {
            background-color: #fff;
            border-left: 4px solid #203864;
            padding: 20px;
            border-radius: 4px;
            color: #495057;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <?php
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
            u.id_usuario,
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
            u.rating, u.porcentaje_completacion, u.nombre, u.apellido, u.url_foto_perfil, u.id_usuario";

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
                <?php 
                // Mostrar foto de perfil del usuario
                if (isset($data['url_foto_perfil']) && !empty($data['url_foto_perfil'])): 
                    $ruta_foto_perfil = "../../" . htmlspecialchars($data['url_foto_perfil']);
                ?>

                <div class="d-flex justify-content-center mb-4">
    
                <?php if (!empty($ruta_foto_perfil)): // Verificamos si existe la URL ?>
                    
                    <img src="<?php echo htmlspecialchars($ruta_foto_perfil); ?>" 
                        alt="Foto de perfil" 
                        class="shadow-sm"
                        style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover;">
                
                <?php else: ?>
                    
                    <div class="shadow-sm" 
                        style="width: 120px; height: 120px; border-radius: 50%; background-color: #e9ecef; color: #adb5bd; font-size: 3.5rem; display: flex; align-items: center; justify-content: center;">
                        
                        <span class="fw-bold">
                            <?php echo strtoupper(substr($data['nombre_usuario'], 0, 1)); ?>
                        </span>
                        
                    </div>
                    
                <?php endif; ?>
            </div>

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
                
                <?php 
                // Verificar si el usuario actual es diferente al que publicó el request
                if ($id_usuario_req && $id_usuario_req != $data['id_usuario']):
                    
                    // Verificar si ya existe un chat activo
                    $sql_chatViejo = "SELECT id_chat FROM chats WHERE ((id_usuario1 = ? AND id_usuario2 = ?) OR (id_usuario1 = ? AND id_usuario2 = ?)) AND (estado = TRUE)";
                    $stmt_chatViejo = $mysqli->prepare($sql_chatViejo);
                    $stmt_chatViejo->bind_param("iiii", $id_usuario_req, $data['id_usuario'], $data['id_usuario'], $id_usuario_req);
                    $stmt_chatViejo->execute();
                    $result_chatViejo = $stmt_chatViejo->get_result();

                    if($result_chatViejo->num_rows > 0):
                ?>
                        <div class="alert alert-info text-center" role="alert">
                            Ya tienes un chat activo con este usuario.
                        </div>
                    <?php else: ?>
                        <a href="asociarChatReq.php?id_usuario=<?php echo urlencode($data['id_usuario']); ?>&id_request=<?php echo urlencode($data['id_requests']); ?>">
                            <button class="btn btn-primary w-100 py-2 fw-bold shadow-sm rounded-pill">ACEPTAR TRABAJO</button>
                        </a>
                    <?php endif; 
                    $stmt_chatViejo->close();
                else: 
                    // Usuario ve su propio request
                ?>
                    <div class="alert alert-info text-center" role="alert">
                        ES TU PROPIA SOLICITUD.
                    </div>
                <?php endif; ?>
                
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
    <?php endif; ?>
</div>
</body>
</html>
