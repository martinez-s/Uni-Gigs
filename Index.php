<?php
session_start();
include  'conect.php';
$error_message = $_SESSION['error'] ?? null;
$success_message = $_SESSION['success'] ?? null; 

unset($_SESSION['error']);
unset($_SESSION['success']);

include __DIR__ . '/app/includes/Navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="public/styles/styles.css">
    <title>Document</title>
</head>
<body>
    <div id="Inicio" class="banner-container">
        <div class="container">
            <div class="row align-items-center mt-5">
                <div class="col-md-6 mb-4 mb-md-0 mt-md-3 mt-5">
                    <h1 class="Titulo">Bienvenido a Uni-Gigs</h1>
                    <p class="lead text-justify">Uni-Gigs es una plataforma para estudiantes, donde hacemos los trabajos de los demás porque no nos queremos. 
                        Pero hey, al menos pagan por hacerlos.</p>
                </div>
                <div class="col-md-6 text-center">
                    <img src="public/img/img_banner.png" class="banner-img img-fluid" alt="Descripción de la imagen">
                </div>
            </div>
        </div>
    </div>
    <div id="Sobre_Nosotros" class="container mb-5">
        <h2 class="Titulo text-center mt-5">Sobre nosotros</h2>
        <div class="row mt-5 mb-5">
            <div class="col-md-6 ">
                <h3>¿Qué es Uni-Gigs?</h3>
                <p class="text-justify">Uni-Gigs es una plataforma diseñada para conectar a estudiantes que necesitan ayuda con sus tareas académicas con otros estudiantes 
                    dispuestos a ofrecer sus servicios. Nuestra misión es facilitar el intercambio de conocimientos y habilidades entre estudiantes, 
                    promoviendo un ambiente colaborativo y de apoyo mutuo.
                </p>
            </div>
            <div class="col-md-6">
                <h3>¿Cómo funciona?</h3>
                <p class="text-justify">Los usuarios pueden registrarse como "solicitantes" o "proveedores". Los solicitantes pueden publicar sus necesidades académicas, 
                    mientras que los proveedores pueden ofrecer sus servicios en diversas áreas de estudio. La plataforma permite la comunicación directa 
                    entre ambas partes, facilitando la negociación y el acuerdo sobre los términos del servicio.
                </p>
            </div>
        </div>
    </div>
    <div id="Funciones" class="container mb-5 mt-5">
        <h2 class="Titulo text-center mt-5">Funciones</h2>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mt-3">
            <div class="col">
                <div class="card h-100 shadow-sm border-0"> <img src="public/img/Relleno.jpg" class="card-img-top" alt="...">
                    <div class="card-body">
                        <h5 class="card-title">Card title</h5>
                        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card’s content.</p>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card h-100 shadow-sm border-0">
                    <img src="public/img/Relleno.jpg" class="card-img-top" alt="...">
                    <div class="card-body">
                        <h5 class="card-title">Card title</h5>
                        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card’s content.</p>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card h-100 shadow-sm border-0">
                    <img src="public/img/Relleno.jpg" class="card-img-top" alt="...">
                    <div class="card-body">
                        <h5 class="card-title">Card title</h5>
                        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card’s content.</p>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card h-100 shadow-sm border-0">
                    <img src="public/img/Relleno.jpg" class="card-img-top" alt="...">
                    <div class="card-body">
                        <h5 class="card-title">Card title</h5>
                        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card’s content.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Modales de Login -->
    <div class="modal fade" id="modal_login" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="conte_log">
                        <form action="login.php" method="post">
                            <h1 class="Titulo titu_modal">INICIA SESIÓN</h1>
                            <label for="correo" class="lb_modal">CORREO</label>
                            <br>
                            <input type="text" id="correo" name="correo" class="inputs">
                            <br>
                            <label for="clave" class="lb_modal">CONTRASEÑA</label>
                            <br>
                            <input type="password" id="clave" name="clave" class="inputs">
                            <a href="mailto:unigigs.admi@gmail.com?subject=Solicitud%20de%20Cambio%20de%20Contrase%F1a&body=IMPORTANTE:%20No%20modifique%20el%20formato%20de%20este%20correo.%0A%0APor%20favor,%20procesen%20el%20cambio%20de%20mi%20clave%20con%20los%20siguientes%20datos:%0A%0AC%E9dula%20de%20Identidad:%20[ESCRIBIR_AQUI]%0A%0ANueva%20Contrase%F1a:%20[ESCRIBIR_AQUI]" class="texto_log_cont"><p >¿OLVIDASTE LA CONTRASEÑA?</p></a>
                            <button type="submit" class="btn_inicio">INICIAR</button>
                            <a class="texto_log_regis" data-bs-toggle="modal" data-bs-target="#modal_registro"><p>¿NO TIENES CUENTA? REGISTRATE</p></a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- MODLALES DE REGISTRO -->
