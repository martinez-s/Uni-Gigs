<?php
session_start();
require_once __DIR__ . '/../includes/conect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$response = ['success' => false, 'chats' => []];

try {
    // Obtener todos los chats del usuario
    $query = "
        SELECT 
            c.id_chat,
            c.id_usuario1,
            c.id_usuario2,
            c.estado,
            CASE 
                WHEN c.id_usuario1 = ? THEN c.id_usuario2 
                ELSE c.id_usuario1 
            END as id_otro_usuario,
            CASE 
                WHEN c.id_usuario1 = ? THEN u2.nombre 
                ELSE u1.nombre 
            END as nombre_otro_usuario,
            CASE 
                WHEN c.id_usuario1 = ? THEN u2.apellido 
                ELSE u1.apellido 
            END as apellido_otro_usuario,
            u1.url_foto_perfil as foto_usuario1,
            u2.url_foto_perfil as foto_usuario2,
            (SELECT contenido FROM mensajes 
             WHERE id_chat = c.id_chat 
             ORDER BY fecha DESC LIMIT 1) as ultimo_mensaje,
            (SELECT fecha FROM mensajes 
             WHERE id_chat = c.id_chat 
             ORDER BY fecha DESC LIMIT 1) as ultima_fecha
        FROM chats c
        JOIN usuarios u1 ON c.id_usuario1 = u1.id_usuario
        JOIN usuarios u2 ON c.id_usuario2 = u2.id_usuario
        WHERE c.id_usuario1 = ? OR c.id_usuario2 = ?
        ORDER BY ultima_fecha DESC
    ";
    
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('iiiii', $id_usuario, $id_usuario, $id_usuario, $id_usuario, $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $chats = [];
    while ($row = $result->fetch_assoc()) {
        // Determinar foto del otro usuario
        $foto_otro = ($row['id_usuario1'] == $id_usuario) ? $row['foto_usuario2'] : $row['foto_usuario1'];
        
        // Formatear última fecha
        $ultima_fecha = $row['ultima_fecha'];
        if ($ultima_fecha) {
            $fecha = new DateTime($ultima_fecha);
            $hoy = new DateTime();
            $diferencia = $hoy->diff($fecha);
            
            if ($diferencia->days == 0) {
                $ultima_fecha = $fecha->format('H:i');
            } elseif ($diferencia->days == 1) {
                $ultima_fecha = 'Ayer';
            } elseif ($diferencia->days < 7) {
                $ultima_fecha = $fecha->format('l');
            } else {
                $ultima_fecha = $fecha->format('d/m/Y');
            }
        } else {
            $ultima_fecha = '';
        }
        
        $chats[] = [
            'id_chat' => $row['id_chat'],
            'id_otro_usuario' => $row['id_otro_usuario'],
            'nombre_otro_usuario' => $row['nombre_otro_usuario'],
            'apellido_otro_usuario' => $row['apellido_otro_usuario'],
            'foto_otro_usuario' => $foto_otro,
            'ultimo_mensaje' => $row['ultimo_mensaje'],
            'ultima_fecha' => $ultima_fecha,
            'estado' => $row['estado']
        ];
    }
    
    $response['success'] = true;
    $response['chats'] = $chats;
    
    $stmt->close();
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>