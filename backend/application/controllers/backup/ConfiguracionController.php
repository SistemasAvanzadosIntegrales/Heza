<?php

/**
 * ConfiguracionController
 **/
class ConfiguracionController extends Zend_Controller_Action {
	
	/**
	 * init
	 **/
	public function init() {
		//$this->view->headScript()->appendFile('/js/backend/configuracion.js');
	}//function
	
	/**
	 * indexAction
	 **/
	public function indexAction() {
		$this->view->configuracion = My_Comun::obtener('Configuracion',' id',1);
	}
	
	/**
	 * guardarAction
	 **/
	public function guardarAction() {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$bitacora               = array();
		$bitacora[0]["modelo"]  = "Configuracion";
		$bitacora[0]["campo"]   = "tasa_isr";
		$bitacora[0]["id"]      = $_POST["id"];
		$bitacora[0]["agregar"] = "Agregar configuración";
		$bitacora[0]["editar"]  = "Editar configuración";
		
		$empresaId = My_Comun::Guardar("Configuracion", $_POST, $_POST["id"], $bitacora);
		
		echo($empresaId);
	}
}

?>