<form id="form_registro" action="registro.php" method="post" enctype="multipart/form-data">
    
    <div class="modal fade" id="modal_registro" aria-hidden="true" aria-labelledby="modal_registro" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container conte_regis">
                        <h1 class="Titulo titu_modal">REGISTRATE</h1>
                        <h2 class="lb_subtitulo text-center">CUENTANOS SOBRE TI, INGRESA TUS DATOS BÁSICOS</h2>
                        <div class="row">
                            <div class="col-lg-6 col-md-12">
                                <label for="correo" class="lb_modal">CORREO</label><br>
                                <input type="email" name="correo" class="form-control inputs" required placeholder="ejemplo@correo.com"><br>

                                <label for="clave" class="lb_modal">CONTRASEÑA</label><br>
                                <input type="password" name="clave" class="form-control inputs" required>
                            </div>
                            <div class="col-lg-6 col-md-12">
                                <label for="fecha_nacimiento" class="lb_modal">FECHA DE NACIMIENTO</label><br>
                                <input type="date" name="fecha_nacimiento" class="form-control inputs" required><br>

                                <label for="clave_confirm" class="lb_modal">CONFIRMAR CONTRASEÑA</label><br>
                                <input type="password" name="clave_confirm" class="form-control inputs" required>
                            </div>
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            <button type="button" class="btn_siguiente_r" onclick="validarPaso1()">SIGUIENTE</button>
                        </div>
                        <a class="texto_log_regis text-center d-block mt-2" data-bs-toggle="modal" data-bs-target="#modal_login" style="cursor: pointer;"><p>¿YA TIENES CUENTA? INICIA SESION</p></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal_registro2" aria-hidden="true" aria-labelledby="modal_registro2" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container conte_regis">
                        <h1 class="Titulo titu_modal">REGISTRATE</h1>
                        <h2 class="lb_subtitulo text-center">PERSONALIZA TU CUENTA</h2>
                        <div class="row">
                            <div class="col">
                                <div class="d-flex justify-content-center my-4">
                                    <label for="input-imagen-perfil" class="circulo-imagen-perfil" style="cursor: pointer; border: 2px dashed #ccc; width: 150px; height: 150px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                        <span class="texto-placeholder">Subir Foto</span>
                                    </label>
                                    <input type="file" id="input-imagen-perfil" name="imagen_perfil" accept="image/*" style="display: none;">
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-center">
                            <button type="button" class="btn_siguiente_r" data-bs-target="#modal_registro3" data-bs-toggle="modal">SIGUIENTE</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<div class="modal fade" id="modal_registro3" aria-hidden="true" aria-labelledby="modal_registro3" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container conte_regis">
                    <h1 class="Titulo titu_modal">REGISTRATE</h1>
                    <h2 class="lb_subtitulo text-center">VERIFICACION DE ESTUDIANTE</h2>
                    
                    <div id="ocr_loading" class="text-center mb-3" style="display:none;">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-primary fw-bold">Analizando carnet con IA...</p>
                    </div>

                    <div class="row">
                        <div class="col-lg-6 col-md-12 flex-column justify-content-center">
                            <div class="contenedor_carnet text-center">
                                <p class="p_carnet">SUBE UNA FOTO DE TU <br> CARNET ESTUDIANTIL</p>
                                <label for="input-imagen-carnet" class="imagen_carnet" style="cursor: pointer; border: 2px dashed #0d6efd; padding: 20px; display: block; background: #f8f9fa;">
                                    <i class="bi bi-camera-fill fs-2 text-primary"></i>
                                </label>
                                <input type="file" id="input-imagen-carnet" name="imagen_carnet" accept="image/*" style="display: none;" onchange="procesarCarnet(this)">
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-12 flex-column justify-content-center columna_regis3">
                            <label for="reg_nombre" class="lb_modal_carnet">NOMBRE</label><br>
                            <input type="text" name="nombre" id="reg_nombre" class="inputs_carnet form-control_carnet" required readonly style="background-color: #e9ecef;"><br>

                            <label for="reg_apellido" class="lb_modal_carnet">APELLIDO</label><br>
                            <input type="text" name="apellido" id="reg_apellido" class="inputs_carnet form-control_carnet" required readonly style="background-color: #e9ecef;"><br>

                            <label for="reg_cedula" class="lb_modal_carnet">CÉDULA</label><br>
                            <input type="text" name="cedula" id="reg_cedula" class="inputs_carnet form-control_carnet" required readonly style="background-color: #e9ecef;"><br>

                            <label for="reg_universidad" class="lb_modal_carnet">UNIVERSIDAD</label><br>
                            <input type="text" name="universidad" id="reg_universidad" class="inputs_carnet form-control_carnet" readonly style="background-color: #e9ecef;"><br>

                            <label for="reg_carrera" class="lb_modal_carnet">CARRERA</label><br>
                            <input type="text" name="carrera" id="reg_carrera" class="inputs_carnet form-control_carnet" required readonly style="background-color: #e9ecef;"><br>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" id="btn_finalizar" name="btn_finalizar" class="ocr-btn-submit" disabled style="opacity: 0.6; cursor: not-allowed;">
                            FINALIZAR REGISTRO
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</form>
    <div id="Registro" class="container-fluid p-5 bg-light">
        <div class="row">
            <div class="col">
                <div class="banner-footer text-center text-dark d-flex flex-column justify-content-center align-items-center">
                    <h2 class="Titulo">¿Listo para comenzar?</h2>
                    <p class="lead">Únete a Uni-Gigs hoy mismo y descubre cómo podemos ayudarte a alcanzar tus metas académicas.</p>
                    <a class="btn btn-dark btn-lg" data-bs-toggle="modal" data-bs-target="#modal_registro">REGISTRATE</a>
                </div>
            </div>
        </div>
    </div>

