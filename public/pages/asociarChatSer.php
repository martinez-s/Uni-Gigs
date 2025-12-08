<?php 
    include_once __DIR__ . '/../../conect.php'; // Se asume que $mysqli está definida aquí
    session_start();
    if(!isset($_SESSION['id_usuario'])){
        header("Location: ../../login.php");
        exit();
    }

    $id_usuario_req = $_SESSION['id_usuario'];

    if ($_SERVER['REQUEST_METHOD'] === 'GET' ) {
    try {
        
        // **VALIDACIÓN DE id_usuario_ser**
        // Nota: El mensaje de error dice 'id_servicio', pero la validación usa 'id_usuario'.
        if (!isset($_GET['id_usuario']) || empty($_GET['id_usuario'])) {
            die("Error: Se requiere el parámetro id_usuario."); // Cambiado a id_usuario
        }
        
        $id_usuario_ser = $_GET['id_usuario'];
        $activo = true;
        
        // **CORRECCIÓN: Definición de id_reserva_ser**
        // Si no tienes el ID de reserva en este contexto, usa NULL.
        // Asegúrate de que este campo sea NULLABLE en tu base de datos.
        $id_reserva_ser = NULL; 

        // -------------------------------------------------------------
        // Bloque para verificar y reactivar chat viejo
        // -------------------------------------------------------------
        $sql_chatViejo= "SELECT id_chat FROM chats WHERE ((id_usuario1 = ? AND id_usuario2 = ?) OR (id_usuario1 = ? AND id_usuario2 = ?)) AND (estado = FALSE)";
        $stmt_chatViejo = $mysqli->prepare($sql_chatViejo);
        $stmt_chatViejo->bind_param("iiii", $id_usuario_req, $id_usuario_ser, $id_usuario_ser, $id_usuario_req);
        $stmt_chatViejo->execute();
        $result_chatViejo = $stmt_chatViejo->get_result();

        if($result_chatViejo->num_rows > 0){
            $sql_reactivar= "UPDATE chats SET estado = TRUE WHERE id_chat = ?";
            $stmt_reactivar = $mysqli->prepare($sql_reactivar);
            $chatViejo = $result_chatViejo->fetch_assoc();
            $id_chatViejo = $chatViejo['id_chat'];
            $stmt_reactivar->bind_param("i", $id_chatViejo);
            $stmt_reactivar->execute();
            header("Location: ../../mensajeria.php");
            exit();
        }

        // -------------------------------------------------------------
        // Bloque para crear nuevo chat
        // -------------------------------------------------------------
        $sql= "INSERT INTO chats (id_usuario1, id_usuario2, id_solicitante, estado) VALUES (?,?,?,?)";
        $stmt = $mysqli->prepare($sql);
        
        // **¡CORRECCIÓN CLAVE!**
        // 1. Enviamos $id_usuario_req al campo id_solicitante.
        // 2. Usamos "iiii" si 'estado' es un entero (lo más común para booleanos).
        $stmt->bind_param("iiii", $id_usuario_req, $id_usuario_ser, $id_usuario_req, $activo); 
        $stmt->execute();

        header("Location: ../../mensajeria.php");
        exit();


    } catch (Exception $e) {
        // 🚨 CAMBIO TEMPORAL PARA DEBUGGING 🚨
        error_log("Error al asociar chat: " . $e->getMessage());
        die("Ocurrió un error en el servidor: " . $e->getMessage()); // MUESTRA EL MENSAJE REAL
        // 🚨 FIN DEL CAMBIO TEMPORAL 🚨
    }
}
?>