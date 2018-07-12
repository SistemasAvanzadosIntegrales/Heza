<?php

/**
 * @class        SubsidioEmpleoController
 * @author:      
 * @contact:     
 * @description: 
 * @version:     1.0
 * @path call:   subsidio-empleo/index.phtml
 * @copyright:   Avansys
 **/
class SubsidioEmpleoController extends Zend_Controller_Action {
	
	/**
	 * @function     init
	 * @author:      
	 * @contact:     
	 * @description: 
	 * @version:     1.0
	 * @path call:   subsidio-empleo/index.phtml
	 * @copyright:   Avansys
	 **/
	public function init () {
		$this->view->headScript()->appendFile('/js/backend/subsidio-empleo.js');
	}
	
	/**
	 * @function     indexAction
	 * @author:      
	 * @contact:     
	 * @description: 
	 * @version:     1.0
	 * @path call:   subsidio-empleo/index.phtml
	 * @copyright:   Avansys
	 **/
	public function indexAction () {
		
	}
	
	/**
	 * @function     gridAction
	 * @author:      
	 * @contact:     
	 * @description: 
	 * @version:     1.0
	 * @path call:   subsidio-empleo/index.phtml
	 * @copyright:   Avansys
	 **/
	public function gridAction () {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$empresa    = $this->_getParam('empresa_id');
		$ejercicio  = $this->_getParam('ejercicio_id');
		$conexion   = My_Comun::obtener("Empresa", "id", $empresa);
		$cnx        = Conexion::abreConexion($conexion->usuario_bd_contpaq, $conexion->pass_bd_contpaq, $conexion->nombre_bd_contpaq, $conexion->server_bd_contpaq);
		$data       = '';
		$remanente  = 0;
		$ejercicio_ = My_Comun::obtener("Ejercicio", "id", $ejercicio);
		$index      = 0;
		$grid       = array();
		
		$meses = array (
			'1'  => 'Enero',
			'2'  => 'Febrero',
			'3'  => 'Marzo',
			'4'  => 'Abril',
			'5'  => 'Mayo',
			'6'  => 'Junio',
			'7'  => 'Julio',
			'8'  => 'Agosto',
			'9'  => 'Septiembre',
			'10' => 'Octubre',
			'11' => 'Noviembre',
			'12' => 'Diciembre',
		);
		
		for($i = 1; $i <= 12; $i++){
			$sql = "
				    SELECT SUM(sc.Importes".$i.")
						   FROM SaldosCuentas sc
				INNER JOIN Cuentas c
						   ON sc.IdCuenta = c.Id 
				INNER JOIN AgrupadoresSAT a 
						   ON c.IdAgrupadorSAT = a.Id
					 WHERE (a.Codigo = '110.01') OR (a.Codigo = '113.06')
						   AND sc.Ejercicio = (SELECT Id FROM Ejercicios WHERE Ejercicio = '".$ejercicio_->nombre."')
						   AND sc.Tipo = '3'";
				
				$query = sqlsrv_query($cnx, $sql) OR print_r(sqlsrv_errors());
				$query = sqlsrv_fetch_array($query);
			
			$data[$i]['periodo']   = $meses[$i];
			$data[$i]['monto']     = $query[0];
			$data[$i]['remanente'] = $query[0];
			$remanente            += $query[0];
		}
		
		sqlsrv_close($cnx);
		
		$registros = My_Comun::registrosGridArray($data);
		
		foreach($registros['registros'] AS $id => $periodo) {
			
			$grid[$index]['fecha']     = '<span class="registro rel="'.$id.'">'.$periodo['periodo'].'</span>';
			$grid[$index]['monto']     = '<span class="registro rel="'.$id.'">$'.number_format($periodo['monto'], 2).'</span>';
			$grid[$index]['remanente'] = '<span class="registro rel="'.$id.'">$'.number_format($periodo['remanente'], 2).'</span>';
			
			$index++;
		}
		
		My_Comun::armarGrid($registros, $grid);
	}
	
	/**
	 * @function     gridAplicacionAction
	 * @author:      
	 * @contact:     
	 * @description: 
	 * @version:     1.0
	 * @path call:   subsidio-empleo/index.phtml
	 * @copyright:   Avansys
	 **/
	public function gridAplicacionAction () {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$empresa    = $this->_getParam('empresa_id');
		$ejercicio  = $this->_getParam('ejercicio_id');
		
		$filtro  = " 1=1 ";
		$filtro .= " AND empresa_id = '".$empresa."' ";
		$filtro .= " AND ejercicio_id = '".$ejercicio."' ";
		$filtro .= " AND tipo = 1 ";
		
		$registros = My_Comun::registrosGrid('Aplicaciones', $filtro);
		$grid = array();
		$i = 0;
		
		$meses = array (
			'1'  => 'Enero',
			'2'  => 'Febrero',
			'3'  => 'Marzo',
			'4'  => 'Abril',
			'5'  => 'Mayo',
			'6'  => 'Junio',
			'7'  => 'Julio',
			'8'  => 'Agosto',
			'9'  => 'Septiembre',
			'10' => 'Octubre',
			'11' => 'Noviembre',
			'12' => 'Diciembre',
		);
		
		foreach($registros['registros'] AS $registro) {
			
			$impuesto = My_Comun::obtener('AplicacionesImpuesto', 'id', $registro['impuesto_id']);
			
			$grid[$i]['periodo']  = '<span class="registro" rel="'.$registro['id'].'">'.$meses[$registro['periodo_id']].'</span>';
			$grid[$i]['impuesto'] = '<span class="registro" rel="'.$registro['id'].'">'.utf8_encode($impuesto->nombre).'</span>';
			$grid[$i]['monto']    = '<span class="registro" rel="'.$registro['id'].'">$'.$registro['monto'].'</span>';
			
			$i++;
		}
		
		echo My_Comun::armarGrid($registros, $grid);
	}
	
