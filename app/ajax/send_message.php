<?php
session_start();
require_once __DIR__ . '/../../conect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit();
}

if (!isset($_POST['chat_id'])) {
    echo json_encode(['success' => false, 'message' => 'Chat no especificado']);
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$chat_id = intval($_POST['chat_id']);
$message = isset($_POST['message']) ? trim($_POST['message']) : '';
$file_data = isset($_POST['file_data']) ? json_decode($_POST['file_data'], true) : null;

try {
    $check_query = "SELECT * FROM chats WHERE id_chat = ? AND (id_usuario1 = ? OR id_usuario2 = ?)";
    $check_stmt = $mysqli->prepare($check_query);
    $check_stmt->bind_param('iii', $chat_id, $id_usuario, $id_usuario);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
        exit();
    }
    
    $tipo_mensaje = 'texto';
    $url_archivo = null;
    $nombre_archivo = null;
    
    if ($file_data && $file_data['success']) {
        $tipo_mensaje = $file_data['file_type'];
        $url_archivo = $file_data['file_url'];
        $nombre_archivo = $file_data['file_name'];
        $contenido = $file_data['file_name'];
    } else {
        $contenido = $message;
    }
    
    if (empty($contenido) && empty($file_data)) {
        echo json_encode(['success' => false, 'message' => 'El mensaje no puede estar vacío']);
        exit();
    }
    

    if ($tipo_mensaje === 'texto') {
        $query = "INSERT INTO mensajes (contenido, tipo_mensaje, id_emisor, id_chat, fecha) 
                  VALUES (?, ?, ?, ?, NOW())";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('ssii', $contenido, $tipo_mensaje, $id_usuario, $chat_id);
    } else {
        $query = "INSERT INTO mensajes (contenido, tipo_mensaje, url_archivo, nombre_archivo, id_emisor, id_chat, fecha) 
                  VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('ssssii', $contenido, $tipo_mensaje, $url_archivo, $nombre_archivo, $id_usuario, $chat_id);
    }
    
    if ($stmt->execute()) {
        $message_id = $stmt->insert_id;
        

        $update_query = "UPDATE chats SET estado = 1 WHERE id_chat = ?";
        $update_stmt = $mysqli->prepare($update_query);
        $update_stmt->bind_param('i', $chat_id);
        $update_stmt->execute();
        $update_stmt->close();
        
        echo json_encode([
            'success' => true, 
            'message_id' => $message_id,
            'tipo_mensaje' => $tipo_mensaje,
            'contenido' => $contenido,
            'url_archivo' => $url_archivo,
            'nombre_archivo' => $nombre_archivo
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al enviar mensaje']);
    }
    
    $stmt->close();
    $check_stmt->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>