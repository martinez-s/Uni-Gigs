<?php
// 1. INICIO DE SESIÓN
session_start();

// Variable para controlar qué ID vamos a consultar
$id_servicio_seleccionado = null;

// 2. LÓGICA DE OBTENCIÓN DE DATOS (POST vs SESSION)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_servicio'])) {
    // CASO A: Venimos del formulario POST
    $id_servicio = intval($_POST['id_servicio']);
    $id_usuario = intval($_POST['id_usuario']);
    $id_carrera = intval($_POST['id_carrera']);
    
    $_SESSION['current_service_id'] = $id_servicio;
    $_SESSION['service_user_id'] = $id_usuario;
    $_SESSION['service_carrera_id'] = $id_carrera;
    
    $id_servicio_seleccionado = $id_servicio;

} elseif (isset($_SESSION['current_service_id'])) {
    // CASO B: Recarga de página
    $id_servicio_seleccionado = $_SESSION['current_service_id'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uni-Gigs - Detalle Servicio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="stylesNav.css">
    
    <style>
        /* CSS ESPECÍFICO PARA EL DISEÑO DE DETALLES */
        .img-placeholder {
            background-color: #f8f9fa;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            min-height: 300px; /* Altura para la foto */
            display: flex;
            align-items: center;
            justify-content: center;
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
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #e9ecef;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #adb5bd;
            font-size: 2rem;
        }
        
        /* Ajuste de estrellas */
        .star-rating-display {
            display: inline-flex;
            align-items: center;
        }
    </style>
</head>
<body>
    
    <?php
    include('../../conect.php');
    include 'NavBar.php'; 
    
    $data = null; 

    // 3. CONSULTA SQL PARA SERVICIOS
    if ($id_servicio_seleccionado) {
        
        $sql = "SELECT 
            s.id_servicio, 
            s.titulo, 
            s.descripcion, 
            s.precio, 
            s.fecha_creacion, 
            -- s.fecha_limite,  <-- Los servicios suelen no tener fecha limite, lo dejo comentado o puedes usarlo como tiempo de entrega
            m.nombre AS nombre_materia, 
            tt.nombre AS tipo_trabajo_nombre,
            c.nombre_carrera, 
            u.rating, 
            u.porcentaje_completacion, 
            u.nombre AS nombre_usuario, 
            u.apellido AS apellido_usuario,
            MIN(fs.url_foto) AS url_foto
        FROM 
            servicios s
        JOIN 
            carreras c ON s.id_carrera = c.id_carrera
        JOIN 
            usuarios u ON s.id_usuario = u.id_usuario
        JOIN 
            materias m ON s.id_materia = m.id_materia
        JOIN 
            tipos_trabajos tt ON s.id_tipo_trabajo = tt.id_tipo_trabajo
        LEFT JOIN 
            fotos_servicios fs ON s.id_servicio = fs.id_servicio
        WHERE 
            s.id_servicio = ? 
        GROUP BY 
            s.id_servicio, s.titulo, s.descripcion, s.precio, s.fecha_creacion,
            m.nombre, tt.nombre, c.nombre_carrera, 
            u.rating, u.porcentaje_completacion, u.nombre, u.apellido";
            
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("i", $id_servicio_seleccionado);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $data = $resultado->fetch_assoc();
            $stmt->close();
        }
    }
    ?>

    <div class="container my-5">
    
    <?php if ($data): ?>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <span class="badge bg-success bg-opacity-10 text-success mb-2">Servicio #<?php echo $data['id_servicio']; ?></span>
            <h2 class="fw-bold text-dark mb-0">
                <?php echo htmlspecialchars($data['titulo']); ?>
            </h2>
        </div>
    </div>

    <div class="row g-4">
        
        <div class="col-lg-4">
            <div class="img-placeholder sticky-top" style="top: 20px; z-index: 1;">
                <?php if (!empty($data['url_foto'])): ?>
                    <img src="../../public/img/imgSer/<?php echo htmlspecialchars($data['url_foto']); ?>" alt="Foto del servicio" class="w-100 h-100" style="object-fit: cover;">
                <?php else: ?>
                    <div class="d-flex flex-column align-items-center text-muted">
                        <span class="material-symbols-outlined" style="font-size: 48px;">image</span>
                        <span class="mt-2 small">Ver imagen del servicio</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-5">
            
            <h5 class="fw-bold mb-3 text-secondary" style="letter-spacing: 1px; font-size: 0.8rem;">DETALLES DEL SERVICIO</h5>
            
            <div class="row g-3 mb-4">
                
                <div class="col-6">
                    <div class="detail-card">
                        <span class="detail-label">Carrera</span>
                        <div class="detail-value">
                            <span class="material-symbols-outlined text-primary" style="font-size: 18px;">license</span>
                            <span class="text-truncate"><?php echo htmlspecialchars($data['nombre_carrera']); ?></span>
                        </div>
                    </div>
                </div>

                <div class="col-6">
                    <div class="detail-card">
                        <span class="detail-label">Materia</span>
                        <div class="detail-value">
                            <span class="material-symbols-outlined text-info" style="font-size: 18px;">book_2</span>
                            <span class="text-truncate"><?php echo isset($data['nombre_materia']) ? htmlspecialchars($data['nombre_materia']) : 'General'; ?></span>
                        </div>
                    </div>
                </div>

                <div class="col-6">
                    <div class="detail-card">
                        <span class="detail-label">Tipo</span>
                        <div class="detail-value">
                            <span class="material-symbols-outlined text-secondary" style="font-size: 18px;">type_specimen</span>
                            <span class="text-truncate"><?php echo isset($data['tipo_trabajo_nombre']) ? htmlspecialchars($data['tipo_trabajo_nombre']) : 'Varios'; ?></span>
                        </div>
                    </div>
                </div>

                <div class="col-6">
                    <div class="detail-card">
                        <span class="detail-label">Publicado</span>
                        <div class="detail-value">
                            <span class="material-symbols-outlined text-muted" style="font-size: 18px;">calendar_today</span>
                            <span><?php echo isset($data['fecha_creacion']) ? date('d/m/Y', strtotime($data['fecha_creacion'])) : 'N/A'; ?></span>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="detail-card price-card d-flex justify-content-between align-items-center">
                        <div>
                            <span class="detail-label text-success">Precio del Servicio</span>
                            <div class="detail-value text-success">
                                <span style="font-size: 1.4rem; font-weight: 800;">
                                    <?php echo '$' . number_format($data['precio'], 2, '.', ','); ?>
                                </span>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-success" style="font-size: 32px;">payments</span>
                    </div>
                </div>

            </div>

            <h5 class="fw-bold mb-3 text-secondary" style="letter-spacing: 1px; font-size: 0.8rem;">ACERCA DEL SERVICIO</h5>
            <div class="desc-box shadow-sm" style="min-height: 100px;">
                <?php echo nl2br(htmlspecialchars($data['descripcion'])); ?>
            </div>

        </div>

        <div class="col-lg-3">
            <div class="user-card-modern">

                <?php 
                // 1. Verifica si existe el campo y si no está vacío.
                if (isset($data['url_foto_perfil']) && !empty($data['url_foto_perfil'])): 
                    
                    // Define la ruta completa de la imagen (ajusta la carpeta 'profiles' según tu estructura real)
                    $ruta_foto_perfil = "../../public/img/profiles/" . htmlspecialchars($data['url_foto_perfil']);
                ?>
                    <img src="<?php echo $ruta_foto_perfil; ?>" alt="Foto de perfil de usuario" class="avatar-image shadow-sm">

                <?php else: ?>
                    
                    <div class="avatar-placeholder shadow-sm mb-4" style="width: 120px; height: 120px;">
                        <span class="fw-bold"><?php echo strtoupper(substr($data['nombre_usuario'], 0, 1)); ?></span>
                    </div>

                <?php endif; ?>
                
                <h6 class="fw-bold text-dark mb-1">
                    <?php echo htmlspecialchars($data['nombre_usuario']) . ' ' . htmlspecialchars($data['apellido_usuario']); ?>
                </h6>
                <p class="text-muted small mb-3">Proveedor</p>
                
                <div class="d-flex justify-content-center mb-4">
                    <div class="star-rating-display" data-rating="<?php echo isset($data['rating']) ? htmlspecialchars($data['rating']) : 0; ?>"></div>
                </div>
                 <div class="d-flex justify-content-center mb-4">
                    <h6 style="font-size: 0.8rem;">Gigs Completados: <?php echo htmlspecialchars($data['porcentaje_completacion']); ?>%</h6>
                </div>

                <button class="btn btn-success w-100 py-2 fw-bold shadow-sm rounded-pill">
                    CONTACTAR
                </button>
                
                <div class="mt-3 text-center">
                    <small class="text-muted" style="font-size: 0.75rem;">
                        <i class="bi bi-shield-check me-1"></i>Garantía de servicio
                    </small>
                </div>
            </div>
        </div>

    </div>
    
    <?php elseif ($id_servicio_seleccionado === null): ?>
        <div class="text-center py-5">
            <div class="mb-3">
                <span class="material-symbols-outlined text-muted" style="font-size: 64px;">search_off</span>
            </div>
            <h4 class="fw-light">No has seleccionado ningún servicio.</h4>
            <a href="index.php" class="btn btn-outline-primary mt-3 px-4 rounded-pill">Volver al inicio</a>
        </div>
        
    <?php else: ?>
        <div class="text-center py-5">
             <div class="mb-3">
                <span class="material-symbols-outlined text-danger" style="font-size: 64px;">error_outline</span>
            </div>
            <h4 class="text-danger">Servicio no encontrado</h4>
            <p class="text-muted">El ID solicitado no existe o fue eliminado.</p>
            <a href="index.php" class="btn btn-secondary mt-3 px-4 rounded-pill">Volver</a>
        </div>
    <?php endif; ?>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const ratingContainers = document.querySelectorAll('.star-rating-display');
        ratingContainers.forEach(container => {
            let ratingVal = container.getAttribute('data-rating');
            const rating = ratingVal ? parseFloat(ratingVal) : 0;
            container.innerHTML = '';
            
            // Estilos inline para asegurar visualización
            container.style.display = 'inline-flex';
            container.style.alignItems = 'center';

            for (let i = 1; i <= 5; i++) {
                let iconName = 'star_border'; 
                let colorClass = 'text-secondary'; 
                if (rating >= i) { iconName = 'star'; colorClass = 'text-warning'; } 
                else if (rating >= i - 0.5) { iconName = 'star_half'; colorClass = 'text-warning'; }

                const star = document.createElement('span');
                star.className = `material-symbols-outlined ${colorClass}`;
                star.textContent = iconName;
                star.style.fontSize = '24px';
                star.style.marginRight = '-3px';
                star.style.userSelect = 'none';
                star.style.fontVariationSettings = "'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24";
                container.appendChild(star);
            }
        });
    });
    </script>

</body>
</html>




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
            star.style.fontSize = '20px'; 
            star.style.fontVariationSettings = "'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24";

            // Agregar al contenedor
            container.appendChild(star);
        }
    });
});
</script>

    
</body>
</html>