<?php

$servidor= "localhost";
$usuario= "root";
$bd= "inscripcioncursado";
$pass="";
$conex= mysqli_connect($servidor, $usuario, $pass, $bd);
if (!$conex){
   echo "ERROR AL ABRIR LA BD!!";
    EXIT();
   }
mysqli_set_charset($conex, "utf8mb4");
mysqli_query($conex, "SET NAMES 'utf8mb4'");
mysqli_query($conex, "SET CHARACTER SET utf8mb4");
?>