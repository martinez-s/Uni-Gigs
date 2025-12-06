<?php
// Asegúrate de que tu archivo de conexión esté incluido
include('conect.php'); 

header('Content-Type: application/json');

if (isset($_GET['id_carrera']) && is_numeric($_GET['id_carrera'])) {
    
    $id_carrera = (int)$_GET['id_carrera'];
    $materias = [];

    // 🔑 CONSULTA SQL CORREGIDA: Usando JOIN con materias_carreras
    $sql = "
        SELECT 
            m.id_materia, 
            m.nombre 
        FROM materias m
        INNER JOIN materias_carreras mc 
            ON m.id_materia = mc.id_materia 
        WHERE mc.id_carrera = ? 
        ORDER BY m.nombre
    ";
    
    $stmt = $mysqli->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("i", $id_carrera);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $materias[] = [
                'id_materia' => $row['id_materia'],
                'nombre' => htmlspecialchars($row['nombre']) 
            ];
        }

        echo json_encode(['success' => true, 'data' => $materias]);
        $stmt->close();
    } else {
        // En caso de un error en la sintaxis SQL (poco probable con la corrección)
        echo json_encode(['success' => false, 'message' => 'Error al preparar la consulta SQL.']);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'ID de Carrera inválido o no proporcionado.']);
}

$mysqli->close();
?>