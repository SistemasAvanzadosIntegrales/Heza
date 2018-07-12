<?php

/**
 * @class        CoeficienteController
 * @author:      
 * @contact:     
 * @description: 
 * @version:     1.0
 * @path call:   usuario/index.phtml
 * @copyright:   Avansys
 **/
class CoeficienteController extends Zend_Controller_Action {
	
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
		$this->view->headScript()->appendFile('/js/backend/coeficiente.js');
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
		$this->view->puedeAgregar=strpos($sess->cliente->permisos,"AGREGAR_EMPRESA")!==false;
		$this->view->puedeEliminar=strpos($sess->cliente->permisos,"ELIMINAR_EMPRESA")!==false;
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
		
		// $nombre = $this->_getParam('nombre');
		// $razon  = $this->_getParam('razon');
		
		/*
		
		if($nombre != '') {
			
			$nombre = explode(" ", trim($nombre));
			
			for( $i = 0; $i <= $nombre[$i]; $i++) {
				
				$nombre[$i] = trim(str_replace(array("'", "\"", ), array("�", "�"), $nombre[$i]));
				
				if($nombre[$i] != "")
					$filtro .= " AND (nombre LIKE '%".$nombre[$i]."%')  ";
			}
		}
		
		if($razon != '') {
			
			$razon = explode(" ", trim($razon));
			for($i = 0; $i <= $razon[$i]; $i++) {
				
				$razon[$i] = trim(str_replace(array("'", "\"", ), array("�", "�"), $razon[$i]));
				if($razon[$i] != "")
					$filtro .= " AND (razon_social LIKE '%".$razon[$i]."%')  ";
			}
		}*/
		
		$registros = My_Comun::registrosGrid("Coeficiente", $filtro);
		$grid = array();
		$i = 0;
		
		foreach($registros['registros'] AS $registro) {
			
			$empresa   = My_Comun::obtener("Empresa",   "id", $registro->id_empresa);
			$ejercicio = My_Comun::obtener("Ejercicio", "id", $registro->id_ejercicio);
			
			$grid[$i]['id_empresa']              = '<span class="registro" rel="'.$registro->id.'">'.$empresa->nombre.'</span>';
			$grid[$i]['id_ejercicio']            = '<span class="registro" rel="'.$registro->id.'">'.$ejercicio->nombre.'</span>';
			$grid[$i]['id_periodo_inicio']       = '<span class="registro" rel="'.$registro->id.'">'.$meses[$registro->id_periodo_inicio].'</span>';
			$grid[$i]['id_periodo_fin']          = '<span class="registro" rel="'.$registro->id.'">'.$meses[$registro->id_periodo_fin].'</span>';
			$grid[$i]['coeficiente_utilidad']    = '<span class="registro" rel="'.$registro->id.'">'.$registro->coeficiente_utilidad.'</span>';
			
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
		$bitacora[0]["modelo"]  = "Coeficiente";
		$bitacora[0]["campo"]   = "id_empresa";
		$bitacora[0]["id"]      = $_POST["id"];
		$bitacora[0]["agregar"] = "Agregar coeficiente";
		$bitacora[0]["editar"]  = "Editar coeficiente";
		
		$coeficiente = My_Comun::Guardar("Coeficiente", $_POST, $_POST["id"], $bitacora);
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
			
			$registro = My_Comun::obtener("Coeficiente", "id", $_POST["id"]);
			
			$array["id"]                   = (string)$registro->id;
			$array["id_empresa"]           = (string)$registro->id_empresa;
			$array["id_ejercicio"]         = (string)$registro->id_ejercicio;
			$array["id_periodo_inicio"]    = (string)$registro->id_periodo_inicio;
			$array["id_periodo_fin"]       = (string)$registro->id_periodo_fin;
			$array["coeficiente_utilidad"] = (string)$registro->coeficiente_utilidad;
		}
		else {
			$array["error"] = "Error al obtener el coeficiente: identificador no existe.";
		}
		
