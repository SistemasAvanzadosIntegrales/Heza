<?php

/**
 * @class        AutorizacionController
 * @author:      
 * @contact:     
 * @description: 
 * @version:     1.0
 * @path call:   usuario/index.phtml
 * @copyright:   Avansys
 **/
class AutorizacionController extends Zend_Controller_Action {
	
	/**
	 * @function     init
	 * @author:      
	 * @contact:     
	 * @description: 
	 * @version:     1.0
	 * @path call:   autorizacion/index.phtml
	 * @copyright:   Avansys
	 **/
	public function init () {
		$this->view->headScript()->appendFile('/js/backend/autorizacion.js');
	}
	
	/**
	 * @function     indexAction
	 * @author:      
	 * @contact:     
	 * @description: 
	 * @version:     1.0
	 * @path call:   autorizacion/index.phtml
	 * @copyright:   Avansys
	 **/
	public function indexAction () {
		
		$sess = new Zend_Session_Namespace('permisos');
		$this->view->puedeAgregar = strpos($sess->cliente->permisos, "AGREGAR_AUTORIZACION") !== false;
	}
	
	/**
	 * @function     gridAction
	 * @author:      
	 * @contact:     
	 * @description: 
	 * @version:     1.0
	 * @path call:   autorizacion/index.phtml
	 * @copyright:   Avansys
	 **/
	public function gridAction () {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		$sess   = new Zend_Session_Namespace('permisos');
		
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
		
		// $status = (isset($this->_getParam('status'))) ? 'AND status_resuelto = '.$this->_getParam('status').'' : '';
		
		$sql = "SELECT empresa_id FROM usuario_empresa WHERE usuario_id = ".Zend_Auth::getInstance()->getIdentity()->id."";
		$empresasUser = My_Comun::crearQuery("UsuarioEmpresa", $sql);
		$emp = array();
		
		foreach ($empresasUser AS $id => $empresaUser) {
			$emp[] = $empresaUser['empresa_id'];
 		}
		
		$emp     = implode(',', $emp);
		$filtro  = " 1 = 1 ";
		// $filtro .= $status;
		$filtro .= " AND id_empresa IN (".$emp.")";
		$grid    = array();
		$i       = 0;
		
		$autorizaciones = My_Comun::registrosGrid("ImpuestoPeriodoPmSolicitud", $filtro);
		
		foreach($autorizaciones['registros'] AS $autorizacion) {
			
			$empresa   = My_Comun::obtener("Empresa",   "id", $autorizacion->id_empresa);
			$ejercicio = My_Comun::obtener("Ejercicio", "id", $autorizacion->id_ejercicio);
			$usuario   = My_Comun::obtener("Usuario",   "id", $autorizacion->id_usuario);
			$resolvio  = My_Comun::obtener("Usuario",   "id", $autorizacion->user_resolved);
			$mes       = $meses[$autorizacion->id_periodo];
			$status    = '';
			
			if ($autorizacion->status_resuelto == 1) {
				$status = 'Aceptado';
			}
			else if ($autorizacion->status_resuelto == 2) {
				$status = 'Denegado';
			}
			else {
				$status = 'Sin resolver aún';
			}
			
			// $color = ($autorizacion->status_resuelto == 2) ? '#5cb85c' : '';
			
			$grid[$i]['Empresa']     = $empresa->nombre;
			$grid[$i]['Ejercicio']   = $ejercicio->nombre;
			$grid[$i]['Periodo']     = $mes;
			$grid[$i]['Usuario']     = $usuario->nombre;
			$grid[$i]['Fecha']       = $autorizacion->fecha;
			$grid[$i]['Estatus']     = ($autorizacion->status_resuelto) ? 'Resuelto' : 'No resuelto';
			$grid[$i]['Resolucion']  = $status;
			$grid[$i]['Resolvio']    = $resolvio->nombre;
			$grid[$i]['Comentarios'] = $autorizacion->comentarios;
			$grid[$i]['Autorizar']   = '<span onclick="autorizar('.$autorizacion->id.', &quot;'.$usuario->nombre.'&quot;);" title="Autorizar cambios"><i class="boton fa fa-reply fa-lg azul"></i></span>';
			
			$i++;
		}
		
		My_Comun::armarGrid($autorizaciones, $grid);
	}
	