	/**
	 * @function     gridAction
	 * @author:      Christian Murillo
	 * @contact:     
	 * @description: 
	 * @version:     1.0
	 * @path call:   compensacion/index.phtml
	 * @copyright:   Avansys
	 **/
	public function obtenerRemanenteAction () {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$empresa    = $this->_getParam('empresa_id');
		$ejercicio  = $this->_getParam('ejercicio_id');
		$conexion   = My_Comun::obtener("Empresa", "id", $empresa);
		$cnx        = Conexion::abreConexion($conexion->usuario_bd_contpaq, $conexion->pass_bd_contpaq, $conexion->nombre_bd_contpaq, $conexion->server_bd_contpaq);
		$remanente  = 0;
		$ejercicio_ = My_Comun::obtener("Ejercicio", "id", $ejercicio);
		
		for($i = 1; $i <= 12; $i++){
			$sql = "
				    SELECT SUM(sc.Importes".$i.")
						   FROM SaldosCuentas sc
				INNER JOIN Cuentas c
						   ON sc.IdCuenta = c.Id 
				INNER JOIN AgrupadoresSAT a 
						   ON c.IdAgrupadorSAT = a.Id
					 WHERE (a.Codigo = '110.01') OR (a.Codigo = '113.06')
						   AND sc.Ejercicio = (SELECT Id FROM Ejercicios WHERE Ejercicio = '".$ejercicio_->nombre."')
						   AND sc.Tipo = '3'";
				
				$query = sqlsrv_query($cnx, $sql) OR print_r(sqlsrv_errors());
				$query = sqlsrv_fetch_array($query);
			
			$remanente += $query[0];
			$isr_compensacion = $this->get_isr_compensaciones($empresa, $ejercicio, $i);
			$remanente       -= $isr_compensacion;
			$iva_compensacion = $this->get_iva_compensaciones($empresa, $ejercicio, $i);
			$remanente       -= $isr_compensacion;
		}
		
		sqlsrv_close($cnx);
		
		$consulta = "select sum(monto_aplicar) as compensaciones_tot from compensacion where periodo in 
					(select id_periodo from impuesto_periodo_pm where id_empresa='".$empresa."' and id_ejercicio='".$ejercicio."' and `status`=1)";
		
		$regs = My_Comun::crearQuery('Compensacion',$consulta);
		$remanente -=($regs[0]['compensaciones_tot']);
		
		echo round($remanente, 2);
	}
	
	/**
	 * @function     get_isr_compensaciones
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function	get_isr_compensaciones ($empresa, $ejercicio, $periodo) {
		
		$query = 
			"SELECT SUM(monto_aplicar) AS monto
			   FROM compensacion
			  WHERE empresa_id        = ".$empresa."
			        AND ejercicio_id  = ".$ejercicio."
			        AND periodo       = ".$periodo."
			        AND tipo_impuesto = 'ISR'";
		
		$monto   = My_Comun::crearQuery('Aplicaciones', $query);
		
		$monto = ($monto[0]['monto']) ? $monto[0]['monto'] : 0;
		
		return $monto;
	}
	
	/**
	 * @function     get_iva_compensaciones
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function	get_iva_compensaciones ($empresa, $ejercicio, $periodo) {
		
		$query = 
			"SELECT SUM(monto_aplicar) AS monto
			   FROM compensacion
			  WHERE empresa_id        = ".$empresa."
			        AND ejercicio_id  = ".$ejercicio."
			        AND periodo       = ".$periodo."
			        AND tipo_impuesto = 'IVA'";
		
		$monto   = My_Comun::crearQuery('Aplicaciones', $query);
		
		$monto = ($monto[0]['monto']) ? $monto[0]['monto'] : 0;
		
		return $monto;
	}
	
	/**
	 * @function     obtenerAction
	 * @author:      
	 * @contact:     
	 * @description: 
	 * @version:     1.0
	 * @path call:   subsidio-empleo/index.phtml
	 * @copyright:   Avansys
	 **/
	public function obtenerAction () {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$array = array();
		$array["error"] = '';
		
		if($_POST["id"] != "0") {
			
			$registro           = My_Comun::obtener("SubsidioEmpleo", "id", $_POST["id"]);
			$array["id"]        = (string)$registro->id;
			$array["fecha"]     = (string)$registro->fecha;
			$array["monto"]     = (float)$registro->monto;
			$array["remanente"] = (float)$registro->remanente;
			$array["empresa"]   = (string)$registro->empresa_id;
			$array["ejercicio"] = (string)$registro->ejercicio_id;
			$array["status"]    = (string)$registro->status;
			
		}
		else {
			$array["error"] = "Error al obtener el subsidio de empleo: identificador no existe.";
		}
		
		echo json_encode($array);
	}
	
