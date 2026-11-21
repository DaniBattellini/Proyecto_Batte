<?php

 require_once "conexion.php";
 ob_start();

 require_once "funcionesval.php";



$error = "";



if(!empty(trim($_POST['nombre'])) && !empty(trim($_POST['apellido'])) && 
   !empty(trim($_POST['dni'])) && !empty(trim($_POST['clave'])) && !empty(trim($_POST['cuil'])) && !empty(trim($_POST['carrera']))){
	

	if (ValidacionDatos()){
         
		$nombre = preg_replace('/\s+/',' ',$_POST['nombre']);
		$apellido = preg_replace('/\s+/',' ',$_POST['apellido']);
		$dni = $_POST['dni'];
		$clave = $_POST['clave'];
        $cuil = $_POST['cuil'];
        $carrera = $_POST['carrera'];

        // --- Verificar DNI duplicado antes de insertar ---
        $stmtCheck = mysqli_prepare($conex, "SELECT COUNT(*) FROM usuario WHERE dni = ?");
        mysqli_stmt_bind_param($stmtCheck, "s", $dni);
        mysqli_stmt_execute($stmtCheck);
        mysqli_stmt_bind_result($stmtCheck, $countDni);
        mysqli_stmt_fetch($stmtCheck);
        mysqli_stmt_close($stmtCheck);

        if ($countDni > 0) {
            $error = "DNI duplicado: ya existe un usuario registrado con ese DNI.";
            header("Location:registro.php?mensaje=" . urlencode($error));
            exit();
        }
        // -------------------------------------------------
            
        $sql="INSERT INTO usuario(dni,clave,perfil) VALUES('$dni','$clave',1)";

        $result=mysqli_query($conex,$sql);

        if ($result){
			
			$ultimoid=mysqli_insert_id($conex);
			
			$sql="INSERT INTO alumnos(dni, cuil, nombre, apellido, carrera, Usuario_idUsuario) VALUES ('$dni', '$cuil', '$nombre', '$apellido', '$carrera', $ultimoid)";
			
			$result=mysqli_query($conex,$sql);
             
            header("Location:registro.php?mensaje=ok");

        }else{ 
			//codigo 1062 duplicado (segunda capa de seguridad)
            if(mysqli_errno($conex)==1062){
				$error.="DNI duplicado: ya existe un usuario registrado con ese DNI.";
				header("location:registro.php?mensaje=" . urlencode($error));
			}else{
            $error.="Error en la Inserción de datos ";
            header("Location:registro.php?mensaje=" . urlencode($error));
        }
     
     }
	
	}else{
		header("Location:registro.php?mensaje=" . urlencode($error));
	}
}else{

  	$error.="Faltan Datos ";
	header("Location:registro.php?mensaje=" . urlencode($error));
  
}

ob_end_flush();
?>

<link rel="stylesheet" href="http://www.fpdf.org/">


