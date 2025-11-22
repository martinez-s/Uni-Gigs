<?php
session_start();
include('conect.php');

if (isset($_POST['correo']) && isset($_POST['clave']))
  {
    function validate($data){
      $data = trim($data);
      $data = stripslashes($data);
      $data = htmlspecialchars($data);
      return $data;
    }

    $Usuario = validate($_POST['correo'] ?? '');
    $Clave = validate($_POST['clave'] ?? '');

    if(empty($Usuario) && empty($Clave))
    {
      header("Location: Login.html.php?error=Complete todos los campos");
      exit();    
    }
    if (empty($Usuario)){
      header("Location: Login.html.php?error=Debe ingresar su correo");
      exit();
    }
    elseif (empty($Clave)) {
      header("Location: Login.html.php?error=Debe ingresar su clave");
      exit();
    }
    else {
      // $Clave = md5($Clave);

      $sql = "Select * from estudiantes where correo = '$Usuario' and clave = '$Clave'";
      $resultado = $mysqli->query($sql);

      if(mysqli_num_rows($resultado) === 1) 
      {
        $row = mysqli_fetch_assoc($resultado);
        if($row['correo'] === $Usuario && $row['clave'] === $Clave){
          $_SESSION['correo'] = $row['correo'];
          $_SESSION['id_estudiante'] = $row['id_estudiante'];
          header("Location: Index.html.php");
          exit();
        }
        else{
          header("Location: Login.html.php?error= El usuario o la contraseña son incorrectos");
          exit();
        }
      }
      else {
        header("Location: Login.html.php?error= El usuario o la contraseña son incorrectos");
        exit();
      }
    } 
  }
  else {
    header("Location: Login.html.php");
    exit();
  }

?>