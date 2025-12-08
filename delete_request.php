<?php
session_start();
require_once 'conect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No has iniciado sesión.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$idRequest = $input['id'] ?? null;
$idUsuario = $_SESSION['id_usuario'];

if (!$idRequest) {
    echo json_encode(['success' => false, 'message' => 'ID inválido.']);
    exit;
}

$sql = "DELETE FROM requests WHERE id_requests = ? AND id_usuario = ?";
$stmt = $mysqli->prepare($sql);

if ($stmt) {
    $stmt->bind_param("ii", $idRequest, $idUsuario);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Request eliminado correctamente.']);
    } else {

        echo json_encode(['success' => false, 'message' => 'No se pudo eliminar (o no tienes permiso).']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Error de base de datos.']);
}
?>