		echo json_encode($array);
	}
	
	/**
	 * eliminarAction
	 **/
	function eliminarAction () {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$regi = My_Comun::obtener("Coeficiente", "id", $_POST["id"]);
		
		$bitacora = array();
		$bitacora[0]["modelo"]       = "Coeficiente";
		$bitacora[0]["campo"]        = "id_empresa";
		$bitacora[0]["id"]           = $_POST["id"];
		$bitacora[0]["eliminar"]     = "Eliminar coeficiente";
		$bitacora[0]["deshabilitar"] = "Deshabilitar coeficiente";
		$bitacora[0]["habilitar"]    = "Habilitar coeficiente";
		
		echo "<pre>";
		print_r($_POST["id"]);
		echo "</pre>";
		
		echo My_Comun::eliminar("Coeficiente", $_POST["id"], $bitacora);
	}
	
	/**
	 * exportarAction
	 **//*
	public function exportarAction () {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$nombre = $this->_getParam('nombre');
		$razon  = $this->_getParam('razon');
		$filtro = " 1=1 ";
		$i      = 6;
		$data   = array();
		
		if($this->_getParam('status') != "") {
			
			$filtro .= " AND status='".str_replace("'", "�", $this->_getParam('status'))."' ";
			
			if( $this->_getParam('status') == 0 )
				$data[] = array("A$i" =>"Estatus:","B$i" => "Inactivo");
			else
				$data[] = array("A$i" =>"Estatus:","B$i" => "Activo");
			
			$i++;
		}
		
		if( $nombre != '' ) {
			
			$data[] = array("A$i" =>"Nombre:","B$i" => $nombre);
			$i++;
			$nombre = explode(" ", trim($nombre));
			
			for( $j = 0; $j <= $nombre[$j]; $j++) {
				
				$nombre[$j] = trim(str_replace(array("'", "\"", ), array("�", "�"), $nombre[$j]));
				
				if( $nombre[$j] != "" )
					$filtro .= " AND ( nombre LIKE '%".$nombre[$j]."%') ";
			}
		}
		
		if( $razon != '' ) {
			
			$data[] = array("A$i" =>"Razón social:","B$i" => $razon);
			$i++;
			$razon = explode(" ", trim($razon));
			
			for( $j = 0; $j <= $razon[$j]; $j++) {
				
				$razon[$j] = trim(str_replace(array("'", "\"", ), array("�", "�"), $razon[$j]));
				
				if( $razon[$j] != "" ){
					$filtro .= " AND ( razon_social LIKE '%".$razon[$j]."%' ) ";
				}
			}
		}
		
		$i++;
		$registros = My_Comun::obtenerFiltro("Empresa", $filtro, "nombre ASC");
		
		ini_set("memory_limit", "130M");
		ini_set('max_execution_time', 0);
		
		$objPHPExcel = new My_PHPExcel_Excel();
		
		$columns_name = array (
				"A$i" => array(
						"name" => 'No. DE EMPRESA',
						"width" => 16
						),
				"B$i" => array(
						"name" => 'NOMBRE ',
						"width" => 30
						),
				"C$i" => array(
						"name" => 'RAZÓN SOCIAL',
						"width" => 50
						),
				"D$i" => array(
						"name" => 'NOMBRE BD CONTPAQ',
						"width" => 50
						),
				"E$i" => array(
						"name" => 'COEFICIENTE DE UTILIDAD',
						"width" => 50
						),
				"F$i" => array(
						"name" => 'TASA',
						"width" => 50
						),
				"G$i" => array(
						"name" => '#CUENTA ISR RETENIDO',
						"width" => 50
						),
				"H$i" => array(
						"name" => 'Retención salarios',
						"width" => 50
						),
				"I$i" => array(
						"name" => 'Retención ISR honorarios',
						"width" => 50
						),
				"J$i" => array(
						"name" => 'Retención asimilados',
						"width" => 50
						),
				"K$i" => array(
						"name" => 'Retención dividendos',
						"width" => 50
						),
				"L$i" => array(
						"name" => 'Retencion intereses',
						"width" => 50
						),
				"M$i" => array(
						"name" => 'Retención pagos al extranjero',
						"width" => 50
						),
				"N$i" => array(
						"name" => 'Retención venta de acciones',
						"width" => 50
						),
				"O$i" => array(
						"name" => 'Retención venta de partes sociales',
						"width" => 50
						),
				"P$i" => array(
						"name" => 'Retención ISR arrendamiento',
						"width" => 50
						),
				"Q$i" => array(
						"name" => 'TIPO DE EMPRESA',
						"width" => 50
						),
				"R$i" => array(
						"name" => 'ESTATUS',
						"width" => 13
						)
		);
		
		//Datos tabla
		foreach( $registros AS $registro ) {
			
			if($registro->status == "0")
				$a = "Inactivo";
			else
				$a =  "Activo";
			
			$i++;
			
			$data[] = array(
						"A$i" =>$registro->id,
						"B$i" =>$registro->nombre,
						"C$i" =>$registro->razon_social,
						"D$i" =>$registro->nombre_bd_contpaq,
						"E$i" =>$registro->coeficiente_utilidad,
						"F$i" =>$registro->tasa,
						"G$i" =>$registro->isr_retenido,
						"H$i" =>$registro->retencion_salarios,
						"I$i" =>$registro->retencion_isr_honorarios,
						"J$i" =>$registro->retencion_asimilados,
						"K$i" =>$registro->retencion_dividendos,
						"L$i" =>$registro->retencion_intereses,
						"M$i" =>$registro->retencion_pagos_extranjero,
						"N$i" =>$registro->retencion_venta_acciones,
						"O$i" =>$registro->retencion_venta_partes_sociales,
						"P$i" =>$registro->retencion_isr_arrendamiento,
						"Q$i" =>$registro->TipoEmpresa->tipo_empresa,
						"R$i" =>$a
					);
		}
		
		$objPHPExcel->createExcel('Empresas', $columns_name, $data, 10,array('rango'=>'A4:G4','texto'=>'Empresas'));
	}
	
	/**
	 * imprimirAction
	 **//*
	public function imprimirAction () {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$nombre = $this->_getParam('nombre');
		$razon  = $this->_getParam('razon');
		
		$filtro=" 1=1 ";
		
		if( $this->_getParam('status') != "") {
			
			$filtro .= " AND status='".str_replace("'", "�", $this->_getParam('status'))."' ";
			
			if( $this->_getParam('status') == 0 )
				$data[] = array("A$i" => "Estatus:", "B$i" => "Inactivo");
			else
				$data[] = array("A$i" => "Estatus:", "B$i" => "Activo");
			
			$i++;
		}
		
		if( $nombre != '' ) {
			
			$data[] = array("A$i" => "Nombre:", "B$i" => $nombre);
			$i++;
			$nombre = explode(" ", trim($nombre));
			
			for( $j = 0; $j <= $nombre[$j]; $j++ ) {
				
				$nombre[$j] = trim(str_replace(array("'", "\"", ), array("�", "�"), $nombre[$j]));
				
				if( $nombre[$j] != "" ) {
					$filtro .= " AND ( nombre LIKE '%".$nombre[$j]."%'  ) ";
				}
			}
		}
		
		if($razon != '') {
			
			$data[] = array("A$i" => "Razón social:", "B$i" => $razon);
			$i++;
			$razon = explode(" ", trim($razon));
			
			for( $j = 0; $j <= $razon[$j]; $j++ ) {
				
				$razon[$j] = trim(str_replace(array("'", "\"", ), array("�", "�"), $razon[$j]));
				
				if( $razon[$j] != "" ) {
					$filtro .= " AND ( razon_social LIKE '%".$razon[$j]."%' ) ";
				}
			}
		}
		
		$registros = My_Comun::obtenerFiltro("Empresa", $filtro, 'nombre ASC');
		
		ob_start();
		
		$pdf = new My_Fpdf_Pdf('L');
		
		$pdf->AliasNbPages();
		$pdf->AddPage();
		
		$pdf->Header("IMPRESIÓN DE EMPRESAS");
		
		$pdf->SetFont('Arial','B',11);
		$pdf->SetWidths(array(	35,
								55,
								50,
								40,
								30,
								40,
								25));
		
		$pdf->Row(array('NO. DE EMPRESA',
						'NOMBRE','RAZÓN SOCIAL',
						'NOMBRE BD CONTPAQ',
						'# CTA ISR RETENIDO',
						'TIPO DE EMPRESA',
						'ESTATUS'), 0, 1);
		
		$pdf->SetFont('Arial','',10);
		
		foreach($registros AS $registro) {
			
			$estatus = '';
			
			switch( $registro['status'] ) {
				case 0: $estatus = 'Inactivo'; break;
				case 1: $estatus = 'Activo'; break;
			}
			
			$pdf->Row (
				array (
					$registro->id, 
					$registro->nombre, 
					$registro->razon_social,
					$registro->nombre_bd_contpaq,
					$registro->isr_retenido,
					$registro->TipoEmpresa->tipo_empresa,
					$estatus
				), 0, 1
			);
		}
		
		$pdf->Output();
		
		ob_end_flush(); 
	}*/
}

?>