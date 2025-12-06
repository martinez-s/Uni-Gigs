<?php
session_start();
require_once __DIR__ . '/../includes/conect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit();
}

if (!isset($_POST['chat_id']) || !isset($_POST['message']) || empty($_POST['message'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$chat_id = intval($_POST['chat_id']);
$message = trim($_POST['message']);
$response = ['success' => false];

try {
    // Verificar que el usuario pertenece al chat
    $check_query = "SELECT * FROM chats WHERE id_chat = ? AND (id_usuario1 = ? OR id_usuario2 = ?)";
    $check_stmt = $mysqli->prepare($check_query);
    $check_stmt->bind_param('iii', $chat_id, $id_usuario, $id_usuario);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        $response['message'] = 'Acceso denegado';
        echo json_encode($response);
        exit();
    }
    $check_stmt->close();
    
    // Insertar mensaje
    $query = "INSERT INTO mensajes (contenido, tipo_mensaje, id_emisor, id_chat, fecha) 
              VALUES (?, 'texto', ?, ?, NOW())";
    
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('sii', $message, $id_usuario, $chat_id);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message_id'] = $stmt->insert_id;
        
        // Actualizar estado del chat a activo si estaba inactivo
        $update_query = "UPDATE chats SET estado = 1 WHERE id_chat = ?";
        $update_stmt = $mysqli->prepare($update_query);
        $update_stmt->bind_param('i', $chat_id);
        $update_stmt->execute();
        $update_stmt->close();
    } else {
        $response['message'] = 'Error al enviar mensaje';
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>