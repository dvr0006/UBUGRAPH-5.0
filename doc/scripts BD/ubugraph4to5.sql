-- Modificaciones a la base de datos para la versión UBUGraph 5.0
-- a partir de la estructura UBUGraph 4.0
-- -------------------------------------------------------------------------------------
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
-- Convert en DOUBLE el campo DURACION que es integer
ALTER TABLE `nodos` DROP `DURACION`;
ALTER TABLE `nodos` ADD `DURACION` DOUBLE NOT NULL AFTER `ID_GRAFO`;