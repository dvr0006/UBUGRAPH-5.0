-- Modificaciones a la base de datos para la versión UBUGraph 5.0 a partir de la estructura UBUGraph 4.0
-- -----------------------------------------------------------------------------------------------------
-- Tabla NODOS
-- Añadir atributos para representar parámetros de distintas distribuciones de probabilidad
ALTER TABLE `nodos` ADD `DISTRIBUCION` ENUM('NORMAL','BETA','TRIANGULAR','UNIFORME') CHARACTER SET utf8 COLLATE utf8_spanish2_ci NULL , 
	ADD `MEDIA` DOUBLE NULL , 
	ADD `VARIANZA` DOUBLE NULL , 
	ADD `PARAMETRO_01` DOUBLE NULL , 
	ADD `PARAMETRO_02` DOUBLE NULL , 
	ADD `PARAMETRO_03` DOUBLE NULL ;
	
-- Tabla GRAFOS
-- Permitir el valor 'PERT_PROBALISTICO' en el campo enumerado 'RESOLUCION'
ALTER TABLE `grafos` CHANGE `RESOLUCION` `RESOLUCION` ENUM('ROY','PERT','PERT_PROBABILISTICO') CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL;

-- Tabla NODOS
-- Convertir en DOUBLE el campo DURACION que es integer
ALTER TABLE `nodos` DROP `DURACION`;
ALTER TABLE `nodos` ADD `DURACION` DOUBLE NOT NULL AFTER `ID_GRAFO`;

-- Tabla PREGUNTAS
-- Añadir atributos para representar los valores para preguntas estocásticas (Tiempo final del proyecto y riesgo asumible)
ALTER TABLE `preguntas` ADD `TIEMPO_FIN` DOUBLE NULL , ADD `RIESGO` DOUBLE NULL ;
ALTER TABLE `preguntas` CHANGE `NOMBRE_1` `NOMBRE_1` VARCHAR(2) CHARACTER SET utf8 COLLATE utf8_spanish2_ci NULL;
ALTER TABLE `preguntas` CHANGE `NOMBRE_2` `NOMBRE_2` VARCHAR(2) CHARACTER SET utf8 COLLATE utf8_spanish2_ci NULL;
ALTER TABLE `preguntas` CHANGE `NOMBRE_3` `NOMBRE_3` VARCHAR(2) CHARACTER SET utf8 COLLATE utf8_spanish2_ci NULL;

-- Tabla RESPUESTAS
-- Añadir atributos para representar los valores para las respuestas estocásticas del usuario (Tiempo final del proyecto y riesgo asumible)
ALTER TABLE `respuestas` ADD `RESPUESTA_TIEMPO` DOUBLE NULL , ADD `RESPUESTA_RIESGO` DOUBLE NULL ;
ALTER TABLE `respuestas` CHANGE `RESPUESTA_1` `RESPUESTA_1` INT(5) NULL;
ALTER TABLE `respuestas` CHANGE `RESPUESTA_2` `RESPUESTA_2` INT(5) NULL;
ALTER TABLE `respuestas` CHANGE `RESPUESTA_3` `RESPUESTA_3` INT(5) NULL;
ALTER TABLE `respuestas` CHANGE `RESPUESTA_4` `RESPUESTA_4` INT(5) NULL;
ALTER TABLE `respuestas` CHANGE `RESPUESTA_5` `RESPUESTA_5` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_spanish2_ci NULL;

-- Tabla RESPUESTAS_CORRECTAS
-- Añadir atributos para representar los valores para las respuestas estocásticas correctas (Tiempo final del proyecto y riesgo asumible)
ALTER TABLE `respuestas_correctas` ADD `RESPUESTA_TIEMPO` DOUBLE NULL , ADD `RESPUESTA_RIESGO` DOUBLE NULL ;
ALTER TABLE `respuestas_correctas` CHANGE `RESPUESTA_1` `RESPUESTA_1` INT(5) NULL;
ALTER TABLE `respuestas_correctas` CHANGE `RESPUESTA_2` `RESPUESTA_2` INT(5) NULL;
ALTER TABLE `respuestas_correctas` CHANGE `RESPUESTA_3` `RESPUESTA_3` INT(5) NULL;
ALTER TABLE `respuestas_correctas` CHANGE `RESPUESTA_4` `RESPUESTA_4` INT(5) NULL;
ALTER TABLE `respuestas_correctas` CHANGE `RESPUESTA_5` `RESPUESTA_5` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_spanish2_ci NULL;