<?php

/**
 * @class        UsuarioController
 * @author:      
 * @contact:     
 * @description: 
 * @version:     1.0
 * @path call:   usuario/index.phtml
 * @copyright:   Avansys
 **/
class UsuarioController extends Zend_Controller_Action {
	
	/**
	 * @function     init
	 * @author:      
	 * @contact:     
	 * @description: 
	 * @version:     1.0
	 * @path call:   compensacion/index.phtml
	 * @copyright:   Avansys
	 **/
	public function init () {
		$this->view->headScript()->appendFile('/js/backend/usuario.js');
	}
	
	/**
	 * @function     indexAction
	 * @author:      
	 * @contact:     
	 * @description: 
	 * @version:     1.0
	 * @path call:   compensacion/index.phtml
	 * @copyright:   Avansys
	 **/
	public function indexAction () {
		
		$sess = new Zend_Session_Namespace('permisos');
		$this->view->puedeAgregar = strpos($sess->cliente->permisos, "AGREGAR_USUARIO") !== false;
	}
	
	/**
	 * @function     gridAction
	 * @author:      
	 * @contact:     
	 * @description: 
	 * @version:     1.0
	 * @path call:   compensacion/index.phtml
	 * @copyright:   Avansys
	 **/
	public function gridAction () {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		$sess   = new Zend_Session_Namespace('permisos');
		
		$filtro = " 1=1 ";
		$nombre = $this->_getParam('nombre');
		$status = $this->_getParam('status');
		
		if( $this->_getParam('status') != "" )
			$filtro .= " AND status = ".$this->_getParam('status');
		
		if($nombre != '') {
			
			$nombre = explode(" ", trim($nombre));
			
			for( $i = 0; $i <= $nombre[$i]; $i++) {
				
				$nombre[$i] = trim(str_replace(array("'", "\"", ), array("�", "�"), $nombre[$i]));
				
				if( $nombre[$i] != "" )
					$filtro.=" AND (nombre LIKE '%".$nombre[$i]."%' OR correo_electronico LIKE '%".$nombre[$i]."%') ";
			}
		}
		
		$registros = My_Comun::registrosGrid("Usuario", $filtro);
		$grid = array();
		$i = 0;
		
		$permisos = My_Comun::tienePermiso("PERMISOS_USUARIO");
		$editar   = My_Comun::tienePermiso("EDITAR_USUARIO");
		$eliminar = My_Comun::tienePermiso("ELIMINAR_USUARIO");
			
		foreach($registros['registros'] AS $registro) {
			
			$grid[$i]['nombre']             = $registro->nombre;
			$grid[$i]['correo_electronico'] = $registro->correo_electronico;
			
			if( $registro->status == 0 ) {
				
				$grid[$i]['permisos'] = '<i class="boton fa fa-check fa-lg text-danger"></i>';   
				$grid[$i]['editar']   = '<i class="boton fa fa-pencil fa-lg text-danger"></i>';
				
				if( $eliminar )
					$grid[$i]['eliminar'] = '<span onclick="eliminar('.$registro->id.','.$registro->status.');" title="Eliminar"><i class="boton fa fa-times-circle fa-lg azul"></i></span>';
				else
					$grid[$i]['eliminar'] = '<i class="boton fa fa-times-circle text-danger fa-lg "></i>';
			}
			else {
				
				if($permisos)
					$grid[$i]['permisos'] = '<span onclick="permisos('.$registro->id.');" title="Permisos"><i class="boton fa fa-check fa-lg azul"></i></span>';
				else
					$grid[$i]['permisos'] = '<i class="boton fa fa-check text-danger fa-lg"></i>';
					
				if($editar)
					$grid[$i]['editar'] = '<span onclick="agregar(\'/usuario/agregar\','.$registro->id.', \'frm-1\' );" title="Editar"><i class="boton fa fa-pencil fa-lg azul"></i></span>';
				else
					$grid[$i]['editar'] = '<i class="boton fa fa-pencil fa-lg text-danger"></i>';
				
				if($eliminar)
					$grid[$i]['eliminar'] = '<span onclick="eliminar('.$registro->id.','.$registro->status.');" title="Deshabilitar / Habilitar"><i class="boton fa fa-times-circle fa-lg azul"></i></i></span>';
				else
					$grid[$i]['eliminar'] = '<i class="boton fa fa-times-circle fa-lg text-danger"></i>';
			}
			
			$i++;
		}
		
		My_Comun::armarGrid($registros, $grid);
	}
	
	/**
	 * @function     agregarAction
	 * @author:      
	 * @contact:     
	 * @description: 
	 * @version:     1.0
	 * @path call:   compensacion/index.phtml
	 * @copyright:   Avansys
	 **/
	public function agregarAction () {
		
		$this->_helper->layout->disableLayout();
		$this->view->llave = My_Comun::aleatorio(20);
		
		if( $_POST["id"] != "0" ){
			$this->view->registro = My_Comun::obtener("Usuario", "id", $_POST["id"]);
		}
	}
	
