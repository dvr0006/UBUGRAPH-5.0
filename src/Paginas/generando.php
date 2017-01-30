<!DOCTYPE HTML>
<?php
	//Comprobamos que el usuario esta logueado
	session_start();
	if (!isset($_SESSION["usuario"]))
	{ 
		header("Location: /");
	}
	require_once("funciones.php");
    require_once("funcionesPert.php");
	//Cargamos el idioma
	require_once("../".idioma());
	require_once("./Actividad.php");
    require_once("StandardNormal.php");
?>
<html>	
	<head>
		<link rel="stylesheet" type="text/css" 	href="/estilos/estilo.css">
	</head>
	<body>
		 <div id="main" style="text-align: center;">
			<?php
				$nombres = array();
				$precedencias = array();
				$duraciones = array();
				$actividades = array();
				
				$conexion = conectarse();
				
                $mediaCritica=0;
                $varianzaCritica=0;
                
				//Buscamos primero si hay grafos pendientes de resolver para este usuario.
				$consulta = "SELECT * FROM grafos WHERE CALIFICACION IS NULL AND ID_USUARIO = {$_SESSION["id_usuario"]};";
				$result = $conexion->query($consulta);
				$tuplas = $result->num_rows;
				//En caso de haber alguno pendiente cargamos los datos de la BD
				if($tuplas != 0)
				{
					$reg = $result->fetch_assoc();
					$metodo = strtolower($reg["RESOLUCION"]);
					$consulta = "SELECT * FROM nodos WHERE ID_GRAFO = {$reg["ID_GRAFO"]};";
					$result = $conexion->query($consulta);
					$reg = $result->fetch_assoc();
					$idGrafo = $reg["ID_GRAFO"];
					
					while($reg)
					{
						array_push($nombres, $reg["NOMBRE"]);
						array_push($precedencias, $reg["PRECEDENCIAS"]);
						array_push($duraciones, $reg["DURACION"]);
						$actividad = new Actividad($reg["NOMBRE"],$reg["DURACION"],$reg["DISTRIBUCION"],$reg["MEDIA"],$reg["VARIANZA"],$reg["PARAMETRO_01"],$reg["PARAMETRO_02"],$reg["PARAMETRO_03"]);
                        array_push($actividades, $actividad);
						$reg = $result->fetch_assoc();
					}
				}
				//En caso contrario cargamos los parámetros del formulario para generar un grafo nuevo
				else if(isset($_POST["numActividades"]) && isset($_POST["probabilidad"]) && isset($_POST["metodo"]))
				{
					$numAct = $_POST["numActividades"];
					$metodo = $_POST["metodo"];
					$probabilidad = $_POST["probabilidad"];
					$ids = array(0 => "A", 1 => "B", 2 => "C", 3 => "D", 4 => "E", 5 => "F", 6 => "G", 7 => "H", 8 => "I", 9 => "J", 10 => "K", 11 => "L", 12 => "M", 13 => "N", 14 => "O", 15 => "P", 16 => "Q", 17 => "R", 18 => "S", 19 => "T", 20 => "U", 21 => "V", 22 => "W", 23 => "X", 24 => "Y", 25 => "Z");
					
					//Variable para controlar si hay que generar un nuevo grafo
					$generarNuevoGrafo=true;
					while($generarNuevoGrafo){
						//Insertamos el nuevo grafo en la BD
						$consulta = "INSERT INTO grafos(ID_GRAFO, ID_USUARIO, RESOLUCION, CALIFICACION, FECHA) VALUES(0, {$_SESSION["id_usuario"]}, UPPER('{$metodo}'), NULL, NOW());";
						$result = $conexion->query($consulta);
						
						$idGrafo = $conexion->insert_id;
						
						//Generamos la tabla de precedencias					
						for($i = 0; $i < $numAct; $i++)
						{
							array_push($nombres, $ids[$i]);
							
							$p = "";
							for($j = 0; $j < $i; $j++)
							{
								if($j != $i)
								{
									if(rand(1,100) <= $probabilidad)
									{
										if($p == "")
										{
											$p = $nombres[$j];
										}
										else
										{
											$p = $p." ".$nombres[$j];
										}
									}
								}
							}
							
							array_push($precedencias, $p);

							//Generar actividad auxiliar dependiento del método de resolución
							if($metodo == "pert_probabilistico"){
								$actividad=randomActividadProbabilistica($ids[$i]);
							}
							else{
								$actividad=new Actividad($ids[$i], rand(1,25),null,null,null,null,null,null);
							}
							array_push($actividades, $actividad);
							
							$duracionNodo = $actividad->getDuracion();
							array_push($duraciones, $duracionNodo);
							
							//Guardamos cada uno de los nodos generados en la BD
							$null01 = is_null($actividad->getDistribucion())?'NULL':"'".$actividad->getDistribucion()."'";
							$null02 = is_null($actividad->getMedia())?'NULL':$actividad->getMedia();
							$null03 = is_null($actividad->getVarianza())?'NULL':$actividad->getVarianza();
							$null04 = is_null($actividad->getParametro_01())?'NULL':$actividad->getParametro_01();
							$null05 = is_null($actividad->getParametro_02())?'NULL':$actividad->getParametro_02();
							$null06 = is_null($actividad->getParametro_03())?'NULL':$actividad->getParametro_03();
							$consulta = "INSERT INTO nodos(NOMBRE, ID_GRAFO, DURACION, PRECEDENCIAS, DISTRIBUCION, MEDIA, VARIANZA, PARAMETRO_01, PARAMETRO_02, PARAMETRO_03) VALUES('{$ids[$i]}', {$idGrafo}, {$duracionNodo}, '{$p}', {$null01}, {$null02}, {$null03}, {$null04}, {$null05}, {$null06});";
							$conexion->query($consulta);
						}
                        //Comprobar si hay que generar un nuevo grafo (solo queremos grafos con un único camino crítico)
                        //Porque vamos a preguntar al alumno por datos del camino crítico
                        $info=infoCaminosCriticos($nombres, $precedencias, $duraciones, $idGrafo, $conexion);
                        $numCaminosCriticos=$info[0];
                        $mediaCritica=$info[1];
                        $varianzaCritica=$info[2];
						if($numCaminosCriticos!=1 && $metodo=="pert_probabilistico"){
                            //Procede generar otro grafo candidato
                            $nombres = array();
                            $precedencias = array();
                            $duraciones = array();
                            $actividades = array();
                            //Borrar de la base de datos el grafo generado
                            $consulta = "DELETE FROM grafos WHERE ID_GRAFO = {$idGrafo};";
                            $conexion->query($consulta);
						}
						else{
							//El grafo generado es válido
							$generarNuevoGrafo=false;
						}
					}
				}
				//Sin tampoco hay datos del formulario, entonces no tenemos datos y avisamos del error.
				else
				{
					header("Location: ../paginas/error.php?e=".urlencode($texto["Generando_1"]));
				}
				
				//Mostramos al usuario la tabla de precedencias.
				echo "\n<h2 style=\"text-align: center;\">{$texto["Generando_2"]}  (".strtoupper($metodo).")</h2>";
				echo "\n<table style=\"width: 100%;\">";
					echo "\n<tr>";
						echo "\n<th>{$texto["Generando_3"]}</th>";
						echo "\n<th>{$texto["Generando_4"]}</th>";
						echo "\n<th>{$texto["Generando_5"]}</th>";
						if($metodo == "pert_probabilistico"){
							echo "\n<th>{$texto["Generando_13"]}</th>";
							echo "\n<th>{$texto["Generando_14"]}</th>";
							echo "\n<th>{$texto["Generando_15"]}</th>";
							echo "\n<th>{$texto["Generando_16"]}</th>";
							echo "\n<th>{$texto["Generando_17"]}</th>";
							echo "\n<th>{$texto["Generando_18"]}</th>";
						}
					echo "\n</tr>";
					for($i = 0; $i < sizeof($nombres); $i++)
					{
						echo "\n<tr>";
							echo "\n<td>{$nombres[$i]}</td>";
							echo "\n<td>{$precedencias[$i]}</td>";
							echo "\n<td>{$duraciones[$i]}</td>";
							if($metodo == "pert_probabilistico"){
								echo "\n<td>{$actividades[$i]->getDistribucion()}</td>";
								echo "\n<td>{$actividades[$i]->getMedia()}</td>";
								echo "\n<td>{$actividades[$i]->getVarianza()}</td>";
								echo "\n<td>{$actividades[$i]->getParametro_01()}</td>";
								echo "\n<td>{$actividades[$i]->getParametro_02()}</td>";
								echo "\n<td>{$actividades[$i]->getParametro_03()}</td>";
							}
						echo "\n</tr>";
					}
				echo "\n</table><br>";
				$metodoOriginal=$metodo;
				if ($metodo == "pert" || $metodo == "pert_probabilistico")
					$metodo = "pertCorregido";
				
				//Formulario de resolucion del grafo.
				echo "<form id=\"generar_problema\" action=\"./{$metodo}.php\" method=\"post\">";			

				//Campos ocultos necesarios para resolver posteriormente el grafo
				if($nombres != null)
				{
					for($i = 0; $i < sizeof($nombres); $i++)
					{
						echo "\n<input hidden name=\"nombres[]\" value=\"{$nombres[$i]}\"/>";
						echo "\n<input hidden name=\"precedencias[]\" value=\"{$precedencias[$i]}\"/>";
						echo "\n<input hidden name=\"duraciones[]\" value=\"{$duraciones[$i]}\"/>";
					}
				}
				
				//Comprobamos si ya hay preguntas generadas para este grafo
				$consulta = "SELECT * FROM preguntas WHERE ID_GRAFO = {$idGrafo};";		
				$result = $conexion->query($consulta);
				$tuplas = $result->num_rows;
				//Si no las hay las generamos ahora
				if($tuplas == 0)
				{
				    if($metodoOriginal=="pert_probabilistico"){
				        //Generar preguntas probabilísticas
				        //Pregunta 6
				        //Probabilidad a partir de un tiempo de finalización aleatorio
                        $preg_resp6=StandardNormal::getPreguntaProbabilidadFromTiempo($mediaCritica, $varianzaCritica);
				        $p6=$preg_resp6["pregunta"];
                        $r6=$preg_resp6["respuesta"];
            
                        //Pregunta 7
                        //Tiempo de finalización a partir de un tiempo de una probabilidad aleatoria
                        $preg_resp7=StandardNormal::getPreguntaTiempoFromProbabilidad($mediaCritica, $varianzaCritica);
				        $p7=$preg_resp7["pregunta"];
                        $r7=$preg_resp7["respuesta"];
                        //Guardamos las preguntas en la base de datos
                        $consulta = "INSERT INTO preguntas(ID_GRAFO, TIEMPO_FIN, RIESGO) VALUES({$idGrafo}, '{$p6}', '{$p7}');";
                        $conexion->query($consulta);
                        //Guardamos las respuestas correctas en la base de datos
                        $consulta = "INSERT INTO respuestas_correctas(ID_GRAFO, RESPUESTA_TIEMPO, RESPUESTA_RIESGO) VALUES({$idGrafo}, '{$r6}', '{$r7}');";
                        $conexion->query($consulta);
				    }
                    else{
                        $x = $nombres[rand(0,count($nombres) -1)];
                        $y = $nombres[rand(0,count($nombres) -1)];
                        $z = $nombres[rand(0,count($nombres) -1)];
                        //Y las guardamos en la BD
                        $consulta = "INSERT INTO preguntas(ID_GRAFO, NOMBRE_1, NOMBRE_2, NOMBRE_3) VALUES({$idGrafo}, '{$x}', '{$y}', '{$z}');";
                        $conexion->query($consulta);
                    }
					
				}
				//Si si que las hay, simplemente las leemos
				else
				{
					$res = $result->fetch_assoc();
					
                    if($metodoOriginal=="pert_probabilistico"){
                        $p6 = $res["TIEMPO_FIN"];
                        $p7 = $res["RIESGO"];
                    }
                    else{
                        $x = $res["NOMBRE_1"];
                        $y = $res["NOMBRE_2"];
                        $z = $res["NOMBRE_3"];
                    }
				}
		
				//Cerramos la conexion con la BD
				mysqli_close($conexion);
	
				//Mostramos al usuario las preguntas que debe responder y los campos para hacerlo.
				echo "\n<h2 style=\"text-align: center;\">{$texto["Generando_12"]}</h2>";
				
                if($metodoOriginal=="pert_probabilistico"){
                    echo "\n<label>{$texto["Generando_19"]} {$p6}{$texto["Generando_20"]}</label>";
                    echo "<input type=\"number\" required step=\"0.01\" name=\"pregunta_tiempo\" min=\"0\" value=\"-1\"><br>";

                    echo "\n<label>{$texto["Generando_21"]} {$p7}{$texto["Generando_22"]}</label>";
                    echo "<input type=\"number\" required step=\"0.1\" name=\"pregunta_riesgo\" min=\"0\" value=\"-1\"><br>";
                }
                else{
                    echo "\n<label>{$texto["Generando_6"]} {$x}?</label>";
                    echo "<input type=\"number\" required step=\"1\" name=\"pregunta1\" min=\"0\" value=\"-1\"><br>";
                    
                    echo "\n<label>{$texto["Generando_7"]} {$y}?</label>";
                    echo "<input type=\"number\" required step=\"1\" name=\"pregunta2\" min=\"0\" value=\"-1\"><br>";
                    
                    echo "\n<label>{$texto["Generando_8"]} {$z}?</label>";
                    echo "<input type=\"number\" required step=\"1\" name=\"pregunta3\" min=\"0\" value=\"-1\"><br>";
                    
                    echo "\n<label>{$texto["Generando_9"]}</label>";
                    echo "<input type=\"number\" required step=\"1\" name=\"pregunta4\" min=\"0\" value=\"-1\"><br>";
                    
                    echo "\n<label>{$texto["Generando_10"]}</label>";
                    echo "<input type=\"text\" required name=\"pregunta5\" maxlength=\"50\">";
                }
				
				//Este campo indica que se debe resolver el formulario y es necesario para su correcto procesamiento
				echo "\n<input hidden name=\"resolver\" value=\"s\"/>";
				//Por ultimo mostramos el boton de envio del formulario.
				echo "\n<br><br><input type=\"submit\" value=\"{$texto["Generando_11"]}\"/>";
				echo "</form>";
			?>
		</div>
	</body>
</html>