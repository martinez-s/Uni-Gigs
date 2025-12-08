<?php
session_start();
require_once '../../conect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No has iniciado sesión.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id_reportador = $_SESSION['id_usuario'];
$id_reportado = $data['id_reportado'] ?? null;
$razon = $data['razon'] ?? '';

if (!$id_reportado || empty($razon)) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos (ID o razón).']);
    exit;
}

if ($id_reportador == $id_reportado) {
    echo json_encode(['success' => false, 'message' => 'No puedes reportarte a ti mismo.']);
    exit;
}

$sql = "INSERT INTO reportes (id_reportador, id_reportado, razon) VALUES (?, ?, ?)";
$stmt = $mysqli->prepare($sql);

if ($stmt) {
    $stmt->bind_param("iis", $id_reportador, $id_reportado, $razon);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Reporte enviado con éxito.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar en la BD.']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Error en la consulta SQL.']);
}
?>