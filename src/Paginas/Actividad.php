<?php
	/**
	 * Clase que implementa una actividad (arista) en un grafo para una solucion PERT
     * Añadidos los atributos relativos a las distribuciones de probabilidad para el PERT probabilístico (25-11-2016)
	 * @author Ruben Arranz Alonso de L.
	 */	
	class Actividad
	{
		//////ATRIBUTOS//////
		
		/**
		 * Los IDs de las actividades que preceden a la nuestra
		 */
		private $actividadesPrecedentes = array();
		
		/**
		 * Los IDs de las actividades que las que esta es precedente
		 */
		private $actividadesPosteriores = array();
		
		/**
		 * La duracion de la tarea que representa la actividad.
		 */
		private $duracion;
		
		/**
		 * Si la actividad es ficticia o no
		 */
		private $ficticia = false;
		
		/**
		 * El ID de la actividad, también es el nombre que se mostrara.
		 */
		private $id;
		
		/**
		 * ID del nod en el que se inicia la actividad
		 */
		private $nodoDestino = -1;
		
		/**
		 * ID del nodo en el que finaliza la actividad
		 */
		private $nodoOrigen = -1;
		
		/**
		 * Tiempo early de fin
		 */
		private $tef = 0;
		
		/**
		 * Tiempo early de inicio
		 */
		private $tei = 0;
		
		/**
		 * Tiempo late de fin
		 */
		private $tlf = 0;
		
		/**
		 * Tiempo late de inicio
		 */
		private $tli = 0;
        
        /**
         * Distribución de probabilidad (NORMAL, BETA, TRIANGULAR, UNIFORME)
         */
        private $distribucion = null;
         
        /**
         * Media de la distribución de probabilidad
         */
        private $media = null;
        
        /**
         * Varianza de la distribución de probabilidad
         */
        private $varianza = null;
		
        /**
         * Parámetro 01 de la distribución de probabilidad (media, tiempo optimista, a, mínimo)
         */
        private $parametro_01 = null;
        
        /**
         * Parámetro 02 de la distribución de probabilidad (desviación típica, tiempo pesimista, b, máximo)
         */
        private $parametro_02 = null;
        
        /**
         * Parámetro 03 de la distribución de probabilidad (-, tiempo mas probable, c, -)
         */
        private $parametro_03 = null;
        
		//////CONSTRUCTORES//////
		
		
		/**
		 * Constructor de la clase
		 * @param id El id de la actividad. También el nombre que se mostrara.
		 * @param d la duración de la tarea que representa la actividad.
		 */
		public function Actividad_Old($identificador, $d)
		{
			$this->id = $identificador;
			$this->duracion = $d;
		}
		
        /**
         * Constructor de la clase (Actividades PERT probabilístico)
         * @param id El id de la actividad. También el nombre que se mostrara.
         * @param distribucion La distribución de probabilidad.
         * 
         */
        public function Actividad($identificador, $duracion, $distribucion, $media, $varianza, $parametro_01, $parametro_02, $parametro_03)
        {
            $this->id = $identificador;
			if ($distribucion == NULL){
				//Métodos no probabilísticos
				$this->duracion = $duracion;
			}
			else{
				//Métodos probabilísticos
				if ($media == null || $varianza == null){
					switch($distribucion){
						case 'NORMAL':
							$this->media = $parametro_01;
							$this->varianza = $parametro_02;
							break;
						case 'BETA':
							$this->media = ($parametro_01+$parametro_02+4*$parametro_03)/6.0;
							$this->varianza = pow($parametro_02-$parametro_01,2)/36.0;
							break;
						case 'TRIANGULAR':
							$this->media = ($parametro_01+$parametro_02+$parametro_03)/3.0;
							$this->varianza = (pow($parametro_01,2)+pow($parametro_02,2)+pow($parametro_03,2)-
								$parametro_01*$parametro_02-$parametro_01*$parametro_03-$parametro_02*$parametro_03)/18.0;
							break;
						case 'UNIFORME':
							$this->media = ($parametro_01+$parametro_02)/2.0;
							$this->varianza = pow($parametro_02-$parametro_01,2)/12.0;
							break;
					}
				}
				else{
					$this->media=$media;
					$this->varianza=$varianza;
				}
                $this->media = round($this->media,2);
                $this->varianza = round($this->varianza,2);
				$this->duracion = $this->media;
				$this->distribucion = $distribucion;
				$this->parametro_01 = $parametro_01;
				$this->parametro_02 = $parametro_02;
				$this->parametro_03 = $parametro_03;
			}
        }
		
		//////FUNCIONES//////
		
		
		/**
		 * Añade un identificador de una actividad como precedente a esta.
		 * @param identificador ID de la actividad que precede
		 */
		public function addActividadPrecedente($identificador)
		{			
			 $this->actividadesPrecedentes[] = $identificador;
		}
		
		/**
		 * Añade un identificador de una actividad al que esta precede.
		 * @param identificador ID de la actividad a la que se precede
		 */
		public function addActividadPosterior($identificador)
		{			
			 $this->actividadesPosteriores[] = $identificador;
		}
		
		/**
		 * Elimina todas las actividades precedentes
		 */
		public function clearActividadesPrecedentes()
		{
			 $this->actividadesPrecedentes = array();
		}
		
		/**
		 *  Elimina todas las actividades posteriores
		 */
		public function clearActividadesPosteriores()
		{	
			$this->actividadesPosteriores = array();
		}
		
		/**
		 * Elimina un identificador de una actividad como precedente a esta.
		 * @param identificador ID de la actividad
		 */
		public function eliminarActividadPrecedente($identificador)
		{
			 unset($this->actividadesPrecedentes[$identificador]);
		}
		
		/**
		 * Elimina un identificador de una actividad como que esta precede.
		 * @param identificador ID de la actividad
		 */
		public function eliminarActividadPosterior($identificador)
		{	
			unset($this->actividadesPosteriores[$identificador]);
		}
		
		/**
		 * Establece el ID del nodo en el que finaliza la actividad
		 * @param id El identificador del Nodo
		 */
		public function establecerNodoDestino($id)
		{
			$this->nodoDestino = $id;;
		}
		
		/**
		 * Establece el ID del nodo en el que se origina la actividad
		 * @param id El identificador del Nodo
		 */
		public function establecerNodoOrigen($id)
		{
			$this->nodoOrigen = $id;
		}
		
		/**
		 * Devuelve la duración de la tarea que representa esta actividad
		 * @return La duración de la actividad como tarea
		 */
		public function getDuracion()
		{			
			 return $this->duracion;
		}
		
		/**
		 * Devuelve si esta actividad es ficticia o no.
		 * @return si es o no ficticia
		 */
		public function getFicticia()
		{
			return $this->ficticia;
		}
		
		/**
		 * Devuelve la holgura total de la actividad
		 * @return la holgura total de la actividad
		 */
		public function getHolguraTotal()
		{			
			 return $this->tli - $this->tei;
		}
		
		/**
		 * Devuelve el ID de la actividad
		 * @return El ID de la actividad
		 */
		public function getID()
		{			
			 return $this->id;
		}
		
		/**
		 * Obtiene el ID del nodo en el que finaliza la actividad
		 * @return Un identificador
		 */
		public function getNodoDestino()
		{
			return $this->nodoDestino;
		}
		
		/**
		 * Obtiene el ID del nodo en el que se origina la actividad
		 * @return Un identificador
		 */
		public function getNodoOrigen()
		{
			return $this->nodoOrigen;
		}
		
		/**
		 * Devuelve los ids de las actividad precedentes a esta
		 * @return Un array con los identificadores
		 */
		public function getPrecedentes()
		{
			return $this->actividadesPrecedentes;
		}
		
		/**
		 * Devuelve los ids de las actividad a los que precede esta actividad
		 * @return Un array con los identificadores
		 */
		public function getPosteriores()
		{			
			return $this->actividadesPosteriores;
		}
		
		/**
		 * Devuelve el Tiempo Early de Fin
		 * @return el Tiempo Early de Fin
		 */
		 public function getTEF()
		 {
			return $this->tef;
		 }
		 
		 /**
		 * Devuelve el Tiempo Early de Inicio
		 * @return el Tiempo Early de Inicio
		 */
		 public function getTEI()
		 {
			return $this->tei;
		 }
		 
		 /**
		 * Devuelve el Tiempo Late de Fin
		 * @return el Tiempo Late de Fin
		 */
		 public function getTLF()
		 {
			return $this->tlf;
		 }
		 
		 /**
		 * Devuelve el Tiempo Late de Inicio
		 * @return el Tiempo Late de Inicio
		 */
		 public function getTLI()
		 {
			return $this->tli;
		 }
		
		/**
		 * Convierte esta actividad en ficticia
		 */
		public function setFicticia()
		{
			$this->ficticia = true;
		}
		
		/**
		 * Establece el Tiempo Early de Fin
		 * @param tiempo Tiempo a establecer
		 */
		 public function setTEF($tiempo)
		 {
			 $this->tef = $tiempo;
		 }
		 
		 /**
		 * Establece el Tiempo Early de Inicio
		 * @param tiempo Tiempo a establecer
		 */
		 public function setTEI($tiempo)
		 {
			 $this->tei = $tiempo;
		 }
		 
		 /**
		 * Establece el Tiempo Late de Fin
		 * @param tiempo Tiempo a establecer
		 */
		 public function setTLF($tiempo)
		 {
			 $this->tlf = $tiempo;
		 }
		 
		 /**
		 * Establece el Tiempo late de Inicio
		 * @param tiempo Tiempo a establecer
		 */
		 public function setTLI($tiempo)
		 {
			 $this->tli = $tiempo;
		 }

		 /**
		 * Devuelve la distribución de probabilidad
		 * @return la distribución de probabilidad
		 */
		public function getDistribucion()
		{			
			 return $this->distribucion;
		}

		 /**
		 * Devuelve la media de la distribución de probabilidad
		 * @return la media de la distribución de probabilidad
		 */
		public function getMedia()
		{			
			 return $this->media;
		}

		 /**
		 * Devuelve la varianza de la distribución de probabilidad
		 * @return la varianza de la distribución de probabilidad
		 */
		public function getVarianza()
		{			
			 return $this->varianza;
		}

		 /**
		 * Devuelve el parámetro 01 de la distribución de probabilidad
		 * @return el parámetro 01 de la distribución de probabilidad
		 */
		public function getParametro_01()
		{			
			 return $this->parametro_01;
		}

		 /**
		 * Devuelve el parámetro 02 de la distribución de probabilidad
		 * @return el parámetro 02 de la distribución de probabilidad
		 */
		public function getParametro_02()
		{			
			 return $this->parametro_02;
		}

		 /**
		 * Devuelve el parámetro 03 de la distribución de probabilidad
		 * @return el parámetro 03 de la distribución de probabilidad
		 */
		public function getParametro_03()
		{			
			 return $this->parametro_03;
		}
	}
?>