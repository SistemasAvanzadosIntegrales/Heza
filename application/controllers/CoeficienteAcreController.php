<?php

/**
 * @class        CoeficienteAcreController
 * @author:      
 * @contact:     
 * @description: 
 * @version:     1.0
 * @path call:   usuario/index.phtml
 * @copyright:   Avansys
 **/
class CoeficienteAcreController extends Zend_Controller_Action {
	
	/**
	 * @function     init
	 * @author:      Danny Ramirez
	 * @contact:     roberto_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   autorizacion/index.phtml
	 * @copyright:   Avansys
	 **/
	public function init () {
		$this->view->headScript()->appendFile('/js/backend/coeficiente_acre.js');
	}
	
	/**
	 * @function     indexAction
	 * @author:      Danny Ramirez
	 * @contact:     roberto_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   autorizacion/index.phtml
	 * @copyright:   Avansys
	 **/
	public function indexAction () {
		
		$sess = new Zend_Session_Namespace('permisos');
		$this->view->puedeAgregar = strpos($sess->cliente->permisos, "AGREGAR_EMPRESA") !== false;
		$this->view->puedeEliminar = strpos($sess->cliente->permisos, "ELIMINAR_EMPRESA") !== false;
	}
	
	/**
	 * @function     gridAction
	 * @author:      Danny Ramirez
	 * @contact:     roberto_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   autorizacion/index.phtml
	 * @copyright:   Avansys
	 **/
	public function gridAction () {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		$sess = new Zend_Session_Namespace('permisos');
		
		$meses = array (
			'1' => 'Enero',
			'2' => 'Febrero',
			'3' => 'Marzo',
			'4' => 'Abril',
			'5' => 'Mayo',
			'6' => 'Junio',
			'7' => 'Julio',
			'8' => 'Agosto',
			'9' => 'Septiembre',
			'10' => 'Octubre',
			'11' => 'Noviembre',
			'12' => 'Diciembre',
		);
		
		$filtro=" 1=1 ";
		
		$registros = My_Comun::registrosGrid("CoeficienteAcre", $filtro);
		$grid = array();
		$i = 0;
		
		foreach($registros['registros'] AS $registro) {
			
			$empresa   = My_Comun::obtener("Empresa",   "id", $registro->id_empresa);
			$ejercicio = My_Comun::obtener("Ejercicio", "id", $registro->id_ejercicio);
			
			$grid[$i]['id_empresa']                 = '<span class="registro" rel="'.$registro->id.'">'.$empresa->nombre.'</span>';
			$grid[$i]['id_ejercicio']               = '<span class="registro" rel="'.$registro->id.'">'.$ejercicio->nombre.'</span>';
			$grid[$i]['id_periodo_inicio']          = '<span class="registro" rel="'.$registro->id.'">'.$meses[$registro->id_periodo_inicio].'</span>';
			$grid[$i]['id_periodo_fin']             = '<span class="registro" rel="'.$registro->id.'">'.$meses[$registro->id_periodo_fin].'</span>';
			$grid[$i]['coeficiente_acreditamiento'] = '<span class="registro" rel="'.$registro->id.'">'.$registro->coeficiente_acreditamiento.'</span>';
			
			$i++;
		}
		
		My_Comun::armarGrid($registros, $grid);
	}
	
	/**
	 * @function     guardarAction
	 * @author:      Danny Ramirez
	 * @contact:     roberto_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   autorizacion/index.phtml
	 * @copyright:   Avansys
	 **/
	public function guardarAction () {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$bitacora = array();
		$bitacora[0]["modelo"]  = "CoeficienteAcre";
		$bitacora[0]["campo"]   = "id_empresa";
		$bitacora[0]["id"]      = $_POST["id"];
		$bitacora[0]["agregar"] = "Agregar coeficiente acreditamiento";
		$bitacora[0]["editar"]  = "Editar coeficiente acreditamiento";
		
		$coeficiente = My_Comun::Guardar("CoeficienteAcre", $_POST, $_POST["id"], $bitacora);
		echo($coeficiente);
	}
	
	/**
	 * obtenerAction
	 **/
	public function obtenerAction () {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$array = array();
		$array["error"] = '';
		
		if($_POST["id"] != "0") {
			
			$registro = My_Comun::obtener("CoeficienteAcre", "id", $_POST["id"]);
			
			$array["id"]                         = (string)$registro->id;
			$array["id_empresa"]                 = (string)$registro->id_empresa;
			$array["id_ejercicio"]               = (string)$registro->id_ejercicio;
			$array["id_periodo_inicio"]          = (string)$registro->id_periodo_inicio;
			$array["id_periodo_fin"]             = (string)$registro->id_periodo_fin;
			$array["coeficiente_acreditamiento"] = (string)$registro->coeficiente_acreditamiento;
		}
		else {
			$array["error"] = "Error al obtener el coeficiente de acreditamiento: identificador no existe.";
		}
		
		echo json_encode($array);
	}
	
	/**
	 * eliminarAction
	 **/
	function eliminarAction () {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$regi = My_Comun::obtener("CoeficienteAcre", "id", $_POST["id"]);
		
		$bitacora = array();
		$bitacora[0]["modelo"]       = "Coeficiente";
		$bitacora[0]["campo"]        = "id_empresa";
		$bitacora[0]["id"]           = $_POST["id"];
		$bitacora[0]["eliminar"]     = "Eliminar coeficiente acreditamiento";
		$bitacora[0]["deshabilitar"] = "Deshabilitar coeficiente acreditamiento";
		$bitacora[0]["habilitar"]    = "Habilitar coeficiente acreditamiento";
		
		echo My_Comun::eliminar("CoeficienteAcre", $_POST["id"], $bitacora);
	}
}

?>