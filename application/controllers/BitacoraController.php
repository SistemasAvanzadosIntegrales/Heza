<?php

/**
 * BitacoraController
 **/
class BitacoraController extends Zend_Controller_Action {
	
	/**
	 * init
	 **/
	public function init(){
		$this->view->headScript()->appendFile('/js/backend/bitacora.js');
	}
	
	/**
	 * indexAction
	 **/
	public function indexAction() {
	
		$sess = new Zend_Session_Namespace('permisos');
		$this->view->puedeAgregar = strpos($sess->cliente->permisos,"AGREGAR_USUARIO")!==false;
		$this->view->usuarios = My_Comun::obtenerFiltro("Usuario","1=1","nombre ASC");
	}
	
	/**
	 * gridAction
	 **/
	public function gridAction() {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$sess   = new Zend_Session_Namespace('permisos');
		$filtro = " 1=1 ";
		
		$usuario    = $this->_getParam('usuario');
		$modelo     = $this->_getParam('modelo');
		$accion     = $this->_getParam('accion');
		$referencia = $this->_getParam('referencia');
		$desde      = $this->_getParam('desde');
		$hasta      = $this->_getParam('hasta');
		
		if( $usuario != '' ) {
			$nombre = explode(" ", trim($usuario));
			for($i=0; $i<=$usuario[$i]; $i++) {
				$usuario[$i] = trim(str_replace(array("'", "\"", ), array("�", "�"),$usuario[$i]));
				if( $nombre[$i] != "" )
					$filtro .= " AND (Bitacora.Usuario.id = '".$usuario[$i]."')  ";
			}
		}
		
		if( $modelo != '' ) {
			$modelo = explode(" ", trim($modelo));
			for($i = 0; $i <= $modelo[$i]; $i++) {
				$modelo[$i] = trim(str_replace(array("'", "\"", ), array("�", "�"), $modelo[$i]));
				if( $modelo[$i] != "" )
					$filtro .= " AND (modelo LIKE '%".$modelo[$i]."%') ";
			}
		}
		
		if( $accion != '' ) {
			$accion = explode(" ", trim($accion));
			for($i=0; $i<=$accion[$i]; $i++) {
				$accion[$i] = trim(str_replace(array("'", "\"", ), array("�", "�"),$accion[$i]));
				if( $accion[$i] != "" )
					$filtro .= " AND (accion LIKE '%".$accion[$i]."%') ";
			}
		}
		
		if( $referencia != '' ) {
			$referencia = explode(" ", trim($referencia));
			for($i = 0; $i <= $referencia[$i]; $i++) {
				$referencia[$i] = trim(str_replace(array("'", "\"", ), array("�", "�"), $referencia[$i]));
				if( $referencia[$i] != "" )
					$filtro .= " AND (referencia LIKE '%".$referencia[$i]."%') ";
			}
		}
		
		if( $desde != '' && $hasta != '' ) {
			$desde   = $desde." 00:00:00";
			$hasta   = $hasta." 23:59:59";
			$filtro .= " AND (updated_at >= '".$desde."') AND (updated_at <= '".$hasta."') ";
		}
		
		$registros = My_Comun::registrosGrid("Bitacora", $filtro);
		$grid      = array();
		$i         = 0;
		
		$permisos = My_Comun::tienePermiso("PERMISOS_USUARIO");
		$editar   = My_Comun::tienePermiso("EDITAR_USUARIO");
		$eliminar = My_Comun::tienePermiso("ELIMINAR_USUARIO");
		
		foreach( $registros['registros'] AS $registro ) {
			
			$name = $registro->Usuario->nombre;
			
			$grid[$i]['updated_at'] = $registro->updated_at;
			$grid[$i]['usuario']    = $name;
			$grid[$i]['modelo']     = $registro->modelo;
			$grid[$i]['accion']     = $registro->accion;
			$grid[$i]['referencia'] = $registro->referencia;
			$grid[$i]['bit_id']     = $registro->id;
			
			$i++;
		}
		
		My_Comun::armarGrid($registros, $grid);
	}
	
}
?>