<?php 
    include_once __DIR__ . '/../../conect.php';
    session_start();
    if(!isset($_SESSION['id_usuario'])){
        header("Location: ../../login.php");
        exit();
    }

    $id_usuario_ser = $_SESSION['id_usuario'];

    if ($_SERVER['REQUEST_METHOD'] === 'GET' ) {
    try {
        
        if (!isset($_GET['id_usuario']) || empty($_GET['id_usuario'])) {
            die("Error: Se requiere el parámetro id_usuario.");
        }
        $id_request = $_GET['id_request'];
        $id_usuario_req = $_GET['id_usuario'];
        $activo = true;

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

        $sql= "INSERT INTO chats (id_usuario1, id_usuario2, id_solicitante, estado) VALUES (?,?,?,?)";
        $stmt = $mysqli->prepare($sql);

        $stmt->bind_param("iiii", $id_usuario_req, $id_usuario_ser, $id_usuario_req, $activo); 
        $stmt->execute();

        

        $sql_gig = "INSERT INTO gigs (id_prestador, id_solicitante, id_request,estado, fecha_creacion) VALUES (?, ?, ?,?, NOW())";
        $stmt_gig = $mysqli->prepare($sql_gig);
        $stmt_gig->bind_param("iiii", $id_usuario_req, $id_usuario_ser, $id_request, $activo);
        $stmt_gig->execute();
        

        header("Location: ../../mensajeria.php");
        exit();


    } catch (Exception $e) {
        error_log("Error al asociar chat: " . $e->getMessage());
        die("Ocurrió un error en el servidor: " . $e->getMessage());
    }
}
?>