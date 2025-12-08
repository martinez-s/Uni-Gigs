<?php
session_start();
include('conect.php'); 

// Página del formulario de Login (por si hay error)
const LOGIN_PAGE = "Index.php"; 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . LOGIN_PAGE);
    exit();
}

$correo = trim($_POST['correo'] ?? '');
$clave  = $_POST['clave'] ?? '';

if (empty($correo) || empty($clave)) {
    $_SESSION['error'] = "Por favor, complete todos los campos.";
    header("Location: " . LOGIN_PAGE);
    exit();
}

// 1. BUSCAMOS AL USUARIO
$sql = "SELECT id_usuario, correo, clave, nombre, estado FROM usuarios WHERE correo = ?";
$stmt = $mysqli->prepare($sql);

if (!$stmt) {
    $_SESSION['error'] = "Error interno del sistema.";
    header("Location: " . LOGIN_PAGE);
    exit();
}

$stmt->bind_param("s", $correo);
$stmt->execute();
$resultado = $stmt->get_result();

if ($row = $resultado->fetch_assoc()) {

    // 2. VERIFICAMOS LA CONTRASEÑA
    if (password_verify($clave, $row['clave'])) {

        // 3. VERIFICAMOS SI ESTÁ ACTIVO
        if ($row['estado'] == 0) {
            $_SESSION['error'] = "Tu cuenta está desactivada. Contacta soporte.";
            header("Location: " . LOGIN_PAGE);
            exit();
        }

        // --- INICIO DE SESIÓN EXITOSO ---
        session_regenerate_id(true); 

        $_SESSION['id_usuario'] = $row['id_usuario'];
        $_SESSION['correo']     = $row['correo'];
        $_SESSION['nombre']     = $row['nombre'];
        
        // Liberamos la consulta del usuario
        $stmt->close();

        // 4. DECISIÓN DE RUTA (EL SEMÁFORO)
        // Preguntamos: "¿Este usuario está en la lista de admins?"
        $stmt_admin = $mysqli->prepare("SELECT id_admin FROM administradores WHERE id_usuario = ?");
        $stmt_admin->bind_param("i", $row['id_usuario']);
        $stmt_admin->execute();
        $stmt_admin->store_result();

        if ($stmt_admin->num_rows > 0) {
            // === CASO 1: ES ADMIN ===
            $_SESSION['rol'] = 'admin'; // Le damos el pase VIP
            $_SESSION['success'] = "Bienvenido, Administrador.";
            
            // REDIRECCIÓN A LA VISTA DE ADMIN
            header("Location: admin.php"); 

        } else {
            // === CASO 2: ES ESTUDIANTE NORMAL ===
            $_SESSION['rol'] = 'usuario';
            $_SESSION['success'] = "¡Hola de nuevo!";
            
            // REDIRECCIÓN A LA VISTA PRINCIPAL
            header("Location: public/pages/principal.php"); 
        }

        $stmt_admin->close(); 
        exit();
    }
}

// Si llega aquí, falló el correo o la clave
$_SESSION['error'] = "Credenciales incorrectas.";
if (isset($stmt)) $stmt->close();

header("Location: " . LOGIN_PAGE);
exit();
?>