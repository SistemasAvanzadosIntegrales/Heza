<?php

/**
 * @class        CompensacionController
 * @author:      
 * @contact:     
 * @description: 
 * @version:     1.0
 * @path call:   compensacion/index.phtml
 * @copyright:   Avansys
 **/
class CompensacionController extends Zend_Controller_Action {
	
	/**
	 * @function     init
	 * @author:      
	 * @contact:     
	 * @description: 
	 * @version:     1.0
	 * @path call:   compensacion/index.phtml
	 * @copyright:   Avansys
	 **/
	public function init(){
		$this->view->headScript()->appendFile('/js/backend/compensacion.js');
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
		$this->view->puedeAgregar  = strpos($sess->cliente->permisos, "AGREGAR_COMPENSACIONES" )  !== false;
		$this->view->puedeEliminar = strpos($sess->cliente->permisos, "ELIMINAR_COMPENSACIONES" ) !== false;
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
		$sess = new Zend_Session_Namespace('permisos');
		
		$filtro = '';
		$grid = array();
		$i = 0;
		
		$empresa   = $this->_getParam('empresa_id');
		$ejercicio = $this->_getParam('ejercicio_id');
		
		$userid = Zend_Auth::getInstance()->getIdentity()->id;
		
		if($empresa != '')
			$filtro .= " AND (c.empresa_id = '".$empresa."')  ";
		
		if($ejercicio != '')
			$filtro .= " AND (c.ejercicio_id = '".$ejercicio."')  ";
		
		$query = "
			SELECT DISTINCT c.id, c.fecha, c.tipo_impuesto, 
			                c.periodo, e.nombre AS ejercicio,
			                c.tipo_declaracion, c.numero_operacion, 
			                c.monto_original, c.monto_aplicar
			           FROM compensacion c
			     INNER JOIN ejercicio e 
			                ON (e.id = c.ejercicio_id)
			          WHERE c.status = 1 ".$filtro."";
		
		$registros = My_Comun::registrosGridQuery($query);
		
		foreach($registros['registros'] AS $id => $subsidio) {
			
			$grid[$i]['fecha']            = '<span class="registro" rel="'.$subsidio['id'].'">'.$subsidio['fecha'].'</span>';
			$grid[$i]['tipo_impuesto']    = '<span class="registro" rel="'.$subsidio['id'].'">'.$subsidio['tipo_impuesto'].'</span>';
			$grid[$i]['periodo']          = '<span class="registro" rel="'.$subsidio['id'].'">'.$subsidio['periodo'].'</span>';
			$grid[$i]['ejercicio']        = '<span class="registro" rel="'.$subsidio['id'].'">'.$subsidio['ejercicio'].'</span>';
			$grid[$i]['tipo_declaracion'] = '<span class="registro" rel="'.$subsidio['id'].'">'.$subsidio['tipo_declaracion'].'</span>';
			$grid[$i]['numero_operacion'] = '<span class="registro" rel="'.$subsidio['id'].'">'.$subsidio['numero_operacion'].'</span>';
			$grid[$i]['monto_original']   = '<span class="registro" rel="'.$subsidio['id'].'">'.$subsidio['monto_original'].'</span>';
			$grid[$i]['monto_aplicar']    = '<span class="registro" rel="'.$subsidio['id'].'">'.$subsidio['monto_aplicar'].'</span>';
			
			$i++;
		}
		
		My_Comun::armarGrid($registros, $grid);
	}
	
	/**
	 * @function     gridAplicacionAction
	 * @author:      
	 * @contact:     
	 * @description: 
	 * @version:     1.0
	 * @path call:   compensacion/index.phtml
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
		$filtro .= " AND tipo = 2 ";
		
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
	 * @function     gridAplicacionAction
	 * @author:      
	 * @contact:     
	 * @description: 
	 * @version:     1.0
	 * @path call:   compensacion/index.phtml
	 * @copyright:   Avansys
	 **/
	public function obtenerAction () {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$array = array();
		$array["error"] = '';
		
		if( $_POST["id"] != "0" ) {
			
			$registro                  = My_Comun::obtener("Compensacion", "id", $_POST["id"]);
			$array["id"]               = (string)$registro->id;
			$array["tipo_impuesto"] = (string)$registro->tipo_impuesto;
                        $array["fecha"]            = (string)$registro->fecha;
                        $array["periodo"] = (int)$registro->periodo;
			$array["tipo_declaracion"]            = (string)$registro->tipo_declaracion;
			$array["numero_operacion"]        = (string)$registro->numero_operacion;
                        $array["monto_original"] = (float)$registro->monto_original;
                        $array["monto_aplicar"] = (float)$registro->monto_aplicar;
                        $array["status"] = (float)$registro->status;
		}
		else {
			$array["error"] = "Error al obtener la compensacion: identificador no existe.";
		}
		
		echo json_encode($array);
	}
	
