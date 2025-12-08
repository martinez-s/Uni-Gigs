<?php 
session_start();

// 1. CONEXIÓN Y VALIDACIÓN DE SESIÓN
require_once __DIR__ . '/../../conect.php';

if (!isset($conn) && isset($mysqli) && $mysqli instanceof mysqli) {
    $conn = $mysqli;
}

if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Error interno de conexión.");
}

// Verificar sesión
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../../login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario']; // ID REAL DEL USUARIO

// Obtener nombre del usuario (Opcional, para validación visual)
$stml_usuario = $conn->prepare("SELECT nombre FROM usuarios WHERE id_usuario = ?");
$stml_usuario->bind_param("i", $id_usuario);
$stml_usuario->execute();
$stml_usuario->close();



$sql_gigs_servicios = "
    SELECT 
        g.id_gig, g.estado AS gig_estado,
        s.id_servicio AS id_item, 'servicio' AS tipo, 
        s.id_usuario, s.id_carrera, s.titulo, s.descripcion, s.precio,
        c.nombre_carrera, u.rating, u.porcentaje_completacion,
        MIN(f.url_foto) AS url_foto
    FROM gigs g
    JOIN servicios s ON g.id_servicio_request = s.id_servicio
    JOIN carreras c ON s.id_carrera = c.id_carrera
    JOIN usuarios u ON s.id_usuario = u.id_usuario
    LEFT JOIN fotos_servicios f ON s.id_servicio = f.id_servicio
    WHERE g.id_prestador = ? AND g.id_request IS NULL
    GROUP BY g.id_gig, g.estado, s.id_servicio, s.id_usuario, s.id_carrera, s.titulo, s.descripcion, s.precio, c.nombre_carrera, u.rating, u.porcentaje_completacion
";

$sql_gigs_requests = "
    SELECT 
        g.id_gig, g.estado AS gig_estado,
        r.id_requests AS id_item, 'request' AS tipo, 
        r.id_usuario, r.id_carrera, r.titulo, r.descripcion, r.precio,
        c.nombre_carrera, u.rating, u.porcentaje_completacion,
        MIN(f.url_foto) AS url_foto
    FROM gigs g
    JOIN requests r ON g.id_request = r.id_requests
    JOIN carreras c ON r.id_carrera = c.id_carrera
    JOIN usuarios u ON r.id_usuario = u.id_usuario
    LEFT JOIN fotos_requests f ON r.id_requests = f.id_request
    WHERE g.id_prestador = ? AND g.id_servicio_request IS NULL
    GROUP BY g.id_gig, g.estado, r.id_requests, r.id_usuario, r.id_carrera, r.titulo, r.descripcion, r.precio, c.nombre_carrera, u.rating, u.porcentaje_completacion
";

$sql_query_completa = "($sql_gigs_servicios) UNION ALL ($sql_gigs_requests) ORDER BY gig_estado DESC, id_item DESC";

// Arreglos para separar los datos
$gigs_en_curso = [];
$gigs_terminados = [];

