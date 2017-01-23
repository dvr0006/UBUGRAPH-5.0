<?php 
	/**
	* @author Adrián Santamaría Leal
	*/
	require_once("funcionesRoy.php");
	require_once("funcionesPert.php");
	require 'mustache/src/Mustache/Autoloader.php';
	Mustache_Autoloader::register();
	
	/**
	* Se dibuja la tabla en HTML
	* @param array nombres nombres de las actividades
	* @param array precedencias precedencias de cada actividad
	* @param array duraciones duraciones de las actividades
	* @return tablaHTML tabla en código HTML
	*/
	function dibujarTabla($nombres, $precedencias, $duraciones){
		require_once("funciones.php");
		require("../".idioma());
		
		$m = new Mustache_Engine;
		$tablaXML = "<tr>
						<td>{{nombre}}</td>
						<td>{{precedencia}}</td>
						<td>{{duracion}}</td>
					</tr>
					";
		$tabla = "";
		$filas = array("nombre"=>$texto["Generando_3"], "precedencia"=>$texto["Generando_4"], "duracion"=>$texto["Generando_5"]);
		$tabla .= $m->render($tablaXML, $filas);
		for($j=0; $j < sizeof($nombres);$j++){
				$filas = array("nombre"=>$nombres[$j],"precedencia"=>$precedencias[$j], "duracion"=>$duraciones[$j]);
				$tabla .= $m->render($tablaXML, $filas);
		}
		
		$tablaHTML = "\n\t\t<questiontext format=\"html\">
						<text><![CDATA[<h2 style=\"text-align: center;\">{$texto["Generando_2"]}  
						</h2>
						<table align=\"center\" border=\"1\" style=\"width: 100%\">
						$tabla
						</table><br>
						]]>";
		return $tablaHTML;
	}
	
	/**
	* Se obtiene el grafo ROY
	* @param array nombres nombres de las actividades
	* @param array precedencias precedencias de cada actividad
	* @param array duraciones duraciones de las actividades
	* @param grafo grafo con las actividades 
	*/
	function obtenerGrafoRoy($nombres,$duraciones,$precedencias,&$grafo){
		$grafo = generarNodos($nombres,$precedencias,$duraciones);
		establecerPrecedenciasRoy($grafo,$nombres,$duraciones,$precedencias);
		calcularTiempos($grafo);
		$gv = generarGrafoRoy($grafo);
		$data = dibujarGrafo($gv);
		return $data;
	}
	
	/**
	* Se obtiene el grafo PERT
	* @param array nombres nombres de las actividades
	* @param array precedencias precedencias de cada actividad
	* @param array duraciones duraciones de las actividades
	* @param grafo grafo con las actividades 
	*/
	function obtenerGrafoPert($nombres,$duraciones,$precedencias,&$grafo){
		$precedenciasRoy = $precedencias;
		$grafo = generarActividades($nombres,$precedencias,$duraciones);
		establecerPrecedenciasPert($grafo,$nombres, $duraciones, $precedencias);
		$nodos = establecerFicticias($grafo,$nombres, $duraciones, $precedencias,$precedenciasRoy);
		$gv = generarGrafoPert($grafo,$nodos);
		$data = dibujarGrafo($gv);
		return $data;
	}
	
	/**
	* Se genera una pregunta numérica aleatoria
	* @param array nombres nombres de las actividades
	* @param grafo grafo con las actividades 
	* @return preg_resp array con la pregunta y la respuesta
	*/
	function generarPreguntaNumerica($nombres,$grafo){
		$m = new Mustache_Engine;
		$preguntas = array("¿Cuál es la holgura de la actividad {{actividad}}?","¿Cuál es el tiempo de finalización del proyecto?");
		$pos = rand(0,sizeof($preguntas)-1);
		$pregunta = $preguntas[$pos];
		if($pos == 0){
			$actividadAleatoria = $nombres[rand(0,sizeof($nombres)-1)];
			$actividad = array("actividad"=>$actividadAleatoria);
			$respuesta = $grafo[$actividadAleatoria]->getHolguraTotal();
			$pregunta = $m->render($pregunta,$actividad);
		} else {
			$respuesta = $grafo["Fin"]->getTLI();
		}
		return array("pregunta"=>$pregunta,"respuesta"=>$respuesta);			
	}
	
	/**
	* Se genera una pregunta de verdadero o falso
	* @param grafo grafo con las actividades 
	* @return preg_resp array con la pregunta y la respuesta
	*/
	function generarPreguntaVF($grafo){
		$numFicticias = 0;
		foreach($grafo as $value){
			if($value->getFicticia())
				$numFicticias++;
		}
		$nFicticiasPreg = rand(0,$numFicticias);
		if($numFicticias == $nFicticiasPreg){
			$respuesta = true;
		}else
			$respuesta = false;
		$pregunta = "¿Este grafo PERT tiene $nFicticiasPreg actividades ficticias?";
		return array("pregunta"=>$pregunta,"respuesta"=>$respuesta);	
	}
	
	/**
	* Se genera una pregunta de selección múltiple con una solo respuesta
	* @param grafo grafo con las actividades 
	* @return preg_resp array con la pregunta y la respuesta
	*/
	function generarPreguntaSelSimple($grafo){
		$nodosCriticos = 0;
		foreach($grafo as $value){
			if($value->getID() != "Inicio" && $value->getID() != "Fin"){
				$nombre = $value->getID();
				if($value->getCritico()){
					$nodosCriticos++;
				}				
			}
		}
		$pregunta = "¿Cuántas actividades forman parte del (los) camino(s) crítico(s)? (si hubiese varios sume las actividades).";
		$respuesta = $nodosCriticos;
		return array("pregunta"=>$pregunta,"respuesta"=>$respuesta);
	}
	
	/**
	* Se genera una pregunta de selección múltiple con varias respuestas
	* @param array nombres nombres de las actividades
	* @param grafo grafo con las actividades 
	* @return preg_resp array con la pregunta y la respuesta
	*/
	function generarPreguntaSelMult($nombres,$grafo){
		$nodosCriticos = array();
		foreach($grafo as $value){
			if($value->getID() != "Inicio" && $value->getID() != "Fin"){
				if($value->getCritico()){
					array_push($nodosCriticos,$value->getID());
				}				
			}
		}
		$pregunta = "Seleccione las actividades críticas.";
		$respuesta = $nodosCriticos;
		return array("pregunta"=>$pregunta,"respuesta"=>$respuesta,"nombres"=>$nombres);
	}

    /**
    * Se genera una pregunta numérica aleatoria
    * @param array nombres nombres de las actividades
    * @param grafo grafo con las actividades 
    * @return preg_resp array con la pregunta y la respuesta
    */
    function generarPreguntaNumericaEstocastica($nombres,$grafo){
        $m = new Mustache_Engine;
        $preguntas = array("¿Cuál es la probabilidad de que el proyecto acabe en el momento {{momento}}?","¿En qué momento acabará el proyecto con un {{probabilidad}} de probabilidad?");
        $pos = rand(0,sizeof($preguntas)-1);
        $pregunta = $preguntas[$pos];
        if($pos == 0){
            $actividadAleatoria = $nombres[rand(0,sizeof($nombres)-1)];
            $actividad = array("actividad"=>$actividadAleatoria);
            $respuesta = $grafo[$actividadAleatoria]->getHolguraTotal();
            $pregunta = $m->render($pregunta,$actividad);
        } else {
            $respuesta = $grafo["Fin"]->getTLI();
        }
        return array("pregunta"=>$pregunta,"respuesta"=>$respuesta);            
    }
    
    /**
    * Se genera una pregunta de verdadero o falso
    * @param grafo grafo con las actividades 
    * @return preg_resp array con la pregunta y la respuesta
    */
    function generarPreguntaVFEstocastica($grafo){
        $numFicticias = 0;
        foreach($grafo as $value){
            if($value->getFicticia())
                $numFicticias++;
        }
        $nFicticiasPreg = rand(0,$numFicticias);
        if($numFicticias == $nFicticiasPreg){
            $respuesta = true;
        }else
            $respuesta = false;
        $pregunta = "¿Es cierto que el proyecto acabará en el día {{dia}} con un {{probabilidad}} de probabilidad?";
        return array("pregunta"=>$pregunta,"respuesta"=>$respuesta);    
    }
    
    /**
    * Se genera una pregunta de selección múltiple con una solo respuesta
    * @param grafo grafo con las actividades 
    * @return preg_resp array con la pregunta y la respuesta
    */
    function generarPreguntaSelSimpleEstocastica($grafo){
        $nodosCriticos = 0;
        foreach($grafo as $value){
            if($value->getID() != "Inicio" && $value->getID() != "Fin"){
                $nombre = $value->getID();
                if($value->getCritico()){
                    $nodosCriticos++;
                }               
            }
        }
        $pregunta = "¿Cuántas actividades forman parte del (los) camino(s) crítico(s)? (si hubiese varios sume las actividades).";
        $respuesta = $nodosCriticos;
        return array("pregunta"=>$pregunta,"respuesta"=>$respuesta);
    }
    
    /**
    * Se genera una pregunta de selección múltiple con varias respuestas
    * @param array nombres nombres de las actividades
    * @param grafo grafo con las actividades 
    * @return preg_resp array con la pregunta y la respuesta
    */
    function generarPreguntaSelMultEstocastica($nombres,$grafo){
        $nodosCriticos = array();
        foreach($grafo as $value){
            if($value->getID() != "Inicio" && $value->getID() != "Fin"){
                if($value->getCritico()){
                    array_push($nodosCriticos,$value->getID());
                }               
            }
        }
        $pregunta = "Seleccione el instante en el que acabará el proyecto con un {{probabilidad}} de probabilidad.";
        $respuesta = $nodosCriticos;
        return array("pregunta"=>$pregunta,"respuesta"=>$respuesta,"nombres"=>$nombres);
    }
	/**
	* Se escribe la pregunta con el formato adecuado (XML) en un fichero
	* @param file archivo donde se va a escribir
	* @param tabla tabla de precedencias en HTML
	* @param preg_resp array con la pregunta y la respuesta correspondiente
	* @param tipo tipo de pregunta que se va a escribir
	* @param i número de pregunta
	* @param data grafo resuelto
	* @return file archivo con la pregunta escrita
	*/
	function escribirPregunta($file,$tabla,$preg_resp,$tipo,$i,$data){
		fputs($file,"\n\t<question type=\"$tipo\">");	
		fputs($file,"\n\t\t<name>\n\t\t\t<text>Pregunta $i</text>\n\t\t</name>");
		fputs($file,"$tabla
					{$preg_resp["pregunta"]}
			</text>");
		fputs($file,"\n\t\t</questiontext>");
		fputs($file,"\n\t\t<generalfeedback format=\"html\">");
		fputs($file,"\n\t\t\t<text><![CDATA[$data<br>]]></text>");
		fputs($file,"\n\t\t</generalfeedback>");
		if(!is_array($preg_resp["respuesta"]))
			$tagAnswer = "\n\t\t<answer fraction=\"100\">\n\t\t\t<text>{$preg_resp["respuesta"]}</text>\n\t\t</answer>";
		if($tipo == "truefalse"){
			if($preg_resp["respuesta"]){
				$verdadero = 100;
				$falso = 0;
			}
			else {
				$verdadero = 0;
				$falso = 100;
			}
			$tagAnswer = "\n\t\t<answer fraction=\"$verdadero\">\n\t\t\t<text>true</text>\n\t\t</answer>
						  \n\t\t<answer fraction=\"$falso\">\n\t\t\t<text>false</text>\n\t\t</answer>" ;
		}
		if($tipo == "multichoice"){
			if(!is_array($preg_resp["respuesta"])){
				$nRespuestas = 3;
				$respuestas = array();
				array_push($respuestas, $preg_resp["respuesta"]);
				$respuesta = $preg_resp["respuesta"];
				$negativo = false;
				for($j = 0; $j< $nRespuestas ;$j++){
					$masmenos = rand(0,1);
					do{
						if($masmenos == 0 || $negativo)
							$respuesta++;
						else
							$respuesta--;
						if($respuesta == 0)
							$negativo = true;
						
					}while(in_array($respuesta,$respuestas));
					array_push($respuestas,$respuesta);
					$tagAnswer.= "\n\t\t<answer fraction=\"0\">\n\t\t\t<text>$respuesta</text>\n\t\t</answer>";
					
				}
				$tagAnswer.= "\n\t\t<shuffleanswers>true</shuffleanswers>";
			} else{
				$nombres = $preg_resp["nombres"];
				$respuestas = $preg_resp["respuesta"];
				$ponderacion = 100/sizeof($respuestas);
				$tagAnswer= "";
				foreach($nombres as $value){
					$valor = 0;
					if(in_array($value,$respuestas))
						$valor = $ponderacion;
					$tagAnswer.= "\n\t\t<answer fraction=\"$valor\">\n\t\t\t<text>$value</text>\n\t\t</answer>";	
				}
				$tagAnswer.="\n\t\t<single>false</single>";
				$tagAnswer.="\n\t\t<answernumbering>none</answernumbering>";
			}
		}
		
		fputs($file,$tagAnswer);
		fputs($file,"\n\t</question>");		
		return $file;
	}
?>