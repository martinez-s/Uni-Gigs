<?php
$Usuario='root';
$clave='';
$db='uni-gigs';
$host='localhost';

$mysqli =  new mysqli($host, $Usuario, $clave, $db);
if ($mysqli->connect_error)
{
  die("Error de Conexion: " . $mysqli->connect_error);
}
?>
