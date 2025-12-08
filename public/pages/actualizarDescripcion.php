<?php

session_start();

require_once '../../conect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión expirada. Recarga la página.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$nuevaDescripcion = trim($input['descripcion'] ?? '');
$idUsuario = $_SESSION['id_usuario'];

if (strlen($nuevaDescripcion) > 500) {
      echo json_encode(['success' => false, 'message' => 'La descripción es demasiado larga.']);
      exit;
}

$sql = "UPDATE usuarios SET descripcion = ? WHERE id_usuario = ?";
$stmt = $mysqli->prepare($sql);

if ($stmt) {
    $stmt->bind_param("si", $nuevaDescripcion, $idUsuario);
    
    if ($stmt->execute()) {

        echo json_encode([
            'success' => true, 
            'nueva_descripcion' => $nuevaDescripcion
        ]);
    } else {

        echo json_encode(['success' => false, 'message' => 'Error al guardar en la base de datos.']);
    }
    $stmt->close();
} else {
     echo json_encode(['success' => false, 'message' => 'Error en la preparación de la consulta.']);
}
?>