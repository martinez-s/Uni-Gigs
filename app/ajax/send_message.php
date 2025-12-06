<?php
session_start();
require_once __DIR__ . '/../../conect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit();
}

if (!isset($_POST['chat_id']) || !isset($_POST['message'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$chat_id = intval($_POST['chat_id']);
$message = trim($_POST['message']);

if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'El mensaje no puede estar vacío']);
    exit();
}

try {
    // Verificar acceso
    $check_query = "SELECT * FROM chats WHERE id_chat = ? AND (id_usuario1 = ? OR id_usuario2 = ?)";
    $check_stmt = $mysqli->prepare($check_query);
    $check_stmt->bind_param('iii', $chat_id, $id_usuario, $id_usuario);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
        exit();
    }
    
    // Insertar mensaje
    $query = "INSERT INTO mensajes (contenido, tipo_mensaje, id_emisor, id_chat, fecha) 
              VALUES (?, 'texto', ?, ?, NOW())";
    
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('sii', $message, $id_usuario, $chat_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message_id' => $stmt->insert_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al enviar mensaje']);
    }
    
    $stmt->close();
    $check_stmt->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>