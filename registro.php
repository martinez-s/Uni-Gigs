<?php

session_start();
include('conect.php'); // Asegúrate de que este archivo existe y tiene la conexión $mysqli

if (isset($_POST['btn_finalizar'])) {

    // 1. Recogida de datos (limpiando espacios)
    $correo = trim($_POST['correo']);
    $fecha  = $_POST['fecha_nacimiento'];
    $clave  = $_POST['clave'];
    $clave2 = $_POST['clave_confirm'];
    
    // Datos del último modal
    $nombre = trim($_POST['nombre']);
    $apelli = trim($_POST['apellido']);
    $cedula = trim($_POST['cedula']); 
    
    // --- VALIDACIONES ---

    // A. Contraseñas iguales
    if ($clave !== $clave2) {
        echo "<script>alert('Las contraseñas no coinciden.'); window.history.back();</script>";
        exit;
    }

    // B. Verificar duplicados (Correo o Cédula) - VERSIÓN COMPATIBLE CON TU CONECT.PHP
    $sql_check = "SELECT id_usuario FROM usuarios WHERE correo = ? OR cedula = ?";
    $stmt = $mysqli->prepare($sql_check);
    $stmt->bind_param("ss", $correo, $cedula);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        echo "<script>alert('Error: El correo o la cédula ya están registrados.'); window.location.href='registro.php';</script>";
        exit;
    }
    $stmt->close();

    // --- SUBIDA DE ARCHIVOS ---

    $carpeta_destino = "public/img/imgusuarios/";
    
    // Crear carpeta si no existe
    if (!file_exists($carpeta_destino)) {
        mkdir($carpeta_destino, 0777, true);
    }

    $ruta_foto_bd = ""; // Valor por defecto

    // Foto Perfil
    if (isset($_FILES['imagen_perfil']) && $_FILES['imagen_perfil']['error'] === UPLOAD_ERR_OK) {
        $nombre_archivo = uniqid() . "_p_" . basename($_FILES['imagen_perfil']['name']);
        $ruta_completa = $carpeta_destino . $nombre_archivo;
        
        if (move_uploaded_file($_FILES['imagen_perfil']['tmp_name'], $ruta_completa)) {
            $ruta_foto_bd = $ruta_completa; // Esto se guarda en la BD
        }
    }

    // Foto Carnet
    if (isset($_FILES['imagen_carnet']) && $_FILES['imagen_carnet']['error'] === UPLOAD_ERR_OK) {
        $nombre_carnet = uniqid() . "_carnet_" . basename($_FILES['imagen_carnet']['name']);
        move_uploaded_file($_FILES['imagen_carnet']['tmp_name'], $carpeta_destino . $nombre_carnet);
    }

    // --- INSERCIÓN EN BD ---

    // Hash de contraseña


    // Valores por defecto
    $estado = 1;
    $rating = 0;
    $porcentaje = 0.00;
    $id_carrera = 1;

    // Consulta adaptada a MySQLi (?)
    $sql = "INSERT INTO usuarios 
            (nombre, apellido, fecha_nacimiento, clave, correo, cedula, url_foto_perfil, estado, rating, porcentaje_completacion, id_carrera) 
            VALUES 
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($insert = $mysqli->prepare($sql)) {
        // "sssssssiiid" define los tipos: s=string, i=int, d=double
        $insert->bind_param("sssssssiiid", 
            $nombre, 
            $apelli, 
            $fecha, 
            $clave, 
            $correo, 
            $cedula, 
            $ruta_foto_bd, 
            $estado, 
            $rating, 
            $porcentaje, 
            $id_carrera
        );

        if ($insert->execute()) {
            echo "<script>alert('¡Registro Exitoso! Ahora puedes iniciar sesión.'); window.location.href='login.php';</script>";
        } else {
            echo "Error al insertar: " . $insert->error;
        }
        $insert->close();
    } else {
        echo "Error en la consulta: " . $mysqli->error;
    }

}