	/**
	 * @function     guardarAction
	 * @author:      
	 * @contact:     
	 * @description: 
	 * @version:     1.0
	 * @path call:   compensacion/index.phtml
	 * @copyright:   Avansys
	 **/
	public function guardarAction () {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$bitacora               = array();
		$bitacora[0]["modelo"]  = "Usuario";
		$bitacora[0]["campo"]   = "nombre";
		$bitacora[0]["id"]      = $_POST["id"];
		$bitacora[0]["agregar"] = "Agregar usuario";
		$bitacora[0]["editar"]  = "Editar usuario";
		
		$usuarioId = My_Comun::Guardar("Usuario", $_POST, $_POST["id"], $bitacora);
		
		echo($usuarioId);
	}
	
	/**
	 * @function     permisosAction
	 * @author:      
	 * @contact:     
	 * @description: 
	 * @version:     1.0
	 * @path call:   compensacion/index.phtml
	 * @copyright:   Avansys
	 **/
	public function permisosAction () {
		
		$this->_helper->layout->disableLayout();
		
		$this->view->registro = My_Comun::obtener('Usuario', "id", $_POST["id"]);
		$this->view->nombre   = $this->view->registro->nombre;
		$this->view->permisos = explode("|",$this->view->registro->permisos);
		
		$con = Doctrine_Manager::getInstance()->connection();
		
		$query = "
			SELECT DISTINCT e.id, e.nombre, 
			                (SELECT DISTINCT usuario_id 
			                            FROM usuario_empresa 
			                           WHERE empresa_id = e.id 
			                                 AND usuario_id = ".$this->view->registro->id.") AS user
			           FROM empresa e
			LEFT OUTER JOIN usuario_empresa ue
			                ON e.id = ue.empresa_id";
		
		$available = $con->execute($query)->fetchAll();
		
		$this->view->empresas = $available;
	}
	
	/**
	 * @function     guardarpermisosAction
	 * @author:      
	 * @contact:     
	 * @description: 
	 * @version:     1.0
	 * @path call:   compensacion/index.phtml
	 * @copyright:   Avansys
	 **/
	public function guardarpermisosAction() {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE); 
		
