<?php
session_start();
require_once __DIR__ . '/../../conect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit();
}

if (!isset($_GET['chat_id']) || empty($_GET['chat_id'])) {
    echo json_encode(['success' => false, 'message' => 'Chat no especificado']);
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$chat_id = intval($_GET['chat_id']);

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
    $check_stmt->close();
    
   
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
        $fecha_completa = $fecha->format('d/m/Y H:i');
        
        $file_size_formatted = '';
        if (!empty($row['nombre_archivo'])) {
            $file_path = __DIR__ . '/../' . $row['url_archivo'];
            if (file_exists($file_path)) {
                $size = filesize($file_path);
                $file_size_formatted = formatFileSize($size);
            }
        }
        
        $messages[] = [
            'id_mensaje' => $row['id_mensaje'],
            'contenido' => htmlspecialchars($row['contenido'], ENT_QUOTES, 'UTF-8'),
            'tipo_mensaje' => $row['tipo_mensaje'],
            'url_archivo' => $row['url_archivo'],
            'nombre_archivo' => $row['nombre_archivo'],
            'tamano_archivo' => $file_size_formatted,
            'id_emisor' => $row['id_emisor'],
            'id_chat' => $row['id_chat'],
            'nombre_emisor' => $row['nombre'],
            'apellido_emisor' => $row['apellido'],
            'foto_emisor' => $row['url_foto_perfil'],
            'hora' => $hora,
            'fecha_completa' => $fecha_completa,
            'es_mio' => ($row['id_emisor'] == $id_usuario)
        ];
    }
    
    $response['success'] = true;
    $response['messages'] = $messages;
    
    $stmt->close();
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        return $bytes . ' bytes';
    } elseif ($bytes == 1) {
        return '1 byte';
    } else {
        return '0 bytes';
    }
}

echo json_encode($response);
?>