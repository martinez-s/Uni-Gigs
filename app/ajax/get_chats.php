<?php
session_start();
require_once __DIR__ . '/../../conect.php';  // ✅ Sube dos niveles desde app/ajax/ hasta raíz

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

try {
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
            CASE 
                WHEN c.id_usuario1 = ? THEN u2.url_foto_perfil 
                ELSE u1.url_foto_perfil 
            END as foto_otro_usuario,
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
    $stmt->bind_param('iiiiii', $id_usuario, $id_usuario, $id_usuario, $id_usuario, $id_usuario, $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $chats = [];
    while ($row = $result->fetch_assoc()) {
        // Formatear fecha
        $ultima_fecha_formateada = '';
        if ($row['ultima_fecha']) {
            $fecha = new DateTime($row['ultima_fecha']);
            $hoy = new DateTime();
            $diferencia = $hoy->diff($fecha);
            
            if ($diferencia->days == 0) {
                $ultima_fecha_formateada = 'Hoy, ' . $fecha->format('H:i');
            } elseif ($diferencia->days == 1) {
                $ultima_fecha_formateada = 'Ayer, ' . $fecha->format('H:i');
            } elseif ($diferencia->days < 7) {
                $dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
                $ultima_fecha_formateada = $dias[$fecha->format('w')] . ', ' . $fecha->format('H:i');
            } else {
                $ultima_fecha_formateada = $fecha->format('d/m H:i');
            }
        }
        
        // Acortar mensaje
        $ultimo_mensaje = $row['ultimo_mensaje'] ?? 'Sin mensajes aún';
        if (strlen($ultimo_mensaje) > 35) {
            $ultimo_mensaje = substr($ultimo_mensaje, 0, 35) . '...';
        }
        
        $chats[] = [
            'id_chat' => $row['id_chat'],
            'id_otro_usuario' => $row['id_otro_usuario'],
            'nombre_otro_usuario' => $row['nombre_otro_usuario'],
            'apellido_otro_usuario' => $row['apellido_otro_usuario'],
            'foto_otro_usuario' => $row['foto_otro_usuario'],
            'ultimo_mensaje' => $ultimo_mensaje,
            'ultima_fecha' => $ultima_fecha_formateada,
            'estado' => $row['estado']
        ];
    }
    
    echo json_encode(['success' => true, 'chats' => $chats]);
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>