<?php include __DIR__ . '/app/includes/Footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="login_regis.js"></script> 

<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // --- 1. MANEJO DE ERRORES/ÉXITOS DESDE PHP ---
        <?php if ($error_message): ?>
            Swal.fire({
                icon: "error",
                title: "Atención",
                text: "<?php echo htmlspecialchars($error_message); ?>",
                confirmButtonColor: '#d33',
                confirmButtonText: 'Reintentar'
            }).then((result) => {
                // Si el error es de login, abrir modal login. Si es genérico, abrir el que corresponda.
                // Por defecto abrimos registro si falló algo del registro
                var registroModal = new bootstrap.Modal(document.getElementById('modal_registro'));
                registroModal.show();
            });
        <?php endif; ?>

        <?php if ($success_message): ?>
            Swal.fire({
                icon: "success",
                title: "¡Listo!",
                text: "<?php echo htmlspecialchars($success_message); ?>",
                confirmButtonColor: '#28a745',
            }).then((result) => {
                var loginModal = new bootstrap.Modal(document.getElementById('modal_login'));
                loginModal.show();
            });
        <?php endif; ?>
    });

    // --- 2. VALIDACIÓN DEL PASO 1 (BOTÓN SIGUIENTE) ---
    function validarPaso1() {
        // Obtenemos los inputs
        const correoInput = document.querySelector('#form_registro input[name="correo"]');
        const clave = document.querySelector('#form_registro input[name="clave"]');
        const fechaInput = document.querySelector('#form_registro input[name="fecha_nacimiento"]');
        const clave2 = document.querySelector('#form_registro input[name="clave_confirm"]');

        // Validaciones básicas de HTML (Campos vacíos)
        let esValido = true;
        if (!correoInput.checkValidity()) { correoInput.reportValidity(); esValido = false; } 
        else if (!clave.checkValidity()) { clave.reportValidity(); esValido = false; } 
        else if (!fechaInput.checkValidity()) { fechaInput.reportValidity(); esValido = false; } 
        else if (!clave2.checkValidity()) { clave2.reportValidity(); esValido = false; }

        if (!esValido) return;

        // A. Validar Dominio Unimar
        const correo = correoInput.value.trim().toLowerCase();
        if (!correo.endsWith("@unimar.edu.ve")) {
            Swal.fire({ icon: "error", title: "Correo incorrecto", text: "Solo se permite el registro con correo institucional (@unimar.edu.ve)." });
            return;
        }

        // B. Validar Edad (16 años)
        const fechaNacimiento = new Date(fechaInput.value);
        const hoy = new Date();
        let edad = hoy.getFullYear() - fechaNacimiento.getFullYear();
        const mesDiff = hoy.getMonth() - fechaNacimiento.getMonth();
        if (mesDiff < 0 || (mesDiff === 0 && hoy.getDate() < fechaNacimiento.getDate())) {
            edad--;
        }

        if (edad < 16) {
            Swal.fire({ icon: "warning", title: "Edad insuficiente", text: "Debes tener al menos 16 años para registrarte." });
            return;
        }

        // C. Validar Contraseñas
        if (clave.value !== clave2.value) {
            Swal.fire({ icon: "warning", title: "Contraseñas no coinciden", text: "Verifica que ambas contraseñas sean iguales." });
            return;
        }

        // Si todo está bien, pasamos al siguiente modal
        const modal1El = document.getElementById('modal_registro');
        const modal1 = bootstrap.Modal.getInstance(modal1El) || new bootstrap.Modal(modal1El);
        
        const modal2El = document.getElementById('modal_registro2');
        const modal2 = new bootstrap.Modal(modal2El);

        modal1.hide();
        modal2.show();
    }