	/**
	 * @function     verEvaluacionAction
	 * @author:      Danny Ramirez
	 * @contact:     roberto_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   autorizacion/index.phtml
	 * @copyright:   Avansys
	 **/
	public function verAutorizacionAction () {
		
		$this->_helper->layout->disableLayout();
		
		$this->view->autorizacion = $_POST["id"];
		$this->view->nombre       = $_POST["nombre"];
	}
	
	/**
	 * @function     guardarAutorizacionAction
	 * @author:      
	 * @contact:     
	 * @description: 
	 * @version:     1.0
	 * @path call:   autorizacion/index.phtml
	 * @copyright:   Avansys
	 **/
	public function guardarAutorizacionAction () {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
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
		
		$autorizacion = array();
		$autorizacion['id']              = $_POST['id'];
		$autorizacion['comentarios']     = $_POST['comentarios'];
		$autorizacion['status_resuelto'] = $_POST['respuesta'];
		$autorizacion['user_resolved']   = Zend_Auth::getInstance()->getIdentity()->id;
		
		My_Comun::Guardar("ImpuestoPeriodoPmSolicitud", $autorizacion, $autorizacion['id'], NULL);
		
		/**
		 * Correo electronico
		 **/
		$impuesto = My_Comun::obtener("ImpuestoPeriodoPmSolicitud", "id", $_POST['id']);
		
		$usuario   = My_Comun::obtener("Usuario",   "id", $impuesto->id_usuario);
		$resolved  = My_Comun::obtener("Usuario",   "id", $impuesto->user_resolved);
		$empresa   = My_Comun::obtener("Empresa",   "id", $impuesto->id_empresa);
		$ejercicio = My_Comun::obtener("Ejercicio", "id", $impuesto->id_ejercicio);
		
		$status = '';
		if ($impuesto->status_resuelto == 1) {
			$status = 'Aceptado';
		}
		else {
			$status = 'Denegado';
		}
		
		$titulo = 'Respuesta a la solicitud';
		$cuerpo = '	<div class="text-center">
						<h3>'.$resolved->nombre.':</h3>
						<p style="text-align:justify;">Ha '.$status.' su petición del Ejercicio '.$ejercicio->nombre.', de la Empresa '.$empresa->nombre.' del periodo '.$meses[$impuesto->id_periodo].'.</p>
						<p style="text-align:justify;">'.$impuesto->comentarios.'</p>
					</div>';
		
		$de = My_Comun::EMAIL;
		$de_nombre = My_Comun::SISTEMA;
		$para = 'roberto_ramirez@avansys.com.mx';
		$para_nombre = 'Roberto Ramirez';
		
		My_Comun::correo($titulo, $cuerpo, $de, $de_nombre, $para, $para_nombre, "", "");
		
		/**
		 * Remover el congelado del reporte en caso de ser aceptada la petición.
		 **/
		for ($i = $impuesto->id_periodo; $i <= 12; $i++) {
			
			$sql = "SELECT id 
			          FROM impuesto_periodo_pm 
			         WHERE id_empresa = '".$impuesto->id_empresa."'
			               AND id_ejercicio = '".$impuesto->id_ejercicio."'
			               AND id_periodo   = '".$i."'
			               AND status = 1";
			
			$periodo = My_Comun::crearQuery("UsuarioEmpresa", $sql);
			
			// Crear ronda general
			$descongelar = array();
			$descongelar['id']     = $periodo[0]['id'];
			$descongelar['status'] = 0;
			
			// $bitacora                   = array();
			// $bitacora[0]["modelo"]      = "ImpuestoPeriodoPm";
			// $bitacora[0]["campo"]       = "status";
			// $bitacora[0]["id"]          = $ImpuestoPeriodoPm[0]->id;
			// $bitacora[0]["congelar"]    = "Congelar periodo";
			// $bitacora[0]["descongelar"] = "Descongelar usuario";
			
			echo My_Comun::Guardar("ImpuestoPeriodoPm", $descongelar, $periodo[0]['id'], null);
		}
		
		echo 'Se descongelo el ejercicio correctamente';
	}
	
	/**
	 * @function     exportarAction
	 * @author:      
	 * @contact:     
	 * @description: 
	 * @version:     1.0
	 * @path call:   compensacion/index.phtml
	 * @copyright:   Avansys
	 **//*
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
	 **//*
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
	}*/
}

?>