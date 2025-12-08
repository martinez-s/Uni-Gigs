<?php
session_start();

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {

    header("Location: index.php"); 
    exit(); 
}

include('conect.php');

if (isset($_POST['btn_actualizar_tasa'])) {
    $nueva_tasa = $_POST['tasa_cambio'];
    $stmt = $mysqli->prepare("UPDATE configuracion SET tasa_dolar = ? WHERE id = 1");
    $stmt->bind_param("d", $nueva_tasa);
    if ($stmt->execute()) {
        $mensaje = "Tasa actualizada correctamente.";
        $tipo_mensaje = "success";
    }
    $stmt->close();
}

if (isset($_GET['accion']) && $_GET['accion'] == 'eliminar' && isset($_GET['id'])) {
    $id_borrar = intval($_GET['id']);
    try {
        if ($mysqli->query("DELETE FROM usuarios WHERE id_usuario = $id_borrar")) {
            $mensaje = "Usuario eliminado.";
            $tipo_mensaje = "success";
        } else {
            throw new Exception($mysqli->error);
        }
    } catch (Exception $e) {
        $mensaje = "No se puede eliminar: El usuario tiene datos asociados.";
        $tipo_mensaje = "error";
    }
}

if (isset($_GET['accion']) && $_GET['accion'] == 'cambiar_estado' && isset($_GET['id'])) {
    $id_estado = intval($_GET['id']);
    if ($mysqli->query("UPDATE usuarios SET estado = NOT estado WHERE id_usuario = $id_estado")) {
        $mensaje = "Estado actualizado.";
        $tipo_mensaje = "success";
    }
}

$res_tasa = $mysqli->query("SELECT tasa_dolar, ultima_actualizacion FROM configuracion WHERE id = 1");
$row_tasa = ($res_tasa) ? $res_tasa->fetch_assoc() : ['tasa_dolar' => 0, 'ultima_actualizacion' => date('Y-m-d')];
$tasa_actual = $row_tasa['tasa_dolar'];
$fecha_tasa = $row_tasa['ultima_actualizacion'];

