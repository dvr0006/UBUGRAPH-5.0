<!DOCTYPE HTML>
<?php
	session_start();
	if (!isset($_SESSION["usuario"]))
	{ 
		header("Location: /");
	}
	require_once("funcionesGeneraPreguntaEstocastica.php");

?>
<html>
	<head>
		<link rel="stylesheet" type="text/css" 	href="/estilos/estilo.css">		
	</head>
	<body>
		<div id="main">
		<?php
			
			$numAct = $_POST["numActividades"];
			$probabilidad = $_POST["probabilidad"];
			$ids = $ids = array(0 => "A", 1 => "B", 2 => "C", 3 => "D", 4 => "E", 5 => "F", 6 => "G", 7 => "H", 8 => "I", 9 => "J", 10 => "K", 11 => "L", 12 => "M", 13 => "N", 14 => "O", 15 => "P", 16 => "Q", 17 => "R", 18 => "S", 19 => "T", 20 => "U", 21 => "V", 22 => "W", 23 => "X", 24 => "Y", 25 => "Z");
		
			$contCloze = $_POST["cloze"];
			$numPreguntas = $contCloze;
			
			$file = fopen("./XML/preguntasEstocasticas.xml","w");
			fputs($file,"<?xml version=\"1.0\" ?>");
			fputs($file,"\n<quiz>");
					
			for($index=1;$index<=$numPreguntas;$index++){
				$nombres = array();
				$precedencias = array();
				$duraciones = array();
                $actividades = array();
                
                $mediaCritica=0;
                $varianzaCritica=0;				

                //Variable para controlar si hay que generar un nuevo grafo (Si no hay un único camino crítico)
                $generarNuevoGrafo=true;
                while($generarNuevoGrafo){
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
                        $actividad=randomActividadProbabilistica($ids[$i]);
                        array_push($actividades, $actividad);
                        
                        $duracionNodo = $actividad->getDuracion();
                        array_push($duraciones, $duracionNodo);
                    }
                    //Comprobar si hay que generar un nuevo grafo (solo queremos grafos con un único camino crítico)
                    //Porque vamos a preguntar al alumno por datos del camino crítico
                    $info=infoCaminosCriticos($nombres, $precedencias, $duraciones, null, null, $actividades);
                    $numCaminosCriticos=$info[0];
                    $mediaCritica=$info[1];
                    $varianzaCritica=$info[2];
                    if($numCaminosCriticos!=1){
                        //Procede generar otro grafo candidato
                        $nombres = array();
                        $precedencias = array();
                        $duraciones = array();
                        $actividades = array();
                    }
                    else{
                        //El grafo generado es válido
                        $generarNuevoGrafo=false;
                    }
                }
				$tabla = dibujarTablaEstocastica($nombres, $precedencias, $actividades);
				$grafo = null;
				
				if($contCloze > 0){
					$data = obtenerGrafoPertEstocastica($nombres,$duraciones,$precedencias,$grafo);
					$preg_resp = generarPreguntaClozeEstocastica($mediaCritica,$varianzaCritica);
					$tipo = "cloze";
					$contCloze--;
				}
				
				$file = escribirPreguntaEstocastica($file,$tabla,$preg_resp,$tipo,$index,$data);
				
			}
			fputs($file,"\n</quiz>");
			echo "El fichero XML se ha creado correctamente.";
			
		?>
	</body>
</html>