	/**
	 * @function     exportarAction
	 * @author:      
	 * @contact:     
	 * @description: 
	 * @version:     1.0
	 * @path call:   subsidio-empleo/index.phtml
	 * @copyright:   Avansys
	 **/
	public function exportarAction () {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$empresa   = $this->_getParam('empresa_id');
		$ejercicio = $this->_getParam('ejercicio_id');
		
		$i = 7;
		$data = array();
		
		$i++;
		
		$sql = "SELECT * 
				  FROM aplicaciones 
				 WHERE empresa_id = ".$empresa."
				       AND ejercicio_id = ".$ejercicio."
				       AND tipo = 1";
		
		$registros = My_Comun::crearQuery('Aplicaciones', $sql);
		
		$meses = array (
			'1'  => 'Enero',
			'2'  => 'Febrero',
			'3'  => 'Marzo',
			'4'  => 'Abril',
			'5'  => 'Mayo',
			'6'  => 'Junio',
			'7'  => 'Julio',
			'8'  => 'Agosto',
			'9'  => 'Septiembre',
			'10' => 'Octubre',
			'11' => 'Noviembre',
			'12' => 'Diciembre',
		);
		
		ini_set("memory_limit", "130M");
		ini_set('max_execution_time', 0);
		
		$objPHPExcel = new My_PHPExcel_Excel();
		
		$columns_name = 
			array (
				"B$i" => array(
						"name" => 'Periodo',
						"width" => 20
						),
				"C$i" => array(
						"name" => 'Impuesto',
						"width" => 50
						),
				"D$i" => array(
						"name" => 'Monto aplicado',
						"width" => 20
						)
			);
		
		//Datos tabla
		foreach( $registros AS $registro ) {
			
			$impuesto = My_Comun::obtener('AplicacionesImpuesto', 'id', $registro['impuesto_id']);
			
			$i++;
			
			$data[] = 
				array(
					"B$i" => $meses[$registro['periodo_id']],
					"C$i" => utf8_encode($impuesto->nombre),
					"D$i" => '$'.$registro['monto']
				);
		}
		
		$objPHPExcel->createExcel('SubsidioEmpleo', $columns_name, $data, 10, array('rango' => 'A4:E4', 'texto' => 'Impresión de aplicaciones a subsidios al empleo'));
	}
	
	/**
	 * @function     imprimirAction
	 * @author:      
	 * @contact:     
	 * @description: 
	 * @version:     1.0
	 * @path call:   subsidio-empleo/index.phtml
	 * @copyright:   Avansys
	 **/
	public function imprimirAction () {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$empresa   = $this->_getParam('empresa_id');
		$ejercicio = $this->_getParam('ejercicio_id');
		
		$sql = "SELECT * 
				  FROM aplicaciones 
				 WHERE empresa_id = ".$empresa."
				       AND ejercicio_id = ".$ejercicio."
				       AND tipo = 1";
		
		$registros = My_Comun::crearQuery('Aplicaciones', $sql);
		
		$meses = array (
			'1'  => 'Enero',
			'2'  => 'Febrero',
			'3'  => 'Marzo',
			'4'  => 'Abril',
			'5'  => 'Mayo',
			'6'  => 'Junio',
			'7'  => 'Julio',
			'8'  => 'Agosto',
			'9'  => 'Septiembre',
			'10' => 'Octubre',
			'11' => 'Noviembre',
			'12' => 'Diciembre',
		);
		
		$pdf = new My_Fpdf_Pdf();
		
		$pdf->AliasNbPages();
		$pdf->AddPage();
		
		$pdf->Header("Impresión de aplicaciones a subsidios al empleo");
		
		$pdf->SetFont('Arial','B',11);
		$pdf->SetWidths(array(35, 95, 60));
		$pdf->Row(array('Periodo', 'Impuesto', 'Monto aplicado',''), 0, 1);
		$pdf->SetFont('Arial', '', 10);
		
		
		
		foreach($registros AS $registro) {
			
			$impuesto = My_Comun::obtener('AplicacionesImpuesto', 'id', $registro['impuesto_id']);
			
			$pdf->Row (
				array (
					$meses[$registro['periodo_id']], 
					utf8_encode($impuesto->nombre), 
					'$'.$registro['monto']
				), 0, 1
			);
		}
		
		ob_end_clean();
		
		$pdf->Output();
	}
}

?>