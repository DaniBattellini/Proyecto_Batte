-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 21-11-2026 a las 13:51:41
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `inscripcioncursado`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alumnos`
--

CREATE TABLE `alumnos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(45) NOT NULL,
  `apellido` varchar(45) NOT NULL,
  `dni` varchar(8) DEFAULT NULL,
  `cuil` varchar(11) DEFAULT NULL,
  `carrera` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Usuario_idUsuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `alumnos`
--

INSERT INTO `alumnos` (`id`, `nombre`, `apellido`, `dni`, `cuil`, `carrera`, `Usuario_idUsuario`) VALUES
(23, 'jorgee', 'lolo', '12345671', '12345678911', 'Tecnicatura Superior en Desarrollo de Software', 30),
(24, 'Daniela ', 'Vargas', '12852369', '23128523695', 'Tecnicatura Superior en Gestión de las Organizaciones', 31),
(25, 'test alumno', 'apellido test', '49894867', NULL, 'Tecnicatura Superior en Desarrollo de Software', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bedelia`
--

CREATE TABLE `bedelia` (
  `idBedelia` int(11) NOT NULL,
  `Usuario_idUsuario` int(11) NOT NULL,
  `dni` varchar(8) NOT NULL,
  `clave` varchar(45) NOT NULL,
  `rol` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `bedelia`
--

INSERT INTO `bedelia` (`idBedelia`, `Usuario_idUsuario`, `dni`, `clave`, `rol`) VALUES
(1, 29, '12345678', '12345678', 'bedel');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrera`
--

CREATE TABLE `carrera` (
  `idCarrera` int(11) NOT NULL,
  `nombre_Carrera` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `carrera`
--

INSERT INTO `carrera` (`idCarrera`, `nombre_Carrera`, `descripcion`) VALUES
(1, 'Tecnicatura Superior en Gestión de las Organizaciones', 'Carrera'),
(2, 'Tecnicatura Superior en Desarrollo de Software', 'Carrera');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_inscripciones`
--

CREATE TABLE `historial_inscripciones` (
  `idHistorial` int(11) NOT NULL,
  `alumno_id` int(11) NOT NULL,
  `materia_id` int(11) DEFAULT NULL,
  `nombre_materia` varchar(255) NOT NULL,
  `condicion_cursado` enum('presencial','semipresencial','libre') NOT NULL,
  `fecha_inscripcion` datetime NOT NULL DEFAULT current_timestamp(),
  `anio_lectivo` year(4) NOT NULL,
  `codigo_qr` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `id`
--

CREATE TABLE `id` (
  `id_alumno` int(11) NOT NULL,
  `id_carrera` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inscripciones`
--

CREATE TABLE `inscripciones` (
  `id` int(11) NOT NULL,
  `alumno_id` int(11) NOT NULL,
  `materia` varchar(255) NOT NULL,
  `condicion_cursado` enum('presencial','semipresencial','libre') NOT NULL,
  `fecha_inscripcion` timestamp NOT NULL DEFAULT current_timestamp(),
  `correo_electronico` varchar(255) DEFAULT NULL,
  `materia_id` int(11) DEFAULT NULL,
  `anio_lectivo` year(4) NOT NULL DEFAULT year(curdate()),
  `codigo_qr` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `inscripciones`
--

INSERT INTO `inscripciones` (`id`, `alumno_id`, `materia`, `condicion_cursado`, `fecha_inscripcion`, `correo_electronico`, `materia_id`, `anio_lectivo`, `codigo_qr`) VALUES
(82, 23, 'Sistemas Operativos', 'presencial', '2026-02-23 21:47:56', 'ejemplo@gmail.com', NULL, '2026', NULL),
(83, 23, 'Sistemas Operativos', 'presencial', '2026-02-25 03:10:49', 'danielabattellini25@gmail.com', NULL, '2026', NULL),
(84, 23, 'Comunicación', 'presencial', '2026-02-25 03:10:49', 'danielabattellini25@gmail.com', NULL, '2026', NULL),
(85, 23, 'Unidad de Definición Institucional I - Lengua', 'presencial', '2026-02-25 03:10:49', 'danielabattellini25@gmail.com', NULL, '2026', NULL),
(86, 23, 'Matemática', 'presencial', '2026-02-25 03:10:49', 'danielabattellini25@gmail.com', NULL, '2026', NULL),
(87, 23, 'Inglés Técnico I', 'presencial', '2026-02-25 03:10:49', 'danielabattellini25@gmail.com', NULL, '2026', NULL),
(88, 23, 'Administración', 'semipresencial', '2026-02-25 03:10:49', 'danielabattellini25@gmail.com', NULL, '2026', NULL),
(89, 23, 'Tecnologías de la Información', 'semipresencial', '2026-02-25 03:10:49', 'danielabattellini25@gmail.com', NULL, '2026', NULL),
(90, 23, 'Lógica y Estructura de Datos', 'semipresencial', '2026-02-25 03:10:49', 'danielabattellini25@gmail.com', NULL, '2026', NULL),
(91, 24, 'Sistemas Operativos', 'presencial', '2026-06-15 21:57:44', '', NULL, '2026', NULL),
(93, 25, 'Sistemas Operativos', 'presencial', '2026-11-21 12:00:20', '', NULL, '2026', NULL),
(94, 25, 'Inglés Técnico I', 'presencial', '2026-11-21 12:00:20', '', NULL, '2026', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inscripciones_mesas`
--

CREATE TABLE `inscripciones_mesas` (
  `idInscripcionMesa` int(11) NOT NULL,
  `mesa_id` int(11) NOT NULL,
  `alumno_id` int(11) NOT NULL,
  `rinde` tinyint(1) NOT NULL,
  `fecha_inscripcion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `inscripciones_mesas`
--

INSERT INTO `inscripciones_mesas` (`idInscripcionMesa`, `mesa_id`, `alumno_id`, `rinde`, `fecha_inscripcion`) VALUES
(2, 24, 25, 1, '2026-11-21 12:45:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materia`
--

CREATE TABLE `materia` (
  `idMateria` int(11) NOT NULL,
  `inscripciones` varchar(50) NOT NULL,
  `nombreM` varchar(45) DEFAULT NULL,
  `Carrera_idCarrera` int(11) DEFAULT NULL,
  `anio` int(11) DEFAULT NULL,
  `fecha_inscripcion` date NOT NULL,
  `correo_electronico` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mesas_materias`
--

CREATE TABLE `mesas_materias` (
  `idMesa` int(11) NOT NULL,
  `periodo_id` int(11) NOT NULL,
  `carrera_id` int(11) NOT NULL,
  `materia` varchar(255) NOT NULL,
  `fecha_examen` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `mesas_materias`
--

INSERT INTO `mesas_materias` (`idMesa`, `periodo_id`, `carrera_id`, `materia`, `fecha_examen`) VALUES
(2, 3, 1, 'Sistemas Operativos', '2026-11-28 12:42:00'),
(3, 3, 1, 'Comunicación', NULL),
(4, 3, 1, 'Unidad de Definición Institucional I - Lengua', NULL),
(5, 3, 1, 'Matemática', NULL),
(6, 3, 1, 'Inglés Técnico I', NULL),
(7, 3, 1, 'Administración', '2026-11-25 14:41:00'),
(8, 3, 1, 'Tecnologías de la Información', NULL),
(9, 3, 1, 'Lógica y Estructura de Datos', '2026-11-28 14:41:00'),
(10, 3, 1, 'Estadística', NULL),
(11, 3, 1, 'Unidad de Definición Institucional II', NULL),
(12, 3, 1, 'Problemáticas Socio Contemporáneas', NULL),
(13, 3, 1, 'Inglés Técnico II', NULL),
(14, 3, 1, 'Innovación y Desarrollo Emprendedor', NULL),
(15, 3, 1, 'Estrategia Empresarial', NULL),
(16, 3, 1, 'Sistema de Información para la Gestión de las Organizaciones', NULL),
(17, 3, 1, 'Gestión Financiera', NULL),
(18, 3, 1, 'Práctica Profesionalizante I', NULL),
(19, 3, 1, 'Ética y Responsabilidad Social', NULL),
(20, 3, 1, 'Evaluación y Gestión de Proyectos de Inversión', NULL),
(21, 3, 1, 'Control de Gestión', NULL),
(22, 3, 1, 'Gestión de Seguridad, Salud Ocupacional y Medio Ambiente', NULL),
(23, 3, 1, 'Práctica Profesionalizante II', NULL),
(24, 3, 2, 'Sistemas Operativos', '2026-11-28 14:44:00'),
(25, 3, 2, 'Comunicación', NULL),
(26, 3, 2, 'Unidad de Definición Institucional I - Lengua', NULL),
(27, 3, 2, 'Matemática', NULL),
(28, 3, 2, 'Inglés Técnico I', NULL),
(29, 3, 2, 'Administración', NULL),
(30, 3, 2, 'Tecnologías de la Información', NULL),
(31, 3, 2, 'Lógica y Estructura de Datos', NULL),
(32, 3, 2, 'Ingeniería de Software I', NULL),
(33, 3, 2, 'Unidad de Definición Institucional II', NULL),
(34, 3, 2, 'Problemáticas Socio Contemporáneas', NULL),
(35, 3, 2, 'Inglés Técnico II', NULL),
(36, 3, 2, 'Innovación y Desarrollo Emprendedor', NULL),
(37, 3, 2, 'Estadística', NULL),
(38, 3, 2, 'Programación I', NULL),
(39, 3, 2, 'Ingeniería de Software II', NULL),
(40, 3, 2, 'Base de Datos I', NULL),
(41, 3, 2, 'Práctica Profesionalizante I', NULL),
(42, 3, 2, 'Derecho y Legislación Laboral', NULL),
(43, 3, 2, 'Ética y Responsabilidad Social', NULL),
(44, 3, 2, 'Redes y Comunicación', NULL),
(45, 3, 2, 'Programación II', NULL),
(46, 3, 2, 'Gestión de Proyectos de Software', NULL),
(47, 3, 2, 'Base de Datos II', NULL),
(48, 3, 2, 'Práctica Profesionalizante II', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notas_mesas`
--

CREATE TABLE `notas_mesas` (
  `idNota` int(11) NOT NULL,
  `mesa_id` int(11) NOT NULL,
  `alumno_id` int(11) NOT NULL,
  `nota` decimal(4,2) NOT NULL,
  `actualizada_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `notas_mesas`
--

INSERT INTO `notas_mesas` (`idNota`, `mesa_id`, `alumno_id`, `nota`, `actualizada_en`) VALUES
(2, 24, 25, 8.00, '2026-11-21 12:50:46');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `periodos_inscripcion`
--

CREATE TABLE `periodos_inscripcion` (
  `idPeriodo` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'inactivo',
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `periodos_inscripcion`
--

INSERT INTO `periodos_inscripcion` (`idPeriodo`, `fecha_inicio`, `fecha_fin`, `estado`, `descripcion`) VALUES
(1, '2026-02-01', '2026-03-31', 'inactivo', 'Periodo de inscripción primer cuatrimestre 2026');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `periodos_mesas`
--

CREATE TABLE `periodos_mesas` (
  `idPeriodoMesa` int(11) NOT NULL,
  `descripcion` varchar(150) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'inactivo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `periodos_mesas`
--

INSERT INTO `periodos_mesas` (`idPeriodoMesa`, `descripcion`, `fecha_inicio`, `fecha_fin`, `estado`) VALUES
(3, 'mesas agosto', '2026-11-21', '2026-11-22', 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `idUsuario` int(11) NOT NULL,
  `dni` varchar(8) NOT NULL,
  `perfil` varchar(45) NOT NULL,
  `clave` varchar(45) NOT NULL,
  `nombre_carrera` varchar(50) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`idUsuario`, `dni`, `perfil`, `clave`, `nombre_carrera`, `telefono`, `email`, `foto_perfil`) VALUES
(1, '49894867', '1', '123123123', '', '34089595954', 'test@gmail.com', 'imagenes/perfiles/1795261919_usr_1.jpg'),
(2, '12345678', '2', '123123123', '', '3408585858', 'test@gmail.com', NULL),
(3, '23000001', '1', '12345678', '', NULL, NULL, NULL),
(4, '30000001', '1', '12345678', '', NULL, NULL, NULL),
(5, '30000001', '1', '12345678', '', NULL, NULL, NULL),
(6, '30000235', '1', '36985214', '', NULL, NULL, NULL),
(7, '30000235', '1', '12345678', '', NULL, NULL, NULL),
(8, '22000564', '1', '98745612', '', NULL, NULL, NULL),
(9, '23000001', '1', '98745612', '', NULL, NULL, NULL),
(10, '23000002', '1', '12345678', '', NULL, NULL, NULL),
(11, '46136464', '1', '46136464', '', NULL, NULL, NULL),
(12, '34821347', '1', '34821347', '', NULL, NULL, NULL),
(13, '41493465', '1', '41493465', '', NULL, NULL, NULL),
(14, '12732505', '1', '12732505', '', NULL, NULL, NULL),
(15, '12732505', '1', '', 'Técnico en desarrollo de Software', NULL, NULL, NULL),
(16, '13654987', '1', 'Técnico en Gestión', '', NULL, NULL, NULL),
(17, '14256387', '1', '14256387', '', NULL, NULL, NULL),
(18, '96385274', '1', '96385274', '', NULL, NULL, NULL),
(19, '43434343', '1', '12345678', '', NULL, NULL, NULL),
(20, '12345678', '1', '123123123', '', NULL, NULL, NULL),
(21, '12345678', '1', '123123123', '', NULL, NULL, NULL),
(22, '12345678', '1', '123123123', '', NULL, NULL, NULL),
(23, '12345678', '1', '123123123', '', NULL, NULL, NULL),
(24, '12345678', '1', '123123123', '', NULL, NULL, NULL),
(25, '12345678', '1', '123123123', '', NULL, NULL, NULL),
(26, '12345678', '1', '123123123', '', NULL, NULL, NULL),
(27, '12345678', '1', '123123123', '', NULL, NULL, NULL),
(28, '77777777', '1', '123123123', '', NULL, NULL, NULL),
(29, '12345678', '2', '123123123', '', '34089595954', 'testbedelia@gmail.com', 'imagenes/perfiles/1795262290_bedel_29.jpg'),
(30, '12345671', '1', '123123123', '', '3408959595', 'test@gmail.com', 'imagenes/perfiles/1781540180_usr_30.jpeg'),
(31, '12852369', '1', '12369874', '', NULL, NULL, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `alumnos`
--
ALTER TABLE `alumnos`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `fk_Alumno_Usuario1_idx` (`Usuario_idUsuario`);

--
-- Indices de la tabla `bedelia`
--
ALTER TABLE `bedelia`
  ADD PRIMARY KEY (`idBedelia`),
  ADD KEY `fk_Bedelia_Usuario1_idx` (`Usuario_idUsuario`);

--
-- Indices de la tabla `carrera`
--
ALTER TABLE `carrera`
  ADD PRIMARY KEY (`idCarrera`);

--
-- Indices de la tabla `historial_inscripciones`
--
ALTER TABLE `historial_inscripciones`
  ADD PRIMARY KEY (`idHistorial`),
  ADD KEY `fk_historial_alumno` (`alumno_id`),
  ADD KEY `fk_historial_materia` (`materia_id`),
  ADD KEY `idx_historial_anio` (`anio_lectivo`);

--
-- Indices de la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `alumno_id` (`alumno_id`),
  ADD KEY `fk_inscripcion_materia` (`materia_id`),
  ADD KEY `idx_inscripcion_fecha` (`fecha_inscripcion`),
  ADD KEY `idx_inscripcion_condicion` (`condicion_cursado`);

--
-- Indices de la tabla `inscripciones_mesas`
--
ALTER TABLE `inscripciones_mesas`
  ADD PRIMARY KEY (`idInscripcionMesa`),
  ADD UNIQUE KEY `uq_inscripcion_mesa_alumno` (`mesa_id`,`alumno_id`),
  ADD KEY `idx_inscripcion_mesa_alumno` (`alumno_id`);

--
-- Indices de la tabla `materia`
--
ALTER TABLE `materia`
  ADD PRIMARY KEY (`idMateria`),
  ADD KEY `fk_Materia_Carrera1_idx` (`Carrera_idCarrera`);

--
-- Indices de la tabla `mesas_materias`
--
ALTER TABLE `mesas_materias`
  ADD PRIMARY KEY (`idMesa`),
  ADD UNIQUE KEY `uq_mesa_periodo_carrera_materia` (`periodo_id`,`carrera_id`,`materia`),
  ADD KEY `idx_mesa_carrera` (`carrera_id`);

--
-- Indices de la tabla `notas_mesas`
--
ALTER TABLE `notas_mesas`
  ADD PRIMARY KEY (`idNota`),
  ADD UNIQUE KEY `uq_nota_mesa_alumno` (`mesa_id`,`alumno_id`);

--
-- Indices de la tabla `periodos_inscripcion`
--
ALTER TABLE `periodos_inscripcion`
  ADD PRIMARY KEY (`idPeriodo`);

--
-- Indices de la tabla `periodos_mesas`
--
ALTER TABLE `periodos_mesas`
  ADD PRIMARY KEY (`idPeriodoMesa`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`idUsuario`),
  ADD KEY `idx_usuario_email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `alumnos`
--
ALTER TABLE `alumnos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `bedelia`
--
ALTER TABLE `bedelia`
  MODIFY `idBedelia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `carrera`
--
ALTER TABLE `carrera`
  MODIFY `idCarrera` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `historial_inscripciones`
--
ALTER TABLE `historial_inscripciones`
  MODIFY `idHistorial` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT de la tabla `inscripciones_mesas`
--
ALTER TABLE `inscripciones_mesas`
  MODIFY `idInscripcionMesa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `materia`
--
ALTER TABLE `materia`
  MODIFY `idMateria` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mesas_materias`
--
ALTER TABLE `mesas_materias`
  MODIFY `idMesa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT de la tabla `notas_mesas`
--
ALTER TABLE `notas_mesas`
  MODIFY `idNota` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `periodos_inscripcion`
--
ALTER TABLE `periodos_inscripcion`
  MODIFY `idPeriodo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `periodos_mesas`
--
ALTER TABLE `periodos_mesas`
  MODIFY `idPeriodoMesa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `idUsuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `alumnos`
--
ALTER TABLE `alumnos`
  ADD CONSTRAINT `fk_Alumno_Usuario1` FOREIGN KEY (`Usuario_idUsuario`) REFERENCES `usuario` (`idUsuario`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `bedelia`
--
ALTER TABLE `bedelia`
  ADD CONSTRAINT `fk_Bedelia_Usuario1` FOREIGN KEY (`Usuario_idUsuario`) REFERENCES `usuario` (`idUsuario`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `historial_inscripciones`
--
ALTER TABLE `historial_inscripciones`
  ADD CONSTRAINT `fk_historial_alumno` FOREIGN KEY (`alumno_id`) REFERENCES `alumnos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_historial_materia` FOREIGN KEY (`materia_id`) REFERENCES `materia` (`idMateria`) ON DELETE SET NULL;

--
-- Filtros para la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  ADD CONSTRAINT `fk_inscripcion_materia` FOREIGN KEY (`materia_id`) REFERENCES `materia` (`idMateria`) ON DELETE SET NULL,
  ADD CONSTRAINT `inscripciones_ibfk_1` FOREIGN KEY (`alumno_id`) REFERENCES `alumnos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `inscripciones_mesas`
--
ALTER TABLE `inscripciones_mesas`
  ADD CONSTRAINT `fk_inscripcion_mesa` FOREIGN KEY (`mesa_id`) REFERENCES `mesas_materias` (`idMesa`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_inscripcion_mesa_alumno` FOREIGN KEY (`alumno_id`) REFERENCES `alumnos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `materia`
--
ALTER TABLE `materia`
  ADD CONSTRAINT `fk_Materia_Carrera1` FOREIGN KEY (`Carrera_idCarrera`) REFERENCES `carrera` (`idCarrera`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `mesas_materias`
--
ALTER TABLE `mesas_materias`
  ADD CONSTRAINT `fk_mesa_carrera` FOREIGN KEY (`carrera_id`) REFERENCES `carrera` (`idCarrera`),
  ADD CONSTRAINT `fk_mesa_periodo` FOREIGN KEY (`periodo_id`) REFERENCES `periodos_mesas` (`idPeriodoMesa`) ON DELETE CASCADE;

--
-- Filtros para la tabla `notas_mesas`
--
ALTER TABLE `notas_mesas`
  ADD CONSTRAINT `fk_nota_inscripcion_mesa` FOREIGN KEY (`mesa_id`,`alumno_id`) REFERENCES `inscripciones_mesas` (`mesa_id`, `alumno_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