		Usuario::guardarPermisos($_POST['permisos'], $_POST['id'], $_POST['empresas']);
	}
	
	/**
	 * @function     eliminarAction
	 * @author:      
	 * @contact:     
	 * @description: 
	 * @version:     1.0
	 * @path call:   compensacion/index.phtml
	 * @copyright:   Avansys
	 **/
	public function eliminarAction () {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$regi = My_Comun::obtener("Usuario", "id", $_POST["id"]);
		
		$bitacora                    = array();
		$bitacora[0]["modelo"]       = "Usuario";
		$bitacora[0]["campo"]        = "nombre";
		$bitacora[0]["id"]           = $_POST["id"];
		$bitacora[0]["eliminar"]     = "Eliminar usuario";
		$bitacora[0]["deshabilitar"] = "Deshabilitar usuario";
		$bitacora[0]["habilitar"]    = "Habilitar usuario";
		
		echo My_Comun::eliminar("Usuario", $_POST["id"], $bitacora);
	}
	
	/**
	 * @function     exportarAction
	 * @author:      
	 * @contact:     
	 * @description: 
	 * @version:     1.0
	 * @path call:   compensacion/index.phtml
	 * @copyright:   Avansys
	 **/
	public function exportarAction () {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$nombre = $this->_getParam('nombre');
		
		$filtro = " 1=1 ";
		$i = 6;
		$data = array();
		
		if( $this->_getParam('status') != "" ) {
			
			$filtro .= " AND status='".str_replace("'", "�", $this->_getParam('status'))."' ";
			
			if( $this->_getParam('status') == 0 )
				$data[] = array("A$i" =>"Estatus:", "B$i" => "Deshabilitado");
			else
				$data[] = array("A$i" => "Estatus:", "B$i" => "Habilitado");
			
			$i++;
		}
		
		if( $nombre != '' ) {
			
			$data[] = array("A$i" => "Nombre:", "B$i" => $nombre);
			
			$i++;
			
			$nombre = explode(" ", trim($nombre));
			
			for( $j = 0; $j <= $nombre[$j]; $j++ ) {
				
				$nombre[$j] = trim(str_replace(array("'", "\"",), array("�","�"), $nombre[$j]));
				
				if( $nombre[$j] != "" )
					$filtro .= " AND ( nombre LIKE '%".$nombre[$j]."%' ) ";
			}
		}
		
		$i++;
		$registros = My_Comun::obtenerFiltro("Usuario", $filtro, "nombre ASC");
		
		ini_set("memory_limit", "130M");
		ini_set('max_execution_time', 0);
		
		$objPHPExcel = new My_PHPExcel_Excel();
		
		$columns_name = 
			array (
				"A$i" => array(
						"name" => 'No. DE USUARIO',
						"width" => 16
						),
				"B$i" => array(
						"name" => 'NOMBRE ',
						"width" => 30
						),
				"C$i" => array(
						"name" => 'CORREO',
						"width" => 50
						),
				"D$i" => array(
						"name" => 'ESTATUS',
						"width" => 13
						)	
			);
		
		//Datos tabla
		foreach($registros AS $registro) {
			
			if($registro->status == "0") {
				$a = "Deshabilitado";
			}else{
				$a =  "Habilitado";
			}
			
			$i++;
			
			$data[] = 
				array(
					"A$i" => $registro->id,
					"B$i" => $registro->nombre,
					"C$i" => $registro->correo_electronico,
					"D$i" => $a
				);
		}
		
		$objPHPExcel->createExcel('Usuario', $columns_name, $data, 10, array('rango' => 'A4:G4', 'texto' => 'Usuarios Heza'));
	}
	
	/**
	 * @function     imprimirAction
	 * @author:      
	 * @contact:     
	 * @description: 
	 * @version:     1.0
	 * @path call:   compensacion/index.phtml
	 * @copyright:   Avansys
	 **/
	public function imprimirAction() {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$nombre = $this->_getParam('nombre');
		
		$filtro = " 1=1 ";
		
		if( $this->_getParam('status') != "" ) {
			
			$filtro .= " AND status='".str_replace("'", "�", $this->_getParam('status'))."' ";
			
			if( $this->_getParam('status') == 0 )
				$data[] = array("A$i" => "Estatus:", "B$i" => "Deshabilitado");
			else
				$data[] = array("A$i" => "Estatus:", "B$i" => "Habilitado");
			
			$i++;
		}
		
		if( $nombre != '' ) {
			
			$data[] = array("A$i" => "Nombre:", "B$i" => $nombre);
			$i++;
			$nombre = explode(" ", trim($nombre));
			
			for( $j = 0; $j <= $nombre[$j]; $j++) {
				
				$nombre[$j] = trim(str_replace(array("'", "\"",), array("�", "�"), $nombre[$j]));
				
				if( $nombre[$j] != "" ) {
					$filtro .= " AND ( nombre LIKE '%".$nombre[$j]."%' ) ";
				}
			}
		}
		
		$registros = My_Comun::obtenerFiltro("Usuario", $filtro);
		
		$pdf = new My_Fpdf_Pdf();
		
		$pdf->AliasNbPages();
		$pdf->AddPage();
		
		$pdf->Header("IMPRESIÓN DE USUARIOS");
		
		$pdf->SetFont('Arial','B',11);
		$pdf->SetWidths(array(35, 55, 55, 40));
		$pdf->Row(array('NO. DE USUARIO', 'NOMBRE', 'CORREO', 'ESTATUS'), 0, 1);
		
		$pdf->SetFont('Arial', '', 10);
		
		foreach( $registros AS $registro ) {
			
			$estatus = '';
			
			switch( $registro['status'] ) {
				case 0: $estatus = 'Inhabilitado'; break;
				case 1: $estatus = 'Habilitado'; break;
			}
			
			$pdf->Row(
				array(
					$registro->id, 
					$registro->nombre, 
					$registro->correo_electronico, 
					$estatus
				), 0, 1
			);
		}
		
		$pdf->Output();
	}
	
	/**
	 * @function     misDatosAction
	 * @author:      
	 * @contact:     
	 * @description: 
	 * @version:     1.0
	 * @path call:   compensacion/index.phtml
	 * @copyright:   Avansys
	 **/
	public function misDatosAction () {
		
		$this->_helper->layout->disableLayout();
		$this->view->registro = My_Comun::obtener("Usuario", "id", $_POST["id"]);
	}
	
	/**
	 * @function     guardarUsuarioAction
	 * @author:      
	 * @contact:     
	 * @description: 
	 * @version:     1.0
	 * @path call:   compensacion/index.phtml
	 * @copyright:   Avansys
	 **/
	public function guardarUsuarioAction () {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$bitacora               = array();
		$bitacora[0]["modelo"]  = "Usuario";
		$bitacora[0]["campo"]   = "nombre";
		$bitacora[0]["id"]      = $_POST["id"];
		$bitacora[0]["agregar"] = "Agregar usuario";
		$bitacora[0]["editar"]  = "Editar usuario";
		
		if( $_POST["confirmar"] == $_POST["contrasena"] )
			echo My_Comun::guardar("Usuario", $_POST, $_POST["id"], $bitacora);
		else
			echo "Las contraseñas no corresponden";
		
	}
}

?>