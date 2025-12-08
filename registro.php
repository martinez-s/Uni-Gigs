<?php
session_start();
include('conect.php'); 

if (isset($_POST['btn_finalizar'])) {

    $correo = trim($_POST['correo']);
    $fecha  = $_POST['fecha_nacimiento'];
    $clave  = $_POST['clave'];
    $clave2 = $_POST['clave_confirm'];

    $nombre = trim($_POST['nombre']);
    $apelli = trim($_POST['apellido']); 
    $cedula = trim($_POST['cedula']);

    $nombre_carrera_post = trim($_POST['carrera']);
    $id_carrera = null;

    $sql_carrera = "SELECT id_carrera FROM carreras WHERE nombre_carrera LIKE ? LIMIT 1";
    if ($stmt_c = $mysqli->prepare($sql_carrera)) {
        $param_carrera = "%" . $nombre_carrera_post . "%"; 
        $stmt_c->bind_param("s", $param_carrera);
        $stmt_c->execute();
        $stmt_c->bind_result($id_encontrado);
        if ($stmt_c->fetch()) {
            $id_carrera = $id_encontrado;
        }
        $stmt_c->close();
    }

    if ($id_carrera === null) {
        $_SESSION['error'] = "Error: La carrera detectada (" . htmlspecialchars($nombre_carrera_post) . ") no coincide con ninguna en nuestro sistema. Por favor intenta escanear de nuevo.";
        header("Location: index.php");
        exit();
    }

    if (substr(strtolower($correo), -14) !== "@unimar.edu.ve") {
        $_SESSION['error'] = "El correo debe pertenecer al dominio @unimar.edu.ve";
        header("Location: index.php");
        exit();
    }

    $fecha_nac_obj = new DateTime($fecha);
    $hoy_obj       = new DateTime();
    $edad_obj      = $hoy_obj->diff($fecha_nac_obj);

    if ($edad_obj->y < 16) {
        $_SESSION['error'] = "Lo sentimos, debes tener al menos 16 años para registrarte.";
        header("Location: index.php");
        exit();
    }

    if ($clave !== $clave2) {
        $_SESSION['error'] = "Las contraseñas no coinciden.";
        header("Location: index.php");
        exit();
    }

    $sql_check = "SELECT id_usuario FROM usuarios WHERE correo = ? OR cedula = ?";
    $stmt = $mysqli->prepare($sql_check);
    $stmt->bind_param("ss", $correo, $cedula);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $_SESSION['error'] = "El correo o la cédula ya están registrados.";
        header("Location: index.php");
        exit();
    }
    $stmt->close();

    $carpeta_destino = "public/img/imgusuarios/";
    if (!file_exists($carpeta_destino)) {
        mkdir($carpeta_destino, 0777, true);
    }

    $ruta_foto_bd = "public/img/default_avatar.jpg";

    if (isset($_FILES['imagen_perfil']) && $_FILES['imagen_perfil']['error'] === UPLOAD_ERR_OK) {
        $nombre_archivo = uniqid() . "_p_" . basename($_FILES['imagen_perfil']['name']);
        $ruta_completa = $carpeta_destino . $nombre_archivo;
        
        if (move_uploaded_file($_FILES['imagen_perfil']['tmp_name'], $ruta_completa)) {
            $ruta_foto_bd = $ruta_completa;
        }
    }

    if (isset($_FILES['imagen_carnet']) && $_FILES['imagen_carnet']['error'] === UPLOAD_ERR_OK) {
        $nombre_carnet = uniqid() . "_carnet_" . basename($_FILES['imagen_carnet']['name']);
        move_uploaded_file($_FILES['imagen_carnet']['tmp_name'], $carpeta_destino . $nombre_carnet);
    }

    $clave_hash = password_hash($clave, PASSWORD_DEFAULT);

    $estado = 1;
    $rating = 0;
    $porcentaje = 0.00;
    $sql = "INSERT INTO usuarios 
            (nombre, apellido, fecha_nacimiento, clave, correo, cedula, url_foto_perfil, estado, rating, porcentaje_completacion, id_carrera) 
            VALUES 
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($insert = $mysqli->prepare($sql)) {
        $insert->bind_param("sssssssiiid", 
            $nombre, $apelli, $fecha, $clave_hash, $correo, $cedula, 
            $ruta_foto_bd, $estado, $rating, $porcentaje, $id_carrera
        );

        if ($insert->execute()) {
            $_SESSION['success'] = "¡Registro Exitoso! Inicia sesión.";
            header("Location: index.php");
        } else {
            $_SESSION['error'] = "Error en base de datos: " . $insert->error;
            header("Location: index.php");
        }
        $insert->close();
    } else {
        $_SESSION['error'] = "Error preparando la consulta.";
        header("Location: index.php");
    }

} else {
    header("Location: index.php");
}
?>