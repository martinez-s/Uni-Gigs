<?php
session_start();
require_once __DIR__ . '/../../conect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit();
}

if (!isset($_FILES['file'])) {
    echo json_encode(['success' => false, 'message' => 'No se recibió ningún archivo']);
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$file = $_FILES['file'];
$chat_id = isset($_POST['chat_id']) ? intval($_POST['chat_id']) : 0;

try {
    // Verificar errores de subida
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Error en la subida del archivo: ' . $file['error']);
    }
    
    // Validar tamaño máximo (10MB)
    $max_size = 10 * 1024 * 1024; // 10MB
    if ($file['size'] > $max_size) {
        throw new Exception('El archivo es demasiado grande (máximo 10MB)');
    }
    
    // Obtener información del archivo
    $original_name = basename($file['name']);
    $file_size = $file['size'];
    $file_type = $file['type'];
    $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    
    // Definir tipos permitidos
    $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $allowed_extensions = array_merge($image_extensions, [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 
        'mp3', 'mp4', 'avi', 'mov'
    ]);
    
    // Validar extensión
    if (!in_array($extension, $allowed_extensions)) {
        throw new Exception('Tipo de archivo no permitido');
    }
    
    // Crear nombre único para el archivo
    $unique_name = time() . '_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\.]/', '_', $original_name);
    
    // Ruta de destino
    $upload_dir = __DIR__ . '/../uploads/mensajes/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $destination = $upload_dir . $unique_name;
    
    // Mover el archivo
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception('Error al mover el archivo');
    }
    
    // Determinar tipo de mensaje
    $tipo_mensaje = in_array($extension, $image_extensions) ? 'imagen' : 'archivo';
    
    // URL accesible del archivo
    $file_url = 'app/uploads/mensajes/' . $unique_name;
    
    echo json_encode([
        'success' => true,
        'file_name' => $original_name,
        'file_url' => $file_url,
        'file_type' => $tipo_mensaje,
        'file_size' => $file_size,
        'extension' => $extension
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>