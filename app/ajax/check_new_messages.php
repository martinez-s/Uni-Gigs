<?php
session_start();
require_once __DIR__ . '/../../conect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false]);
    exit();
}

if (!isset($_GET['chat_id']) || !isset($_GET['last_message_id'])) {
    echo json_encode(['success' => false]);
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$chat_id = intval($_GET['chat_id']);
$last_message_id = intval($_GET['last_message_id']);

try {
    // Verificar acceso
    $check_query = "SELECT * FROM chats WHERE id_chat = ? AND (id_usuario1 = ? OR id_usuario2 = ?)";
    $check_stmt = $mysqli->prepare($check_query);
    $check_stmt->bind_param('iii', $chat_id, $id_usuario, $id_usuario);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        echo json_encode(['success' => false]);
        exit();
    }
    
    // Obtener nuevos mensajes
    $query = "
        SELECT m.*, u.nombre, u.apellido 
        FROM mensajes m
        JOIN usuarios u ON m.id_emisor = u.id_usuario
        WHERE m.id_chat = ? AND m.id_mensaje > ?
        ORDER BY m.fecha ASC
    ";
    
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('ii', $chat_id, $last_message_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $new_messages = [];
    while ($row = $result->fetch_assoc()) {
        $fecha = new DateTime($row['fecha']);
        $hora = $fecha->format('H:i');
        
        $new_messages[] = [
            'id_mensaje' => $row['id_mensaje'],
            'contenido' => htmlspecialchars($row['contenido']),
            'tipo_mensaje' => $row['tipo_mensaje'],
            'id_emisor' => $row['id_emisor'],
            'nombre_emisor' => $row['nombre'],
            'apellido_emisor' => $row['apellido'],
            'hora' => $hora,
            'es_mio' => ($row['id_emisor'] == $id_usuario)
        ];
    }
    
    echo json_encode([
        'success' => true, 
        'new_messages' => $new_messages,
        'count' => count($new_messages)
    ]);
    
    $stmt->close();
    $check_stmt->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false]);
}
?>