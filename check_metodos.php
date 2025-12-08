

<?php


if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');


require_once 'conect.php'; 


$id_usuario = $_SESSION['id_usuario'] ?? 0;
$has_methods = false;

if ($id_usuario > 0 && isset($mysqli) && $mysqli instanceof mysqli) {

    function has_registered_payment_method($mysqli, $id_usuario) {

        $stmt = $mysqli->prepare("SELECT COUNT(*) FROM usuario_metodos_pago WHERE id_usuario = ? AND id_metodo = 1");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        return $count > 0;
    }

    $has_methods = has_registered_payment_method($mysqli, $id_usuario);
}

// Retornar el resultado para el JavaScript
echo json_encode(['success' => true, 'has_payment_method' => $has_methods]);

if (isset($mysqli) && $mysqli instanceof mysqli) {
    $mysqli->close(); // Mejor práctica: cerrar la conexión.
}
exit;
?>