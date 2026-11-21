<?php
// Conexion a la Base de Datos Biblioteca  
session_start();

require_once "conexion.php";

// Obtener carreras disponibles
$carreras = array();
$conex_temp = new mysqli("localhost", "root", "", "inscripcioncursado");
if ($conex_temp->connect_error) {
    die("Error de conexión: " . $conex_temp->connect_error);
}
$conex_temp->set_charset("utf8mb4");
$result_temp = $conex_temp->query("SELECT idCarrera, nombre_Carrera FROM carrera ORDER BY nombre_Carrera");
if ($result_temp && $result_temp->num_rows > 0) {
    while ($row = $result_temp->fetch_assoc()) {
        $carreras[] = $row;
    }
}
$conex_temp->close();

/* Si no existe mensaje resultante de actualización (Porque aún no hizo actualizacion) */

if (!isset($_GET['msje'])){

  // Guarda el id enviado por parámetro en URL, desde listado.php, y lo evalúa con $_GET

  $id=$_GET['id'];
  $_SESSION['ids']=$id;

}    

$sql="SELECT * FROM alumnos where id = $id";

//die($sql);

$result=mysqli_query($conex,$sql); 

$fila=mysqli_fetch_array($result);


  include('primero.php');
  include('header_pagina.php');

   ?>

   
  
  <div class="container mt-2 mb-5">
  <div class="text-center mt-5 mb-2"><h2>Editar datos personales</h2></div>	
  <br>
  <HR>
  <br>
  	
  <form class="row g-3" action="bedelia_editar_insertar.php" method="POST">

      <div class="col-sm-6 mb-3">
        <label for="telefono" class="form-label">* DNI</label>
        <input type="text" class="form-control" name="dni" id="dni" value="<?php echo $fila['dni']; ?>" required readonly>
        <input type="hidden" class="form-control" name="usuario" id="usuario" value="<?php echo $fila['Usuario_idUsuario']; ?>">
      </div>
      
      <div class="col-sm-6">
        <label for="nombre" class="form-label">* Nombre</label>
        <input type="text" class="form-control" name="nombre" id="nombre" placeholder="Editar tu Nombre" value="<?php echo $fila['nombre']; ?>" required>
      </div>
      <div class="col-sm-6 mb-3">
        <label for="apellido" class="form-label">* Apellido</label>
        <input type="text" class="form-control" name="apellido" id="apellido" placeholder="Ingresa tu Apellido" value="<?php echo $fila['apellido']; ?>" required>
      </div>
    
      <div class="col-sm-6 mb-3">
        <label for="dni" class="form-label">* CUIL</label>
        <input type="text" class="form-control" name="cuil" id="cuil" placeholder="Ingresa 11 dígitos del cuil" value="<?php echo $fila['cuil']; ?>" required>
      </div>

      <div class="col-sm-6 mb-3">
        <label for="carrera" class="form-label">* Carrera</label>
        <select class="form-control" name="carrera" id="carrera" required>
          <option value="">-- Selecciona una carrera --</option>
          <?php foreach ($carreras as $carrera): ?>
            <option value="<?= htmlspecialchars($carrera['nombre_Carrera']) ?>" <?= ($fila['carrera'] === $carrera['nombre_Carrera']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($carrera['nombre_Carrera']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
  
      <div class="col-12 text-center">
          <div> <h5> ¿Estas seguro que quieres actualizar los datos de este Alumno?</h5>
              <br>
              <button type="submit" class="btn btn-primary" name="enviar" id="enviar">Actualizar datos</button>
              <a class="btn btn-success" href="listado_alumno.php">Cancelar</a>
      </div>
 
 
  </form>
   


</body>

</html>