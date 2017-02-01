-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 01-02-2017 a las 02:12:39
-- Versión del servidor: 5.6.17
-- Versión de PHP: 5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de datos: `ubugraph`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grafos`
--

CREATE TABLE IF NOT EXISTS `grafos` (
  `ID_GRAFO` int(7) NOT NULL AUTO_INCREMENT,
  `ID_USUARIO` int(5) NOT NULL,
  `RESOLUCION` enum('ROY','PERT','PERT_PROBABILISTICO') CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
  `CALIFICACION` int(2) DEFAULT NULL,
  `FECHA` datetime NOT NULL,
  `GRAFO` text COLLATE utf8_spanish2_ci,
  PRIMARY KEY (`ID_GRAFO`),
  KEY `ID_USUARIO` (`ID_USUARIO`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci AUTO_INCREMENT=28 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupos`
--

CREATE TABLE IF NOT EXISTS `grupos` (
  `ID_GRUPO` int(5) NOT NULL AUTO_INCREMENT,
  `ID_TUTOR` int(5) NOT NULL,
  `NOMBRE` varchar(50) COLLATE utf8_spanish2_ci NOT NULL,
  `CODIGO` varchar(11) COLLATE utf8_spanish2_ci NOT NULL,
  PRIMARY KEY (`ID_GRUPO`),
  KEY `ID_TUTOR` (`ID_TUTOR`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nodos`
--

CREATE TABLE IF NOT EXISTS `nodos` (
  `NOMBRE` varchar(2) CHARACTER SET utf32 COLLATE utf32_spanish_ci NOT NULL,
  `ID_GRAFO` int(7) NOT NULL,
  `DURACION` double NOT NULL,
  `PRECEDENCIAS` varchar(100) COLLATE utf8mb4_spanish2_ci NOT NULL,
  `DISTRIBUCION` enum('NORMAL','BETA','TRIANGULAR','UNIFORME') CHARACTER SET utf8 COLLATE utf8_spanish2_ci DEFAULT NULL,
  `MEDIA` double DEFAULT NULL,
  `VARIANZA` double DEFAULT NULL,
  `PARAMETRO_01` double DEFAULT NULL,
  `PARAMETRO_02` double DEFAULT NULL,
  `PARAMETRO_03` double DEFAULT NULL,
  PRIMARY KEY (`NOMBRE`,`ID_GRAFO`),
  KEY `ID_GRAFO` (`ID_GRAFO`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preguntas`
--

CREATE TABLE IF NOT EXISTS `preguntas` (
  `ID_GRAFO` int(7) NOT NULL,
  `NOMBRE_1` varchar(2) CHARACTER SET utf8 COLLATE utf8_spanish2_ci DEFAULT NULL,
  `NOMBRE_2` varchar(2) CHARACTER SET utf8 COLLATE utf8_spanish2_ci DEFAULT NULL,
  `NOMBRE_3` varchar(2) CHARACTER SET utf8 COLLATE utf8_spanish2_ci DEFAULT NULL,
  `TIEMPO_FIN` double DEFAULT NULL,
  `RIESGO` double DEFAULT NULL,
  PRIMARY KEY (`ID_GRAFO`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pupilos`
--

CREATE TABLE IF NOT EXISTS `pupilos` (
  `ID_USUARIO` int(25) NOT NULL,
  `ID_GRUPO` int(5) NOT NULL,
  PRIMARY KEY (`ID_USUARIO`,`ID_GRUPO`),
  KEY `ID_GRUPO` (`ID_GRUPO`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `respuestas`
--

CREATE TABLE IF NOT EXISTS `respuestas` (
  `ID_GRAFO` int(7) NOT NULL,
  `RESPUESTA_1` int(5) DEFAULT NULL,
  `RESPUESTA_2` int(5) DEFAULT NULL,
  `RESPUESTA_3` int(5) DEFAULT NULL,
  `RESPUESTA_4` int(5) DEFAULT NULL,
  `RESPUESTA_5` varchar(50) COLLATE utf8_spanish2_ci DEFAULT NULL,
  `RESPUESTA_TIEMPO` double DEFAULT NULL,
  `RESPUESTA_RIESGO` double DEFAULT NULL,
  PRIMARY KEY (`ID_GRAFO`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `respuestas_correctas`
--

CREATE TABLE IF NOT EXISTS `respuestas_correctas` (
  `ID_GRAFO` int(7) NOT NULL,
  `RESPUESTA_1` int(5) DEFAULT NULL,
  `RESPUESTA_2` int(5) DEFAULT NULL,
  `RESPUESTA_3` int(5) DEFAULT NULL,
  `RESPUESTA_4` int(5) DEFAULT NULL,
  `RESPUESTA_5` varchar(50) COLLATE utf8_spanish2_ci DEFAULT NULL,
  `RESPUESTA_TIEMPO` double DEFAULT NULL,
  `RESPUESTA_RIESGO` double DEFAULT NULL,
  PRIMARY KEY (`ID_GRAFO`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE IF NOT EXISTS `usuarios` (
  `ID_USUARIO` int(5) NOT NULL AUTO_INCREMENT,
  `NOMBRE` varchar(25) COLLATE utf8_spanish2_ci NOT NULL,
  `CLAVE` blob NOT NULL,
  `TIPO` enum('A','P','G') COLLATE utf8_spanish2_ci NOT NULL COMMENT 'A -> Alumno, P -> Profesor, G -> Gestor (administrador)',
  `CORREO` varchar(50) COLLATE utf8_spanish2_ci NOT NULL,
  `ACTIVA` enum('S','N') COLLATE utf8_spanish2_ci NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ID_USUARIO`),
  UNIQUE KEY `NOMBRE` (`NOMBRE`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci AUTO_INCREMENT=15 ;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`ID_USUARIO`, `NOMBRE`, `CLAVE`, `TIPO`, `CORREO`, `ACTIVA`) VALUES
(1, 'Administrador', 0xc28d52037d9940f16055a0f508347a8a, 'G', 'ubugrap@gmail.com', 'S');

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `grafos`
--
ALTER TABLE `grafos`
  ADD CONSTRAINT `grafos_ibfk_1` FOREIGN KEY (`ID_USUARIO`) REFERENCES `usuarios` (`ID_USUARIO`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `grupos`
--
ALTER TABLE `grupos`
  ADD CONSTRAINT `grupos_ibfk_1` FOREIGN KEY (`ID_TUTOR`) REFERENCES `usuarios` (`ID_USUARIO`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `nodos`
--
ALTER TABLE `nodos`
  ADD CONSTRAINT `nodos_ibfk_1` FOREIGN KEY (`ID_GRAFO`) REFERENCES `grafos` (`ID_GRAFO`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `preguntas`
--
ALTER TABLE `preguntas`
  ADD CONSTRAINT `preguntas_ibfk_1` FOREIGN KEY (`ID_GRAFO`) REFERENCES `grafos` (`ID_GRAFO`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `pupilos`
--
ALTER TABLE `pupilos`
  ADD CONSTRAINT `pupilos_ibfk_1` FOREIGN KEY (`ID_USUARIO`) REFERENCES `usuarios` (`ID_USUARIO`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pupilos_ibfk_2` FOREIGN KEY (`ID_GRUPO`) REFERENCES `grupos` (`ID_GRUPO`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `respuestas`
--
ALTER TABLE `respuestas`
  ADD CONSTRAINT `respuestas_ibfk_1` FOREIGN KEY (`ID_GRAFO`) REFERENCES `grafos` (`ID_GRAFO`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `respuestas_correctas`
--
ALTER TABLE `respuestas_correctas`
  ADD CONSTRAINT `respuestas_correctas_ibfk_1` FOREIGN KEY (`ID_GRAFO`) REFERENCES `grafos` (`ID_GRAFO`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
