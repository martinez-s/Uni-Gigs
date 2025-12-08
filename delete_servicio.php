<?php

session_start();
header('Content-Type: application/json; charset=utf-8');

$response = ['success' => false, 'message' => 'Error desconocido'];

try {
    if (!file_exists('conect.php')) {
        throw new Exception('No se encuentra el archivo de conexión.');
    }
    require_once 'conect.php';

    if (!isset($_SESSION['id_usuario'])) {
        throw new Exception('Sesión expirada.');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $idServicio = $input['id'] ?? null;
    $idUsuario = $_SESSION['id_usuario'];

    if (!$idServicio) {
        throw new Exception('ID de servicio no válido.');
    }

    $mysqli->query("DELETE FROM fotos_servicios WHERE id_servicio = $idServicio");

    $sql = "DELETE FROM servicios WHERE id_servicio = ? AND id_usuario = ?";
    $stmt = $mysqli->prepare($sql);

    if (!$stmt) {
        throw new Exception("Error SQL: " . $mysqli->error);
    }

    $stmt->bind_param("ii", $idServicio, $idUsuario);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $response = ['success' => true, 'message' => 'Servicio eliminado correctamente.'];
    } else {
        throw new Exception('No se pudo eliminar. Puede que no exista o no sea tuyo.');
    }
    $stmt->close();

} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

ob_clean(); 
echo json_encode($response);
exit;
?>