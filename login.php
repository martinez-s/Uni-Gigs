<?php
session_start();
include('conect.php'); 

const LOGIN_PAGE = "Index.php"; 
const SUCCESS_REDIRECT_PAGE = "public/pages/principal.php"; 

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

$sql = "SELECT id_usuario, correo, clave FROM usuarios WHERE correo = ?";
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

        session_regenerate_id(true); 

        $_SESSION['id_usuario'] = $row['id_usuario'];
        $_SESSION['correo']     = $row['correo'];
        $_SESSION['success']    = "Inicio de sesión exitoso";

        $stmt_admin = $mysqli->prepare("SELECT COUNT(*) FROM administradores WHERE id_usuario = ?");

        $stmt->close();

        header("Location: " . SUCCESS_REDIRECT_PAGE);
        exit();
    }
}

$_SESSION['error'] = "El usuario o la contraseña son incorrectos.";

if (isset($stmt)) {
    $stmt->close();
}

header("Location: " . LOGIN_PAGE);
exit();
?>