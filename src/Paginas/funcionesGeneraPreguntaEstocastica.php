<?php 
	/**
	* @author Daniel Velasco Revilla
    * Basado en funcionesGeneraPregunta.php de Adrían Santamaría Leal
	*/
	require_once("funcionesRoy.php");
	require_once("funcionesPert.php");
	require 'mustache/src/Mustache/Autoloader.php';
	Mustache_Autoloader::register();
	
	/**
	* Se dibuja la tabla en HTML
    * @param array nombres nombres de las actividades
    * @param array precedencias precedencias de cada actividad
    * @param array de actividades
	* @return tablaHTML tabla en código HTML
	*/
	function dibujarTablaEstocastica($nombres, $precedencias, $actividades){
		require_once("funciones.php");
        require_once("StandardNormal.php");
		require("../".idioma());
		
		$m = new Mustache_Engine;
		$plantillaXML = "<tr>
						<td>{{nombre}}</td>
						<td>{{precedencia}}</td>
						<td>{{duracion}}</td>
                        <td>{{distribucion}}</td>
                        <td>{{media}}</td>
                        <td>{{varianza}}</td>
                        <td>{{parametro_01}}</td>
                        <td>{{parametro_02}}</td>
                        <td>{{parametro_03}}</td>
					</tr>
					";
		$tabla = "";
		$filas = array("nombre"=>$texto["Generando_3"], "precedencia"=>$texto["Generando_4"], "duracion"=>$texto["Generando_5"],"distribucion"=>$texto["Generando_13"],"media"=>$texto["Generando_14"],"varianza"=>$texto["Generando_15"],"parametro_01"=>$texto["Generando_16"],"parametro_02"=>$texto["Generando_17"],"parametro_03"=>$texto["Generando_18"]);
		$tabla .= $m->render($plantillaXML, $filas);
        foreach($actividades as $a){
            $index=-1;
            for($i=0; $i<count($nombres); $i++){
                if($a->getID()==$nombres[$i]){
                    $index=$i;
                    break;
                }
            }
			$filas = array("nombre"=>$a->getID(),"precedencia"=>$precedencias[$index], "duracion"=>$a->getDuracion(),"distribucion"=>$a->getDistribucion(),"media"=>$a->getMedia(),"varianza"=>$a->getVarianza(),"parametro_01"=>$a->getParametro_01(),"parametro_02"=>$a->getParametro_02(),"parametro_03"=>$a->getParametro_03());
			$tabla .= $m->render($plantillaXML, $filas);
		}
		
		$tablaHTML = "\n\t\t<questiontext format=\"html\">
						<text><![CDATA[<h2 style=\"text-align: center;\">{$texto["Generando_2"]}  
						</h2>
						<table align=\"center\" border=\"1\" style=\"width: 100%\">
						$tabla
						</table><br>
						<font size=2 color=maroon>Ayuda:</font><br>
						<font size=1 color=maroon>Parametro 01: Normal(media), Beta(tiempo pesimista), Triangular(a), Uniforme(mínimo)<br>
						Parametro 02: Normal(varianza), Beta(tiempo más probable), Triangular(b), Uniforme(máximo)<br>
						Parametro 03: Normal(NO PROCEDE), Beta(tiempo optimista), Triangular(c), Uniforme(NO PROCEDE)</font><br><br>
						]]>";
		return $tablaHTML;
	}
	
	/**
	* Se obtiene el grafo PERT
	* @param array nombres nombres de las actividades
	* @param array precedencias precedencias de cada actividad
	* @param array duraciones duraciones de las actividades
	* @param grafo grafo con las actividades 
	*/
	function obtenerGrafoPertEstocastica($nombres,$duraciones,$precedencias,&$grafo){
		$precedenciasRoy = $precedencias;
		$grafo = generarActividades($nombres,$precedencias,$duraciones);
		establecerPrecedenciasPert($grafo,$nombres, $duraciones, $precedencias);
		$nodos = establecerFicticias($grafo,$nombres, $duraciones, $precedencias,$precedenciasRoy);
		$gv = generarGrafoPert($grafo,$nodos);
		$data = dibujarGrafo($gv);
		return $data;
	}
	
	/**
	* Se genera una pregunta cloze aleatoria
    * @author Daniel Velasco Revilla
	* @param mediaCritica Suma de las medias de las actividades que pertenecen al camino crítico
    * @param varianzaCritica Suma de las varianzas de las actividades que pertenecen al camino crítico
	* @return preg_resp array con la pregunta y la respuesta
	*/
	function generarPreguntaClozeEstocastica($mediaCritica, $varianzaCritica){
        //Primera pregunta
        $preg_resp=StandardNormal::getPreguntaProbabilidadFromTiempo($mediaCritica, $varianzaCritica);
        $preguntaProb=$preg_resp["pregunta"];
        $respuestaProb=$preg_resp["respuesta"];
        //Segunda pregunta
        $preg_resp=StandardNormal::getPreguntaTiempoFromProbabilidad($mediaCritica, $varianzaCritica);
        $preguntaTime=$preg_resp["pregunta"];
        $respuestaTime=$preg_resp["respuesta"];
        //Componer el texto de la pregunta cloze con las respuestas embebidas
        $pregunta="La probabilidad de que el proyecto finalice antes de ";
        $pregunta.=$preguntaProb." unidades de tiempo es del ";
        $pregunta.="{1:NUMERICAL:=".$respuestaProb.":1}% (2 decimales). Y si deseamos que el proyecto finalice a tiempo con una probabilidad del ";
        $pregunta.=$preguntaTime."% debemos comprometernos a finalizarlo antes de ";
        $pregunta.="{1:NUMERICAL:=".$respuestaTime.":1} unidades de tiempo (1 decimal).";        
        return array("pregunta"=>$pregunta,"respuesta"=>null);            
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
	function escribirPreguntaEstocastica($file,$tabla,$preg_resp,$tipo,$i,$data){
		fputs($file,"\n\t<question type=\"$tipo\">");	
		fputs($file,"\n\t\t<name>\n\t\t\t<text>Pregunta $i</text>\n\t\t</name>");
		fputs($file,"$tabla
					{$preg_resp["pregunta"]}
			</text>");
		fputs($file,"\n\t\t</questiontext>");
		fputs($file,"\n\t\t<generalfeedback format=\"html\">");
		fputs($file,"\n\t\t\t<text><![CDATA[$data<br>]]></text>");
		fputs($file,"\n\t\t</generalfeedback>");
    	fputs($file,"\n\t</question>");		
		return $file;
	}
?>