if ($stmt = $conn->prepare($sql_query_completa)) {
    $stmt->bind_param("ii", $id_usuario, $id_usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    while ($row = $resultado->fetch_assoc()) {
        // LÓGICA DE SEPARACIÓN:
        // Si gig_estado es 1 -> En Curso
        // Si es 0, 2, o cualquier otro -> Terminados
        if ($row['gig_estado'] == 1) {
            $gigs_en_curso[] = $row;
        } else {
            $gigs_terminados[] = $row;
        }
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="StylesNav.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Mis Gigs - Uni-Gigs</title>
</head>
<body>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="main.js"></script>
    
    <?php include 'NavBar.php'; ?>

    <div id="Resultados" class="banner-container">
        <div class="container-fluid px-5">
            <div class="row align-items-center mt-5 mb-4">
                <div class="col-md-12">
                    <h4 class="Titulo mb-0">Gestión de mis Gigs (Proveedor)</h4> 
                    <hr>
                </div>
            </div>

            <ul class="nav nav-tabs mb-4" id="gigsTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold" id="curso-tab" data-bs-toggle="tab" data-bs-target="#curso-pane" type="button" role="tab" aria-controls="curso-pane" aria-selected="true">
                        <i class="bi bi-hourglass-split me-2"></i>En Curso 
                        <span class="badge bg-primary ms-1"><?php echo count($gigs_en_curso); ?></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold text-secondary" id="terminados-tab" data-bs-toggle="tab" data-bs-target="#terminados-pane" type="button" role="tab" aria-controls="terminados-pane" aria-selected="false">
                        <i class="bi bi-check-circle-fill me-2"></i>Terminados
                        <span class="badge bg-secondary ms-1"><?php echo count($gigs_terminados); ?></span>
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="gigsTabContent">
                
                <div class="tab-pane fade show active" id="curso-pane" role="tabpanel" aria-labelledby="curso-tab" tabindex="0">
                    <div class="row">
                        <?php 
                        if (count($gigs_en_curso) > 0) {
                            foreach ($gigs_en_curso as $row) {
                                renderizarCardGig($row); // Llamamos a la función
                            }
                        } else {
                            echo '<div class="col-12"><div class="alert alert-info">No tienes Gigs en curso actualmente.</div></div>';
                        }
                        ?>
                    </div>
                </div>

                <div class="tab-pane fade" id="terminados-pane" role="tabpanel" aria-labelledby="terminados-tab" tabindex="0">
                    <div class="row">
                        <?php 
                        if (count($gigs_terminados) > 0) {
                            foreach ($gigs_terminados as $row) {
                                renderizarCardGig($row); // Llamamos a la función
                            }
                        } else {
                            echo '<div class="col-12"><div class="alert alert-secondary">No tienes Gigs terminados en el historial.</div></div>';
                        }
                        ?>
                    </div>
                </div>

            </div> </div> 
    </div>

    <?php
    // ==========================================================
    // FUNCIÓN DE RENDERIZADO (Reutilizable)
    // ==========================================================
    function renderizarCardGig($row) {
        $es_servicio = ($row['tipo'] === 'servicio');
        $es_request = ($row['tipo'] === 'request');
        $tiene_foto = !empty($row['url_foto']);
        $es_en_curso = ($row['gig_estado'] == 1);
        ?>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">
            <div class="card h-100 <?php echo $es_en_curso ? 'border-primary' : 'border-light bg-light'; ?> shadow-sm">
                <div class="card-body d-flex flex-column">
                    
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="card-title text-truncate" style="max-width: 80%;" title="<?php echo htmlspecialchars($row['titulo']); ?>">
                            <?php echo htmlspecialchars($row['titulo']); ?>
                        </h5>
                        <?php if ($es_en_curso): ?>
                            <span class="badge bg-success bg-opacity-10 text-success" title="En Curso"><i class="bi bi-circle-fill" style="font-size: 8px;"></i></span>
                        <?php else: ?>
                            <span class="badge bg-secondary" title="Terminado">Fin</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="separator-line mb-3"></div>
                    
                    <div class="img-wrapper mb-3 rounded overflow-hidden bg-white border" style="height: 150px; display: flex; align-items: center; justify-content: center;">
                        <?php 
                        if ($tiene_foto) { 
                            $folder = $es_servicio ? 'imgSer' : 'imgReq';
                            $ruta_imagen = "../../public/img/{$folder}/" . htmlspecialchars($row['url_foto']);
                            echo '<img src="'.$ruta_imagen.'" alt="Foto" style="width: 100%; height: 100%; object-fit: cover;">';
                        } else {
                            echo '<span class="material-symbols-outlined text-muted" style="font-size: 40px;">image_not_supported</span>';
                        } 
                        ?>
                    </div>

                    <div class="d-flex gap-2 mb-2">
                        <span class="badge <?php echo $es_servicio ? 'bg-info text-dark' : 'bg-warning text-dark'; ?>">
                            <?php echo $es_servicio ? 'Servicio' : 'Request'; ?>
                        </span>
                        <span class="badge bg-light text-secondary border">
                             <i class="bi bi-mortarboard-fill me-1"></i><?php echo htmlspecialchars($row['nombre_carrera']); ?>
                        </span>
                    </div>
                    
                    <p class="card-text flex-grow-1 text-muted small">
                        <?php echo mb_strimwidth(htmlspecialchars($row['descripcion']), 0, 80, "..."); ?>
                    </p>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3 mt-2"> 
                        <?php if (!empty($row['rating'])): ?>
                            <div class="star-rating-display" data-rating="<?php echo htmlspecialchars($row['rating']); ?>"></div>
                        <?php else: ?>
                            <small class="text-muted">Sin calificar</small>
                        <?php endif; ?>

                        <h5 class="mb-0 text-primary fw-bold">
                            <?php echo !empty($row['precio']) ? '$'.htmlspecialchars($row['precio']) : 'N/A'; ?>
                        </h5> 
                    </div>
                    
                    <form action="DetalleGig.php" method="POST" class="mt-auto">
                        <input type="hidden" name="id_gig" value="<?php echo htmlspecialchars($row['id_gig']); ?>">
                        <button type="submit" class="btn <?php echo $es_en_curso ? 'btn-primary' : 'btn-outline-secondary'; ?> w-100">
                            <?php echo $es_en_curso ? 'Gestionar Gig' : 'Ver Historial'; ?>
                        </button>
                    </form>
                </div> 
            </div> 
        </div>
    <?php
    } 
    ?>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const ratingContainers = document.querySelectorAll('.star-rating-display');
        ratingContainers.forEach(container => {
            let ratingVal = container.getAttribute('data-rating');
            const rating = ratingVal ? parseFloat(ratingVal) : 0;
            container.innerHTML = '';
            container.style.display = 'inline-flex';
            for (let i = 1; i <= 5; i++) {
                let iconName = rating >= i ? 'star' : (rating >= i - 0.5 ? 'star_half' : 'star_border');
                let colorClass = rating >= i - 0.5 ? 'text-warning' : 'text-secondary';
                const star = document.createElement('span');
                star.className = `material-symbols-outlined ${colorClass}`;
                star.textContent = iconName;
                star.style.fontSize = '18px';
                container.appendChild(star);
            }
        });
    });
    </script>
</body>
</html>