$usuarios = $mysqli->query("SELECT id_usuario, nombre, apellido, correo, cedula, estado FROM usuarios ORDER BY id_usuario DESC");
$requests = $mysqli->query("SELECT r.id_requests, r.titulo, r.precio, r.fecha_creacion, u.nombre as autor FROM requests r JOIN usuarios u ON r.id_usuario = u.id_usuario ORDER BY r.fecha_creacion DESC");
$servicios = $mysqli->query("SELECT s.id_servicio, s.titulo, s.precio, s.fecha_creacion, u.nombre as autor FROM servicios s JOIN usuarios u ON s.id_usuario = u.id_usuario ORDER BY s.fecha_creacion DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Uni-Gigs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Montserrat', sans-serif; background-color: #f4f6f9; }
        .sidebar-content { background-color: #2F5597; height: 100%; color: white; display: flex; flex-direction: column; }
        .sidebar-links a { color: rgba(255,255,255,0.85); text-decoration: none; padding: 15px 20px; display: block; transition: 0.3s; border-left: 4px solid transparent; }
        .sidebar-links a:hover, .sidebar-links a.active { background-color: #1e3a6e; color: white; border-left: 4px solid #fff; }
        .sidebar-links i { margin-right: 10px; font-size: 1.1rem; }
        .desktop-sidebar { width: 260px; min-height: 100vh; flex-shrink: 0; display: none; }
        @media (min-width: 768px) { .desktop-sidebar { display: block; } }
        .card-tasa { background: linear-gradient(135deg, #2F5597 0%, #0d6efd 100%); color: white; border: none; border-radius: 15px; box-shadow: 0 4px 15px rgba(47, 85, 151, 0.3); }
        .card-table { border: none; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .table thead th { background-color: #2F5597; color: white; border: none; font-size: 0.85rem; white-space: nowrap; }
        .mobile-header { background-color: #2F5597; color: white; padding: 10px 15px; display: flex; align-items: center; justify-content: space-between; }
    </style>
</head>
<body>

<div class="d-flex w-100">
    <div class="desktop-sidebar">
        <div class="sidebar-content">
            <div class="p-4 text-center border-bottom border-secondary">
                <h4 class="fw-bold mb-0">Uni-Gigs <span class="badge bg-light text-primary fs-6">ADMIN</span></h4>
            </div>
            <div class="sidebar-links mt-3">
                <a href="#" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a>
                <a href="public/pages/principal.php"><i class="bi bi-box-arrow-left"></i> Volver a Web</a>
                <a href="logout.php" class="mt-auto border-top border-secondary"><i class="bi bi-power"></i> Cerrar Sesión</a>
            </div>
        </div>
    </div>

    <div class="offcanvas offcanvas-start bg-dark" tabindex="-1" id="mobileSidebar" style="width: 280px; border:none;">
        <div class="sidebar-content">
            <div class="p-4 d-flex justify-content-between align-items-center border-bottom border-secondary">
                <h4 class="fw-bold mb-0">Uni-Gigs</h4>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="sidebar-links mt-3">
                <a href="#" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a>
                <a href="public/pages/principal.php"><i class="bi bi-box-arrow-left"></i> Volver a Web</a>
                <a href="logout.php"><i class="bi bi-power"></i> Cerrar Sesión</a>
            </div>
        </div>
    </div>

    <div class="flex-grow-1" style="min-width: 0;">
        <div class="mobile-header d-md-none">
            <span class="fw-bold fs-5">Panel Admin</span>
            <button class="btn btn-sm btn-outline-light" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar"><i class="bi bi-list fs-4"></i></button>
        </div>

        <div class="p-3 p-md-4">
            <div class="row g-3 mb-4 align-items-center">
                <div class="col-12 col-lg-8">
                    <h2 class="fw-bold text-primary mb-1">Panel de Control</h2>
                    <p class="text-muted mb-0">Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Admin'); ?></p>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="card card-tasa p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1 text-white-50 text-uppercase" style="font-size: 0.8rem;">Tasa de Cambio</h6>
                                <h2 class="mb-0 fw-bold">Bs. <?php echo number_format($tasa_actual, 2); ?></h2>
                                <small style="font-size: 0.7rem; opacity: 0.8;">Última Act: <?php echo date("d/m H:i", strtotime($fecha_tasa)); ?></small>
                            </div>
                            <button class="btn btn-light text-primary fw-bold rounded-circle p-2 shadow-sm" style="width: 45px; height: 45px;" data-bs-toggle="modal" data-bs-target="#modalTasa"><i class="bi bi-pencil-fill"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-table bg-white">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <ul class="nav nav-tabs card-header-tabs flex-nowrap" style="overflow-x: auto; overflow-y: hidden;" id="myTab" role="tablist">
                        <li class="nav-item"><button class="nav-link active fw-bold text-nowrap" data-bs-toggle="tab" data-bs-target="#users"><i class="bi bi-people me-2"></i>Usuarios</button></li>
                        <li class="nav-item"><button class="nav-link fw-bold text-nowrap" data-bs-toggle="tab" data-bs-target="#requests"><i class="bi bi-basket me-2"></i>Pedidos</button></li>
                        <li class="nav-item"><button class="nav-link fw-bold text-nowrap" data-bs-toggle="tab" data-bs-target="#services"><i class="bi bi-briefcase me-2"></i>Servicios</button></li>
                    </ul>
                </div>

                <div class="card-body p-3 p-md-4">
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="users" role="tabpanel">
                            <div class="table-responsive">
                                <table id="tablaUsuarios" class="table table-hover align-middle w-100">
                                    <thead><tr><th>ID</th><th>Nombre</th><th>Cédula</th><th>Correo</th><th>Estado</th><th>Acciones</th></tr></thead>
                                    <tbody>
                                        <?php while($u = $usuarios->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $u['id_usuario']; ?></td>
                                            <td class="fw-bold text-primary"><?php echo $u['nombre'] . ' ' . $u['apellido']; ?></td>
                                            <td><?php echo $u['cedula']; ?></td>
                                            <td><?php echo $u['correo']; ?></td>
                                            <td>
                                                <span class="badge <?php echo ($u['estado'] == 1) ? 'bg-success' : 'bg-secondary'; ?> rounded-pill px-3">
                                                    <?php echo ($u['estado'] == 1) ? 'Activo' : 'Inactivo'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <button class="btn btn-sm btn-outline-primary" onclick="toggleEstado(<?php echo $u['id_usuario']; ?>, <?php echo $u['estado']; ?>)"><i class="bi <?php echo ($u['estado'] == 1) ? 'bi-eye-fill' : 'bi-eye-slash-fill'; ?>"></i></button>
                                                    <button class="btn btn-sm btn-outline-danger" onclick="eliminarUsuario(<?php echo $u['id_usuario']; ?>)"><i class="bi bi-trash-fill"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="requests" role="tabpanel">
                            <div class="table-responsive">
                                <table id="tablaRequests" class="table table-hover align-middle w-100">
                                    <thead><tr><th>ID</th><th>Título</th><th>Autor</th><th class="text-end">Precio ($)</th><th class="text-end">Ref (Bs)</th></tr></thead>
                                    <tbody>
                                        <?php while($r = $requests->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $r['id_requests']; ?></td>
                                            <td><?php echo $r['titulo']; ?></td>
                                            <td><?php echo $r['autor']; ?></td>
                                            <td class="text-end text-success fw-bold">$<?php echo number_format($r['precio'], 2); ?></td>
                                            <td class="text-end text-primary">Bs. <?php echo number_format($r['precio'] * $tasa_actual, 2); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="services" role="tabpanel">
                            <div class="table-responsive">
                                <table id="tablaServicios" class="table table-hover align-middle w-100">
                                    <thead><tr><th>ID</th><th>Título</th><th>Proveedor</th><th class="text-end">Precio ($)</th><th class="text-end">Ref (Bs)</th></tr></thead>
                                    <tbody>
                                        <?php while($s = $servicios->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $s['id_servicio']; ?></td>
                                            <td><?php echo $s['titulo']; ?></td>
                                            <td><?php echo $s['autor']; ?></td>
                                            <td class="text-end text-success fw-bold">$<?php echo number_format($s['precio'], 2); ?></td>
                                            <td class="text-end text-primary">Bs. <?php echo number_format($s['precio'] * $tasa_actual, 2); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTasa" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold fs-6">Actualizar Tasa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body text-center py-4">
                    <input type="number" step="0.01" name="tasa_cambio" min="0.01" class="form-control form-control-lg text-center fw-bold text-primary" value="<?php echo $tasa_actual; ?>" required>
                </div>
                <div class="modal-footer justify-content-center border-0 pt-0">
                    <button type="submit" name="btn_actualizar_tasa" class="btn btn-primary w-100 rounded-pill">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        const tableConfig = { language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }, responsive: true, pageLength: 5, dom: '<"row mb-3"<"col-md-6"f><"col-md-6 text-end"l>>rtip' };
        $('#tablaUsuarios').DataTable(tableConfig);
        $('#tablaRequests').DataTable(tableConfig);
        $('#tablaServicios').DataTable(tableConfig);
    });

    function eliminarUsuario(id) {
        Swal.fire({ title: '¿Eliminar usuario?', text: "No se podrá recuperar.", icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Sí, eliminar' }).then((r) => { if(r.isConfirmed) window.location.href = `admin.php?accion=eliminar&id=${id}`; });
    }

    function toggleEstado(id, estado) {
        let txt = estado == 1 ? "Desactivar" : "Activar";
        Swal.fire({ title: `¿${txt} usuario?`, icon: 'question', showCancelButton: true, confirmButtonText: 'Sí' }).then((r) => { if(r.isConfirmed) window.location.href = `admin.php?accion=cambiar_estado&id=${id}`; });
    }

    <?php if(isset($mensaje)): ?>
        Swal.fire({ icon: '<?php echo $tipo_mensaje; ?>', title: '<?php echo $mensaje; ?>', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
    <?php endif; ?>
</script>
</body>
</html>