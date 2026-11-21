<header>
    <!-- Encabezado -->
    <div class="container-fluid">
        <div class="row encabezado">
            <div class="col-1 mt-3 mb-3">
                <a href="#">
                    <img class="logo_escuela" src="imagenes/logo.jpg" alt="logo" id="img1">
                </a>
            </div>
            <div class="col-11">
                <h2 class="mt-5">Inscripción A Cursado de Materias</h2>
            </div>
        </div>
    </div>

    <!-- Menú de Navegación -->
    <nav class="navbar navbar-expand-md navbar-dark bg-primary">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <?php
                if (!isset($_SESSION["dnialu"]) && !isset($_SESSION["dnibedel"])) {
                ?>
                    <a class="navbar-brand" href="index.php">Inicio</a>
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="https://ens40-sfe.infd.edu.ar/sitio/">Institucional</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="https://escuela40.net/">Sist_Alumnos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="https://www.instagram.com/stories/ens40_nivelsuperior/sitio/">Novedades</a>
                        </li>
                    </ul>
                <?php
                } else {
                    if (isset($_SESSION["dnialu"])) {
                ?>
                        <a class="navbar-brand" href="inicio_alumno.php">Inicio</a>
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link" href="mis_inscripciones.php">Mis Inscripciones</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="perfil.php">Mi Perfil</a>
                            </li>
                        </ul>
                        <ul class="navbar-nav ms-auto">
                            <li class="nav-item">
                                <span class="nav-link text-light">
                                    <?php echo "Alumno: " . $_SESSION["dnialu"]; ?>
                                </span>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="salir.php">Salir</a>
                            </li>
                        </ul>
                <?php
                    } else {
                ?>
                        <a class="navbar-brand" href="inicio_bedelia.php">Inicio</a>
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link" href="listado_alumno.php">Listado Alumnos</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="listado_InscriptosA.php">Listado de Inscriptos</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="listado_NoInscripMat.php">Listado No Inscriptos</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="periodos.php">📅 Períodos</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="perfil_bedel.php">Mi Perfil</a>
                            </li>
                        </ul>
                        <ul class="navbar-nav ms-auto">
                            <li class="nav-item">
                                <span class="nav-link text-light">
                                    <?php echo "Bedel: " . $_SESSION["dnibedel"]; ?>
                                </span>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="salir.php">Salir</a>
                            </li>
                        </ul>
                <?php
                    }
                }
                ?>
            </div>
        </div>
    </nav>
</header>

