<?php
session_start();
include('conect.php'); 

if (isset($_POST['btn_finalizar'])) {

    // 1. Recogida de datos
    $correo = trim($_POST['correo']);
    $fecha  = $_POST['fecha_nacimiento'];
    $clave  = $_POST['clave'];
    $clave2 = $_POST['clave_confirm'];
    
    // Datos del último modal
    $nombre = trim($_POST['nombre']);
    $apelli = trim($_POST['apellido']); // Ahora coincide con el HTML corregido
    $cedula = trim($_POST['cedula']);   // Ahora existe en el HTML
    
    // --- VALIDACIONES ---

    // A. Contraseñas iguales
    if ($clave !== $clave2) {
        $_SESSION['error'] = "Las contraseñas no coinciden.";
        header("Location: index.php");
        exit();
    }

    // B. Verificar duplicados
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

    // --- SUBIDA DE ARCHIVOS ---
    $carpeta_destino = "public/img/imgusuarios/";
    if (!file_exists($carpeta_destino)) {
        mkdir($carpeta_destino, 0777, true);
    }

    $ruta_foto_bd = "public/img/default_avatar.jpg"; // Imagen por defecto si no suben nada

    // Foto Perfil
    if (isset($_FILES['imagen_perfil']) && $_FILES['imagen_perfil']['error'] === UPLOAD_ERR_OK) {
        $nombre_archivo = uniqid() . "_p_" . basename($_FILES['imagen_perfil']['name']);
        $ruta_completa = $carpeta_destino . $nombre_archivo;
        
        if (move_uploaded_file($_FILES['imagen_perfil']['tmp_name'], $ruta_completa)) {
            $ruta_foto_bd = $ruta_completa;
        }
    }

    // Foto Carnet (Si necesitas guardarla en BD, agrega el campo a la consulta INSERT)
    if (isset($_FILES['imagen_carnet']) && $_FILES['imagen_carnet']['error'] === UPLOAD_ERR_OK) {
        $nombre_carnet = uniqid() . "_carnet_" . basename($_FILES['imagen_carnet']['name']);
        move_uploaded_file($_FILES['imagen_carnet']['tmp_name'], $carpeta_destino . $nombre_carnet);
    }

    // --- INSERCIÓN EN BD ---

    // 1. Hash de contraseña (IMPORTANTE PARA SEGURIDAD)
    $clave_hash = password_hash($clave, PASSWORD_DEFAULT);

    // Valores por defecto
    $estado = 1;
    $rating = 0;
    $porcentaje = 0.00;
    $id_carrera = 1; // Asegúrate de que este ID exista en tu tabla carreras

    $sql = "INSERT INTO usuarios 
            (nombre, apellido, fecha_nacimiento, clave, correo, cedula, url_foto_perfil, estado, rating, porcentaje_completacion, id_carrera) 
            VALUES 
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($insert = $mysqli->prepare($sql)) {
        // Usamos $clave_hash en lugar de $clave
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
    header("Location: index.php"); // Si intentan entrar directo a registro.php
}
?>