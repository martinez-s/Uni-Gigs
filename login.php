<?php

session_start();

include('conect.php'); 

const REDIRECT_PAGE = "Index.php"; 

const SUCCESS_REDIRECT_PAGE = "public/pages/principal.php"; 


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    

    function validate($data){
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
    }

    $Usuario = validate($_POST['correo'] ?? '');
    $Clave = validate($_POST['clave'] ?? '');

    if(empty($Usuario) && empty($Clave)) {
        $_SESSION['error'] = "Complete todos los campos";
        header("Location: " . REDIRECT_PAGE);
        exit();
    } elseif (empty($Usuario)) {
        $_SESSION['error'] = "Debe ingresar su correo";
        header("Location: " . REDIRECT_PAGE);
        exit();
    } elseif (empty($Clave)) {
        $_SESSION['error'] = "Debe ingresar su clave";
        header("Location: " . REDIRECT_PAGE);
        exit();
    }
    
    $sql = "SELECT correo, id_usuario, clave FROM usuarios WHERE correo = ?";
    
    if ($stmt = $mysqli->prepare($sql)) {
        
        $stmt->bind_param("s", $Usuario);
        

        $stmt->execute();
        
        $resultado = $stmt->get_result();
        
    if ($resultado->num_rows === 1) {
            $row = $resultado->fetch_assoc();

            if ($row['clave'] === $Clave) { 

                $_SESSION['correo'] = $row['correo'];
                $_SESSION['id_usuario'] = $row['id_usuario'];

                $_SESSION['success'] = "Inicio de sesión exitoso"; 

                header("Location: public/pages/principal.php");
                exit();
            }
        }      
        $_SESSION['error'] = "El usuario o la contraseña son incorrectos";


        $stmt->close();
    }  
}
    else {
        $_SESSION['error'] = "Error interno del sistema.";
    }


    header("Location: Index.php"); 
    exit();

?>