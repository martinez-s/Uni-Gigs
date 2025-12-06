<?php
session_start();
require_once __DIR__ . '/../../conect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit();
}

if (!isset($_GET['chat_id'])) {
    echo json_encode(['success' => false, 'message' => 'Chat no especificado']);
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$chat_id = intval($_GET['chat_id']);

try {
    // Verificar acceso al chat
    $check_query = "SELECT * FROM chats WHERE id_chat = ? AND (id_usuario1 = ? OR id_usuario2 = ?)";
    $check_stmt = $mysqli->prepare($check_query);
    $check_stmt->bind_param('iii', $chat_id, $id_usuario, $id_usuario);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
        exit();
    }
    
    // Obtener mensajes
    $query = "
        SELECT m.*, u.nombre, u.apellido, u.url_foto_perfil
        FROM mensajes m
        JOIN usuarios u ON m.id_emisor = u.id_usuario
        WHERE m.id_chat = ?
        ORDER BY m.fecha ASC
    ";
    
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $chat_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $fecha = new DateTime($row['fecha']);
        $hora = $fecha->format('H:i');
        
        $messages[] = [
            'id_mensaje' => $row['id_mensaje'],
            'contenido' => htmlspecialchars($row['contenido']),
            'tipo_mensaje' => $row['tipo_mensaje'],
            'id_emisor' => $row['id_emisor'],
            'nombre_emisor' => $row['nombre'],
            'apellido_emisor' => $row['apellido'],
            'foto_emisor' => $row['url_foto_perfil'],
            'hora' => $hora,
            'fecha_completa' => $fecha->format('d/m/Y H:i'),
            'es_mio' => ($row['id_emisor'] == $id_usuario)
        ];
    }
    
    echo json_encode(['success' => true, 'messages' => $messages]);
    
    $stmt->close();
    $check_stmt->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>