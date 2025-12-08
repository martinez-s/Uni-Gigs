<?php
session_start();
include('conect.php'); 


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


    if (password_verify($clave, $row['clave'])) {


        if ($row['estado'] == 0) {
            $_SESSION['error'] = "Tu cuenta está desactivada. Contacta soporte.";
            header("Location: " . LOGIN_PAGE);
            exit();
        }


        session_regenerate_id(true); 

        $_SESSION['id_usuario'] = $row['id_usuario'];
        $_SESSION['correo']     = $row['correo'];
        $_SESSION['nombre']     = $row['nombre'];
        

        $stmt->close();


        $stmt_admin = $mysqli->prepare("SELECT id_admin FROM administradores WHERE id_usuario = ?");
        $stmt_admin->bind_param("i", $row['id_usuario']);
        $stmt_admin->execute();
        $stmt_admin->store_result();

        if ($stmt_admin->num_rows > 0) {

            $_SESSION['rol'] = 'admin';
            $_SESSION['success'] = "Bienvenido, Administrador.";
            

            header("Location: admin.php"); 

        } else {

            $_SESSION['rol'] = 'usuario';
            $_SESSION['success'] = "¡Hola de nuevo!";
            

            header("Location: public/pages/principal.php"); 
        }

        $stmt_admin->close(); 
        exit();
    }
}


$_SESSION['error'] = "Credenciales incorrectas.";
if (isset($stmt)) $stmt->close();

header("Location: " . LOGIN_PAGE);
exit();
?>