<?php
if (!empty($_POST["btnsiguiente"])) {
    $imagen=$_FILES["imagen_perfil"]["temp_name"];
    $nombreImagen=$_FILES["imagen_perfil"]["name"];
    $tipoImagen=strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
    $sizeImagen=$_FILES["imagen_perfil"]["size"];
    $carpetaDestino="public/images/imgusuarios/";

    if ($tipoImagen!="jpg" && $tipoImagen!="png" && $tipoImagen!="jpeg") {
        $registro=$conexion->query("INSERT INTO estudiantes (url_foto_perfil) VALUES ('')");
        $idRegistro=$conexion->insert_id;

        $ruta=$carpetaDestino.$idRegistro.".".$tipoImagen;
        $actualizarImagen=$conexion->query("UPDATE estudiantes SET url_foto_perfil='$ruta' WHERE id_estudiante=$idRegistro");


        if(move_uploaded_file($imagen, $ruta)){
            header("Location: Index.php");
        } else {
            echo "Error al subir la imagen";
        }
    }
}
?>