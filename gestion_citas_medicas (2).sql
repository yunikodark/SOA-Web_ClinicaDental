-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 10-07-2025 a las 21:15:49
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `gestion_citas_medicas`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `citas`
--

CREATE TABLE `citas` (
  `id_cita` int(11) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `id_doctor` int(11) NOT NULL,
  `fecha_cita` date NOT NULL,
  `hora_cita` time NOT NULL,
  `estado` enum('disponible','agendada','completada','cancelada') NOT NULL DEFAULT 'agendada',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `citas`
--

INSERT INTO `citas` (`id_cita`, `id_paciente`, `id_doctor`, `fecha_cita`, `hora_cita`, `estado`, `fecha_creacion`) VALUES
(1, 1, 1, '2025-06-27', '15:30:00', 'completada', '2025-06-26 10:27:08'),
(2, 1, 2, '2025-07-14', '11:00:00', 'agendada', '2025-07-10 16:46:55'),
(3, 1, 1, '2025-07-19', '10:00:00', 'disponible', '2025-07-10 17:03:40'),
(4, 1, 1, '2025-07-19', '10:30:00', 'disponible', '2025-07-10 17:03:40'),
(5, 1, 1, '2025-07-19', '11:00:00', 'disponible', '2025-07-10 17:03:40'),
(6, 1, 1, '2025-07-19', '11:30:00', 'disponible', '2025-07-10 17:03:40'),
(7, 1, 1, '2025-07-19', '12:00:00', 'disponible', '2025-07-10 17:03:40'),
(8, 1, 1, '2025-07-19', '12:30:00', 'disponible', '2025-07-10 17:03:40'),
(9, 1, 1, '2025-07-19', '13:00:00', 'disponible', '2025-07-10 17:03:40'),
(10, 1, 1, '2025-07-19', '13:30:00', 'disponible', '2025-07-10 17:03:40'),
(11, 1, 1, '2025-07-19', '14:00:00', 'disponible', '2025-07-10 17:03:40'),
(12, 1, 1, '2025-07-19', '14:30:00', 'disponible', '2025-07-10 17:03:40'),
(13, 1, 1, '2025-07-19', '15:00:00', 'disponible', '2025-07-10 17:03:40'),
(14, 1, 1, '2025-07-19', '15:30:00', 'disponible', '2025-07-10 17:03:40'),
(15, 1, 1, '2025-07-19', '16:00:00', 'disponible', '2025-07-10 17:03:40'),
(16, 1, 1, '2025-07-19', '16:30:00', 'disponible', '2025-07-10 17:03:40'),
(17, 1, 1, '2025-07-19', '17:00:00', 'disponible', '2025-07-10 17:03:40'),
(18, 1, 1, '2025-07-19', '17:30:00', 'disponible', '2025-07-10 17:03:40'),
(19, 1, 1, '2025-07-21', '10:00:00', 'disponible', '2025-07-10 17:03:40'),
(20, 1, 1, '2025-07-21', '10:30:00', 'disponible', '2025-07-10 17:03:40'),
(21, 1, 1, '2025-07-21', '11:00:00', 'disponible', '2025-07-10 17:03:40'),
(22, 1, 1, '2025-07-21', '11:30:00', 'disponible', '2025-07-10 17:03:40'),
(23, 3, 3, '2025-07-12', '10:30:00', 'agendada', '2025-07-10 17:49:30'),
(24, 3, 3, '2025-07-26', '10:30:00', 'agendada', '2025-07-10 17:56:33'),
(25, 4, 3, '2025-07-19', '10:30:00', 'agendada', '2025-07-10 17:58:06'),
(26, 3, 3, '2025-08-02', '10:30:00', 'agendada', '2025-07-10 18:15:57');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `doctores`
--

CREATE TABLE `doctores` (
  `id_doctor` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_especialidad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `doctores`
--

INSERT INTO `doctores` (`id_doctor`, `id_usuario`, `id_especialidad`) VALUES
(1, 101, 4),
(2, 102, 1),
(3, 204, 6),
(4, 208, 3),
(5, 209, 5),
(6, 210, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `especialidades`
--

CREATE TABLE `especialidades` (
  `id_especialidad` int(11) NOT NULL,
  `nombre_especialidad` varchar(100) NOT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `especialidades`
--

INSERT INTO `especialidades` (`id_especialidad`, `nombre_especialidad`, `estado`) VALUES
(1, 'Ortodoncia', 'activo'),
(2, 'Endodoncia', 'activo'),
(3, 'Periodoncia', 'activo'),
(4, 'Cirugía Oral', 'activo'),
(5, 'Odontopediatría', 'activo'),
(6, 'Estética Dental', 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_medico`
--

CREATE TABLE `historial_medico` (
  `id_historial` int(11) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `id_cita` int(11) DEFAULT NULL,
  `archivo_historial` varchar(255) DEFAULT NULL COMMENT 'Ruta del archivo subido por el paciente',
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `historial_medico`
--

INSERT INTO `historial_medico` (`id_historial`, `id_paciente`, `id_cita`, `archivo_historial`, `fecha_subida`) VALUES
(1, 3, 23, 'uploads/historiales_pacientes/1752169770_Cita-2025-06-27.pdf', '2025-07-10 17:49:30'),
(2, 3, 24, 'uploads/historiales_pacientes/1752170193_Reporte_Prueba_Carga_Jellyfin.pdf', '2025-07-10 17:56:33'),
(3, 4, 25, 'uploads/historiales_pacientes/1752170286_Mi primer tablero.pdf', '2025-07-10 17:58:06'),
(4, 3, 26, 'uploads/historiales_pacientes/1752171357_Reporte_Resiliencia_Jellyfin.pdf', '2025-07-10 18:15:57');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios`
--

CREATE TABLE `horarios` (
  `id_horario` int(11) NOT NULL,
  `id_doctor` int(11) NOT NULL,
  `dia_semana` enum('Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo') NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `horarios`
--

INSERT INTO `horarios` (`id_horario`, `id_doctor`, `dia_semana`, `hora_inicio`, `hora_fin`) VALUES
(1, 2, 'Lunes', '11:00:00', '11:30:00'),
(2, 1, 'Martes', '10:30:00', '11:00:00'),
(3, 3, 'Sábado', '10:30:00', '11:00:00'),
(4, 3, 'Sábado', '11:00:00', '11:30:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pacientes`
--

CREATE TABLE `pacientes` (
  `id_paciente` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pacientes`
--

INSERT INTO `pacientes` (`id_paciente`, `id_usuario`, `fecha_nacimiento`, `direccion`) VALUES
(1, 201, '2007-07-02', 'Lima, Lima'),
(2, 202, '1988-11-22', 'Calle Falsa 456'),
(3, 203, '2007-07-02', 'Lima, Lima'),
(4, 205, '2007-07-02', 'Lima, Lima'),
(5, 206, '2007-07-02', 'av industrial 70043'),
(6, 207, '2006-07-02', 'Lima, Lima'),
(7, 211, '2002-07-02', 'Lima, Lima'),
(8, 212, '2005-07-04', 'Lima, Lima');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registros_citas`
--

CREATE TABLE `registros_citas` (
  `id_registro_cita` int(11) NOT NULL,
  `id_cita` int(11) NOT NULL,
  `anotaciones_doctor` text DEFAULT NULL,
  `recomendaciones` text DEFAULT NULL,
  `tratamiento` text DEFAULT NULL,
  `documento_receta` varchar(255) DEFAULT NULL COMMENT 'Ruta del PDF generado',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `celular` varchar(20) DEFAULT NULL,
  `dni` varchar(20) DEFAULT NULL,
  `correo` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('paciente','doctor','administrador') NOT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `apellido`, `celular`, `dni`, `correo`, `password`, `rol`, `estado`, `fecha_registro`) VALUES
(2, 'Axcel Roy', 'Lopez Ynoann', '987456321', '41256389', 'axcelas2344@gmail.com', '$2y$10$jb9P72lUEJz09TgWLeQ6UOg1GVRaLswyM6NMYWw47Rz78NLrw/YJO', 'administrador', 'activo', '2025-06-26 10:16:55'),
(101, 'Carlos', 'Rivera', '987456321', '45123698', 'carlos.rivera@clinica.com', '$2y$10$6MFNVutD18UCru42e03uuezFP4aKFplIy.8ZmxyqWkCEbY/da3ebq', 'doctor', 'activo', '2025-07-10 17:03:40'),
(102, 'Ana', 'Gómes', '932145687', '47851236', 'ana.gomez@clinica.com', '$2y$10$0VSEjkhxrMJ76Af/siknsOHS/2Iu0/EeEVXM//LiUPTQ/yHgy7i/e', 'doctor', 'activo', '2025-07-10 17:03:40'),
(201, 'Laura', 'Mendez', '987156324', '45213698', 'laura.mendez@email.com', 'laura.mendez@email.com', 'paciente', 'activo', '2025-07-10 17:03:40'),
(202, 'Jorge', 'Torres', '932178456', '65412398', 'jorge.torres@email.com', '$2y$10$7J2.BStC9k9Z4Z1z2J53y.iC1nLpE03jgKk8q.R1dG.F9x9y4yGcS', 'paciente', 'activo', '2025-07-10 17:03:40'),
(203, 'Jose D', 'Lopez Ynoann', '998745621', '49563217', 'jose182vb@gmail.com', '$2y$10$o15n84JhexUDCO5n4/qHS.kQ8iLNpzQM/JVbONa47PFzQZ3A6g/e2', 'paciente', 'activo', '2025-07-10 17:08:44'),
(204, 'Roy Axcel', 'Lopez', '933214567', '78456213', 'royax627@gmail.com', '$2y$10$WKZH6q3azEJ88YUAQ6nHlOO7k6ZGS20mY7JtKxWWl.3MhqltxHyz6', 'doctor', 'activo', '2025-07-10 17:30:23'),
(205, 'David', 'Pérez', '951236874', '23145698', 'davisdd@gmail.com', '$2y$10$upwNufHuhAGJUK3v57tLEO/w4goDpifTkh0Db7zKPW9Y5vcjGwSIK', 'paciente', 'activo', '2025-07-10 17:57:42'),
(206, 'Joel Alonso', 'Vazques Miguel', '963258711', '78945612', 'joel3q182vb@gmail.com', '$2y$10$YSOLe34qQI7Cv6rcnhrhseMAq1ur9ID8OMIsUUrKJ1fm5cUwSVeuW', 'paciente', 'activo', '2025-07-10 18:25:32'),
(207, 'Natly', 'Damian', '963258741', '78451239', 'damiannaty23b@gmail.com', '$2y$10$wc2h1Trya99kwLumkbinW.vUKhk4cq9sslXJABXJ7Ci1GytdLQYFe', 'paciente', 'activo', '2025-07-10 18:31:05'),
(208, 'Zully', 'Rojas Vis', '951236874', '78945621', 'zulyysi4@gmail.com', '$2y$10$uCp9j1kwOfqrg2h6nMCF9eXfLbuZnrv.1OFRl3vMQvXB0ZNp.YFOW', 'doctor', 'activo', '2025-07-10 18:38:29'),
(209, 'Rosa Maria', 'Ynonan Vidaurre', '951478236', '74189562', 'rasaee@gmail.com', '$2y$10$Ks7WE4IsjryHSd1418qkyuHbCQXkro6sfxbaM3/1v6UvwQtPOT372', 'doctor', 'activo', '2025-07-10 18:39:38'),
(210, 'Brayan Smith', 'Damian Yno', '963258700', '74125665', 'brayanss344@gmail.com', '$2y$10$09gXW.e4GBFp/fCCQpdjyeYBNeV6Yg7B5LilMo4L6I0lzIw5KXe4O', 'doctor', 'activo', '2025-07-10 18:42:28'),
(211, 'Roy Axcel', 'Nose Nose', '987456321', '45632177', 'Torresallccarimaroyaxel@gmail.com', '$2y$10$NNsLR/pfJQoTUR38k5Md..FRdls14t5Y4PnfOXYVphcOGeudGAKCO', 'paciente', 'activo', '2025-07-10 19:12:02'),
(212, 'Jose D', 'Ynonan V', '987456231', '74444111', 'yno2004jc@gmail.com', '$2y$10$vEv42W1Bfw/4e7ny2do0NOBSmKZZEUfKoeK7xyz4qcsH9w6NZcU/i', 'paciente', 'activo', '2025-07-10 19:13:10');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `citas`
--
ALTER TABLE `citas`
  ADD PRIMARY KEY (`id_cita`),
  ADD KEY `id_paciente` (`id_paciente`),
  ADD KEY `id_doctor` (`id_doctor`);

--
-- Indices de la tabla `doctores`
--
ALTER TABLE `doctores`
  ADD PRIMARY KEY (`id_doctor`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_especialidad` (`id_especialidad`);

--
-- Indices de la tabla `especialidades`
--
ALTER TABLE `especialidades`
  ADD PRIMARY KEY (`id_especialidad`),
  ADD UNIQUE KEY `nombre_especialidad` (`nombre_especialidad`);

--
-- Indices de la tabla `historial_medico`
--
ALTER TABLE `historial_medico`
  ADD PRIMARY KEY (`id_historial`),
  ADD KEY `id_paciente` (`id_paciente`),
  ADD KEY `id_cita` (`id_cita`);

--
-- Indices de la tabla `horarios`
--
ALTER TABLE `horarios`
  ADD PRIMARY KEY (`id_horario`),
  ADD KEY `id_doctor` (`id_doctor`);

--
-- Indices de la tabla `pacientes`
--
ALTER TABLE `pacientes`
  ADD PRIMARY KEY (`id_paciente`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `registros_citas`
--
ALTER TABLE `registros_citas`
  ADD PRIMARY KEY (`id_registro_cita`),
  ADD UNIQUE KEY `id_cita` (`id_cita`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `citas`
--
ALTER TABLE `citas`
  MODIFY `id_cita` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `doctores`
--
ALTER TABLE `doctores`
  MODIFY `id_doctor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `especialidades`
--
ALTER TABLE `especialidades`
  MODIFY `id_especialidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `historial_medico`
--
ALTER TABLE `historial_medico`
  MODIFY `id_historial` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `horarios`
--
ALTER TABLE `horarios`
  MODIFY `id_horario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `pacientes`
--
ALTER TABLE `pacientes`
  MODIFY `id_paciente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `registros_citas`
--
ALTER TABLE `registros_citas`
  MODIFY `id_registro_cita` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=213;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `citas`
--
ALTER TABLE `citas`
  ADD CONSTRAINT `citas_ibfk_1` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id_paciente`),
  ADD CONSTRAINT `citas_ibfk_2` FOREIGN KEY (`id_doctor`) REFERENCES `doctores` (`id_doctor`);

--
-- Filtros para la tabla `doctores`
--
ALTER TABLE `doctores`
  ADD CONSTRAINT `doctores_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `doctores_ibfk_2` FOREIGN KEY (`id_especialidad`) REFERENCES `especialidades` (`id_especialidad`) ON DELETE CASCADE;

--
-- Filtros para la tabla `historial_medico`
--
ALTER TABLE `historial_medico`
  ADD CONSTRAINT `historial_medico_ibfk_1` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id_paciente`) ON DELETE CASCADE,
  ADD CONSTRAINT `historial_medico_ibfk_2` FOREIGN KEY (`id_cita`) REFERENCES `citas` (`id_cita`) ON DELETE SET NULL;

--
-- Filtros para la tabla `horarios`
--
ALTER TABLE `horarios`
  ADD CONSTRAINT `horarios_ibfk_1` FOREIGN KEY (`id_doctor`) REFERENCES `doctores` (`id_doctor`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pacientes`
--
ALTER TABLE `pacientes`
  ADD CONSTRAINT `pacientes_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `registros_citas`
--
ALTER TABLE `registros_citas`
  ADD CONSTRAINT `registros_citas_ibfk_1` FOREIGN KEY (`id_cita`) REFERENCES `citas` (`id_cita`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
