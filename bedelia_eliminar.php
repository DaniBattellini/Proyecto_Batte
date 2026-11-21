<?php

session_start();

require_once "conexion.php";
 $id=$_POST['idusuario'];

 $_SESSION['ids']=$id;
            
 $sql="DELETE FROM alumnos WHERE id=$id"; 

        
 mysqli_query($conex,$sql);

//  die($sql); 

header("location:listado_alumno.php")
 ?>   