	/**
	 * @function     gridAction
	 * @author:      Danny Ramirez
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
				    SELECT round(SUM(sc.Importes".$i."), 2)
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
			
			$remanente       += $query[0];
      $aplicacion       = $this->obtenerAplicacionesPeriodo($empresa, $ejercicio, $i);
      $remanente       -= $aplicacion;
			$isr_compensacion = $this->get_isr_compensaciones($empresa, $ejercicio, $i);
			$remanente       -= $isr_compensacion;
			$iva_compensacion = $this->get_iva_compensaciones($empresa, $ejercicio, $i);
			$remanente       -= $iva_compensacion;
		}
		
    sqlsrv_close($cnx);
    
    echo $remanente;
	}
  
  /**
   * @function     obtenerAplicacionesPeriodo
   * @author:      
   * @contact:     
   * @description: 
   * @version:     1.0
   * @path call:   subsidio-empleo/index.phtml
   * @copyright:   Avansys
   **/
  public function obtenerAplicacionesPeriodo ($empresa, $ejercicio, $periodo) {
    
    $query = 
      "SELECT SUM(monto) AS monto 
         FROM aplicaciones 
        WHERE empresa_id   = ".$empresa."
              AND ejercicio_id = ".$ejercicio."
              AND periodo_id   = ".$periodo."";
    
    $monto = My_Comun::crearQuery('Aplicaciones', $query);
    $monto = ($monto[0]['monto']) ? $monto[0]['monto'] : 0;
    
    return $monto;
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
	 * @function     guardarAction
	 * @author:      Christian 
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
		$bitacora[0]["modelo"]  = "Compensacion";
		$bitacora[0]["campo"]   = "fecha";
		$bitacora[0]["id"]      = $_POST["id"];
		$bitacora[0]["agregar"] = "Agregar compensacion";
		$bitacora[0]["editar"]  = "Editar compensacion";
		
		// if( $_POST['id'] > 0 ) {
			
			// $query = "
			        // SELECT SUM(monto) AS aplicaciones
			          // FROM aplicacion_compensacion 
			         // WHERE id_compensacion = '".$_POST['id']."'";
			
			// $registro = My_Comun::crearQuery("Compensacion", $query);
			
			// for( $i = 0; $i < count($registro); $i++ ) {
				// $aplicaciones = $registro[$i]['aplicaciones'];
			// }
			
			// $remanente = $_POST['monto'] - $aplicaciones;
			// $_POST['remanente'] = $remanente;
		// }
		// else
			// {
			$_POST['fecha'] = date('Y-m-d');
		// }
		
		//print_r($_POST);
		if( $_POST['periodo'] == 13 ) {
			for($i = 1; $i < 13; $i++) {
				$_POST['periodo']=$i;
				$compensacionId = My_Comun::Guardar("Compensacion", $_POST, $_POST["id"], $bitacora);
			}
		}
		else {
			$compensacionId = My_Comun::Guardar("Compensacion", $_POST, $_POST["id"], $bitacora);
		}
		
		echo($compensacionId);
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
		
		$regi = My_Comun::obtener("Compensacion", "id", $_POST["id"]);
		
		$bitacora                    = array();
		$bitacora[0]["modelo"]       = "Compensacion";
		$bitacora[0]["campo"]        = "fecha";
		$bitacora[0]["id"]           = $_POST["id"];
		$bitacora[0]["eliminar"]     = "Eliminar compensacion";
		$bitacora[0]["deshabilitar"] = "Deshabilitar compensacion";
		$bitacora[0]["habilitar"]    = "Habilitar compensacion";
		
		echo My_Comun::eliminar("Compensacion", $_POST["id"], $bitacora);
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
				       AND tipo = 2";
		
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
		
		$objPHPExcel->createExcel('SubsidioEmpleo', $columns_name, $data, 10, array('rango' => 'A4:E4', 'texto' => 'Impresión de aplicaciones a compensaciones'));
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
				       AND tipo = 2";
		
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
		
		$pdf->Header("Impresión de aplicaciones a compensaciones");
		
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