async function procesarCarnet(input) {
    if (input.files && input.files[0]) {

        const btnFinalizar = document.getElementById('btn_finalizar');

        document.getElementById('ocr_loading').style.display = 'block';
        document.body.style.cursor = 'wait';
        btnFinalizar.disabled = true;
        btnFinalizar.style.opacity = '0.6';

        const formData = new FormData();
        formData.append('imagen_carnet', input.files[0]);

        try {
            const respuesta = await fetch('scanner_ajax.php', { method: 'POST', body: formData });
            const resultado = await respuesta.json();

            document.getElementById('ocr_loading').style.display = 'none';
            document.body.style.cursor = 'default';

            if (resultado.success) {
                const datos = resultado.data;

                document.getElementById('reg_nombre').value = datos.nombre || '';
                document.getElementById('reg_apellido').value = datos.apellido || '';
                document.getElementById('reg_cedula').value = datos.cedula || '';
                document.getElementById('reg_universidad').value = datos.universidad || '';
                document.getElementById('reg_carrera').value = datos.carrera || 'NO DETECTADA';

                btnFinalizar.disabled = false;
                btnFinalizar.style.opacity = '1';
                btnFinalizar.style.cursor = 'pointer';

                const correoInput = document.querySelector('#form_registro input[name="correo"]');
                const correoUsuario = correoInput.value.trim().toLowerCase();

                if (datos.nombre && datos.apellido && datos.cedula) {
                    const correoTeorico = construirCorreoUnimar(datos.nombre, datos.apellido, datos.cedula);
                    
                    if (correoUsuario !== correoTeorico) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Verifica tu correo',
                            html: `Tu correo (<b>${correoUsuario}</b>) difiere del formato del carnet (<b>${correoTeorico}</b>).<br>¿Corregir?`,
                            showCancelButton: true,
                            confirmButtonText: 'Sí, corregir',
                            cancelButtonText: 'No'
                        }).then((r) => { if (r.isConfirmed) correoInput.value = correoTeorico; });
                    } else {
                        Swal.fire({ icon: 'success', title: '¡Carnet Verificado!', text: 'Datos cargados correctamente. Ya puedes finalizar.' });
                    }
                }

                if (!datos.carrera || datos.carrera === 'NO DETECTADA') {
                     document.getElementById('reg_carrera').removeAttribute('readonly');
                     document.getElementById('reg_carrera').style.backgroundColor = '#fff'; 
                     Swal.fire('Atención', 'Por favor escribe tu carrera manualmente.', 'info');
                }

            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'No se pudo leer',
                    text: resultado.message || 'Intenta con una foto más clara.'
                });
            }

        } catch (error) {
            console.error(error);
            document.getElementById('ocr_loading').style.display = 'none';
            document.body.style.cursor = 'default';
            Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión.' });
        }
    }
}

    function construirCorreoUnimar(nombre, apellido, cedula) {
        const limpiar = (texto) => {
            return texto.normalize("NFD").replace(/[\u0300-\u036f]/g, "")
                        .replace(/Ñ/g, "n").replace(/ñ/g, "n")
                        .toLowerCase().trim();
        };
        const primerNombre = limpiar(nombre).split(" ")[0];
        const letraNombre = primerNombre.charAt(0);
        const primerApellido = limpiar(apellido).split(" ")[0];
        const soloNumeros = cedula.replace(/\D/g, ''); 
        const ultimos4 = soloNumeros.slice(-4);

        return `${letraNombre}${primerApellido}.${ultimos4}@unimar.edu.ve`;
    }
</script>
</body>
</html> 