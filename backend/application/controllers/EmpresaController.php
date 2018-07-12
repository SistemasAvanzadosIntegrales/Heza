<?php

/**
 * EmpresaController
 **/
class EmpresaController extends Zend_Controller_Action {
	
	/**
	 * init
	 **/
	public function init () {
		$this->view->headScript()->appendFile('/js/backend/empresa.js');
	}
	
	/**
	 * indexAction
	 **/
	public function indexAction () {
		
		$sess = new Zend_Session_Namespace('permisos');
		$this->view->puedeAgregar  = strpos($sess->cliente->permisos,"AGREGAR_EMPRESA")!==false;
		$this->view->puedeEliminar = strpos($sess->cliente->permisos,"ELIMINAR_EMPRESA")!==false;
		$this->view->tiposEmpresas = My_Comun::obtenerFiltro('TipoEmpresa',' status = 1', ' tipo_empresa asc');
	}
	
	/**
	 * indexAction
	 **/
	public function gridAction () {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		$sess=new Zend_Session_Namespace('permisos');
		
		$filtro=" 1 = 1 ";
		
		$nombre = $this->_getParam('nombre');
		$razon  = $this->_getParam('razon');
		$status = $this->_getParam('status');
		
		if($this->_getParam('status') != "")
			$filtro .= " AND status=".$this->_getParam('status');
		
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
		}
		
		$registros = My_Comun::registrosGrid("Empresa", $filtro);
		$grid = array();
		$i = 0;
		
		$editar = My_Comun::tienePermiso("EDITAR_EMPRESA");
		$eliminar = My_Comun::tienePermiso("ELIMINAR_EMPRESA");
		
		foreach($registros['registros'] AS $registro) {
			
			$off = ($registro->status == 0) ? 'desactivado' : '';
			
			$grid[$i]['nombre']                               = '<span class="registro '.$off.'" rel="'.$registro->id.'">'.$registro->nombre.'</span>';
			$grid[$i]['razon_social']                         = '<span class="registro '.$off.'" rel="'.$registro->id.'">'.$registro->razon_social.'</span>';
			$grid[$i]['nombre_bd_contpaq']                    = '<span class="registro '.$off.'" rel="'.$registro->id.'">'.$registro->nombre_bd_contpaq.'</span>';
			$grid[$i]['usuario_bd_contpaq']                   = '<span class="registro '.$off.'" rel="'.$registro->id.'">'.$registro->usuario_bd_contpaq.'</span>';
			$grid[$i]['pass_bd_contpaq']                      = '<span class="registro '.$off.'" rel="'.$registro->id.'">••••••••</span>';
			$grid[$i]['server_bd_contpaq']                    = '<span class="registro '.$off.'" rel="'.$registro->id.'">'.$registro->server_bd_contpaq.'</span>';
			$grid[$i]['tasa']                                 = '<span class="registro '.$off.'" rel="'.$registro->id.'">'.$registro->tasa.'</span>';
			$grid[$i]['isr_retenido']                         = '<span class="registro '.$off.'" rel="'.$registro->id.'">'.$registro->isr_retenido.'</span>';
			$grid[$i]['retencion_salarios']                   = '<span class="registro '.$off.'" rel="'.$registro->id.'">'.$registro->retencion_salarios.'</span>';
			$grid[$i]['retencion_isr_honorarios']             = '<span class="registro '.$off.'" rel="'.$registro->id.'">'.$registro->retencion_isr_honorarios.'</span>';
			$grid[$i]['retencion_asimilados']                 = '<span class="registro '.$off.'" rel="'.$registro->id.'">'.$registro->retencion_asimilados.'</span>';
			$grid[$i]['retencion_dividendos']                 = '<span class="registro '.$off.'" rel="'.$registro->id.'">'.$registro->retencion_dividendos.'</span>';
			$grid[$i]['retencion_intereses']                  = '<span class="registro '.$off.'" rel="'.$registro->id.'">'.$registro->retencion_intereses.'</span>';
			$grid[$i]['retencion_pagos_extranjero']           = '<span class="registro '.$off.'" rel="'.$registro->id.'">'.$registro->retencion_pagos_extranjero.'</span>';
			$grid[$i]['retencion_venta_acciones']             = '<span class="registro '.$off.'" rel="'.$registro->id.'">'.$registro->retencion_venta_acciones.'</span>';
			$grid[$i]['retencion_venta_partes_sociales']      = '<span class="registro '.$off.'" rel="'.$registro->id.'">'.$registro->retencion_venta_partes_sociales.'</span>';
			$grid[$i]['retencion_isr_arrendamiento']          = '<span class="registro '.$off.'" rel="'.$registro->id.'">'.$registro->retencion_isr_arrendamiento.'</span>';
			$grid[$i]['ejercicio_anterior_enero_febrero_1']   = '<span class="registro '.$off.'" rel="'.$registro->id.'">'.$registro->ejercicio_anterior_enero_febrero_1.'</span>';
			$grid[$i]['ejercicio_anterior_enero_febrero_2']   = '<span class="registro '.$off.'" rel="'.$registro->id.'">'.$registro->ejercicio_anterior_enero_febrero_2.'</span>';
			$grid[$i]['ejercicio_anterior_marzo_diciembre_1'] = '<span class="registro '.$off.'" rel="'.$registro->id.'">'.$registro->ejercicio_anterior_marzo_diciembre_1.'</span>';
			// $grid[$i]['ejercicio_anterior_2_febrero']      = '<span class="registro '.$off.'" rel="'.$registro->id.'">'.$registro->ejercicio_anterior_2_febrero.'</span>';
			$grid[$i]['tipo_empresa_id']                      = '<span class="registro '.$off.'" rel="'.$registro->id.'">'.$registro->TipoEmpresa->tipo_empresa.'</span>';
			$grid[$i]['status']                               = '<span class="registro '.$off.'" rel="'.$registro->id.'">'.(($registro->status) ? 'Activo' : 'Inactivo').'</span>';
			
			$i++;
		}
		
