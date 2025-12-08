<?php
// obtener_materias.php

// 1. Incluir conexión
include('../../conect.php');

// Verificar conexión
if (!isset($mysqli) || $mysqli->connect_errno) {
    header('Content-Type: application/json');
    echo json_encode([]); 
    exit;
}
$mysqli->set_charset("utf8mb4");

// 2. Obtener variable por POST
$id_carrera = isset($_POST['id_carrera']) ? intval($_POST['id_carrera']) : 0;

// 3. Preparar respuesta JSON
header('Content-Type: application/json');

$materias = array();

if ($id_carrera > 0) {
    // Consulta SQL segura
    $sql = "SELECT m.id_materia, m.nombre 
            FROM materias m
            JOIN materias_carreras mc ON m.id_materia = mc.id_materia
            WHERE mc.id_carrera = ?
            ORDER BY m.nombre ASC";

    $stmt = $mysqli->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("i", $id_carrera);
        $stmt->execute();
        $resultado = $stmt->get_result();

        while ($fila = $resultado->fetch_assoc()) {
            $materias[] = $fila;
        }
        $stmt->close();
    }
}

// 4. Devolver JSON y cerrar
echo json_encode($materias);
$mysqli->close();
exit;
?>

