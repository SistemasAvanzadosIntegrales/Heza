<?php

/**
 * EjercicioController
 **/
class EjercicioController extends Zend_Controller_Action {
	
	/**
	 * init
	 **/
	public function init() {
		$this->view->headScript()->appendFile('/js/backend/ejercicio.js');
	}
	
	/**
	 * indexAction
	 **/
	public function indexAction() {
		
		$sess=new Zend_Session_Namespace('permisos');
		print_r($sess->permisos);
		$this->view->puedeAgregar=strpos($sess->cliente->permisos,"AGREGAR_EJERCICIO")!==false;
		$this->view->puedeEliminar=strpos($sess->cliente->permisos,"ELIMINAR_EJERCICIO")!==false;
	}
	
	/**
	 * gridAction
	 **/
	public function gridAction() {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		$sess=new Zend_Session_Namespace('permisos');
		
		$filtro = " 1=1 ";
		
		$nombre = $this->_getParam('nombre');
		
		$status = $this->_getParam('status');
		if($this->_getParam('status')!="")
			$filtro.=" AND status=".$this->_getParam('status');
		
		if($nombre!='') {
			
			$nombre = explode(" ", trim($nombre));
			for( $i = 0; $i <= $nombre[$i]; $i++) {
				$nombre[$i] = trim(str_replace(array("'", "\"", ), array("�", "�"), $nombre[$i]));
				if($nombre[$i] != "")
					$filtro .= " AND (nombre LIKE '%".$nombre[$i]."%')  ";
			}
		}
		
		$registros = My_Comun::registrosGrid("Ejercicio", $filtro);
		$grid = array();
		$i = 0;
		
		$editar   = My_Comun::tienePermiso("EDITAR_EJERCICIO");
		$eliminar = My_Comun::tienePermiso("ELIMINAR_EJERCICIO");
			
		foreach($registros['registros'] as $registro) {
			
			$grid[$i]['nombre']='<span class="registro '.(($registro->status == 0) ? 'desactivado' : '').'" rel="'.$registro->id.'">'.$registro->nombre.'</span>';
			$grid[$i]['status']='<span class="registro '.(($registro->status == 0) ? 'desactivado' : '').'" rel="'.$registro->id.'">Activo</span>';
			
			$i++;
		}
		
		My_Comun::armarGrid($registros,$grid);
	}
	
	/**
	 * obtenerAction
	 **/
	public function obtenerAction() {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$array = array();
		$array["error"] = '';
		
		if( $_POST["id"] != "0" ){
			
			$registro        = My_Comun::obtener("Ejercicio", "id", $_POST["id"]);
			$array["id"]     = (string)$registro->id;
			$array["nombre"] = (string)$registro->nombre;
			$array["status"] = (string)$registro->status;
		}
		else {
			$array["error"]="Error al obtener del ejercicio: identificador no existe.";
		}
		
		echo json_encode($array);
	}
	
	/**
	 * guardarAction
	 **/
	public function guardarAction() {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$bitacora = array();
		$bitacora[0]["modelo"] = "Ejercicio";
		$bitacora[0]["campo"] = "nombre";
		$bitacora[0]["id"] = $_POST["id"];
		$bitacora[0]["agregar"] = "Agregar ejercicio";
		$bitacora[0]["editar"] = "Editar ejercicio";
		
		$ejercicioId = My_Comun::Guardar("Ejercicio", $_POST, $_POST["id"], $bitacora);
		
		echo $ejercicioId;
	}
	
	/**
	 * eliminarAction
	 **/
	function eliminarAction() {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$regi = My_Comun::obtener("Ejercicio", "id", $_POST["id"]);
		
		$bitacora                    = array();
		$bitacora[0]["modelo"]       = "Ejercicio";
		$bitacora[0]["campo"]        = "nombre";
		$bitacora[0]["id"]           = $_POST["id"];
		$bitacora[0]["eliminar"]     = "Eliminar ejercicio";
		$bitacora[0]["deshabilitar"] = "Deshabilitar ejercicio";
		$bitacora[0]["habilitar"]    = "Habilitar ejercicio";
		
		echo My_Comun::eliminar("Ejercicio", $_POST["id"], $bitacora);
	} 
}

?>