		My_Comun::armarGrid($registros,$grid);
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
			
			$registro = My_Comun::obtener("Empresa", "id", $_POST["id"]);
			
			$array["id"]                                   = (string)$registro->id;
			$array["nombre"]                               = (string)$registro->nombre;
			$array["razon_social"]                         = (string)$registro->razon_social;
			$array["nombre_bd_contpaq"]                    = (string)$registro->nombre_bd_contpaq;
			$array["usuario_bd_contpaq"]                   = (string)$registro->usuario_bd_contpaq;
			$array["pass_bd_contpaq"]                      = (string)$registro->pass_bd_contpaq;
			$array["server_bd_contpaq"]                    = (string)$registro->server_bd_contpaq;
			$array["coeficiente_utilidad"]                 = (string)$registro->coeficiente_utilidad;
			$array["tasa"]                                 = (string)$registro->tasa;
			$array["isr_retenido"]                         = (string)$registro->isr_retenido;
			$array["retencion_salarios"]                   = (string)$registro->retencion_salarios;
			$array["retencion_isr_honorarios"]             = (string)$registro->retencion_isr_honorarios;
			$array["retencion_asimilados"]                 = (string)$registro->retencion_asimilados;
			$array["retencion_dividendos"]                 = (string)$registro->retencion_dividendos;
			$array["retencion_intereses"]                  = (string)$registro->retencion_intereses;
			$array["retencion_pagos_extranjero"]           = (string)$registro->retencion_pagos_extranjero;
			$array["retencion_venta_acciones"]             = (string)$registro->retencion_venta_acciones;
			$array["retencion_venta_partes_sociales"]      = (string)$registro->retencion_venta_partes_sociales;
			$array["retencion_isr_arrendamiento"]          = (string)$registro->retencion_isr_arrendamiento;
			$array["ejercicio_anterior_enero_febrero_1"]   = (string)$registro->ejercicio_anterior_enero_febrero_1;
			$array["ejercicio_anterior_enero_febrero_2"]   = (string)$registro->ejercicio_anterior_enero_febrero_2;
			$array["ejercicio_anterior_marzo_diciembre_1"] = (string)$registro->ejercicio_anterior_marzo_diciembre_1;
			// $array["ejercicio_anterior_2_febrero"]      = (string)$registro->ejercicio_anterior_2_febrero;
			$array["tipo_empresa_id"]                      = (string)$registro->tipo_empresa_id;
			$array["status"]                               = (string)$registro->status;
			
		}
		else {
			$array["error"] = "Error al obtener la empresa: identificador no existe.";
		}
		
		echo json_encode($array);
	}
	
	/**
	 * guardarAction
	 **/
	public function guardarAction () {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$bitacora = array();
		$bitacora[0]["modelo"]  = "Empresa";
		$bitacora[0]["campo"]   = "nombre";
		$bitacora[0]["id"]      = $_POST["id"];
		$bitacora[0]["agregar"] = "Agregar empresa";
		$bitacora[0]["editar"]  = "Editar empresa";
		
		$empresaId = My_Comun::Guardar("Empresa", $_POST, $_POST["id"], $bitacora);
		echo($empresaId);
	}
	
	/**
	 * eliminarAction
	 **/
	function eliminarAction () {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$regi = My_Comun::obtener("Empresa", "id", $_POST["id"]);
		
		$bitacora = array();
		$bitacora[0]["modelo"]       = "Empresa";
		$bitacora[0]["campo"]        = "nombre";
		$bitacora[0]["id"]           = $_POST["id"];
		$bitacora[0]["eliminar"]     = "Eliminar empresa";
		$bitacora[0]["deshabilitar"] = "Deshabilitar empresa";
		$bitacora[0]["habilitar"]    = "Habilitar empresa";
			
		echo My_Comun::eliminar("Empresa", $_POST["id"], $bitacora);
	}
	
	/**
	 * exportarAction
	 **/
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
						"name" => 'Perdida de Ejercicios Anteriores Enero y febrero 1 año anterior',
						"width" => 50
						),
				"R$i" => array(
						"name" => 'Perdida de Ejercicios Anteriores Enero y febrero 2 años anteriores',
						"width" => 50
						),
				"S$i" => array(
						"name" => 'Perdida de Ejercicios Anteriores Marzo - Diciembre',
						"width" => 50
						),
				// "T$i" => array(
						// "name" => 'Perdida de Ejercicios Anteriores Febrero 2 años anteriores',
						// "width" => 50
						// ),
				"U$i" => array(
						"name" => 'TIPO DE EMPRESA',
						"width" => 50
						),
				"V$i" => array(
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
						"Q$i" =>$registro->ejercicio_anterior_enero_febrero_1,
						"R$i" =>$registro->ejercicio_anterior_enero_febrero_1,
						"S$i" =>$registro->ejercicio_anterior_marzo_diciembre_1,
						// "T$i" =>$registro->ejercicio_anterior_2_febrero,
						"U$i" =>$registro->TipoEmpresa->tipo_empresa,
						"V$i" =>$a
					);
		}
		
		$objPHPExcel->createExcel('Empresas', $columns_name, $data, 10,array('rango'=>'A4:G4','texto'=>'Empresas'));
	}
	
	/**
	 * imprimirAction
	 **/
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
	}
}

?>