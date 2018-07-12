<?php

/**
 * @class        PagoProvisionalpmController
 * @author:      Christian Murillo
 * @contact:     christian_murillo@avansys.com.mx
 * @description: 
 * @version:     1.0
 * @path call:   pago-provisionalpm/index.phtml
 * @copyright:   Avansys
 **/
class PagoProvisionalpmController extends Zend_Controller_Action {
	
	/**
	 * @function     init
	 * @author:      Christian Murillo
	 * @contact:     christian_murillo@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function init () {
		$this->view->headScript()->appendFile('/js/backend/pago-provisional-pm.js');
	}
	
	/**
	 * @function     indexAction
	 * @author:      Christian Murillo
	 * @contact:     christian_murillo@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function indexAction () {
		
		$sess = new Zend_Session_Namespace('permisos');
		// print_r($sess->permisos);
		$this->view->puedeAgregar = strpos($sess->cliente->permisos, "AGREGAR_PAGO_PM") !== false;
	}
	
	/**
	 * @function     obtenerAction
	 * @author:      Christian Murillo
	 * @contact:     christian_murillo@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function obtenerAction () {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$array = array();
		$array["error"] = '';
		
		if( $_POST["id"] != "0" ) {
			
			$registro           = My_Comun::obtener("SubsidioEmpleo", "id", $_POST["id"]);
			//print_r((string)$registro->id);exit;
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
	 * @function     obtenerejerciciosAction
	 * @author:      Christian Murillo
	 * @contact:     christian_murillo@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function obtenerejerciciosAction () {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$array    = array();
		$query    = "SELECT DISTINCT id, nombre FROM ejercicio";
		$registro = My_Comun::crearQuery("Ejercicio", $query);
		
		$n_reg = count($registro);
		
		if ( $n_reg > 0 ) {
			
			for( $i = 0; $i < $n_reg; $i++ ) {
				$array[$i]["id"]     = (string)$registro[$i]['id'];
				$array[$i]["nombre"] = (string)$registro[$i]['nombre'];
			}
		}
		else {
			$array[$i]["id"]     = '0';
			$array[$i]["nombre"] = 'No Existen Ejercicios';
		}
		
		echo json_encode($array);
	}
	
	/**
	 * @function     obtenerempresasAction
	 * @author:      Christian Murillo
	 * @contact:     christian_murillo@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function obtenerempresasAction () {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$array = array();
		$userid = Zend_Auth::getInstance()->getIdentity()->id;
		
		$query = "
			SELECT DISTINCT e.id, e.nombre, 
			                (SELECT DISTINCT usuario_id 
			                            FROM usuario_empresa 
			                           WHERE empresa_id = e.id 
			                                 AND usuario_id = ".$userid.") AS user
			           FROM empresa e
			LEFT OUTER JOIN usuario_empresa ue
			                ON e.id = ue.empresa_id";
		
		$registro = My_Comun::crearQuery("UsuarioEmpresa", $query);
		
		foreach($registro AS $id => $empresa) { 
			if ( $empresa['user'] != null ) {
				$array[$id]["id"]     = $empresa['id'];
				$array[$id]["nombre"] = $empresa['nombre'];
			}
		}
		
		echo json_encode($array);
	}
	
	/**
	 * @function     congelarArchivoDemandaAction
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	function congelarEjercicioAction () {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		//Obtener id de la asignación de empresa y ejercicio.
		for ($i = 1; $i <= $_POST['periodo']; $i++) {
			
			$filtro  = " 1 = 1 ";
			$filtro .= "AND id_empresa   = '".$_POST['empresa']."' ";
			$filtro .= "AND id_ejercicio = '".$_POST['ejercicio']."' ";
			$filtro .= "AND id_periodo   = '".$i."' ";
			
			$ImpuestoPeriodoPm = My_Comun::obtenerFiltro("ImpuestoPeriodoPm", $filtro, "id ASC");
			
			// Crear ronda general
			$congelar = array();
			$congelar['id']     = $ImpuestoPeriodoPm[0]->id;
			$congelar['status'] = 1;
			
			// $bitacora                   = array();
			// $bitacora[0]["modelo"]      = "ImpuestoPeriodoPm";
			// $bitacora[0]["campo"]       = "status";
			// $bitacora[0]["id"]          = $ImpuestoPeriodoPm[0]->id;
			// $bitacora[0]["congelar"]    = "Congelar periodo";
			// $bitacora[0]["descongelar"] = "Descongelar usuario";
			
			echo My_Comun::Guardar("ImpuestoPeriodoPm", $congelar, $ImpuestoPeriodoPm[0]->id, null);
		}
	}
	
	/**
	 * @function     envdiarAutorizacionAction
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	function enviarAutorizacionAction () {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$usuario   = My_Comun::obtener("Usuario",   "id", Zend_Auth::getInstance()->getIdentity()->id);
		$empresa   = My_Comun::obtener("Empresa",   "id", $_POST['empresa']);
		$ejercicio = My_Comun::obtener("Ejercicio", "id", $_POST['ejercicio']);
		
		$filtro  = " 1 = 1 ";
		$filtro .= "AND id_empresa      = '".$_POST['empresa']."' ";
		$filtro .= "AND id_ejercicio    = '".$_POST['ejercicio']."' ";
		$filtro .= "AND id_usuario      = '".Zend_Auth::getInstance()->getIdentity()->id."' ";
		$filtro .= "AND status_resuelto = '0' ";
		
		//Obtener id de la asignación de empresa y ejercicio.
		$ImpuestoPeriodoPmSolicitud = My_Comun::obtenerFiltro("ImpuestoPeriodoPmSolicitud", $filtro, "id ASC");
		
		echo "<pre>";
		print_r($ImpuestoPeriodoPmSolicitud[0]->id);
		echo "</pre>";
		
		if ( $ImpuestoPeriodoPmSolicitud[0]->id ) {
			echo 'Ya se envío una petición con la información actual, sólo falta esperar la resolución.';
		}
		else {
			
			//Crear Autorización
			$autorizacion = array();
			$autorizacion['id']              = '';
			$autorizacion['id_empresa']      = $_POST['empresa'];
			$autorizacion['id_ejercicio']    = $_POST['ejercicio'];
			$autorizacion['id_periodo']      = $_POST['periodo'];
			$autorizacion['id_usuario']      = Zend_Auth::getInstance()->getIdentity()->id;
			$autorizacion['fecha']           = date("Y-m-d H:i:s");
			$autorizacion['status_resuelto'] = 0;
			
			$bitacora                    = array();
			$bitacora[0]["modelo"]       = "ImpuestoPeriodoPmSolicitud";
			$bitacora[0]["id_empresa"]   = "nombre";
			$bitacora[0]["id"]           = $_POST["id"];
			$bitacora[0]["id_ejercicio"] = $_POST['ejercicio'];
			
			My_Comun::Guardar("ImpuestoPeriodoPmSolicitud", $autorizacion, $autorizacion['id'], $bitacora);
			
			/**
			 * Correo electronico
			 **/
			
			$titulo = 'Solicitud de Autorización de cambio';
			$cuerpo = '	<div class="text-center">
							<h3>'.$usuario->nombre.':</h3>
							<p style="text-align:justify;">Solicita autorización para poder actualizar el ejercicio '.$ejercicio->nombre.' a partir del periodo '.$_POST['periodo'].' 
							de la Empresa '.$empresa->nombre.'.</p>
						</div>';
			
			$de = My_Comun::EMAIL;
			$de_nombre = My_Comun::SISTEMA;
			$para = 'roberto_ramirez@avansys.com.mx';
			$para_nombre = 'Roberto Ramirez';
			
			My_Comun::correo($titulo, $cuerpo, $de, $de_nombre, $para, $para_nombre, "", "");
			
			echo 'Se realizo la petición de autorización, se le informará mediante correo electrónico su resolución';
		}
	}
	
	/**
	 * @function     verAplicacionAction
	 * @author:      Danny Ramirez
	 * @contact:     roberto_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   autorizacion/index.phtml
	 * @copyright:   Avansys
	 **/
	public function verAplicacionAction () {
		
		$this->_helper->layout->disableLayout();
		
		$this->view->monto     = $_POST["monto"];
		$this->view->impuesto  = My_Comun::obtener('AplicacionesImpuesto', 'shortname', $_POST['impuesto']);
		$this->view->tipo      = ($_POST["monto"] == 1) ? 'Subsidio' : 'Compensación';
		$this->view->remanente = $_POST["remanente"];
	}
	
	/**
	 * @function     obtenerRemanente
	 * @author:      
	 * @contact:     
	 * @description: 
	 * @version:     1.0
	 * @path call:   subsidio-empleo/index.phtml
	 * @copyright:   Avansys
	 **/
	public function obtenerRemanenteAction () {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$empresa    = $_POST['id_empresa'];
		$ejercicio  = $_POST['id_ejercicio'];
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
			$remanente       -= $isr_compensacion;
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
	 * @function     obtenerCompensacionStatusAction
	 * @author:      Danny Ramirez
	 * @contact:     
	 * @description: 
	 * @version:     1.0
	 * @path call:   compensacion/index.phtml
	 * @copyright:   Avansys
	 **/
	public function obtenerCompensacionStatusAction () {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$empresa    = $this->_getParam('id_empresa');
		$ejercicio  = $this->_getParam('id_ejercicio');
		
		//Obtener periodo
		$sql = "SELECT id_periodo 
		          FROM impuesto_periodo_pm 
				 WHERE id_empresa = ".$empresa."
				       AND id_ejercicio = ".$ejercicio."
				       LIMIT 1";
		
		$periodo = My_Comun::crearQuery(null, $sql);
		
		if($monto[0]['periodo'] == 0){
			$periodo = 1;
		}
		else {
			if($monto[0]['periodo'] == 12){
				echo 3;
			}
			else {
				$periodo = $monto[0]['periodo'];
			}
		}
		
		$sql = "SELECT COUNT(monto_aplicar) AS monto
		          FROM compensacion 
		         WHERE empresa_id = ".$empresa."
		               AND ejercicio_id = ".$ejercicio."
		               AND periodo = ".$periodo."";
		
		$impuestos = My_Comun::crearQuery(null, $sql);
		
		echo $impuestos[0]['monto'];
	}
	
	/**
	 * @function     guardarAplicacionesAction
	 * @author:      
	 * @contact:     
	 * @description: 
	 * @version:     1.0
	 * @path call:   subsidio-empleo/index.phtml
	 * @copyright:   Avansys
	 **/
	public function guardarAplicacionesAction () {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$empresa   = $_POST['empresa'];
		$ejercicio = $_POST['ejercicio'];
		$periodo   = $_POST['periodo'];
		$data      = $_POST['data'];
		
		if(isset($data)){
			foreach ($data AS $data_) {
				
				$tipo      = 0;
				$impuesto = '';
				$pos = strpos($data_['id'], '_subsidio');
				if ($pos !== false) {
					$impuesto = substr($data_['id'], 0, -9);
					$tipo = 1;
				}
				else {
					$impuesto = substr($data_['id'], 0, -13);
					$tipo = 2;
				}
				
				$impuesto = My_Comun::obtener('AplicacionesImpuesto', 'shortname', $impuesto);
				
				//Guardar aplicaciones de impuestos
				$aplicacion = array();
				$aplicacion['id']           = '';
				$aplicacion['empresa_id']   = $empresa;
				$aplicacion['ejercicio_id'] = $ejercicio;
				$aplicacion['periodo_id']   = $periodo;
				$aplicacion['monto']        = $data_['value'];
				$aplicacion['impuesto_id']  = $impuesto->id;
				$aplicacion['tipo']         = $tipo;
				
				My_Comun::Guardar("Aplicaciones", $aplicacion, "", "");
				
				echo 1;
			}
		}
	}
	
	/**
	 * @function     getStatusEjercicioAction
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	function getStatusCongeladoAction ($empresa, $ejercicio) {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$empresa   = ($empresa == '')   ? $_POST['empresa']   : $empresa;
		$ejercicio = ($ejercicio == '') ? $_POST['ejercicio'] : $ejercicio;
		
		$query = "
			SELECT status 
			       FROM impuesto_periodo_pm 
			 WHERE id_empresa = ".$empresa." 
			       AND id_ejercicio = ".$ejercicio."
			       AND status = 1";
		$status = My_Comun::crearQuery("ImpuestoPeriodoPm", $query);
		
		echo count($status);
		
	}
	
	/**
	 * @function     getStatusAutorizacionAction
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	function getStatusAutorizacionAction () {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$filtro  = " 1 = 1 ";
		$filtro .= "AND id_empresa      = '".$_POST['empresa']."' ";
		$filtro .= "AND id_ejercicio    = '".$_POST['ejercicio']."' ";
		$filtro .= "AND status_resuelto = 0";
		
		$autorizacion = My_Comun::obtenerFiltro("ImpuestoPeriodoPmSolicitud", $filtro, "id ASC");
		
		echo $autorizacion[0]->status_resuelto;
	}
	
	/**
	 * @function     obtenerpagospmAction
	 * @author:      Christian Murillo
	 * @contact:     christian_murillo@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 * @Last call:   Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 **/
	public function obtenerpagospmAction () {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$empresa   = $_POST['id_empresa'];
		$ejercicio = $_POST['id_ejercicio'];
		$get_data  = $this->get_pagos_provisional_pm_data($empresa, $ejercicio);
		
		//Get empresa
		$ac = My_Comun::obtener("Empresa", "id", $empresa);
		$ac = ($ac->tipo_empresa_id == 2) ? false : true;
		
		/**
		 * Estatus congelado
		 **/
		$query = "
			SELECT id_periodo 
			       FROM impuesto_periodo_pm 
			 WHERE id_empresa = ".$empresa." 
			       AND id_ejercicio = ".$ejercicio."
			       AND status = 1";
		$status = My_Comun::crearQuery("ImpuestoPeriodoPm", $query);
		
		$periodos = array();
		$index    = 1;
		
		foreach ($status AS $periodo) {
			$periodos[$index] = $periodo['id_periodo'];
			$index++;
		}
		
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
		
		if ( $get_data ) {
			
			$registro = $this->changekeyname($get_data);
			
			?>
			
			<div style="overflow: auto; margin-bottom: 20px;">
				<table class='table table-bordered table-striped' style="margin-bottom: 0;">
					<thead>
						<th class="pnl-heading-rojo-1" colspan="14"><strong>ISR</strong></th>
					</thead>
					<thead class="concepts-center">
						<th>Total</th>
						<?php
						$congelado = 0;
						foreach ($meses AS $id => $mes) {
							if ($id == $periodos[$id]) {
								echo "<th style='background: #5bc0de; color: #fff;'>".$mes."</th>";
							}
							else {
								if($congelado == 0){
									echo "<th style='background: #d9534f; color: #fff;' id='impuestos'>".$mes."</th>";
								}
								else {
									echo "<th>".$mes."</th>";
								}
								$congelado++;
							}
						}
						?>
						<th>Total</th>
					</thead>
					
					<tbody>
						<tr>
							<td><span class='operator-pm'></span>Ingresos del Periodo</td>
							
							<?php
							
							$total = 0;
							
							for( $i = 1; $i <= 12; $i++ ) {
								$total += $this->get_iva_total_ingresos($empresa, $ejercicio, $i, $ac);
								echo "<td>".number_format($this->get_iva_total_ingresos($empresa, $ejercicio, $i, $ac), 2)."</td>";
							}
							
					echo "	<td>".number_format($total, 2)."</td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm'>+</span>Anticipo de Clientes</td>";
							
							for( $i = 1; $i <= 12; $i++ ) {
					echo "	<td>".number_format($this->get_iva_anticipo_clientes($empresa, $ejercicio, $i, $ac), 2)."</td>";
							}
					echo "	<td></td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm'>+</span>Otros Ingresos</td>";
							for( $i = 1; $i <= 12; $i++ ) {
					echo "	<td>".number_format($registro[$i]['isr_otros_ingresos'], 2)."</td>";
							}
					echo "	<td></td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm'>+</span>Productos Financieros</td>";
							for( $i = 1; $i <= 12; $i++ ) {
					echo   "<td>".number_format($registro[$i]['isr_producto_financiero'], 2)."</td>";
							}
					echo "	<td></td>
						</tr>";
				echo   "<tr class='average-pm'>
							<td><span class='operator-pm'>=</span>Ingresos Nominales del periodo</td>";
							
							$total = 0;
							
							for( $i = 1; $i <= 12; $i++ ) {
								$total += $this->get_isr_ingresos_nominales_periodo($empresa, $ejercicio, $i);
					echo "	<td>".number_format($this->get_isr_ingresos_nominales_periodo($empresa, $ejercicio, $i), 2)."</td>";
							}
					echo "	<td>".number_format($total, 2)."</td>
						</tr>";
				echo   "<tr class='acumulable-pm'>
							<td><span class='operator-pm'></span>Ingresos Acumulable</td>";
							
							for( $i = 1; $i <= 12; $i++ ) {
								echo "<td>".number_format($this->get_isr_ingresos_acumulable($empresa, $ejercicio, $i), 2)."</td>";
							}
					echo   "<td></td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm pow'>x</span>Coeficiente de Utilidad</td>";
							
							for( $i = 1; $i <= 12; $i++ ) {
					echo   "<td>".$this->get_coeficiente_utilidad($empresa, $ejercicio, $i)."</td>";
							}
							echo "<td></td>
						</tr>";
				echo   "<tr class='average-pm'>
							<td><span class='operator-pm'>=</span>Utilidad Fiscal para el Pago Provisional</td>";
							for( $i = 1; $i <= 12; $i++ ) {
								$usr_utilidad_fiscal = $this->get_isr_ingresos_acumulable($empresa, $ejercicio, $i) * $this->get_coeficiente_utilidad($empresa, $ejercicio, $i);
								echo "<td>".number_format($usr_utilidad_fiscal, 2)."</td>";
							}
					echo   "<td></td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm'>+</span>Ingresos Acumulables Inventarios</td>";
							for( $i = 1; $i <= 12; $i++ ) {
								echo "<td>".number_format($registro[$i]['isr_acumulables_inventario'], 2)."</td>";
							}
					echo   "<td></td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm less'>-</span>Anticipos o Rendimentos Distribuidos</td>";
							for( $i = 1; $i <= 12; $i++ ) {
					echo   "<td>".number_format($registro[$i]['isr_anticipos_distribuidos'], 2)."</td>";
							}
					echo   "<td></td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm less'>-</span>Deduccion Inmediata de Inversiones</td>";
							for( $i = 1; $i <= 12; $i++ ) {
								echo "<td>".number_format($registro[$i]['isr_deduccion_inversiones'], 2)."</td>";
							}
					echo   "<td></td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm less'>-</span>PTU Pagada</td>";
							for( $i = 1; $i <= 12; $i++ ) {
								echo "<td>".number_format($registro[$i]['isr_ptu_pagada'], 2)."</td>";
							}
					echo   "<td></td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm less'>-</span>Perdida de Ejercicios Anteriores</td>";
							for( $i = 1; $i <= 12; $i++ ) {
								echo "<td>".number_format($registro[$i]['isr_perdida_anteriores'], 2)."</td>";
							}
					echo   "<td></td>
						</tr>";
				echo   "<tr class='average-pm'>
							<td><span class='operator-pm'>=</span>Base Para el Pago Provisional</td>";
							for( $i = 1; $i <= 12; $i++ ) {
								echo "<td>".number_format($this->get_isr_base_provisional($empresa, $ejercicio, $i), 2)."</td>";
							}
					echo   "<td></td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm pow'>x</span>Tasa</td>";
							
							$query_coe = "
								SELECT tasa 
								  FROM empresa
								 WHERE id = '".$empresa."'";
							
							$tasa = My_Comun::crearQuery('Empresa', $query_coe);
							$tasa = $tasa[0]['tasa'];
							
							for( $i = 1; $i <= 12; $i++ ) {
								echo "<td>".$tasa."%</td>";
							}
					echo   "<td></td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm'>=</span>Impuestos Por Pagar</td>";
							for( $i = 1; $i <= 12; $i++ ) {
								echo "<td>".number_format($this->get_isr_impuesto_pagar($empresa, $ejercicio, $i), 2)."</td>";
							}
					echo   "<td></td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm less'>-</span>Pagos Provisionales Periodos Anteriores</td>";
							for( $i = 1; $i <= 12; $i++ ) {;
								echo "<td>".number_format($this->get_pagos_provisionales_periodos_anteriores($empresa, $ejercicio, $i), 2)."</td>";
							}
					echo   "<td></td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm less'>-</span>Isr Retenido</td>";
							for( $i = 1; $i <= 12; $i++ ) {
								echo "<td>".number_format($this->get_isr_retenido($empresa, $ejercicio, $i), 2)."</td>";
							}
					echo   "<td></td>
						</tr>";
				echo   "<tr class='average-pm'>
							<td><span class='operator-pm'>=</span>Impuestos Por Pagar</td>";
							
							$total = 0;
							
							for( $i = 1; $i <= 12; $i++ ) {
								$total += $this->get_isr_impuesto_pagar_2($empresa, $ejercicio, $i);
					echo   "<td>".number_format($this->get_isr_impuesto_pagar_2($empresa, $ejercicio, $i), 2)."</td>";
							}
					echo   "<td>".number_format($total, 2)."</td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm'></span>Compensaciones</td>";
							for( $i = 1; $i <= 12; $i++ ) {
								echo "<td>".number_format($registro[$i]['isr_compensacion'], 2)."</td>";
							}
					echo   "<td></td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm'></span>Pago Provisional de Isr</td>";
							for( $i = 1; $i <= 12; $i++ ) {
					echo   "<td>".number_format($this->get_isr_pago_provisional($empresa, $ejercicio, $i), 2)."</td>";
							}
					echo   "<td></td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm'></span>Impuesto a Favor</td>";
							for( $i = 1; $i <= 12; $i++ ) {
					echo   "<td>".number_format($this->get_isr_impuesto_favor($empresa, $ejercicio, $i), 2)."</td>";
							}
					echo   "<td></td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm'></span>Impuesto Acumulado</td>";
							for( $i = 1; $i <= 12; $i++ ) {
					echo   "<td></td>";
							}
					echo   "<td></td>
						</tr>";
			echo "	</tbody>
				</table>
			</div>";
			
			?>
			
			<div style="overflow: auto; margin-bottom: 20px;">
				<table class='table table-bordered table-striped' style="margin-bottom: 20px;">
					<thead>
						<th class="pnl-heading-rojo-1" colspan="14"><strong>IVA</strong></th>
					</thead>
					<thead class="concepts-center">
						<th>Concepto</th>
						<th>Ene</th>
						<th>Feb</th>
						<th>Mar</th>
						<th>Abr</th>
						<th>May</th>
						<th>Jun</th>
						<th>Jul</th>
						<th>Ago</th>
						<th>Sep</th>
						<th>Oct</th>
						<th>Nov</th>
						<th>Dic</th>
						<th>Total</th>
					</thead>
					<tbody>
						<tr>
							<td><span class='operator-pm'></span>Ingresos Gravados 16%</td>
							
							<?php
							
							$total = 0;
							
							for( $i = 1; $i <= 12; $i++ ) {
								$total += $registro[$i]['iva_ingresos_16'];
					echo   "<td>".number_format($registro[$i]['iva_ingresos_16'], 2)."</td>";
							}
					echo   "<td>".number_format($total, 2)."</td>
						</tr>";
				// echo   "<tr>
							// <td>Ingresos Ingresos Gravados 15%</td>";
							
							// $total = 0;
							
							// for( $i = 1; $i <= 12; $i++ ) {
								// $total += $registro[$i]['iva_ingresos_15'];
					// echo   "<td>".number_format($registro[$i]['iva_ingresos_15'], 2)."</td>";
							// }
					// echo   "<td>".number_format($total, 2)."</td>
						// </tr>";
				echo   "<tr>
							<td><span class='operator-pm'>+</span>Ingresos Gravados 11%</td>";
							
							$total = 0;
							
							for( $i = 1; $i <= 12; $i++ ) {
								$total += $registro[$i]['iva_ingresos_11'];
					echo   "<td>".number_format($registro[$i]['iva_ingresos_11'], 2)."</td>";
							}
					echo   "<td>".number_format($total, 2)."</td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm'>+</span>Ingresos Gravados 0%</td>";
							
							$total = 0;
							
							for( $i = 1; $i <= 12; $i++ ) {
								$total += $registro[$i]['iva_ingresos_0'];
					echo   "<td>".number_format($registro[$i]['iva_ingresos_0'], 2)."</td>";
							}
					echo   "<td>".number_format($total, 2)."</td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm'>+</span>Ingresos Exentos</td>";
							
							$total = 0;
							
							for( $i = 1; $i <= 12; $i++ ) {
								$total += $registro[$i]['iva_ingresos_exentos'];
					echo   "<td>".number_format($registro[$i]['iva_ingresos_exentos'], 2)."</td>";
							}
					echo   "<td>".number_format($total, 2)."</td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm'>+</span>Otras Bases</td>";
							
							$total = 0;
							
							for( $i = 1; $i <= 12; $i++ ) {
								$total += $registro[$i]['iva_ingresos_otra_base'];
					echo   "<td>".number_format($registro[$i]['iva_ingresos_otra_base'], 2)."</td>";
							}
					echo   "<td>".number_format($total, 2)."</td>
						</tr>";
				echo   "<tr class='average-pm'>
							<td><span class='operator-pm'>=</span>Total Ingresos</td>";
							
							$total = 0;
							
							for( $i = 1; $i <= 12; $i++ ) {
								$total += $this->get_iva_total_ingresos($empresa, $ejercicio, $i);
					echo    "<td>".number_format($this->get_iva_total_ingresos($empresa, $ejercicio, $i), 2)."</td>";
							}
					echo    "<td>".number_format($total, 2)."</td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm'>+</span>Iva Trasladado al 16%</td>";
							
							$total = 0;
							
							for( $i = 1; $i <= 12; $i++ ) {
								$total += ($registro[$i]['iva_ingresos_16'] * 0.16);
								echo "<td>".number_format(($registro[$i]['iva_ingresos_16'] * 0.16), 2)."</td>";
							}
					echo   "<td>".number_format($total, 2)."</td>
						</tr>";
				// echo   "<tr>
							// <td>Iva Trasladado al 15%</td>";
							
							// $total = 0;
							
							// for( $i = 1; $i <= 12; $i++ ) {
								// $total += $registro[$i]['iva_trasladado_15'];
					// echo   "<td>".number_format($registro[$i]['iva_trasladado_15'], 2)."</td>";
							// }
					// echo   "<td>".number_format($total, 2)."</td>
						// </tr>";
				// echo   "<tr>
							// <td><span class='operator-pm'>+</span>Iva Trasladado al 11%</td>";
							
							// $total = 0;
							
							// for( $i = 1; $i <= 12; $i++ ) {
								// $total += $registro[$i]['iva_trasladado_11'];
					// echo   "<td>".number_format($registro[$i]['iva_trasladado_11'], 2)."</td>";
							// }
					// echo   "<td>".number_format($total, 2)."</td>
						// </tr>";
				echo   "<tr class='average-pm'>
							<td><span class='operator-pm'>=</span>Total de Iva Trasladado</td>";
							
							$total = 0;
							
							for( $i = 1; $i <= 12; $i++ ) {
								$total += ($registro[$i]['iva_ingresos_16'] * 0.16);
								echo "<td>".number_format(($registro[$i]['iva_ingresos_16'] * 0.16), 2)."</td>";
							}
					echo   "<td>".number_format($total, 2)."</td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm'>+</span>Base Gravable al 16%</td>";
							
							$total = 0;
							
							for( $i = 1; $i <= 12; $i++ ) {
								$total += $registro[$i]['iva_gravable_16'];
					echo   "<td>".number_format($registro[$i]['iva_gravable_16'], 2)."</td>";
							}
					echo   "<td>".number_format($total, 2)."</td>
						</tr>";
				// echo   "<tr>
							// <td>Base Gravable al 15%</td>";
							
							// $total = 0;
							
							// for( $i = 1; $i <= 12; $i++ ) {
								// $total += $registro[$i]['iva_gravable_15'];
					// echo   "<td>".number_format($registro[$i]['iva_gravable_15'], 2)."</td>";
							// }
					// echo   "<td>".number_format($total, 2)."</td>
						// </tr>";
				echo   "<tr>
							<td><span class='operator-pm'>+</span>Base Gravable al 11%</td>";
							
							$total = 0;
							
							for( $i = 1; $i <= 12; $i++ ) {
								$total += $registro[$i]['iva_gravable_11'];
					echo   "<td>".number_format($registro[$i]['iva_gravable_11'], 2)."</td>";
							}
					echo   "<td>".number_format($total, 2)."</td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm'>+</span>Base Gravable al 0%</td>";
							
							$total = 0;
							
							for( $i = 1; $i <= 12; $i++ ) {
								$total += $registro[$i]['iva_gravable_0'];
					echo   "<td>".number_format($registro[$i]['iva_gravable_0'], 2)."</td>";
							}
					echo   "<td>".number_format($total, 2)."</td>
						</tr>"; 
				echo   "<tr>
							<td><span class='operator-pm'>+</span>Base Gravable Exenta</td>";
							
							$total = 0;
							
							for( $i = 1; $i <= 12; $i++ ) {
								$total += $registro[$i]['iva_gravable_exento'];
					echo   "<td>".number_format($registro[$i]['iva_gravable_exento'], 2)."</td>";
							}
					echo   "<td>".number_format($total, 2)."</td>
						<tr>";
				echo   "<tr class='average-pm'>
							<td><span class='operator-pm'>=</span>Total Base Acreditable</td>";
							
							$total = 0;
							
							for( $i = 1; $i <= 12; $i++ ) {
								$total += $this->get_total_base_acreditable($empresa, $ejercicio, $i);
					echo   "<td>".number_format($this->get_total_base_acreditable($empresa, $ejercicio, $i), 2)."</td>";
							}
					echo   "<td>".number_format($total, 2)."</td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm'>+</span>IVA Acreditable al 16%</td>";
							
							$total = 0;
							
							for( $i = 1; $i <= 12; $i++ ) {
								$total += ($registro[$i]['iva_gravable_16'] * 0.16);
					echo   "<td>".number_format(($registro[$i]['iva_gravable_16'] * 0.16), 2)."</td>";
							}
					echo   "<td>".number_format($total,2)."</td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm'>+</span>IVA Acreditable al 11%</td>";
							
							$total = 0;
							
							for( $i = 1; $i <= 12; $i++ ) {
								$total += ($registro[$i]['iva_gravable_11'] * 0.11);
					echo   "<td>".number_format(($registro[$i]['iva_gravable_11'] * 0.11), 2)."</td>";
							}
					echo   "<td>".number_format($total, 2)."</td>
						</tr>";
				echo   "<tr class='average-pm'>
							<td><span class='operator-pm'>=</span>Total IVA Acreditable</td>";
							
							$total = 0;
							
							for( $i = 1; $i <= 12; $i++ ) {
								$total += (($registro[$i]['iva_gravable_16'] * 0.16) + ($registro[$i]['iva_gravable_11'] * 0.11));
					echo   "<td>".number_format((($registro[$i]['iva_gravable_16'] * 0.16) + ($registro[$i]['iva_gravable_11'] * 0.11)), 2)."</td>";
							}
					echo   "<td>".number_format($total, 2)."</td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm'></span>Coeficiente de Acreditamiento</td>";
							
							for( $i = 1; $i <= 12; $i++ ) {
					echo   "<td>".number_format($this->get_coeficiente_acreditamiento($empresa, $ejercicio, $i), 4)."</td>";
							}
					echo   "<td></td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm'></span>IVA Acreditable</td>";
							
							for( $i = 1; $i <= 12; $i++ ) {
								echo "<td>".number_format($this->get_iva_acreditable($empresa, $ejercicio, $i), 2)."</td>";
							}
					echo   "<td></td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm'></span>IVA Retenido del Mes</td>";
							
							for( $i = 1; $i <= 12; $i++ ) {
					echo   "<td>".number_format($registro[$i]['iva_retenido_mes'], 2)."</td>";
							}
					echo   "<td></td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm'></span>IVA Retenido del Mes Anterior</td>";
							
							for( $i = 1; $i <= 12; $i++ ) {
					echo   "<td>".number_format($registro[$i]['iva_retenido_mes_anterior'], 2)."</td>";
							}
					echo   "<td></td>
						</tr>";
				echo   "<tr class='average-pm'>
							<td><span class='operator-pm'></span>IVA Acreditable</td>";
							
							$total = 0;
							
							for( $i = 1; $i <= 12; $i++ ) {
								$total += $this->get_iva_acreditable_2($empresa, $ejercicio, $i);
					echo   "<td>".number_format($this->get_iva_acreditable_2($empresa, $ejercicio, $i), 2)."</td>";
							}
					echo   "<td>".number_format($total, 2)."</td>
						</tr>";
				echo   "<tr class='title-pm'>
							<td colspan='14'>
								Determinación IVA
							</td>";
				echo   "</tr>";
				echo   "<tr class='blue-pm'>
							<td><span class='operator-pm'></span>IVA a Cargo del Periodo</td>";
							
							for( $i = 1; $i <= 12; $i++ ) {
					echo   "<td>".number_format($this->get_iva_cargo_periodo($empresa, $ejercicio, $i), 2)."</td>";
							}
					echo   "<td></td>
						</tr>";
				echo   "<tr class='blue-pm'>
							<td><span class='operator-pm'></span>IVA a Favor del Periodo</td>";
							
							for( $i = 1; $i <= 12; $i++ ) {
					echo   "<td>".number_format($this->get_iva_favor_periodo($empresa, $ejercicio, $i), 2)."</td>";
							}
					echo   "<td></td>
						</tr>";
				echo   "<tr class='blue-pm'>
							<td><span class='operator-pm'></span>Compensaciones</td>";
							
							$total = 0;
							
							for( $i = 1; $i <= 12; $i++ ) {
								$total += $registro[$i]['iva_compensaciones'];
					echo   "<td>".number_format($registro[$i]['iva_compensaciones'], 2)."</td>";
							}
					echo   "<td>".number_format($total, 2)."</td>
						</tr>";
				echo   "<tr class='blue-pm'>
							<td><span class='operator-pm'></span>IVA a Cargo del Periodo</td>";
							
							$total = 0;
							
							for( $i = 1; $i <= 12; $i++ ) {
								$total += $this->get_iva_cargo_periodo_2($empresa, $ejercicio, $i);
					echo   "<td>".number_format($this->get_iva_cargo_periodo_2($empresa, $ejercicio, $i), 2)."</td>";
							}
					echo   "<td>".number_format($total, 2)."</td>
						</tr>";
				echo   "<tr class='blue-pm'>
							<td><span class='operator-pm'></span>IVA a Favor del Acumulado</td>";
							
							$total = 0;
							
							for( $i = 1; $i <= 12; $i++ ) {
								$total += $this->get_iva_favor_periodo_2($empresa, $ejercicio, $i);
					echo   "<td>".number_format($this->get_iva_favor_periodo_2($empresa, $ejercicio, $i), 2)."</td>";
							}
					echo   "<td>".number_format($total, 2)."</td>
						</tr>";
			echo "	</tbody>
				</table>
			</div>";
			
			?>
			
			<div style="overflow: auto; margin-bottom: 20px;">
				<table class='table table-bordered table-striped' style="margin-bottom: 0px;">
					<thead>
						<th class="pnl-heading-rojo-1" colspan="14"><strong>IMPUESTOS POR PAGAR</strong></th>
					</thead>
					<thead class="concepts-center">
						<th>Concepto</th>
						<th>Ene</th>
						<th>Feb</th>
						<th>Mar</th>
						<th>Abr</th>
						<th>May</th>
						<th>Jun</th>
						<th>Jul</th>
						<th>Ago</th>
						<th>Sep</th>
						<th>Oct</th>
						<th>Nov</th>
						<th>Dic</th>
						<th>Total</th>
					</thead>
					<tbody>
						<tr>
							<td><span class='operator-pm'></span>ISR</td>
							
							<?php
							
							$total = 0;
							
							for( $i = 1; $i <= 12; $i++ ) {
								$total += $this->get_isr_pago_provisional($empresa, $ejercicio, $i);
					echo   "<td id='ISR'>".number_format($this->get_isr_pago_provisional($empresa, $ejercicio, $i), 2)."</td>";
							}
					echo   "<td>".number_format($total, 2)."</td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm'>+</span>IVA</td>";
							
							$total = 0;
							
							for( $i = 1; $i <= 12; $i++ ) {
								$total += $this->get_iva_cargo_periodo_2($empresa, $ejercicio, $i);
					echo   "<td id='IVA'>".number_format($this->get_iva_cargo_periodo_2($empresa, $ejercicio, $i), 2)."</td>";
							}
					echo   "<td>".number_format($total, 2)."</td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm'></span>IEPS</td>";
							
							for( $i = 1; $i <= 12; $i++ ) {
					echo   "<td></td>";
							}
					echo   "<td></td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm'>+</span>Retenci&oacute;n Salarios</td>";
							
							$total = 0;
							
							for( $i = 1; $i <= 12; $i++ ) {
								$total += $registro[$i]['retencion_salarios'];
								echo "<td id='retencion_salarios'>".number_format($registro[$i]['retencion_salarios'], 2)."</td>";
							}
					echo   "<td>".number_format($total, 2)."</td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm'>+</span>Retenci&oacute;n ISR honorarios</td>";
							
							$total = 0;
							
							for( $i = 1; $i <= 12; $i++ ) {
								$total += $registro[$i]['retencion_isr_honorarios'];
					echo   "<td id='retencion_isr_honorarios'>".number_format($registro[$i]['retencion_isr_honorarios'], 2)."</td>";
							}
					echo   "<td>".number_format($total, 2)."</td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm'>+</span>Retenci&oacute;n Asimilados</td>";
							
							$total = 0;
							
							for( $i = 1; $i <= 12; $i++ ) {
								$total += $registro[$i]['retencion_asimilados'];
					echo   "<td id='retencion_asimilados'>".number_format($registro[$i]['retencion_asimilados'], 2)."</td>";
							}
					echo   "<td>".number_format($total, 2)."</td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm'>+</span>Retenci&oacute;n Dividendos</td>";
							
							for( $i = 1; $i <= 12; $i++ ) {
					echo   "<td id='retencion_dividendos'>".number_format($registro[$i]['retencion_dividendos'], 2)."</td>";
							}
					echo   "<td></td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm'>+</span>Retenci&oacute;n Intereses</td>";
							for( $i = 1; $i <= 12; $i++ ) {
					echo   "<td id='retencion_intereses'>".number_format($registro[$i]['retencion_intereses'], 2)."</td>";
							}
					echo   "<td></td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm'>+</span>Retenci&oacute;n Pagos al Extranjero</td>";
							
							$total = 0;
							
							for( $i = 1; $i <= 12; $i++ ) {
								$total += $registro[$i]['retencion_pagos_extranjero'];
					echo   "<td id='retencion_pagos_extranjero'>".number_format($registro[$i]['retencion_pagos_extranjero'], 2)."</td>";
							}
					echo   "<td>".number_format($total, 2)."</td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm'>+</span>Retenci&oacute;n Venta de Acciones</td>";
							
							for( $i = 1; $i <= 12; $i++ ) {
					echo   "<td id='retencion_venta_acciones'>".number_format($registro[$i]['retencion_venta_acciones'], 2)."</td>";
							}
					echo   "<td></td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm'>+</span>Retenci&oacute;n Venta de Partes Sociales</td>";
							
							for( $i = 1; $i <= 12; $i++ ) {
					echo   "<td id='retencion_venta_partes_sociales'>".number_format($registro[$i]['retencion_venta_partes_sociales'], 2)."</td>";
							}
					echo   "<td></td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm'>+</span>Retenci&oacute;n ISR Arrendamiento</td>";
							
							$total = 0;
							
							for( $i = 1; $i <= 12; $i++ ) {
								$total += $registro[$i]['retencion_isr_arrendamiento'];
					echo   "<td id='retencion_isr_arrendamiento'>".number_format($registro[$i]['retencion_isr_arrendamiento'], 2)."</td>";
							}
					echo   "<td>".number_format($total, 2)."</td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm'>+</span>Retenci&oacute;n IVA</td>";
							
							$total = 0;
							
							for( $i = 1; $i <= 12; $i++ ) {
								$total += $registro[$i]['iva_retenido_mes'];
					echo   "<td>".number_format($registro[$i]['iva_retenido_mes'], 2)."</td>";
							}
					echo   "<td>".number_format($total, 2)."</td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm'>=</span>Total Impuestos</td>";
							
							$total=0;
							
							for( $i = 1; $i <= 12; $i++ ) {
								$total += $this->get_total_impuestos($empresa, $ejercicio, $i);
					echo   "<td>".number_format($this->get_total_impuestos($empresa, $ejercicio, $i), 2)."</td>";
							}
					echo   "<td>".number_format($total, 2)."</td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm less'>-</span>Subsidio al Empleo</td>";
							
							$total = 0;
							
							for( $i = 1; $i <= 12; $i++ ) {
								$total += $registro[$i]['subsidio_empleo'];
					echo   "<td>".number_format($registro[$i]['subsidio_empleo'], 2)."</td>";
							}
					echo   "<td>".number_format($total, 2)."</td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm'></span>Compensaciones</td>";
							
							$total=0;
							
							for( $i = 1; $i <= 12; $i++ ) {
								$total += $registro[$i]['compensaciones_otros'];
					echo   "<td>".number_format($registro[$i]['compensaciones_otros'], 2)."</td>";
							}
					echo   "<td>".number_format($total, 2)."</td>
						</tr>";
				echo   "<tr>
							<td><span class='operator-pm'>=</span>Impuestos Por Pagar</td>";
							
							$total = 0;
							
							for( $i = 1; $i <= 12; $i++ ) {
								$total += $this->get_impuestos_pagar($empresa, $ejercicio, $i);
					echo   "<td>".number_format($this->get_impuestos_pagar($empresa, $ejercicio, $i), 2)."</td>";
							}
					echo   "<td>".number_format($total, 2)."</td>
						</tr>";
			echo	"</tbody>
				</table>
			</div>";
		}//end if $get_data
		else {
			echo "	<div id='progressbar' name='progressbar' class='panel-body alert alert-danger' style='text-align: center;' role='alert'>
						No hay datos existentes
					</div>";
		}
	}
	
	/**
	 * @function     obtenerpagospmAction
	 * @author:      Christian Murillo
	 * @contact:     christian_murillo@avansys.com.mx
	 * @description: Funcion para Cambiar las llaves principales y ligarlas con el periodo especifico
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 * @last move:   Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 **/
	public function changekeyname ( $array ) {
		
		$array2 = array();
		
		for( $i = 1; $i <= 12; $i++) {
			foreach ($array AS $id => $array_) {
				if ( $i == $array_['id_periodo'])
					$array2[$i] = $array[$id];
			}
		}
		
		return $array2;
	}
	
	/**
	 * @function     obtenerpagospmAction
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function get_pagos_provisional_pm_data ( $empresa, $ejercicio ) {
		
		$con = Doctrine_Manager::getInstance()->connection();
		
		$query = "
			SELECT DISTINCT ip.*
			           FROM impuesto_periodo_pm ip
			     INNER JOIN ejercicio e 
			                ON ip.id_ejercicio = e.id
			          WHERE ip.id_empresa = '".$empresa."'
			                AND e.id = '".$ejercicio."'
			       ORDER BY ip.id_periodo ASC";
		
		$average = $con->execute($query)->fetchAll();
		$average = (count($average)) ? $average : false;
		
		return $average;
	}
	
	/**
	 * @function     get_iva_total_ingresos
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function get_iva_total_ingresos ( $empresa, $ejercicio, $periodo, $ac = false) {
		
		$con = Doctrine_Manager::getInstance()->connection();
		
		if ($ac) {
			$query = "
			SELECT DISTINCT isr_ingreso_periodo AS average
			           FROM impuesto_periodo_pm
			          WHERE id_empresa = '".$empresa."'
			                AND id_ejercicio = '".$ejercicio."'
			                AND id_periodo = '".$periodo."'";
		}
		else {
			$query = "
			SELECT DISTINCT (SUM(iva_ingresos_16)     + 
			                SUM(iva_ingresos_11)      +
			                SUM(iva_ingresos_0)       + 
			                SUM(iva_ingresos_exentos) + 
			                SUM(iva_ingresos_otra_base)
			                ) AS average
			           FROM impuesto_periodo_pm
			          WHERE id_empresa = '".$empresa."'
			                AND id_ejercicio = '".$ejercicio."'
			                AND id_periodo = '".$periodo."'";
		}
		
		$average = $con->execute($query)->fetchAll();
		$average = ($average[0]['average']) ? $average[0]['average'] : 0;
		
		return $average;
	}
	
	/**
	 * @function     get_iva_anticipo_clientes
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function get_iva_anticipo_clientes ( $empresa, $ejercicio, $periodo, $ac = false) {
		
		$con = Doctrine_Manager::getInstance()->connection();
		
		if ($ac) {
			
			$query = "
			SELECT DISTINCT isr_anticipo_cliente AS average
			           FROM impuesto_periodo_pm
			          WHERE id_empresa = '".$empresa."'
			                AND id_ejercicio = '".$ejercicio."'
			                AND id_periodo = '".$periodo."'";
		}
		else {
			return 0;
		}
		
		$average = $con->execute($query)->fetchAll();
		$average = ($average[0]['average']) ? $average[0]['average'] : 0;
		
		return $average;
	}
	
	/**
	 * @function     get_isr_ingresos_nominales_periodo
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function	get_isr_ingresos_nominales_periodo ( $empresa, $ejercicio, $periodo ) {
		
		$con = Doctrine_Manager::getInstance()->connection();
		
		$ac = My_Comun::obtener("Empresa", "id", $empresa);
		$ac = ($ac->tipo_empresa_id == 2) ? false : true;
		
		$total_ingresos   = $this->get_iva_total_ingresos($empresa, $ejercicio, $periodo, $ac);
		$anticipo_cliente = $this->get_iva_anticipo_clientes($empresa, $ejercicio, $periodo, $ac);
		
		$query = "
			SELECT DISTINCT (SUM(".$anticipo_cliente.")  + 
			                SUM(isr_otros_ingresos)      + 
			                SUM(isr_producto_financiero) + 
			                SUM(".$total_ingresos.")
			                ) AS average
			           FROM impuesto_periodo_pm
			          WHERE id_empresa = '".$empresa."'
			                AND id_ejercicio = '".$ejercicio."'
			                AND id_periodo = '".$periodo."'";
		
		$average = $con->execute($query)->fetchAll();
		$average = ($average[0]['average']) ? $average[0]['average'] : 0;
		
		return $average;
	}
	
	/**
	 * @function     get_isr_ingresos_acumulable
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function	get_isr_ingresos_acumulable ( $empresa, $ejercicio, $periodo ) {
		
		$con = Doctrine_Manager::getInstance()->connection();
		
		$get_isr_ingresos_nominales_periodo = $this->get_isr_ingresos_nominales_periodo($empresa, $ejercicio, $periodo);
		
		if ($periodo == 1) 
			return $get_isr_ingresos_nominales_periodo;
		else {
			$acumulador = 0;
			
			for( $i = 1; $i <= $periodo; $i++ ) {
				$acumulador += $this->get_isr_ingresos_nominales_periodo($empresa, $ejercicio, $i);
			}
			
			return $acumulador;
		}
	}
	
	/**
	 * @function     get_coeficiente_utilidad
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function	get_coeficiente_utilidad ( $empresa, $ejercicio, $periodo ) {
		
		$con         = Doctrine_Manager::getInstance()->connection();
		$coeficiente = 0;
		
		$query = "
			SELECT *
			  FROM coeficiente_utilidad
			 WHERE id_empresa = '".$empresa."'
			       AND id_ejercicio = '".$ejercicio."'";
		
		$query = $con->execute($query)->fetchAll();
		
		foreach ($query AS $coef) {
			if( ($coef['id_periodo_inicio'] <= $periodo) && ($periodo <= $coef['id_periodo_fin']) ) {
				$coeficiente = $coef['coeficiente_utilidad'];
			}
		}
		
		return $coeficiente;
	}
	
	/**
	 * @function     get_isr_base_provisional
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function	get_isr_base_provisional ( $empresa, $ejercicio, $periodo ) {
		
		$con = Doctrine_Manager::getInstance()->connection();
		
		$coeficiente         = $this->get_coeficiente_utilidad($empresa, $ejercicio, $periodo);
		$usr_utilidad_fiscal = $this->get_isr_ingresos_acumulable($empresa, $ejercicio, $periodo) * $coeficiente;
		
		$query = "
			SELECT DISTINCT (SUM(".$usr_utilidad_fiscal.")  + 
			                SUM(isr_acumulables_inventario) - 
			                SUM(isr_anticipos_distribuidos) - 
			                SUM(isr_deduccion_inversiones)  - 
			                SUM(isr_ptu_pagada)             - 
			                SUM(isr_perdida_anteriores)
			                ) AS average
			           FROM impuesto_periodo_pm
			          WHERE id_empresa = '".$empresa."'
			                AND id_ejercicio = '".$ejercicio."'
			                AND id_periodo = '".$periodo."'";
		
		$average = $con->execute($query)->fetchAll();
		$average = ($average[0]['average']) ? $average[0]['average'] : 0;
		
		return $average;
	}
	
	/**
	 * @function     get_isr_impuesto_pagar
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function	get_isr_impuesto_pagar ( $empresa, $ejercicio, $periodo ) {
		
		$isr_base_provisional = $this->get_isr_base_provisional($empresa, $ejercicio, $periodo);
		
		$query_coe = "
			SELECT tasa 
			  FROM empresa
			 WHERE id = '".$empresa."'";
		
		$tasa = My_Comun::crearQuery('Empresa', $query_coe);
		$tasa = $tasa[0]['tasa'] * 0.01;
		
		if ($isr_base_provisional < 0) 
			return 0;
		else 
			return $isr_base_provisional * $tasa;
	}
	
	/**
	 * @function     get_isr_retenido
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function	get_isr_retenido ( $empresa, $ejercicio, $periodo ) {
		
		$con      = Doctrine_Manager::getInstance()->connection();
		$conexion = My_Comun::obtener("Empresa", "id", $empresa);
		
		//Conexion
		$cnx = Conexion::abreConexion($conexion->usuario_bd_contpaq, $conexion->pass_bd_contpaq, $conexion->nombre_bd_contpaq, $conexion->server_bd_contpaq);
		
		$query = "
			SELECT isr_retenido 
			  FROM empresa
			 WHERE id = '".$empresa."'";
		
		$isr_retenido = My_Comun::crearQuery('Empresa', $query);
		$isr_retenido = $isr_retenido[0]['isr_retenido'];
		
		if ( $isr_retenido > 0 ) {
			
			//Get nombre Ejercicio
			$ejercicio_obj = My_Comun::obtener("Ejercicio", "id", $ejercicio);
			
			$query = "
				SELECT SUM(sc.Importes".$periodo.") AS isr_retenido
				  FROM SaldosCuentas sc
			INNER JOIN Ejercicios e
					   ON e.id = sc.Ejercicio
				 WHERE sc.IdCuenta = '".$isr_retenido."'
				  AND e.Ejercicio = '".$ejercicio_obj->nombre."'
				  AND sc.Tipo = '1'";
			
			$query = sqlsrv_query($cnx, $query) OR print_r(sqlsrv_errors());
			$query = sqlsrv_fetch_array($query);
			
			$isr_retenido = $query['isr_retenido'];
		}
		
		return $isr_retenido;
	}
	
	/**
	 * @function     get_pagos_provisionales_periodos_anteriores
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function	get_pagos_provisionales_periodos_anteriores ($empresa, $ejercicio, $periodo) {
		
		$con = Doctrine_Manager::getInstance()->connection();
		
		if ($periodo == 1) 
			return 0;
		else {
			
			// $periodo1 = $periodo - 1;
			$periodo1 = $periodo;
			$isr_retenido = $this->get_isr_retenido($empresa, $ejercicio, $periodo1);
			
			// if ($periodo == 2)
				// return $isr_retenido + $isr_impuesto_pagar;
			// else {
				// $periodo2             = $periodo - 2;
				$periodo2             = $periodo - 1;
				$isr_retenido_2       = $this->get_isr_retenido($empresa, $ejercicio, $periodo2);
				$isr_impuesto_pagar_2 = $this->get_isr_impuesto_pagar ($empresa, $ejercicio, $periodo - 1);
				$periodos_anteriores  = $isr_impuesto_pagar_2 + $isr_retenido_2;
			// }
			
			return $periodos_anteriores + $isr_retenido + $isr_impuesto_pagar;
		}
	}
	
	/**
	 * @function     get_isr_impuesto_pagar_2
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function	get_isr_impuesto_pagar_2 ($empresa, $ejercicio, $periodo) {
		
		$con = Doctrine_Manager::getInstance()->connection();
		
		$impuesto_por_pagar  = $this->get_isr_impuesto_pagar($empresa, $ejercicio, $periodo);
		$isr_retenido        = $this->get_isr_retenido($empresa, $ejercicio, $periodo);
		$pagos_provisionales = $this->get_pagos_provisionales_periodos_anteriores($empresa, $ejercicio, $periodo);
		
		if ($impuesto_por_pagar > ($pagos_provisionales + $isr_retenido)) 
			return $impuesto_por_pagar - $pagos_provisionales - $isr_retenido;
		else 
			return 0;
	}
	
	/**
	 * @function     set_isr_compensaciones
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function	set_isr_compensaciones ($empresa, $ejercicio, $periodo) {
		
		for ($i = $periodo; $i <= 12; $i++) {
			
			$query = 
				"SELECT SUM(monto_aplicar) AS monto
				   FROM compensacion
				  WHERE empresa_id        = ".$empresa."
						AND ejercicio_id  = ".$ejercicio."
						AND periodo       = ".$i."
						AND tipo_impuesto = 'ISR'";
			
			$monto   = My_Comun::crearQuery('Aplicaciones', $query);
			$monto   = ($monto[0]['monto']) ? $monto[0]['monto'] : 0;
			
			$filtro  = " 1=1 ";
			$filtro .= "AND id_empresa = '".$empresa."' ";
			$filtro .= "AND id_ejercicio = '".$ejercicio."' ";
			$filtro .= "AND id_periodo = '".$i."' ";
			
			//crearEjercisios
			$ImpuestoPeriodoPm = My_Comun::obtenerFiltro("ImpuestoPeriodoPm", $filtro, "id_periodo ASC");
			
			// Actualizar campo de la base de datos.
			$data                     = array();
			$data['id']               = $ImpuestoPeriodoPm[0]->id;
			$data['id_empresa']       = $empresa;
			$data['id_ejercicio']     = $ejercicio;
			$data['id_periodo']       = $i;
			$data['isr_compensacion'] = $monto;
			
			if ($ImpuestoPeriodoPm[0]->id) {
				My_Comun::Guardar("ImpuestoPeriodoPm", $data, $data['id'], "");
			}
		}
	}
	
	/**
	 * @function     set_iva_compensaciones
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function	set_iva_compensaciones ($empresa, $ejercicio, $periodo) {
		
		for ($i = $periodo; $i <= 12; $i++) {
			
			$query = 
				"SELECT SUM(monto_aplicar) AS monto
				   FROM compensacion
				  WHERE empresa_id        = ".$empresa."
						AND ejercicio_id  = ".$ejercicio."
						AND periodo       = ".$i."
						AND tipo_impuesto = 'IVA'";
			
			$monto   = My_Comun::crearQuery('Aplicaciones', $query);
			$monto   = ($monto[0]['monto']) ? $monto[0]['monto'] : 0;
			
			$filtro  = " 1=1 ";
			$filtro .= "AND id_empresa = '".$empresa."' ";
			$filtro .= "AND id_ejercicio = '".$ejercicio."' ";
			$filtro .= "AND id_periodo = '".$i."' ";
			
			//crearEjercisios
			$ImpuestoPeriodoPm = My_Comun::obtenerFiltro("ImpuestoPeriodoPm", $filtro, "id_periodo ASC");
			
			// Actualizar campo de la base de datos.
			$data                     = array();
			$data['id']               = $ImpuestoPeriodoPm[0]->id;
			$data['id_empresa']       = $empresa;
			$data['id_ejercicio']     = $ejercicio;
			$data['id_periodo']       = $i;
			$data['iva_compensaciones'] = $monto;
			
			if ($ImpuestoPeriodoPm[0]->id) {
				My_Comun::Guardar("ImpuestoPeriodoPm", $data, $data['id'], "");
			}
		}
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
	 * @function     get_isr_pago_provisional
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function	get_isr_pago_provisional ($empresa, $ejercicio, $periodo) {
		
		$con = Doctrine_Manager::getInstance()->connection();
		
		$get_isr_impuesto_pagar_2 = $this->get_isr_impuesto_pagar_2($empresa, $ejercicio, $periodo);
		
		$query = "
				SELECT isr_compensacion 
				  FROM impuesto_periodo_pm 
				 WHERE id_empresa = '".$empresa."' 
					   AND id_ejercicio = '".$ejercicio."'
					   AND id_periodo = '".$periodo."'";
		
		$isr_compensacion = $con->execute($query)->fetchAll();
		$isr_compensacion = ($isr_compensacion[0]['isr_compensacion']) ? $isr_compensacion[0]['isr_compensacion'] : 0;
		
		if ($isr_compensacion > $get_isr_impuesto_pagar_2) 
			return 0;
		else 
			return $get_isr_impuesto_pagar_2 - $isr_compensacion;
	}
	
	/**
	 * @function     get_isr_pago_provisional
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function	get_isr_impuesto_favor ($empresa, $ejercicio, $periodo) {
		
		$con = Doctrine_Manager::getInstance()->connection();
		
		$isr_impuesto_pagar = $this->get_isr_impuesto_pagar ($empresa, $ejercicio, $periodo);
		$pagos_anteriores   = $this->get_pagos_provisionales_periodos_anteriores($empresa, $ejercicio, $periodo);
		
		$isr_retenido = $this->get_isr_retenido($empresa, $ejercicio, $periodo);
		
		if (($pagos_anteriores + $isr_retenido) > $isr_impuesto_pagar)
			return (($isr_retenido + $pagos_anteriores) - $isr_impuesto_pagar);
		else 
			return 0;
	}
	
	/**
	 * @function     get_total_base_acreditable
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function	get_total_base_acreditable ($empresa, $ejercicio, $periodo) {
		
		$con = Doctrine_Manager::getInstance()->connection();
		
		$query = "
			SELECT DISTINCT (SUM(iva_gravable_16) + 
			                SUM(iva_gravable_11)  + 
			                SUM(iva_gravable_0)   + 
			                SUM(iva_gravable_exento)
			                ) AS average
			           FROM impuesto_periodo_pm
			          WHERE id_empresa = '".$empresa."'
			                AND id_ejercicio = '".$ejercicio."'
			                AND id_periodo = '".$periodo."'";
		
		$average = $con->execute($query)->fetchAll();
		$average = ($average[0]['average']) ? $average[0]['average'] : 0;
		
		return $average;
	}
	
	/**
	 * @function     get_coeficiente_acreditamiento
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function	get_coeficiente_acreditamiento ($empresa, $ejercicio, $periodo) {
		
		$con         = Doctrine_Manager::getInstance()->connection();
		$coeficiente = 0;
		
		$query = "
			SELECT *
			  FROM coeficiente_acreditamiento
			 WHERE id_empresa = '".$empresa."'
			       AND id_ejercicio = '".$ejercicio."'";
		
		$query = $con->execute($query)->fetchAll();
		
		foreach ($query AS $coef) {
			if( ($coef['id_periodo_inicio'] <= $periodo) && ($periodo <= $coef['id_periodo_fin']) ) {
				$coeficiente = $coef['coeficiente_acreditamiento'];
			}
			else {
				// $ac = My_Comun::obtener("Empresa", "id", $empresa);
				// $ac = ($ac->tipo_empresa_id == 2) ? false : true;
				
				$total_ingresos = $this->get_iva_total_ingresos($empresa, $ejercicio, $periodo);
				
				$query = "
					SELECT DISTINCT iva_ingresos_16 AS average
							   FROM impuesto_periodo_pm
							  WHERE id_empresa = '".$empresa."'
									AND id_ejercicio = '".$ejercicio."'
									AND id_periodo = '".$periodo."'";
				
				$average = $con->execute($query)->fetchAll();
				$average = ($average[0]['average']) ? $average[0]['average'] : 0;
				
				$coeficiente = ($average / $total_ingresos) ? $coeficiente : 0;
			}
		}
		
		return $coeficiente;
	}
	
	/**
	 * @function     get_iva_acreditable
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function	get_iva_acreditable($empresa, $ejercicio, $periodo) {
		
		$con = Doctrine_Manager::getInstance()->connection();
		
		$query = "
			SELECT DISTINCT iva_gravable_16
			           FROM impuesto_periodo_pm
			          WHERE id_empresa = '".$empresa."'
			                AND id_ejercicio = '".$ejercicio."'
			                AND id_periodo = '".$periodo."'";
		
		$iva_gravable_16 = $con->execute($query)->fetchAll();
		$iva_gravable_16 = ($iva_gravable_16[0]['iva_gravable_16']) ? $iva_gravable_16[0]['iva_gravable_16'] : 0;
		$iva_gravable_16 = $iva_gravable_16 * 0.16;
		
		$coeficiente = $this->get_coeficiente_acreditamiento($empresa, $ejercicio, $periodo);
		
		return $coeficiente * $iva_gravable_16;
	}
	
	/**
	 * @function     get_iva_acreditable_2
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function	get_iva_acreditable_2($empresa, $ejercicio, $periodo) {
		
		$con = Doctrine_Manager::getInstance()->connection();
		
		$iva_acreditable = $this->get_iva_acreditable($empresa, $ejercicio, $periodo);
		
		$query = "
			SELECT DISTINCT iva_retenido_mes
			           FROM impuesto_periodo_pm
			          WHERE id_empresa = '".$empresa."'
			                AND id_ejercicio = '".$ejercicio."'
			                AND id_periodo = '".$periodo."'";
		
		$iva_retenido_mes = $con->execute($query)->fetchAll();
		$iva_retenido_mes = ($iva_retenido_mes[0]['iva_retenido_mes']) ? $iva_retenido_mes[0]['iva_retenido_mes'] : 0;
		
		$query = "
			SELECT DISTINCT iva_retenido_mes_anterior
			           FROM impuesto_periodo_pm
			          WHERE id_empresa = '".$empresa."'
			                AND id_ejercicio = '".$ejercicio."'
			                AND id_periodo = '".$periodo."'";
		
		$iva_retenido_mes_anterior = $con->execute($query)->fetchAll();
		$iva_retenido_mes_anterior = ($iva_retenido_mes_anterior[0]['iva_retenido_mes_anterior']) ? $iva_retenido_mes_anterior[0]['iva_retenido_mes_anterior'] : 0;
		
		return $iva_acreditable - $iva_retenido_mes + $iva_retenido_mes_anterior;
	}
	
	/**
	 * @function     get_iva_cargo_periodo
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function	get_iva_cargo_periodo($empresa, $ejercicio, $periodo) {
		
		$con = Doctrine_Manager::getInstance()->connection();
		
		$query = "
			SELECT DISTINCT iva_ingresos_16
			           FROM impuesto_periodo_pm
			          WHERE id_empresa = '".$empresa."'
			                AND id_ejercicio = '".$ejercicio."'
			                AND id_periodo = '".$periodo."'";
		
		$iva_ingresos_16 = $con->execute($query)->fetchAll();
		$iva_ingresos_16 = ($iva_ingresos_16[0]['iva_ingresos_16']) ? $iva_ingresos_16[0]['iva_ingresos_16'] : 0;
		$iva_ingresos_16 = $iva_ingresos_16 * 0.16;
		
		$get_iva_acreditable_2 = $this->get_iva_acreditable_2($empresa, $ejercicio, $periodo);
		
		if ($iva_ingresos_16 > $get_iva_acreditable_2)
			return $iva_ingresos_16 - $get_iva_acreditable_2;
		else 
			return 0;
	}
	
	/**
	 * @function     get_iva_favor_periodo
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function	get_iva_favor_periodo($empresa, $ejercicio, $periodo) {
		
		$con = Doctrine_Manager::getInstance()->connection();
		
		$query = "
			SELECT DISTINCT iva_ingresos_16
			           FROM impuesto_periodo_pm
			          WHERE id_empresa = '".$empresa."'
			                AND id_ejercicio = '".$ejercicio."'
			                AND id_periodo = '".$periodo."'";
		
		$iva_ingresos_16 = $con->execute($query)->fetchAll();
		$iva_ingresos_16 = ($iva_ingresos_16[0]['iva_ingresos_16']) ? $iva_ingresos_16[0]['iva_ingresos_16'] : 0;
		$iva_ingresos_16 = $iva_ingresos_16 * 0.16;
		
		$get_iva_acreditable_2 = $this->get_iva_acreditable_2($empresa, $ejercicio, $periodo);
		
		if ($iva_ingresos_16 < $get_iva_acreditable_2)
			return $iva_ingresos_16 - $get_iva_acreditable_2;
		else 
			return 0;
	}
	
	/**
	 * @function     get_iva_cargo_periodo_2
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function	get_iva_cargo_periodo_2($empresa, $ejercicio, $periodo) {
		
		$con = Doctrine_Manager::getInstance()->connection();
		
		$iva_cargo_periodo = $this->get_iva_cargo_periodo($empresa, $ejercicio, $periodo);
		$iva_favor_periodo = $this->get_iva_favor_periodo($empresa, $ejercicio, $periodo);
		
		$query = "
			SELECT DISTINCT iva_compensaciones
			           FROM impuesto_periodo_pm
			          WHERE id_empresa = '".$empresa."'
			                AND id_ejercicio = '".$ejercicio."'
			                AND id_periodo = '".$periodo."'";
		
		$iva_compensaciones = $con->execute($query)->fetchAll();
		$iva_compensaciones = ($iva_compensaciones[0]['iva_compensaciones']) ? $iva_compensaciones[0]['iva_compensaciones'] : 0;
		
		if ($iva_cargo_periodo > $iva_favor_periodo)
			return ($iva_cargo_periodo - $iva_favor_periodo) - $iva_compensaciones;
		else
			return 0 - $iva_compensaciones;
	}
	
	/**
	 * @function     get_iva_favor_periodo_2
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function	get_iva_favor_periodo_2($empresa, $ejercicio, $periodo) {
		
		$con = Doctrine_Manager::getInstance()->connection();
		
		$iva_favor_periodo         = 0;
		$iva_compensaciones        = 0;
		$iva_favor_periodo_before  = 0;
		$iva_compensaciones_before = 0;
		$iva_favor_acumulado       = 0;
		
		$iva_favor_periodo = $this->get_iva_favor_periodo($empresa, $ejercicio, $periodo);
		
		$query = "
			SELECT DISTINCT iva_compensaciones
			           FROM impuesto_periodo_pm
			          WHERE id_empresa = '".$empresa."'
			                AND id_ejercicio = '".$ejercicio."'
			                AND id_periodo = '".$periodo."'";
		
		$iva_compensaciones = $con->execute($query)->fetchAll();
		$iva_compensaciones = ($iva_compensaciones[0]['iva_compensaciones']) ? $iva_compensaciones[0]['iva_compensaciones'] : 0;
		
		if ($periodo == 1 AND $periodo == 2) {
			return $iva_favor_acumulado + $iva_favor_periodo - $iva_compensaciones;
		}
		else {
			$iva_favor_periodo_before = $this->get_iva_favor_periodo($empresa, $ejercicio, $periodo - 2);
			
			$query = "
				SELECT DISTINCT iva_compensaciones
						   FROM impuesto_periodo_pm
						  WHERE id_empresa = '".$empresa."'
								AND id_ejercicio = '".$ejercicio."'
								AND id_periodo = '".($periodo - 2)."'";
			
			$iva_compensaciones_before = $con->execute($query)->fetchAll();
			$iva_compensaciones_before = ($iva_compensaciones_before[0]['iva_compensaciones']) ? $iva_compensaciones_before[0]['iva_compensaciones'] : 0;
			
			$iva_favor_acumulado = $iva_favor_periodo_before - $iva_compensaciones_before;
			
			return $iva_favor_acumulado + $iva_favor_periodo - $iva_compensaciones;
		}
	}
	
	/**
	 * @function     get_total_impuestos
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function	get_total_impuestos($empresa, $ejercicio, $periodo) {
		
		$con = Doctrine_Manager::getInstance()->connection();
		
		$isr = $this->get_isr_pago_provisional($empresa, $ejercicio, $periodo);
		$iva = $this->get_iva_cargo_periodo_2($empresa, $ejercicio, $periodo);
		
		$query = "
			SELECT DISTINCT (SUM(".$isr.")                       + 
			                SUM(".$iva.")                        + 
			                SUM(retencion_salarios)              + 
			                SUM(retencion_isr_honorarios)        + 
			                SUM(retencion_asimilados)            + 
			                SUM(retencion_dividendos)            + 
			                SUM(retencion_intereses)             + 
			                SUM(retencion_pagos_extranjero)      + 
			                SUM(retencion_venta_acciones)        + 
			                SUM(retencion_venta_partes_sociales) + 
			                SUM(retencion_isr_arrendamiento)     + 
			                SUM(iva_retenido_mes)
			                ) AS average
			           FROM impuesto_periodo_pm
			          WHERE id_empresa = '".$empresa."'
			                AND id_ejercicio = '".$ejercicio."'
			                AND id_periodo = '".$periodo."'";
		
		$average = $con->execute($query)->fetchAll();
		$average = ($average[0]['average']) ? $average[0]['average'] : 0;
		
		return $average;
	}
	
	/**
	 * @function     get_impuestos_pagar
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function	get_impuestos_pagar($empresa, $ejercicio, $periodo) {
		
		$con = Doctrine_Manager::getInstance()->connection();
		
		$total_impuestos = $this->get_total_impuestos($empresa, $ejercicio, $periodo);
		
		$query = "
			SELECT DISTINCT subsidio_empleo
			           FROM impuesto_periodo_pm
			          WHERE id_empresa = '".$empresa."'
			                AND id_ejercicio = '".$ejercicio."'
			                AND id_periodo = '".$periodo."'";
		
		$subsidio_empleo = $con->execute($query)->fetchAll();
		$subsidio_empleo = ($subsidio_empleo[0]['subsidio_empleo']) ? $subsidio_empleo[0]['subsidio_empleo'] : 0;
		
		$query = "
			SELECT DISTINCT compensaciones_otros
			           FROM impuesto_periodo_pm
			          WHERE id_empresa = '".$empresa."'
			                AND id_ejercicio = '".$ejercicio."'
			                AND id_periodo = '".$periodo."'";
		
		$compensaciones_otros = $con->execute($query)->fetchAll();
		$compensaciones_otros = ($compensaciones_otros[0]['compensaciones_otros']) ? $compensaciones_otros[0]['compensaciones_otros'] : 0;
		
		return $total_impuestos - $subsidio_empleo - $compensaciones_otros;
	}
	
	/**
	 * @function     setdatacontpaqAction
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function	setdatacontpaqAction() {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$empresa   = $_POST["empresa"];
		$ejercicio = $_POST["ejercicio"];
		
		$query = "
			SELECT DISTINCT COUNT(*) AS count
					   FROM impuesto_periodo_pm
					  WHERE id_empresa = ".$empresa."
							AND id_ejercicio = ".$ejercicio."
							AND status = 1";
			
		$periodos = My_Comun::crearQuery("ImpuestoPeriodoPm", $query);
		
		$periodos = ($periodos[0]['count']) ? ($periodos[0]['count'] + 1) : 1;
		
		if ( $periodos != 13) {
			
			echo "<pre>";
			print_r('Sincronizando desde el periodo '.$periodos.'');
			echo "</pre>";
			
			//Conexion
			$conexion      = My_Comun::obtener("Empresa", "id", $empresa);
			$cnx = Conexion::abreConexion($conexion->usuario_bd_contpaq, $conexion->pass_bd_contpaq, $conexion->nombre_bd_contpaq, $conexion->server_bd_contpaq);
			
			if ($cnx) {
				
				$filtro  = " 1=1 ";
				$filtro .= " AND (id_empresa = '".$empresa."')  ";
				$filtro .= " AND (id_ejercicio = '".$ejercicio."')  ";
				
				//crearEjercisios
				$ejercicios = My_Comun::obtenerFiltro("ImpuestoPeriodoPm", $filtro, "id_periodo ASC");
				
				if ( count($ejercicios) != 12) {
					$ejercicios = (count($ejercicios)) ? count($ejercicios) : 1;
					for ($i = $ejercicios; $i <= 12; $i++) {
						
						//Crear ronda general
						$periodo = array();
						$periodo['id']           = '';
						$periodo['id_empresa']   = $empresa;
						$periodo['id_ejercicio'] = $ejercicio;
						$periodo['id_periodo']   = $i;
						
						My_Comun::Guardar("ImpuestoPeriodoPm", $periodo, "", "");
						
						echo "<pre>";
						print_r('Periodo '.$i.' creado con exito');
						echo "</pre>";
					}
				}
				
				// ISR
				$this->isr_ingresos_del_periodo($empresa, $ejercicio, $periodos);
				$this->isr_anticipo_de_clientes($empresa, $ejercicio, $periodos);
				$this->isr_otros_ingresos($empresa, $ejercicio, $periodos);
				$this->isr_producto_financiero($empresa, $ejercicio, $periodos);
				$this->isr_anticipos_distribuidos($empresa, $ejercicio, $periodos);
				$this->isr_deduccion_inversiones($empresa, $ejercicio, $periodos);
				$this->isr_ptu_pagada($empresa, $ejercicio, $periodos);
				$this->isr_perdida_anteriores($empresa, $ejercicio, $periodos);
				$this->set_isr_compensaciones($empresa, $ejercicio, $periodos);
				$this->set_iva_compensaciones($empresa, $ejercicio, $periodos);
				
				// IVA
				$this->iva_ingresos_gravados_16($empresa, $ejercicio, $periodos);
				$this->iva_ingresos_gravados_11($empresa, $ejercicio, $periodos);
				$this->iva_ingresos_gravados_0($empresa, $ejercicio, $periodos);
				$this->iva_ingresos_gravados_exento($empresa, $ejercicio, $periodos);
				$this->iva_ingresos_gravados_otrabase($empresa, $ejercicio, $periodos);
				$this->iva_ingresos_gravable_16($empresa, $ejercicio, $periodos);
				$this->iva_ingresos_gravable_11($empresa, $ejercicio, $periodos);
				$this->iva_ingresos_gravable_0($empresa, $ejercicio, $periodos);
				$this->iva_ingresos_gravable_exenta($empresa, $ejercicio, $periodos);
				$this->iva_retenido_mes($empresa, $ejercicio, $periodos);
				$this->iva_retenido_mes_anterior($empresa, $ejercicio, $periodos);
				
				// IMPUESTOS POR PAGAR
				$this->set_sum_cuenta_impuestos_pagar($empresa, $ejercicio, $periodos, $conexion->retencion_salarios,              3, 'retencion_salarios');
				$this->set_sum_cuenta_impuestos_pagar($empresa, $ejercicio, $periodos, $conexion->retencion_isr_honorarios,        3, 'retencion_isr_honorarios');
				$this->set_sum_cuenta_impuestos_pagar($empresa, $ejercicio, $periodos, $conexion->retencion_asimilados,            3, 'retencion_asimilados');
				$this->set_sum_cuenta_impuestos_pagar($empresa, $ejercicio, $periodos, $conexion->retencion_dividendos,            3, 'retencion_dividendos');
				$this->set_sum_cuenta_impuestos_pagar($empresa, $ejercicio, $periodos, $conexion->retencion_intereses,             3, 'retencion_intereses');
				$this->set_sum_cuenta_impuestos_pagar($empresa, $ejercicio, $periodos, $conexion->retencion_pagos_extranjero,      3, 'retencion_pagos_extranjero');
				$this->set_sum_cuenta_impuestos_pagar($empresa, $ejercicio, $periodos, $conexion->retencion_venta_acciones,        3, 'retencion_venta_acciones');
				$this->set_sum_cuenta_impuestos_pagar($empresa, $ejercicio, $periodos, $conexion->retencion_venta_partes_sociales, 3, 'retencion_venta_partes_sociales');
				$this->set_sum_cuenta_impuestos_pagar($empresa, $ejercicio, $periodos, $conexion->retencion_isr_arrendamiento,     3, 'retencion_isr_arrendamiento');
				
			}
			else {
				echo "<pre>"; 
				print_r('Hubo un problema con la conexión de la base de datos '.$conexion->nombre_bd_contpaq.'');
				echo "</pre>";
			}
		}
		else {
			echo "<pre>"; 
			print_r('No se puede ejecutar la sincronización ya que se encuentran congeladas los 12 periodos.');
			echo "</pre>";
		}
	}
	
	/**
	 * @function     isr_ingresos_del_periodo
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function isr_ingresos_del_periodo($empresa, $ejercicio, $periodo) {
		
		$ejercicio_obj = My_Comun::obtener("Ejercicio", "id", $ejercicio);
		
		//Conexion
		$conexion      = My_Comun::obtener("Empresa", "id", $empresa);
		$cnx = Conexion::abreConexion($conexion->usuario_bd_contpaq, $conexion->pass_bd_contpaq, $conexion->nombre_bd_contpaq, $conexion->server_bd_contpaq);
		
		for ($i = $periodo; $i <= 12; $i++) {
			
			$filtro  = " 1=1 ";
			$filtro .= "AND id_empresa = '".$empresa."' ";
			$filtro .= "AND id_ejercicio = '".$ejercicio."' ";
			$filtro .= "AND id_periodo = '".$i."' ";
			
			//crearEjercisios
			$ImpuestoPeriodoPm = My_Comun::obtenerFiltro("ImpuestoPeriodoPm", $filtro, "id_periodo ASC");
			
			// SQL
			$sql = "
				    SELECT SUM(sc.Importes".$i.") AS average
				      FROM SaldosCuentas sc
				INNER JOIN Cuentas c
				           ON sc.IdCuenta = c.Id 
				INNER JOIN AgrupadoresSAT a 
				           ON c.IdAgrupadorSAT = a.Id
				     WHERE a.Codigo LIKE '%401%'
				           AND a.Codigo != '401'
				           AND sc.Ejercicio = (SELECT Id FROM Ejercicios WHERE Ejercicio = '".$ejercicio_obj->nombre."')
				           AND sc.Tipo = '3'";
			
			$query = sqlsrv_query($cnx, $sql) OR print_r(sqlsrv_errors());
			$query = sqlsrv_fetch_array($query);
			
			// Actualizar campo de la base d edatos.
			$data                 = array();
			$data['id']           = $ImpuestoPeriodoPm[0]->id;
			$data['id_empresa']   = $empresa;
			$data['id_ejercicio'] = $ejercicio;
			$data['id_periodo']   = $i;
			$data['isr_ingreso_periodo'] = $query['average'];
			
			if ($ImpuestoPeriodoPm[0]->id) {
				My_Comun::Guardar("ImpuestoPeriodoPm", $data, $data['id'], "");
			}
		}
		
		echo "<pre>";
		print_r('isr_ingreso_periodo Actualizado correctamente');
		echo "</pre>";
		
		sqlsrv_close($cnx);
	}
	
	/**
	 * @function     isr_anticipo_de_clientes
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function isr_anticipo_de_clientes($empresa, $ejercicio, $periodo) {
		
		$ejercicio_obj = My_Comun::obtener("Ejercicio", "id", $ejercicio);
		
		//Conexion
		$conexion      = My_Comun::obtener("Empresa", "id", $empresa);
		$cnx = Conexion::abreConexion($conexion->usuario_bd_contpaq, $conexion->pass_bd_contpaq, $conexion->nombre_bd_contpaq, $conexion->server_bd_contpaq);
		
		for ($i = $periodo; $i <= 12; $i++) {
			
			$average = 0;
			$filtro  = " 1=1 ";
			$filtro .= "AND id_empresa = '".$empresa."' ";
			$filtro .= "AND id_ejercicio = '".$ejercicio."' ";
			$filtro .= "AND id_periodo = '".$i."' ";
			
			//crearEjercisios
			$ImpuestoPeriodoPm = My_Comun::obtenerFiltro("ImpuestoPeriodoPm", $filtro, "id_periodo ASC");
			
			// SQL
			$sql = "
				    SELECT SUM(sc.Importes".$i.") AS average
				      FROM SaldosCuentas sc
				INNER JOIN Cuentas c
				           ON sc.IdCuenta = c.Id 
				INNER JOIN AgrupadoresSAT a 
				           ON c.IdAgrupadorSAT = a.Id
				     WHERE a.Codigo LIKE '%206%'
				           AND a.Codigo != '206'
				           AND sc.Ejercicio = (SELECT Id FROM Ejercicios WHERE Ejercicio = '".$ejercicio_obj->nombre."')
				           AND sc.Tipo = '3'";
			
			$abonos = sqlsrv_query($cnx, $sql) OR print_r(sqlsrv_errors());
			$abonos = sqlsrv_fetch_array($abonos);
			
			// SQL
			$sql = "
				    SELECT SUM(sc.Importes".$i.") AS average
				      FROM SaldosCuentas sc
				INNER JOIN Cuentas c
				           ON sc.IdCuenta = c.Id 
				INNER JOIN AgrupadoresSAT a 
				           ON c.IdAgrupadorSAT = a.Id
				     WHERE a.Codigo LIKE '%206%'
				           AND a.Codigo != '206'
				           AND sc.Ejercicio = (SELECT Id FROM Ejercicios WHERE Ejercicio = '".$ejercicio_obj->nombre."')
				           AND sc.Tipo = '2'";
			
			$cargos = sqlsrv_query($cnx, $sql) OR print_r(sqlsrv_errors());
			$cargos = sqlsrv_fetch_array($cargos);
			
			$average = $abonos['average'] - $cargos['average'];
			
			// Actualizar campo de la base d edatos.
			$data                 = array();
			$data['id']           = $ImpuestoPeriodoPm[0]->id;
			$data['id_empresa']   = $empresa;
			$data['id_ejercicio'] = $ejercicio;
			$data['id_periodo']   = $i;
			$data['isr_anticipo_cliente'] = $average;
			
			if ($ImpuestoPeriodoPm[0]->id) {
				My_Comun::Guardar("ImpuestoPeriodoPm", $data, $data['id'], "");
			}
		}
		
		echo "<pre>";
		print_r('isr_anticipo_cliente Actualizado correctamente');
		echo "</pre>";
		
		sqlsrv_close($cnx);
	}
	
	/**
	 * @function     isr_otros_ingresos
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function isr_otros_ingresos($empresa, $ejercicio, $periodo) {
		
		$ejercicio_obj = My_Comun::obtener("Ejercicio", "id", $ejercicio);
		
		//Conexion
		$conexion      = My_Comun::obtener("Empresa", "id", $empresa);
		$cnx = Conexion::abreConexion($conexion->usuario_bd_contpaq, $conexion->pass_bd_contpaq, $conexion->nombre_bd_contpaq, $conexion->server_bd_contpaq);
		
		for ($i = $periodo; $i <= 12; $i++) {
			
			$average = 0;
			$filtro  = " 1=1 ";
			$filtro .= "AND id_empresa = '".$empresa."' ";
			$filtro .= "AND id_ejercicio = '".$ejercicio."' ";
			$filtro .= "AND id_periodo = '".$i."' ";
			
			//crearEjercisios
			$ImpuestoPeriodoPm = My_Comun::obtenerFiltro("ImpuestoPeriodoPm", $filtro, "id_periodo ASC");
			
			// SQL
			$sql = "
				    SELECT SUM(sc.Importes".$i.") AS average
				      FROM SaldosCuentas sc
				INNER JOIN Cuentas c
				           ON sc.IdCuenta = c.Id 
				INNER JOIN AgrupadoresSAT a 
				           ON c.IdAgrupadorSAT = a.Id
				     WHERE a.Codigo LIKE '%403%'
				           AND a.Codigo != '403'
				           AND sc.Ejercicio = (SELECT Id FROM Ejercicios WHERE Ejercicio = '".$ejercicio_obj->nombre."')
				           AND sc.Tipo = '3'";
			
			$query = sqlsrv_query($cnx, $sql) OR print_r(sqlsrv_errors());
			$average = sqlsrv_fetch_array($query);
			
			// Actualizar campo de la base d edatos.
			$data                 = array();
			$data['id']           = $ImpuestoPeriodoPm[0]->id;
			$data['id_empresa']   = $empresa;
			$data['id_ejercicio'] = $ejercicio;
			$data['id_periodo']   = $i;
			$data['isr_otros_ingresos'] = $average['average'];
			
			if ($ImpuestoPeriodoPm[0]->id) {
				My_Comun::Guardar("ImpuestoPeriodoPm", $data, $data['id'], "");
			}
		}
		
		echo "<pre>";
		print_r('isr_otros_ingresos Actualizado correctamente');
		echo "</pre>";
		
		sqlsrv_close($cnx);
	}
	
	/**
	 * @function     isr_producto_financiero
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function isr_producto_financiero($empresa, $ejercicio, $periodo) {
		
		$ejercicio_obj = My_Comun::obtener("Ejercicio", "id", $ejercicio);
		
		//Conexion
		$conexion      = My_Comun::obtener("Empresa", "id", $empresa);
		$cnx = Conexion::abreConexion($conexion->usuario_bd_contpaq, $conexion->pass_bd_contpaq, $conexion->nombre_bd_contpaq, $conexion->server_bd_contpaq);
		
		for ($i = $periodo; $i <= 12; $i++) {
			
			$average = 0;
			$filtro  = " 1=1 ";
			$filtro .= "AND id_empresa = '".$empresa."' ";
			$filtro .= "AND id_ejercicio = '".$ejercicio."' ";
			$filtro .= "AND id_periodo = '".$i."' ";
			
			//crearEjercisios
			$ImpuestoPeriodoPm = My_Comun::obtenerFiltro("ImpuestoPeriodoPm", $filtro, "id_periodo ASC");
			
			// SQL
			$sql = "
				    SELECT SUM(sc.Importes".$i.") AS average
				      FROM SaldosCuentas sc
				INNER JOIN Cuentas c
				           ON sc.IdCuenta = c.Id 
				INNER JOIN AgrupadoresSAT a 
				           ON c.IdAgrupadorSAT = a.Id
				     WHERE (a.Codigo LIKE '%702%' OR a.Codigo LIKE '%704%')
				           AND sc.Ejercicio = (SELECT Id FROM Ejercicios WHERE Ejercicio = '".$ejercicio_obj->nombre."')
				           AND sc.Tipo = '3'";
			
			$query = sqlsrv_query($cnx, $sql) OR print_r(sqlsrv_errors());
			$average = sqlsrv_fetch_array($query);
			
			// Actualizar campo de la base d edatos.
			$data                 = array();
			$data['id']           = $ImpuestoPeriodoPm[0]->id;
			$data['id_empresa']   = $empresa;
			$data['id_ejercicio'] = $ejercicio;
			$data['id_periodo']   = $i;
			$data['isr_producto_financiero'] = $average['average'];
			
			if ($ImpuestoPeriodoPm[0]->id) {
				My_Comun::Guardar("ImpuestoPeriodoPm", $data, $data['id'], "");
			}
		}
		
		echo "<pre>";
		print_r('isr_producto_financiero Actualizado correctamente');
		echo "</pre>";
		
		sqlsrv_close($cnx);
	}
	
	/**
	 * @function     isr_anticipos_distribuidos
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function isr_anticipos_distribuidos($empresa, $ejercicio, $periodo) {
		
		$conexion      = My_Comun::obtener("Empresa", "id", $empresa);
		$ejercicio_obj = My_Comun::obtener("Ejercicio", "id", $ejercicio);
		
		//Conexion
		$cnx = Conexion::abreConexion($conexion->usuario_bd_contpaq, $conexion->pass_bd_contpaq, $conexion->nombre_bd_contpaq, $conexion->server_bd_contpaq);
		
		for ($i = $periodo; $i <= 12; $i++) {
			
			$filtro  = " 1=1 ";
			$filtro .= "AND id_empresa = '".$empresa."' ";
			$filtro .= "AND id_ejercicio = '".$ejercicio."' ";
			$filtro .= "AND id_periodo = '".$i."' ";
			
			//crearEjercisios
			$ImpuestoPeriodoPm = My_Comun::obtenerFiltro("ImpuestoPeriodoPm", $filtro, "id_periodo ASC");
			
			// SQL
			$sql = "SELECT ISNULL(SUM(m.Importe), '0') AS average
			          FROM MovimientosPoliza m 
			    INNER JOIN Cuentas c 
			               ON m.IdCuenta = c.Id 
			    INNER JOIN AgrupadoresSAT a 
			               ON c.IdAgrupadorSAT = a.Id
			         WHERE a.Codigo = '210.06'
			               AND m.Ejercicio = '".$ejercicio_obj->nombre."'
			               AND m.TipoMovto = '0'
			               AND m.Periodo = '".$i."'";
			
			$query = sqlsrv_query($cnx, $sql) OR print_r(sqlsrv_errors());
			$query = sqlsrv_fetch_array($query);
			
			// Actualizar campo de la base d edatos.
			$data                 = array();
			$data['id']           = $ImpuestoPeriodoPm[0]->id;
			$data['id_empresa']   = $empresa;
			$data['id_ejercicio'] = $ejercicio;
			$data['id_periodo']   = $i;
			$data['isr_anticipos_distribuidos'] = $query['average'];
			
			if ($ImpuestoPeriodoPm[0]->id) {
				My_Comun::Guardar("ImpuestoPeriodoPm", $data, $data['id'], "");
			}
		}
		
		echo "<pre>";
		print_r('isr_anticipos_distribuidos Actualizado correctamente');
		echo "</pre>";
		
		sqlsrv_close($cnx);
	}
	
	/**
	 * @function     isr_deduccion_inversiones
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function isr_deduccion_inversiones($empresa, $ejercicio, $periodo) {
		
		$conexion      = My_Comun::obtener("Empresa", "id", $empresa);
		$ejercicio_obj = My_Comun::obtener("Ejercicio", "id", $ejercicio);
		
		//Conexion
		$cnx = Conexion::abreConexion($conexion->usuario_bd_contpaq, $conexion->pass_bd_contpaq, $conexion->nombre_bd_contpaq, $conexion->server_bd_contpaq);
		
		for ($i = $periodo; $i <= 12; $i++) {
			
			$filtro  = " 1=1 ";
			$filtro .= "AND id_empresa = '".$empresa."' ";
			$filtro .= "AND id_ejercicio = '".$ejercicio."' ";
			$filtro .= "AND id_periodo = '".$i."' ";
			
			//crearEjercisios
			$ImpuestoPeriodoPm = My_Comun::obtenerFiltro("ImpuestoPeriodoPm", $filtro, "id_periodo ASC");
			
			// SQL
			$sql = "SELECT ISNULL(SUM(m.Importe / (13 - m.Periodo)), '0') AS average
				      FROM MovimientosPoliza m 
				INNER JOIN Cuentas c 
				           ON m.IdCuenta = c.Id 
				INNER JOIN AgrupadoresSAT a 
				           ON c.IdAgrupadorSAT = a.Id
				     WHERE a.codigo = '810.01'
				           AND m.Ejercicio = '".$ejercicio_obj->nombre."'
				           AND c.CtaMayor = '2'
				           AND m.TipoMovto = '0'
				           AND m.periodo <= '".$i."'";
			
			$query = sqlsrv_query($cnx, $sql) OR print_r(sqlsrv_errors());
			$query = sqlsrv_fetch_array($query);
			
			// Actualizar campo de la base d edatos.
			$data                 = array();
			$data['id']           = $ImpuestoPeriodoPm[0]->id;
			$data['id_empresa']   = $empresa;
			$data['id_ejercicio'] = $ejercicio;
			$data['id_periodo']   = $i;
			$data['isr_deduccion_inversiones'] = $query['average'];
			
			if ($ImpuestoPeriodoPm[0]->id) {
				My_Comun::Guardar("ImpuestoPeriodoPm", $data, $data['id'], "");
			}
		}
		
		echo "<pre>";
		print_r('isr_deduccion_inversiones Actualizado correctamente');
		echo "</pre>";
		
		sqlsrv_close($cnx);
	}
	
	/**
	 * @function     isr_ptu_pagada
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function isr_ptu_pagada($empresa, $ejercicio, $periodo) {
		
		$conexion      = My_Comun::obtener("Empresa", "id", $empresa);
		$ejercicio_obj = My_Comun::obtener("Ejercicio", "id", $ejercicio);
		
		//Conexion
		$cnx = Conexion::abreConexion($conexion->usuario_bd_contpaq, $conexion->pass_bd_contpaq, $conexion->nombre_bd_contpaq, $conexion->server_bd_contpaq);
		
		for ($i = $periodo; $i <= 12; $i++) {
			
			$filtro  = " 1=1 ";
			$filtro .= "AND id_empresa = '".$empresa."' ";
			$filtro .= "AND id_ejercicio = '".$ejercicio."' ";
			$filtro .= "AND id_periodo = '".$i."' ";
			
			//crearEjercisios
			$ImpuestoPeriodoPm = My_Comun::obtenerFiltro("ImpuestoPeriodoPm", $filtro, "id_periodo ASC");
			
			// SQL
			$sql = "SELECT ISNULL(SUM(m.Importe / (13 - m.Periodo)), '0') AS monto
			          FROM MovimientosPoliza m 
			    INNER JOIN Cuentas c 
			               ON m.IdCuenta = c.Id 
			    INNER JOIN AgrupadoresSAT a on c.IdAgrupadorSAT = a.Id
			         WHERE a.codigo = '215.03'
					   AND m.Ejercicio = '".$ejercicio_obj->nombre."'
					   AND c.CtaMayor = '2'
					   AND m.TipoMovto = '0'
					   AND m.Periodo <= '".$i."'";
			
			$query = sqlsrv_query($cnx, $sql) OR print_r(sqlsrv_errors());
			$query = sqlsrv_fetch_array($query);
			
			// Actualizar campo de la base d edatos.
			$data                 = array();
			$data['id']           = $ImpuestoPeriodoPm[0]->id;
			$data['id_empresa']   = $empresa;
			$data['id_ejercicio'] = $ejercicio;
			$data['id_periodo']   = $i;
			$data['isr_ptu_pagada'] = $query['average'];
			
			if ($ImpuestoPeriodoPm[0]->id) {
				My_Comun::Guardar("ImpuestoPeriodoPm", $data, $data['id'], "");
			}
		}
		
		echo "<pre>";
		print_r('isr_ptu_pagada Actualizado correctamente');
		echo "</pre>";
		
		sqlsrv_close($cnx);
	}
	
	/**
	 * @function     isr_perdida_anteriores
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function isr_perdida_anteriores($empresa, $ejercicio, $periodo) {
		
		//Conexion
		$conexion = My_Comun::obtener("Empresa", "id", $empresa);
		$cnx      = Conexion::abreConexion($conexion->usuario_bd_contpaq, 
										   $conexion->pass_bd_contpaq, 
										   $conexion->nombre_bd_contpaq, 
										   $conexion->server_bd_contpaq);
		$ejercicio_obj = My_Comun::obtener("Ejercicio", "id", $ejercicio);
		
		//Obtener primer ejercicio
		$sql           = "SELECT TOP 1 Ejercicio FROM Ejercicios ORDER BY 'Ejercicio' ASC";
		$query         = sqlsrv_query($cnx, $sql) OR print_r(sqlsrv_errors());
		$lastEjercicio = sqlsrv_fetch_array($query);
		
		$filtro      = " 1 = 1 ";
		$filtro     .= "AND id = '".$empresa."' ";
		$empresaData = My_Comun::obtenerFiltro("Empresa", $filtro);
		
		for ($i = $periodo; $i <= 12; $i++) {
			
			$average = 0;
			$filtro  = " 1 = 1 ";
			$filtro .= "AND id_empresa = '".$empresa."' ";
			$filtro .= "AND id_ejercicio = '".$ejercicio."' ";
			$filtro .= "AND id_periodo = '".$i."' ";
			
			//crearEjercisios
			$ImpuestoPeriodoPm = My_Comun::obtenerFiltro("ImpuestoPeriodoPm", $filtro, "id_periodo ASC");
			
			if ( $lastEjercicio[0] == $ejercicio_obj->nombre ) {
				if ($i == 1 || $i == 2) {
					// echo "<pre>";
					// print_r("enero y febrero 1");
					// echo "</pre>";
					$average = $empresaData[0]->ejercicio_anterior_enero_febrero_1;
				}
				else {
					// echo "<pre>";
					// print_r("marzo y diciembre 1");
					// echo "</pre>";
					$average = $empresaData[0]->ejercicio_anterior_marzo_diciembre_1;
				}
			}
			else if ( ($lastEjercicio[0] + 1) == $ejercicio_obj->nombre ) {
				if ($i == 1 || $i == 2) {
					// echo "<pre>";
					// print_r("enero y febrero 2");
					// echo "</pre>";
					$average = $empresaData[0]->ejercicio_anterior_enero_febrero_2;
				}
				else {
					// echo "<pre>";
					// print_r("marzo y diciembre 2");
					// echo "</pre>";
					
					// SQL
					$sql = "SELECT ISNULL(Importes12, '0') AS average
							  FROM SaldosCuentas
							 WHERE IdCuenta 
								   IN (SELECT Id
										 FROM Cuentas
										WHERE IdAgrupadorSAT 
											  IN (SELECT Id
													FROM AgrupadoresSAT
												   WHERE Codigo = '813.01'))
								   AND Ejercicio = (SELECT Id
													  FROM Ejercicios
													 WHERE Ejercicio = (".$ejercicio_obj->nombre." - 1)) --Marzo a Diciembre
								   AND Tipo = '1' --Saldo";
					
					$query = sqlsrv_query($cnx, $sql) OR print_r(sqlsrv_errors());
					$query = sqlsrv_fetch_array($query);
					
					$average = ($query['average']) ? $query['average'] : 0;
				}
			}
			else {
				
				// echo "<pre>";
				// print_r("Estandard");
				// echo "</pre>";
				
				if ($i == 1 || $i == 2) {
					// SQL
					$sql = "SELECT ISNULL(Importes12, '0') AS average
							  FROM SaldosCuentas
							 WHERE IdCuenta 
								   IN (SELECT Id
										 FROM Cuentas
										WHERE IdAgrupadorSAT 
											  IN (SELECT Id
													FROM AgrupadoresSAT
												   WHERE Codigo = '813.01'))
								   AND Ejercicio = (SELECT Id
													  FROM Ejercicios
													 WHERE Ejercicio = (".$ejercicio_obj->nombre." - 2)) --Enero y Febrero
								   AND Tipo = '1' --Saldo";
				}
				else {
					// SQL
					$sql = "SELECT ISNULL(Importes12, '0') AS average
							  FROM SaldosCuentas
							 WHERE IdCuenta 
								   IN (SELECT Id
										 FROM Cuentas
										WHERE IdAgrupadorSAT 
											  IN (SELECT Id
													FROM AgrupadoresSAT
												   WHERE Codigo = '813.01'))
								   AND Ejercicio = (SELECT Id
													  FROM Ejercicios
													 WHERE Ejercicio = (".$ejercicio_obj->nombre." - 1)) --Marzo a Diciembre
								   AND Tipo = '1' --Saldo";
				}
				
				$query = sqlsrv_query($cnx, $sql) OR print_r(sqlsrv_errors());
				$query = sqlsrv_fetch_array($query);
				
				$average = ($query['average']) ? $query['average'] : 0;
			}
			
			// Actualizar campo de la base d edatos.
			$data                 = array();
			$data['id']           = $ImpuestoPeriodoPm[0]->id;
			$data['id_empresa']   = $empresa;
			$data['id_ejercicio'] = $ejercicio;
			$data['id_periodo']   = $i;
			$data['isr_perdida_anteriores'] = $average;
			
			// echo "<pre>";
			// print_r($data);
			// echo "</pre>";
			
			if ($ImpuestoPeriodoPm[0]->id) {
				My_Comun::Guardar("ImpuestoPeriodoPm", $data, $data['id'], "");
			}
		}
		
		echo "<pre>";
		print_r('isr_perdida_anteriores Actualizado correctamente');
		echo "</pre>";
		
		sqlsrv_close($cnx);
	}
	
	/**
	 * @function     iva_ingresos_gravados_16
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function iva_ingresos_gravados_16($empresa, $ejercicio, $periodo) {
		
		$conexion      = My_Comun::obtener("Empresa", "id", $empresa);
		$ejercicio_obj = My_Comun::obtener("Ejercicio", "id", $ejercicio);
		
		//Conexion
		$cnx = Conexion::abreConexion($conexion->usuario_bd_contpaq, $conexion->pass_bd_contpaq, $conexion->nombre_bd_contpaq, $conexion->server_bd_contpaq);
		
		for ($i = $periodo; $i <= 12; $i++) {
			
			$filtro  = " 1=1 ";
			$filtro .= "AND id_empresa = '".$empresa."' ";
			$filtro .= "AND id_ejercicio = '".$ejercicio."' ";
			$filtro .= "AND id_periodo = '".$i."' ";
			
			//crearEjercisios
			$ImpuestoPeriodoPm = My_Comun::obtenerFiltro("ImpuestoPeriodoPm", $filtro, "id_periodo ASC");
			
			// SQL
			$sql = "SELECT SUM(basetasa16) AS average
					  FROM CausacionesIVA c 
			    INNER JOIN Polizas p 
			               ON c.idPoliza = p.Id
			         WHERE ejercicio = '".$ejercicio_obj->nombre."' 
			               AND periodo = '".$i."'";
			
			$query = sqlsrv_query($cnx, $sql) OR print_r(sqlsrv_errors());
			$query = sqlsrv_fetch_array($query);
			
			// Actualizar campo de la base d edatos.
			$data                 = array();
			$data['id']           = $ImpuestoPeriodoPm[0]->id;
			$data['id_empresa']   = $empresa;
			$data['id_ejercicio'] = $ejercicio;
			$data['id_periodo']   = $i;
			$data['iva_ingresos_16'] = $query['average'];
			
			if ($ImpuestoPeriodoPm[0]->id) {
				My_Comun::Guardar("ImpuestoPeriodoPm", $data, $data['id'], "");
			}
		}
		
		echo "<pre>";
		print_r('iva_ingresos_16 Actualizado correctamente');
		echo "</pre>";
		
		sqlsrv_close($cnx);
	}
	
	/**
	 * @function     iva_ingresos_gravados_11
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function iva_ingresos_gravados_11($empresa, $ejercicio, $periodo) {
		
		$conexion      = My_Comun::obtener("Empresa", "id", $empresa);
		$ejercicio_obj = My_Comun::obtener("Ejercicio", "id", $ejercicio);
		
		//Conexion
		$cnx = Conexion::abreConexion($conexion->usuario_bd_contpaq, $conexion->pass_bd_contpaq, $conexion->nombre_bd_contpaq, $conexion->server_bd_contpaq);
		
		for ($i = $periodo; $i <= 12; $i++) {
			
			$filtro  = " 1=1 ";
			$filtro .= "AND id_empresa = '".$empresa."' ";
			$filtro .= "AND id_ejercicio = '".$ejercicio."' ";
			$filtro .= "AND id_periodo = '".$i."' ";
			
			//crearEjercisios
			$ImpuestoPeriodoPm = My_Comun::obtenerFiltro("ImpuestoPeriodoPm", $filtro, "id_periodo ASC");
			
			// SQL
			$sql = "SELECT SUM(basetasa11) AS average
					  FROM CausacionesIVA c 
			    INNER JOIN Polizas p 
			               ON c.idPoliza = p.Id
			         WHERE ejercicio = '".$ejercicio_obj->nombre."' 
			               AND periodo = '".$i."'";
			
			$query = sqlsrv_query($cnx, $sql) OR print_r(sqlsrv_errors());
			$query = sqlsrv_fetch_array($query);
			
			// Actualizar campo de la base d edatos.
			$data                 = array();
			$data['id']           = $ImpuestoPeriodoPm[0]->id;
			$data['id_empresa']   = $empresa;
			$data['id_ejercicio'] = $ejercicio;
			$data['id_periodo']   = $i;
			$data['iva_ingresos_11'] = $query['average'];
			
			if ($ImpuestoPeriodoPm[0]->id) {
				My_Comun::Guardar("ImpuestoPeriodoPm", $data, $data['id'], "");
			}
		}
		
		echo "<pre>";
		print_r('iva_ingresos_11 Actualizado correctamente');
		echo "</pre>";
		
		sqlsrv_close($cnx);
	}
	
	/**
	 * @function     iva_ingresos_gravados_0
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function iva_ingresos_gravados_0($empresa, $ejercicio, $periodo) {
		
		$conexion      = My_Comun::obtener("Empresa", "id", $empresa);
		$ejercicio_obj = My_Comun::obtener("Ejercicio", "id", $ejercicio);
		
		//Conexion
		$cnx = Conexion::abreConexion($conexion->usuario_bd_contpaq, $conexion->pass_bd_contpaq, $conexion->nombre_bd_contpaq, $conexion->server_bd_contpaq);
		
		for ($i = $periodo; $i <= 12; $i++) {
			
			$filtro  = " 1=1 ";
			$filtro .= "AND id_empresa = '".$empresa."' ";
			$filtro .= "AND id_ejercicio = '".$ejercicio."' ";
			$filtro .= "AND id_periodo = '".$i."' ";
			
			//crearEjercisios
			$ImpuestoPeriodoPm = My_Comun::obtenerFiltro("ImpuestoPeriodoPm", $filtro, "id_periodo ASC");
			
			// SQL
			$sql = "SELECT SUM(basetasa0) AS average
					  FROM CausacionesIVA c 
			    INNER JOIN Polizas p 
			               ON c.idPoliza = p.Id
			         WHERE ejercicio = '".$ejercicio_obj->nombre."' 
			               AND periodo = '".$i."'";
			
			$query = sqlsrv_query($cnx, $sql) OR print_r(sqlsrv_errors());
			$query = sqlsrv_fetch_array($query);
			
			// Actualizar campo de la base d edatos.
			$data                 = array();
			$data['id']           = $ImpuestoPeriodoPm[0]->id;
			$data['id_empresa']   = $empresa;
			$data['id_ejercicio'] = $ejercicio;
			$data['id_periodo']   = $i;
			$data['iva_ingresos_0'] = $query['average'];
			
			if ($ImpuestoPeriodoPm[0]->id) {
				My_Comun::Guardar("ImpuestoPeriodoPm", $data, $data['id'], "");
			}
		}
		
		echo "<pre>";
		print_r('iva_ingresos_0 Actualizado correctamente');
		echo "</pre>";
		
		sqlsrv_close($cnx);
	}
	
	/**
	 * @function     iva_ingresos_gravados_exento
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function iva_ingresos_gravados_exento($empresa, $ejercicio, $periodo) {
		
		$conexion      = My_Comun::obtener("Empresa", "id", $empresa);
		$ejercicio_obj = My_Comun::obtener("Ejercicio", "id", $ejercicio);
		
		//Conexion
		$cnx = Conexion::abreConexion($conexion->usuario_bd_contpaq, $conexion->pass_bd_contpaq, $conexion->nombre_bd_contpaq, $conexion->server_bd_contpaq);
		
		for ($i = $periodo; $i <= 12; $i++) {
			
			$filtro  = " 1=1 ";
			$filtro .= "AND id_empresa = '".$empresa."' ";
			$filtro .= "AND id_ejercicio = '".$ejercicio."' ";
			$filtro .= "AND id_periodo = '".$i."' ";
			
			//crearEjercisios
			$ImpuestoPeriodoPm = My_Comun::obtenerFiltro("ImpuestoPeriodoPm", $filtro, "id_periodo ASC");
			
			// SQL
			$sql = "SELECT SUM(basetasaexento) AS average
					  FROM CausacionesIVA c 
			    INNER JOIN Polizas p 
			               ON c.idPoliza = p.Id
			         WHERE ejercicio = '".$ejercicio_obj->nombre."' 
			               AND periodo = '".$i."'";
			
			$query = sqlsrv_query($cnx, $sql) OR print_r(sqlsrv_errors());
			$query = sqlsrv_fetch_array($query);
			
			// Actualizar campo de la base d edatos.
			$data                 = array();
			$data['id']           = $ImpuestoPeriodoPm[0]->id;
			$data['id_empresa']   = $empresa;
			$data['id_ejercicio'] = $ejercicio;
			$data['id_periodo']   = $i;
			$data['iva_ingresos_exentos'] = $query['average'];
			
			if ($ImpuestoPeriodoPm[0]->id) {
				My_Comun::Guardar("ImpuestoPeriodoPm", $data, $data['id'], "");
			}
		}
		
		echo "<pre>";
		print_r('iva_ingresos_exentos Actualizado correctamente');
		echo "</pre>";
		
		sqlsrv_close($cnx);
	}
	
	/**
	 * @function     iva_ingresos_gravados_otrabase
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function iva_ingresos_gravados_otrabase($empresa, $ejercicio, $periodo) {
		
		$conexion      = My_Comun::obtener("Empresa", "id", $empresa);
		$ejercicio_obj = My_Comun::obtener("Ejercicio", "id", $ejercicio);
		
		//Conexion
		$cnx = Conexion::abreConexion($conexion->usuario_bd_contpaq, $conexion->pass_bd_contpaq, $conexion->nombre_bd_contpaq, $conexion->server_bd_contpaq);
		
		for ($i = $periodo; $i <= 12; $i++) {
			
			$filtro  = " 1=1 ";
			$filtro .= "AND id_empresa = '".$empresa."' ";
			$filtro .= "AND id_ejercicio = '".$ejercicio."' ";
			$filtro .= "AND id_periodo = '".$i."' ";
			
			//crearEjercisios
			$ImpuestoPeriodoPm = My_Comun::obtenerFiltro("ImpuestoPeriodoPm", $filtro, "id_periodo ASC");
			
			// SQL
			$sql = "SELECT SUM(BaseOtraTasa) AS average
					  FROM CausacionesIVA c 
			    INNER JOIN Polizas p 
			               ON c.idPoliza = p.Id
			         WHERE ejercicio = '".$ejercicio_obj->nombre."' 
			               AND periodo = '".$i."'";
			
			$query = sqlsrv_query($cnx, $sql) OR print_r(sqlsrv_errors());
			$query = sqlsrv_fetch_array($query);
			
			// Actualizar campo de la base d edatos.
			$data                 = array();
			$data['id']           = $ImpuestoPeriodoPm[0]->id;
			$data['id_empresa']   = $empresa;
			$data['id_ejercicio'] = $ejercicio;
			$data['id_periodo']   = $i;
			$data['iva_ingresos_otra_base'] = $query['average'];
			
			if ($ImpuestoPeriodoPm[0]->id) {
				My_Comun::Guardar("ImpuestoPeriodoPm", $data, $data['id'], "");
			}
		}
		
		echo "<pre>";
		print_r('iva_ingresos_otra_base Actualizado correctamente');
		echo "</pre>";
		
		sqlsrv_close($cnx);
	}
	
	/**
	 * @function     iva_ingresos_gravable_16
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function iva_ingresos_gravable_16($empresa, $ejercicio, $periodo) {
		
		$conexion      = My_Comun::obtener("Empresa", "id", $empresa);
		$ejercicio_obj = My_Comun::obtener("Ejercicio", "id", $ejercicio);
		
		//Conexion
		$cnx = Conexion::abreConexion($conexion->usuario_bd_contpaq, $conexion->pass_bd_contpaq, $conexion->nombre_bd_contpaq, $conexion->server_bd_contpaq);
		
		for ($i = $periodo; $i <= 12; $i++) {
			
			$filtro  = " 1=1 ";
			$filtro .= "AND id_empresa = '".$empresa."' ";
			$filtro .= "AND id_ejercicio = '".$ejercicio."' ";
			$filtro .= "AND id_periodo = '".$i."' ";
			
			//crearEjercisios
			$ImpuestoPeriodoPm = My_Comun::obtenerFiltro("ImpuestoPeriodoPm", $filtro, "id_periodo ASC");
			
			// SQL
			$sql = "SELECT SUM(ImpBase) AS average
			          FROM DevolucionesIVA d
			    INNER JOIN Polizas p 
			               ON d.IdPoliza = p.Id
			         WHERE p.ejercicio = '".$ejercicio_obj->nombre."' 
			               AND periodo = '".$i."'
			               AND d.PorIVA = '16'";
			
			$query = sqlsrv_query($cnx, $sql) OR print_r(sqlsrv_errors());
			$query = sqlsrv_fetch_array($query);
			
			// Actualizar campo de la base d edatos.
			$data                 = array();
			$data['id']           = $ImpuestoPeriodoPm[0]->id;
			$data['id_empresa']   = $empresa;
			$data['id_ejercicio'] = $ejercicio;
			$data['id_periodo']   = $i;
			$data['iva_gravable_16'] = $query['average'];
			
			if ($ImpuestoPeriodoPm[0]->id) {
				My_Comun::Guardar("ImpuestoPeriodoPm", $data, $data['id'], "");
			}
		}
		
		echo "<pre>";
		print_r('iva_gravable_16 Actualizado correctamente');
		echo "</pre>";
		
		sqlsrv_close($cnx);
	}
	
	/**
	 * @function     iva_ingresos_gravable_11
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function iva_ingresos_gravable_11($empresa, $ejercicio, $periodo) {
		
		$conexion      = My_Comun::obtener("Empresa", "id", $empresa);
		$ejercicio_obj = My_Comun::obtener("Ejercicio", "id", $ejercicio);
		
		//Conexion
		$cnx = Conexion::abreConexion($conexion->usuario_bd_contpaq, $conexion->pass_bd_contpaq, $conexion->nombre_bd_contpaq, $conexion->server_bd_contpaq);
		
		for ($i = $periodo; $i <= 12; $i++) {
			
			$filtro  = " 1=1 ";
			$filtro .= "AND id_empresa = '".$empresa."' ";
			$filtro .= "AND id_ejercicio = '".$ejercicio."' ";
			$filtro .= "AND id_periodo = '".$i."' ";
			
			//crearEjercisios
			$ImpuestoPeriodoPm = My_Comun::obtenerFiltro("ImpuestoPeriodoPm", $filtro, "id_periodo ASC");
			
			// SQL
			$sql = "SELECT SUM(ImpBase) AS average
			          FROM DevolucionesIVA d
			    INNER JOIN Polizas p 
			               ON d.IdPoliza = p.Id
			         WHERE p.ejercicio = '".$ejercicio_obj->nombre."' 
			               AND periodo = '".$i."'
			               AND d.PorIVA = '11'";
			
			$query = sqlsrv_query($cnx, $sql) OR print_r(sqlsrv_errors());
			$query = sqlsrv_fetch_array($query);
			
			// Actualizar campo de la base d edatos.
			$data                 = array();
			$data['id']           = $ImpuestoPeriodoPm[0]->id;
			$data['id_empresa']   = $empresa;
			$data['id_ejercicio'] = $ejercicio;
			$data['id_periodo']   = $i;
			$data['iva_gravable_11'] = $query['average'];
			
			if ($ImpuestoPeriodoPm[0]->id) {
				My_Comun::Guardar("ImpuestoPeriodoPm", $data, $data['id'], "");
			}
		}
		
		echo "<pre>";
		print_r('iva_gravable_11 Actualizado correctamente');
		echo "</pre>";
		
		sqlsrv_close($cnx);
	}
	
	/**
	 * @function     iva_ingresos_gravable_0
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function iva_ingresos_gravable_0($empresa, $ejercicio, $periodo) {
		
		$conexion      = My_Comun::obtener("Empresa", "id", $empresa);
		$ejercicio_obj = My_Comun::obtener("Ejercicio", "id", $ejercicio);
		
		//Conexion
		$cnx = Conexion::abreConexion($conexion->usuario_bd_contpaq, $conexion->pass_bd_contpaq, $conexion->nombre_bd_contpaq, $conexion->server_bd_contpaq);
		
		for ($i = $periodo; $i <= 12; $i++) {
			
			$filtro  = " 1=1 ";
			$filtro .= "AND id_empresa = '".$empresa."' ";
			$filtro .= "AND id_ejercicio = '".$ejercicio."' ";
			$filtro .= "AND id_periodo = '".$i."' ";
			
			//crearEjercisios
			$ImpuestoPeriodoPm = My_Comun::obtenerFiltro("ImpuestoPeriodoPm", $filtro, "id_periodo ASC");
			
			// SQL
			$sql = "SELECT SUM(ImpBase) AS average
			          FROM DevolucionesIVA d
			    INNER JOIN Polizas p 
			               ON d.IdPoliza = p.Id
			         WHERE p.ejercicio = '".$ejercicio_obj->nombre."' 
			               AND periodo = '".$i."'
			               AND d.PorIVA = '0'
			               AND d.ExentoIVA = '0'";
			
			$query = sqlsrv_query($cnx, $sql) OR print_r(sqlsrv_errors());
			$query = sqlsrv_fetch_array($query);
			
			// Actualizar campo de la base d edatos.
			$data                 = array();
			$data['id']           = $ImpuestoPeriodoPm[0]->id;
			$data['id_empresa']   = $empresa;
			$data['id_ejercicio'] = $ejercicio;
			$data['id_periodo']   = $i;
			$data['iva_gravable_0'] = $query['average'];
			
			if ($ImpuestoPeriodoPm[0]->id) {
				My_Comun::Guardar("ImpuestoPeriodoPm", $data, $data['id'], "");
			}
		}
		
		echo "<pre>";
		print_r('iva_gravable_0 Actualizado correctamente');
		echo "</pre>";
		
		sqlsrv_close($cnx);
	}
	
	/**
	 * @function     iva_ingresos_gravable_exenta
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function iva_ingresos_gravable_exenta($empresa, $ejercicio, $periodo) {
		
		$conexion      = My_Comun::obtener("Empresa", "id", $empresa);
		$ejercicio_obj = My_Comun::obtener("Ejercicio", "id", $ejercicio);
		
		//Conexion
		$cnx = Conexion::abreConexion($conexion->usuario_bd_contpaq, $conexion->pass_bd_contpaq, $conexion->nombre_bd_contpaq, $conexion->server_bd_contpaq);
		
		for ($i = $periodo; $i <= 12; $i++) {
			
			$filtro  = " 1=1 ";
			$filtro .= "AND id_empresa = '".$empresa."' ";
			$filtro .= "AND id_ejercicio = '".$ejercicio."' ";
			$filtro .= "AND id_periodo = '".$i."' ";
			
			//crearEjercisios
			$ImpuestoPeriodoPm = My_Comun::obtenerFiltro("ImpuestoPeriodoPm", $filtro, "id_periodo ASC");
			
			// SQL
			$sql = "SELECT SUM(ImpBase) AS average
			          FROM DevolucionesIVA d
			    INNER JOIN Polizas p 
			               ON d.IdPoliza = p.Id
			         WHERE p.ejercicio = '".$ejercicio_obj->nombre."' 
			               AND periodo = '".$i."'
			               AND d.PorIVA = '0'
			               AND d.ExentoIVA = '1'";
			
			$query = sqlsrv_query($cnx, $sql) OR print_r(sqlsrv_errors());
			$query = sqlsrv_fetch_array($query);
			
			// Actualizar campo de la base d edatos.
			$data                 = array();
			$data['id']           = $ImpuestoPeriodoPm[0]->id;
			$data['id_empresa']   = $empresa;
			$data['id_ejercicio'] = $ejercicio;
			$data['id_periodo']   = $i;
			$data['iva_gravable_exento'] = $query['average'];
			
			if ($ImpuestoPeriodoPm[0]->id) {
				My_Comun::Guardar("ImpuestoPeriodoPm", $data, $data['id'], "");
			}
		}
		
		echo "<pre>";
		print_r('iva_gravable_exento Actualizado correctamente');
		echo "</pre>";
		
		sqlsrv_close($cnx);
	}
	
	/**
	 * @function     iva_retenido_mes
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function iva_retenido_mes($empresa, $ejercicio, $periodo) {
		
		$conexion      = My_Comun::obtener("Empresa", "id", $empresa);
		$ejercicio_obj = My_Comun::obtener("Ejercicio", "id", $ejercicio);
		
		//Conexion
		$cnx = Conexion::abreConexion($conexion->usuario_bd_contpaq, $conexion->pass_bd_contpaq, $conexion->nombre_bd_contpaq, $conexion->server_bd_contpaq);
		
		for ($i = $periodo; $i <= 12; $i++) {
			
			$filtro  = " 1=1 ";
			$filtro .= "AND id_empresa = '".$empresa."' ";
			$filtro .= "AND id_ejercicio = '".$ejercicio."' ";
			$filtro .= "AND id_periodo = '".$i."' ";
			
			//crearEjercisios
			$ImpuestoPeriodoPm = My_Comun::obtenerFiltro("ImpuestoPeriodoPm", $filtro, "id_periodo ASC");
			
			// SQL
			$sql = "SELECT SUM(IVARetenido) AS average
			          FROM DevolucionesIVA d
			    INNER JOIN Polizas p 
			               ON d.IdPoliza = p.Id
			         WHERE p.ejercicio = '".$ejercicio_obj->nombre."' 
			               AND periodo = '".$i."'";
			
			$query = sqlsrv_query($cnx, $sql) OR print_r(sqlsrv_errors());
			$query = sqlsrv_fetch_array($query);
			
			// Actualizar campo de la base d edatos.
			$data                 = array();
			$data['id']           = $ImpuestoPeriodoPm[0]->id;
			$data['id_empresa']   = $empresa;
			$data['id_ejercicio'] = $ejercicio;
			$data['id_periodo']   = $i;
			$data['iva_retenido_mes'] = $query['average'];
			
			if ($ImpuestoPeriodoPm[0]->id) {
				My_Comun::Guardar("ImpuestoPeriodoPm", $data, $data['id'], "");
			}
		}
		
		echo "<pre>";
		print_r('iva_retenido_mes Actualizado correctamente');
		echo "</pre>";
		
		sqlsrv_close($cnx);
	}
	
	/**
	 * @function     iva_retenido_mes_anterior
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function iva_retenido_mes_anterior($empresa, $ejercicio, $periodo) {
		
		$conexion      = My_Comun::obtener("Empresa", "id", $empresa);
		$ejercicio_obj = My_Comun::obtener("Ejercicio", "id", $ejercicio);
		
		//Conexion
		$cnx = Conexion::abreConexion($conexion->usuario_bd_contpaq, $conexion->pass_bd_contpaq, $conexion->nombre_bd_contpaq, $conexion->server_bd_contpaq);
		
		for ($i = $periodo; $i <= 12; $i++) {
			
			$filtro  = " 1=1 ";
			$filtro .= "AND id_empresa = '".$empresa."' ";
			$filtro .= "AND id_ejercicio = '".$ejercicio."' ";
			$filtro .= "AND id_periodo = '".$i."' ";
			
			//crearEjercisios
			$ImpuestoPeriodoPm = My_Comun::obtenerFiltro("ImpuestoPeriodoPm", $filtro, "id_periodo ASC");
			
			if ($i == 1) {
				// SQL
				$sql = "SELECT SUM(IVARetenido) AS average
						  FROM DevolucionesIVA d
					INNER JOIN Polizas p 
							   ON d.IdPoliza = p.Id
						 WHERE p.ejercicio = '".($ejercicio_obj->nombre - 1)."' 
							   AND periodo = '12'";
			}
			else {
				// SQL
				$sql = "SELECT SUM(IVARetenido) AS average
						  FROM DevolucionesIVA d
					INNER JOIN Polizas p 
							   ON d.IdPoliza = p.Id
						 WHERE p.ejercicio = '".$ejercicio_obj->nombre."' 
							   AND periodo = '".($i - 1)."'";
			}
			
			$query = sqlsrv_query($cnx, $sql) OR print_r(sqlsrv_errors());
			$query = sqlsrv_fetch_array($query);
			
			// Actualizar campo de la base d edatos.
			$data                 = array();
			$data['id']           = $ImpuestoPeriodoPm[0]->id;
			$data['id_empresa']   = $empresa;
			$data['id_ejercicio'] = $ejercicio;
			$data['id_periodo']   = $i;
			$data['iva_retenido_mes_anterior'] = $query['average'];
			
			if ($ImpuestoPeriodoPm[0]->id) {
				My_Comun::Guardar("ImpuestoPeriodoPm", $data, $data['id'], "");
			}
		}
		
		echo "<pre>";
		print_r('iva_retenido_mes_anterior Actualizado correctamente');
		echo "</pre>";
		
		sqlsrv_close($cnx);
	}
	
	/**
	 * @function     set_sum_cuenta_impuestos_pagar
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function	set_sum_cuenta_impuestos_pagar ( $empresa, $ejercicio, $periodo, $cuenta, $tipo, $impuesto ) {
		
		if ( $cuenta > 0 AND $tipo > 0 AND $impuesto != '' ) {
			
			//Conexion
			$conexion = My_Comun::obtener("Empresa", "id", $empresa);
			$cnx      = Conexion::abreConexion($conexion->usuario_bd_contpaq, $conexion->pass_bd_contpaq, $conexion->nombre_bd_contpaq, $conexion->server_bd_contpaq);
			
			for ($i = $periodo; $i <= 12; $i++) {
				
				$filtro  = " 1=1 ";
				$filtro .= "AND id_empresa = '".$empresa."' ";
				$filtro .= "AND id_ejercicio = '".$ejercicio."' ";
				$filtro .= "AND id_periodo = '".$i."' ";
				
				//Get id Impuesto Periodo PM
				$ImpuestoPeriodoPm = My_Comun::obtenerFiltro("ImpuestoPeriodoPm", $filtro, "id_periodo ASC");
				
				//Get nombre Ejercicio
				$ejercicio_obj = My_Comun::obtener("Ejercicio", "id", $ejercicio);
				
				// $query = "
					// SELECT SUM(sc.Importes".$i.") AS average
					  // FROM SaldosCuentas sc
				// INNER JOIN Ejercicios e
						   // ON e.id = sc.Ejercicio
					 // WHERE sc.IdCuenta = '".$cuenta."'
					  // AND e.Ejercicio = '".$ejercicio_obj->nombre."'
					  // AND sc.Tipo = '".$tipo."'";
				
				$query = "	SELECT SUM(sc.Importes".$i.") AS average
								   FROM SaldosCuentas sc
						INNER JOIN Cuentas c
								   ON sc.IdCuenta = c.Id 
						INNER JOIN AgrupadoresSAT a 
								   ON c.IdAgrupadorSAT = a.Id
							 WHERE c.Codigo = '".$cuenta."'
								   AND sc.Ejercicio = (SELECT Id FROM Ejercicios WHERE Ejercicio = '".$ejercicio_obj->nombre."')
								   AND sc.Tipo = '".$tipo."'";
				
				$query = sqlsrv_query($cnx, $query) OR print_r(sqlsrv_errors());
				$query = sqlsrv_fetch_array($query);
				
				// Actualizar campo de la base d edatos.
				$data                 = array();
				$data['id']           = $ImpuestoPeriodoPm[0]->id;
				$data['id_empresa']   = $empresa;
				$data['id_ejercicio'] = $ejercicio;
				$data['id_periodo']   = $i;
				$data[$impuesto]      = $query['average'];
				
				if ($ImpuestoPeriodoPm[0]->id) {
					My_Comun::Guardar("ImpuestoPeriodoPm", $data, $data['id'], "");
				}
			}
			
			echo "<pre>";
			print_r(''.$impuesto.' Actualizado correctamente');
			echo "</pre>";
			
			sqlsrv_close($cnx);
		}
		else {
			echo "<pre>";
			print_r('Cuenta, tipo de saldo o tipo de impuesto no valido');
			echo "</pre>";
		}
	}
	
	/**
	 * @function     exportarAction
	 * @author:      Danny Ramirez
	 * @contact:     danny_ramirez@avansys.com.mx
	 * @description: 
	 * @version:     1.0
	 * @path call:   pago-provisionalpm/index.phtml
	 * @copyright:   Avansys
	 **/
	public function exportarAction() {
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$empresa   = $this->_getParam('empresa_id');
		$ejercicio = $this->_getParam('ejercicio_id');
		
		$get_data  = $this->get_pagos_provisional_pm_data($empresa, $ejercicio);
		
		//Get empresa
		$ac = My_Comun::obtener("Empresa", "id", $empresa);
		$ac = ($ac->tipo_empresa_id == 2) ? false : true;
		
		/**
		 * Estatus congelado
		 **/
		$query = "
			SELECT id_periodo 
			       FROM impuesto_periodo_pm 
			 WHERE id_empresa = ".$empresa." 
			       AND id_ejercicio = ".$ejercicio."
			       AND status = 1";
		$status = My_Comun::crearQuery("ImpuestoPeriodoPm", $query);
		
		$periodos = array();
		$index    = 1;
		
		foreach ($status AS $periodo) {
			$periodos[$index] = $periodo['id_periodo'];
			$index++;
		}
		
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
		
		if ( $get_data ) {
			$registro = $this->changekeyname($get_data);
			
			ini_set("memory_limit", "130M");
			ini_set('max_execution_time', 0);
			
			/**
			 * Excel
			 **/
			$objPHPExcel = new My_PHPExcel_Excel();
			
			//Titulo color blanco
			$styleTitle = array(
							'font'  => array(
								'bold'  => true,
								'color' => array('rgb' => 'FFFFFF'),
								'size'  => 12,
								'name'  => 'Arial'
							)
						);
			
			$styleHeaders = array(
							'font'  => array(
								'bold'  => true,
								'color' => array('rgb' => '000000'),
								'size'  => 11,
								'name'  => 'Arial'
							)
						);
			
			$backgroundTitle =  array(
									'fill' => array (
										'type' => PHPExcel_Style_Fill::FILL_SOLID,
										'color' => array('rgb' => '009999')
									)
								);
			
			$backgroundFrozen =  array(
									'fill' => array (
										'type' => PHPExcel_Style_Fill::FILL_SOLID,
										'color' => array('rgb' => '5BC0DE')
									)
								);
			
			$backgroundNow =  array(
									'fill' => array (
										'type' => PHPExcel_Style_Fill::FILL_SOLID,
										'color' => array('rgb' => 'D9534F')
									)
								);
			
			$backgroundAverage =  array(
									'fill' => array (
										'type' => PHPExcel_Style_Fill::FILL_SOLID,
										'color' => array('rgb' => '4B7BAA')
									),
									'font'  => array(
										'bold'  => true,
										'color' => array('rgb' => 'FFFFFF')
									)
								);
			
			$backgroundGrey =  array(
									'fill' => array (
										'type' => PHPExcel_Style_Fill::FILL_SOLID,
										'color' => array('rgb' => 'E6E6E6')
									)
								);
			
			$stylePrincipal = array(
								'font'  => array(
									'bold'  => true,
									'color' => array('rgb' => '009999'),
									'size'  => 14,
									'name'  => 'Arial'
								)
							);
			
			// Fuente y tamaño de fuente
			$objPHPExcel->objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
			$objPHPExcel->objPHPExcel->getDefaultStyle()->getFont()->setSize(10);
			$objPHPExcel->objPHPExcel->getActiveSheet()->getDefaultStyle()->getAlignment()->setWrapText(true);
			$objPHPExcel->objPHPExcel->getActiveSheet()->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			
			// Logotipo
			$objDrawing = new PHPExcel_Worksheet_Drawing();
				$objDrawing->setPath('./imagenes/logo.png');
				$objDrawing->setWidth(100);
				$objDrawing->setHeight(100);
				$objDrawing->setWorksheet($objPHPExcel->objPHPExcel->getActiveSheet());
			
			// Titulo
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B4', 'Reporte de Impuestos Pm');
			$objPHPExcel->objPHPExcel->getActiveSheet()->mergeCells('B4:P4');
			$objPHPExcel->objPHPExcel->getActiveSheet()->getStyle('B4')->applyFromArray($stylePrincipal);
			$objPHPExcel->objPHPExcel->getActiveSheet()->getStyle('B4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->objPHPExcel->getActiveSheet()->getRowDimension('4')->setRowHeight(20);
			
			// Primer titulo
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B8', 'ISR');
			$objPHPExcel->objPHPExcel->getActiveSheet()->mergeCells('B8:P8');
			$objPHPExcel->objPHPExcel->getActiveSheet()->getStyle('B8')->applyFromArray($styleTitle);
			$objPHPExcel->objPHPExcel->getActiveSheet()->getStyle('B8')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$objPHPExcel->objPHPExcel->getActiveSheet()->getStyle('B8')->applyFromArray($backgroundTitle);
			
			// Titulos
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B9', 'Concepto');
			$objPHPExcel->objPHPExcel->getActiveSheet()->mergeCells('B9:C9');
			$objPHPExcel->objPHPExcel->getActiveSheet()->getStyle('B9:P9')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('D9', 'Enero');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('E9', 'Febrero');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('F9', 'Marzo');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('G9', 'Abril');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('H9', 'Mayo');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('I9', 'Junio');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('J9', 'Julio');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('K9', 'Agosto');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('L9', 'Septiembre');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('M9', 'Octubre');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('N9', 'Noviembre');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('O9', 'Diciembre');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('P9', 'Total');
			$objPHPExcel->objPHPExcel->getActiveSheet()->getStyle('B9:P9')->applyFromArray($styleHeaders);
			
			$columns = array (
					'1' => 'D',
					'2' => 'E',
					'3' => 'F',
					'4' => 'G',
					'5' => 'H',
					'6' => 'I',
					'7' => 'J',
					'8' => 'K',
					'9' => 'L',
					'10' => 'M',
					'11' => 'N',
					'12' => 'O',
				);
				
			$i = 10;
			
			//Ingresos del Periodo
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Ingresos del Periodo');
			$total = 0;
			foreach ($columns AS $periodo => $letter) {
				$total += $this->get_iva_total_ingresos($empresa, $ejercicio, $periodo, $ac);
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($this->get_iva_total_ingresos($empresa, $ejercicio, $periodo, $ac), 2));
			}
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('P'.$i, number_format($total, 2));
			$i++;
			
			//Anticipo de Clientes
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '+');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Anticipo de Clientes');
			foreach ($columns AS $periodo => $letter) {
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($this->get_iva_anticipo_clientes($empresa, $ejercicio, $periodo, $ac), 2));
			}
			$i++;
			
			//Otros Ingresos
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '+');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Otros Ingresos');
			foreach ($columns AS $periodo => $letter) {
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($registro[$periodo]['isr_otros_ingresos'], 2));
			}
			$i++;
			
			//Productos Financieros
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '+');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Productos Financieros');
			foreach ($columns AS $periodo => $letter) {
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($registro[$periodo]['isr_producto_financiero'], 2));
			}
			$i++;
			
			//Ingresos Nominales del periodo
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '=');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Ingresos Nominales del periodo');
			$total = 0;
			foreach ($columns AS $periodo => $letter) {
				$total += $this->get_isr_ingresos_nominales_periodo($empresa, $ejercicio, $periodo);
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($this->get_isr_ingresos_nominales_periodo($empresa, $ejercicio, $periodo), 2));
			}
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('P'.$i, number_format($total, 2));
			$objPHPExcel->objPHPExcel->getActiveSheet()->getStyle('B'.$i.':P'.$i)->applyFromArray($backgroundAverage);
			$i++;
			
			//Ingresos Acumulable
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Ingresos Acumulable');
			foreach ($columns AS $periodo => $letter) {
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($this->get_isr_ingresos_acumulable($empresa, $ejercicio, $periodo), 2));
			}
			$objPHPExcel->objPHPExcel->getActiveSheet()->getStyle('B'.$i.':P'.$i)->applyFromArray($backgroundGrey);
			$i++;
			
			//Coeficiente de Utilidad
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, 'x');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Coeficiente de Utilidad');
			foreach ($columns AS $periodo => $letter) {
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($this->get_coeficiente_utilidad($empresa, $ejercicio, $periodo), 2));
			}
			$i++;
			
			//Utilidad Fiscal para el Pago Provisional
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '=');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Utilidad Fiscal para el Pago Provisional');
			foreach ($columns AS $periodo => $letter) {
				$usr_utilidad_fiscal = $this->get_isr_ingresos_acumulable($empresa, $ejercicio, $periodo) * $this->get_coeficiente_utilidad($empresa, $ejercicio, $periodo);
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($usr_utilidad_fiscal, 2));
			}
			$objPHPExcel->objPHPExcel->getActiveSheet()->getStyle('B'.$i.':P'.$i)->applyFromArray($backgroundAverage);
			$i++;
			
			//Ingresos Acumulables Inventarios
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '+');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Ingresos Acumulables Inventarios');
			foreach ($columns AS $periodo => $letter) {
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($registro[$periodo]['isr_acumulables_inventario'], 2));
			}
			$i++;
			
			//Anticipos o Rendimentos Distribuidos
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '-');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Anticipos o Rendimentos Distribuidos');
			foreach ($columns AS $periodo => $letter) {
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($registro[$periodo]['isr_anticipos_distribuidos'], 2));
			}
			$i++;
			
			//Deduccion Inmediata de Inversiones
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '-');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Deduccion Inmediata de Inversiones');
			foreach ($columns AS $periodo => $letter) {
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($registro[$periodo]['isr_deduccion_inversiones'], 2));
			}
			$i++;
			
			//PTU Pagada
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '-');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'PTU Pagada');
			foreach ($columns AS $periodo => $letter) {
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($registro[$periodo]['isr_ptu_pagada'], 2));
			}
			$i++;
			
			//Perdida de Ejercicios Anteriores
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '-');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Perdida de Ejercicios Anteriores');
			foreach ($columns AS $periodo => $letter) {
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($registro[$periodo]['isr_perdida_anteriores'], 2));
			}
			$i++;
			
			//Base Para el Pago Provisional
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '=');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Base Para el Pago Provisional');
			foreach ($columns AS $periodo => $letter) {
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($this->get_isr_base_provisional($empresa, $ejercicio, $periodo), 2));
			}
			$objPHPExcel->objPHPExcel->getActiveSheet()->getStyle('B'.$i.':P'.$i)->applyFromArray($backgroundAverage);
			$i++;
			
			//Tasa
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, 'x');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Tasa');
			$query_coe = "
				SELECT tasa 
				  FROM empresa
				 WHERE id = '".$empresa."'";
			
			$tasa = My_Comun::crearQuery('Empresa', $query_coe);
			$tasa = $tasa[0]['tasa'];
			foreach ($columns AS $periodo => $letter) {
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, $tasa.'%');
			}
			$i++;
			
			//Impuestos Por Pagar
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '=');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Impuestos Por Pagar');
			foreach ($columns AS $periodo => $letter) {
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($this->get_isr_impuesto_pagar($empresa, $ejercicio, $periodo), 2));
			}
			$i++;
			
			//Pagos Provisionales Periodos Anteriores
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '-');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Pagos Provisionales Periodos Anteriores');
			foreach ($columns AS $periodo => $letter) {
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($this->get_pagos_provisionales_periodos_anteriores($empresa, $ejercicio, $periodo), 2));
			}
			$i++;
			
			//Isr Retenido
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '-');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Isr Retenido');
			foreach ($columns AS $periodo => $letter) {
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($this->get_isr_retenido($empresa, $ejercicio, $periodo), 2));
			}
			$i++;
			
			//Impuestos Por Pagar 2
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '=');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Impuestos Por Pagar');
			$total = 0;
			foreach ($columns AS $periodo => $letter) {
				$total += $this->get_isr_impuesto_pagar_2($empresa, $ejercicio, $periodo);
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($this->get_isr_impuesto_pagar_2($empresa, $ejercicio, $periodo), 2));
			}
			$objPHPExcel->objPHPExcel->getActiveSheet()->getStyle('B'.$i.':P'.$i)->applyFromArray($backgroundAverage);
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('P'.$i, number_format($total, 2));
			$i++;
			
			//Compensaciones
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Compensaciones');
			foreach ($columns AS $periodo => $letter) {
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($registro[$periodo]['isr_compensacion'], 2));
			}
			$i++;
			
			//Pago Provisional de Isr
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Pago Provisional de Isr');
			foreach ($columns AS $periodo => $letter) {
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($this->get_isr_pago_provisional($empresa, $ejercicio, $periodo), 2));
			}
			$i++;
			
			//Impuesto a Favor
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Impuesto a Favor');
			foreach ($columns AS $periodo => $letter) {
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($this->get_isr_impuesto_favor($empresa, $ejercicio, $periodo), 2));
			}
			$i++;
			
			//Impuesto Acumulado
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Impuesto Acumulado');
			foreach ($columns AS $periodo => $letter) {
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, '');
			}
			$i++;
			$i++;
			
			// Segundo titulo
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, 'IVA');
			$objPHPExcel->objPHPExcel->getActiveSheet()->mergeCells('B'.$i.':P'.$i);
			$objPHPExcel->objPHPExcel->getActiveSheet()->getStyle('B'.$i)->applyFromArray($styleTitle);
			$objPHPExcel->objPHPExcel->getActiveSheet()->getStyle('B'.$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$objPHPExcel->objPHPExcel->getActiveSheet()->getStyle('B'.$i)->applyFromArray($backgroundTitle);
			$i++;
			
			//Ingresos Gravados 16%
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Ingresos Gravados 16%');
			$total = 0;
			foreach ($columns AS $periodo => $letter) {
				$total += $registro[$periodo]['iva_ingresos_16'];
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($registro[$periodo]['iva_ingresos_16'], 2));
			}
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('P'.$i, number_format($total, 2));
			$i++;
			
			//Ingresos Gravados 11%
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '+');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Ingresos Gravados 16%');
			$total = 0;
			foreach ($columns AS $periodo => $letter) {
				$total += $registro[$periodo]['iva_ingresos_11'];
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($registro[$periodo]['iva_ingresos_11'], 2));
			}
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('P'.$i, number_format($total, 2));
			$i++;
			
			//Ingresos Gravados 0%
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '+');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Ingresos Gravados 0%');
			$total = 0;
			foreach ($columns AS $periodo => $letter) {
				$total += $registro[$periodo]['iva_ingresos_0'];
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($registro[$periodo]['iva_ingresos_0'], 2));
			}
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('P'.$i, number_format($total, 2));
			$i++;
			
			//Ingresos Exentos
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '+');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Ingresos Exentos');
			$total = 0;
			foreach ($columns AS $periodo => $letter) {
				$total += $registro[$periodo]['iva_ingresos_exentos'];
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($registro[$periodo]['iva_ingresos_exentos'], 2));
			}
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('P'.$i, number_format($total, 2));
			$i++;
			
			//Otras Bases
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '+');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Otras Bases');
			$total = 0;
			foreach ($columns AS $periodo => $letter) {
				$total += $registro[$periodo]['iva_ingresos_otra_base'];
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($registro[$periodo]['iva_ingresos_otra_base'], 2));
			}
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('P'.$i, number_format($total, 2));
			$i++;
			
			//Total Ingresos
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '=');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Total Ingresos');
			$total = 0;
			foreach ($columns AS $periodo => $letter) {
				$total += $this->get_iva_total_ingresos($empresa, $ejercicio, $periodo);
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($this->get_iva_total_ingresos($empresa, $ejercicio, $periodo), 2));
			}
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('P'.$i, number_format($total, 2));
			$objPHPExcel->objPHPExcel->getActiveSheet()->getStyle('B'.$i.':P'.$i)->applyFromArray($backgroundAverage);
			$i++;
			
			//Iva Trasladado al 16%
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '+');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Iva Trasladado al 16%');
			$total = 0;
			foreach ($columns AS $periodo => $letter) {
				$total += ($registro[$periodo]['iva_ingresos_16'] * 0.16);
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format(($registro[$periodo]['iva_ingresos_16'] * 0.16), 2));
			}
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('P'.$i, number_format($total, 2));
			$i++;
			
			//Total de Iva Trasladado
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '=');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Total de Iva Trasladado');
			$total = 0;
			foreach ($columns AS $periodo => $letter) {
				$total += ($registro[$periodo]['iva_ingresos_16'] * 0.16);
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format(($registro[$periodo]['iva_ingresos_16'] * 0.16), 2));
			}
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('P'.$i, number_format($total, 2));
			$objPHPExcel->objPHPExcel->getActiveSheet()->getStyle('B'.$i.':P'.$i)->applyFromArray($backgroundAverage);
			$i++;
			
			//Base Gravable al 16%
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '+');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Base Gravable al 16%');
			$total = 0;
			foreach ($columns AS $periodo => $letter) {
				$total += $registro[$periodo]['iva_gravable_16'];
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($registro[$periodo]['iva_gravable_16'], 2));
			}
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('P'.$i, number_format($total, 2));
			$i++;
			
			//Base Gravable al 11%
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '+');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Base Gravable al 11%');
			$total = 0;
			foreach ($columns AS $periodo => $letter) {
				$total += $registro[$periodo]['iva_gravable_11'];
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($registro[$periodo]['iva_gravable_11'], 2));
			}
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('P'.$i, number_format($total, 2));
			$i++;
			
			//Base Gravable al 0%
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '+');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Base Gravable al 0%');
			$total = 0;
			foreach ($columns AS $periodo => $letter) {
				$total += $registro[$periodo]['iva_gravable_0'];
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($registro[$periodo]['iva_gravable_0'], 2));
			}
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('P'.$i, number_format($total, 2));
			$i++;
			
			//Base Gravable Exenta
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '+');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Base Gravable Exenta');
			$total = 0;
			foreach ($columns AS $periodo => $letter) {
				$total += $registro[$periodo]['iva_gravable_exento'];
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($registro[$periodo]['iva_gravable_exento'], 2));
			}
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('P'.$i, number_format($total, 2));
			$i++;
			
			//Total Base Acreditable
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '=');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Total Base Acreditable');
			$total = 0;
			foreach ($columns AS $periodo => $letter) {
				$total += $this->get_total_base_acreditable($empresa, $ejercicio, $periodo);
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($this->get_total_base_acreditable($empresa, $ejercicio, $periodo), 2));
			}
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('P'.$i, number_format($total, 2));
			$objPHPExcel->objPHPExcel->getActiveSheet()->getStyle('B'.$i.':P'.$i)->applyFromArray($backgroundAverage);
			$i++;
			
			//IVA Acreditable al 16%
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '+');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'IVA Acreditable al 16%');
			$total = 0;
			foreach ($columns AS $periodo => $letter) {
				$total += ($registro[$periodo]['iva_gravable_16'] * 0.16);
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format(($registro[$periodo]['iva_gravable_16'] * 0.16), 2));
			}
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('P'.$i, number_format($total, 2));
			$i++;
			
			//IVA Acreditable al 11%
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '+');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'IVA Acreditable al 11%');
			$total = 0;
			foreach ($columns AS $periodo => $letter) {
				$total += ($registro[$periodo]['iva_gravable_11'] * 0.11);
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format(($registro[$periodo]['iva_gravable_11'] * 0.11), 2));
			}
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('P'.$i, number_format($total, 2));
			$i++;
			
			//Total IVA Acreditable
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '=');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Total IVA Acreditable');
			$total = 0;
			foreach ($columns AS $periodo => $letter) {
				$total += (($registro[$periodo]['iva_gravable_16'] * 0.16) + ($registro[$periodo]['iva_gravable_11'] * 0.11));
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format((($registro[$periodo]['iva_gravable_16'] * 0.16) + ($registro[$periodo]['iva_gravable_11'] * 0.11)), 2));
			}
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('P'.$i, number_format($total, 2));
			$objPHPExcel->objPHPExcel->getActiveSheet()->getStyle('B'.$i.':P'.$i)->applyFromArray($backgroundAverage);
			$i++;
			
			//Coeficiente de Acreditamiento
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Coeficiente de Acreditamiento');
			foreach ($columns AS $periodo => $letter) {
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($this->get_coeficiente_acreditamiento($empresa, $ejercicio, $periodo), 4));
			}
			$i++;
			
			//IVA Acreditable
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'IVA Acreditable');
			foreach ($columns AS $periodo => $letter) {
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($this->get_iva_acreditable($empresa, $ejercicio, $periodo), 2));
			}
			$i++;
			
			//IVA Retenido del Mes
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'IVA Retenido del Mes');
			foreach ($columns AS $periodo => $letter) {
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($registro[$periodo]['iva_retenido_mes'], 2));
			}
			$i++;
			
			//IVA Retenido del Mes Anterior
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'IVA Retenido del Mes Anterior');
			foreach ($columns AS $periodo => $letter) {
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($registro[$periodo]['iva_retenido_mes_anterior'], 2));
			}
			$i++;
			
			//IVA Acreditable
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'IVA Acreditable');
			$total = 0;
			foreach ($columns AS $periodo => $letter) {
				$total += $this->get_iva_acreditable_2($empresa, $ejercicio, $periodo);
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($this->get_iva_acreditable_2($empresa, $ejercicio, $periodo), 2));
			}
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('P'.$i, number_format($total, 2));
			$objPHPExcel->objPHPExcel->getActiveSheet()->getStyle('B'.$i.':P'.$i)->applyFromArray($backgroundAverage);
			$i++;
			
			// Segundo titulo
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, 'Determinación IVA');
			$objPHPExcel->objPHPExcel->getActiveSheet()->mergeCells('B'.$i.':P'.$i);
			$objPHPExcel->objPHPExcel->getActiveSheet()->getStyle('B'.$i)->applyFromArray($backgroundGrey);
			$objPHPExcel->objPHPExcel->getActiveSheet()->getStyle('B'.$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$i++;
			
			//IVA a Cargo del Periodo
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'IVA a Cargo del Periodo');
			foreach ($columns AS $periodo => $letter) {
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($this->get_iva_cargo_periodo($empresa, $ejercicio, $periodo), 2));
			}
			$objPHPExcel->objPHPExcel->getActiveSheet()->getStyle('B'.$i.':P'.$i)->applyFromArray($backgroundFrozen);
			$i++;
			
			//IVA a Favor del Periodo
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'IVA a Favor del Periodo');
			foreach ($columns AS $periodo => $letter) {
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($this->get_iva_favor_periodo($empresa, $ejercicio, $periodo), 2));
			}
			$objPHPExcel->objPHPExcel->getActiveSheet()->getStyle('B'.$i.':P'.$i)->applyFromArray($backgroundFrozen);
			$i++;
			
			//Compensaciones
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Compensaciones');
			$total = 0;
			foreach ($columns AS $periodo => $letter) {
				$total += $registro[$periodo]['iva_compensaciones'];
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($registro[$periodo]['iva_compensaciones'], 2));
			}
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('P'.$i, number_format($total, 2));
			$objPHPExcel->objPHPExcel->getActiveSheet()->getStyle('B'.$i.':P'.$i)->applyFromArray($backgroundFrozen);
			$i++;
			
			//IVA a Cargo del Periodo
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'IVA a Cargo del Periodo');
			$total = 0;
			foreach ($columns AS $periodo => $letter) {
				$total += $this->get_iva_cargo_periodo_2($empresa, $ejercicio, $periodo);
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($this->get_iva_cargo_periodo_2($empresa, $ejercicio, $periodo), 2));
			}
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('P'.$i, number_format($total, 2));
			$objPHPExcel->objPHPExcel->getActiveSheet()->getStyle('B'.$i.':P'.$i)->applyFromArray($backgroundFrozen);
			$i++;
			
			//IVA a Favor del Acumulado
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'IVA a Favor del Acumulado');
			$total = 0;
			foreach ($columns AS $periodo => $letter) {
				$total += $this->get_iva_favor_periodo_2($empresa, $ejercicio, $periodo);
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($this->get_iva_favor_periodo_2($empresa, $ejercicio, $periodo), 2));
			}
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('P'.$i, number_format($total, 2));
			$objPHPExcel->objPHPExcel->getActiveSheet()->getStyle('B'.$i.':P'.$i)->applyFromArray($backgroundFrozen);
			$i++;
			$i++;
			
			// Tercer titulo
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, 'IMPUESTOS POR PAGAR');
			$objPHPExcel->objPHPExcel->getActiveSheet()->mergeCells('B'.$i.':P'.$i);
			$objPHPExcel->objPHPExcel->getActiveSheet()->getStyle('B'.$i)->applyFromArray($styleTitle);
			$objPHPExcel->objPHPExcel->getActiveSheet()->getStyle('B'.$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$objPHPExcel->objPHPExcel->getActiveSheet()->getStyle('B'.$i)->applyFromArray($backgroundTitle);
			$i++;
			
			//ISR
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'ISR');
			$total = 0;
			foreach ($columns AS $periodo => $letter) {
				$total += $this->get_isr_pago_provisional($empresa, $ejercicio, $periodo);
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($this->get_isr_pago_provisional($empresa, $ejercicio, $periodo), 2));
			}
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('P'.$i, number_format($total, 2));
			$i++;
			
			//IVA
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '+');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'IVA');
			$total = 0;
			foreach ($columns AS $periodo => $letter) {
				$total += $this->get_iva_cargo_periodo_2($empresa, $ejercicio, $periodo);
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($this->get_iva_cargo_periodo_2($empresa, $ejercicio, $periodo), 2));
			}
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('P'.$i, number_format($total, 2));
			$i++;
			
			//IEPS
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'IEPS');
			$i++;
			
			//Retenci&oacute;n Salarios
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '+');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Retencion de salarios');
			$total = 0;
			foreach ($columns AS $periodo => $letter) {
				$total += $registro[$periodo]['retencion_salarios'];
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($registro[$periodo]['retencion_salarios'], 2));
			}
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('P'.$i, number_format($total, 2));
			$i++;
			
			//Retenci&oacute;n ISR honorarios
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '+');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Retencion ISR honorarios');
			$total = 0;
			foreach ($columns AS $periodo => $letter) {
				$total += $registro[$periodo]['retencion_isr_honorarios'];
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($registro[$periodo]['retencion_isr_honorarios'], 2));
			}
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('P'.$i, number_format($total, 2));
			$i++;
			
			//Retenci&oacute;n Asimilados
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '+');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Retencion Asimilados');
			$total = 0;
			foreach ($columns AS $periodo => $letter) {
				$total += $registro[$periodo]['retencion_asimilados'];
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($registro[$periodo]['retencion_asimilados'], 2));
			}
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('P'.$i, number_format($total, 2));
			$i++;
			
			//Retenci&oacute;n Dividendos
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '+');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Retencion Dividendos');
			foreach ($columns AS $periodo => $letter) {
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($registro[$periodo]['retencion_dividendos'], 2));
			}
			$i++;
			
			//Retenci&oacute;n Intereses
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '+');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Retencion Intereses');
			foreach ($columns AS $periodo => $letter) {
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($registro[$periodo]['retencion_intereses'], 2));
			}
			$i++;
			
			//Retenci&oacute;n Pagos al Extranjero
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '+');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Retencion Pagos al Extranjero');
			$total = 0;
			foreach ($columns AS $periodo => $letter) {
				$total += $registro[$periodo]['retencion_pagos_extranjero'];
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($registro[$periodo]['retencion_pagos_extranjero'], 2));
			}
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('P'.$i, number_format($total, 2));
			$i++;
			
			//Retenci&oacute;n Venta de Acciones
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '+');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Retencion Venta de Acciones');
			foreach ($columns AS $periodo => $letter) {
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($registro[$periodo]['retencion_venta_acciones'], 2));
			}
			$i++;
			
			//Retenci&oacute;n Venta de Partes Sociales
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '+');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Retencion Venta de Partes Sociales');
			foreach ($columns AS $periodo => $letter) {
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($registro[$periodo]['retencion_venta_partes_sociales'], 2));
			}
			$i++;
			
			//Retenci&oacute;n ISR Arrendamiento
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '+');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Retencion ISR Arrendamiento');
			$total = 0;
			foreach ($columns AS $periodo => $letter) {
				$total += $registro[$periodo]['retencion_isr_arrendamiento'];
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($registro[$periodo]['retencion_isr_arrendamiento'], 2));
			}
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('P'.$i, number_format($total, 2));
			$i++;
			
			//Retenci&oacute;n IVA
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '+');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Retencion IVA');
			$total = 0;
			foreach ($columns AS $periodo => $letter) {
				$total += $registro[$periodo]['iva_retenido_mes'];
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($registro[$periodo]['iva_retenido_mes'], 2));
			}
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('P'.$i, number_format($total, 2));
			$i++;
			
			//Total Impuestos
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '=');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Total Impuestos');
			$total = 0;
			foreach ($columns AS $periodo => $letter) {
				$total += $this->get_total_impuestos($empresa, $ejercicio, $periodo);
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($this->get_total_impuestos($empresa, $ejercicio, $periodo), 2));
			}
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('P'.$i, number_format($total, 2));
			$i++;
			
			//Subsidio al Empleo
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '-');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Subsidio al Empleo');
			$total = 0;
			foreach ($columns AS $periodo => $letter) {
				$total += $registro[$periodo]['subsidio_empleo'];
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($registro[$periodo]['subsidio_empleo'], 2));
			}
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('P'.$i, number_format($total, 2));
			$i++;
			
			//Compensaciones
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Compensaciones');
			$total = 0;
			foreach ($columns AS $periodo => $letter) {
				$total += $registro[$periodo]['compensaciones_otros'];
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($registro[$periodo]['compensaciones_otros'], 2));
			}
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('P'.$i, number_format($total, 2));
			$i++;
			
			//Impuestos Por Pagar
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '=');
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Impuestos Por Pagar');
			$total = 0;
			foreach ($columns AS $periodo => $letter) {
				$total += $this->get_impuestos_pagar($empresa, $ejercicio, $periodo);
				$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue($letter.$i, number_format($this->get_impuestos_pagar($empresa, $ejercicio, $periodo), 2));
			}
			$objPHPExcel->objPHPExcel->getActiveSheet()->setCellValue('P'.$i, number_format($total, 2));
			$i++;
			
			// *********************************** FIN DATA ******************************//
			
			//Ajustar las columnas
			foreach (range('A', 'P') AS $letter) {
				$objPHPExcel->objPHPExcel->getActiveSheet()->getColumnDimension(''.$letter.'')->setAutoSize(true);
			}
			
			//Nombrar hoja
			$objPHPExcel->objPHPExcel->getActiveSheet()->setTitle('ImpuestosPm');
			
			//Guardamos el archivo y forzamos la descarga
			ob_end_clean();
			
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel->objPHPExcel, 'Excel2007');
			
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="ImpuestosPm.xlsx"');
			
			$objWriter->save("php://output");
		}
	}
	
	/*
	public function imprimirAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$filtro=" 1=1 ";

		$empresa=$this->_getParam('empresa_id');
		$ejercicio=$this->_getParam('ejercicio_id');
		
		if($empresa!='')
		{
			$filtro.=" AND (empresa_id = '".$empresa."')  ";
		   
		}//if

		if($ejercicio!='')
		{
			
			 $filtro.=" AND (ejercicio_id = '".$ejercicio."')  ";
			//}//for
		}//if
		
		$registros = My_Comun::obtenerFiltro("SubsidioEmpleo", $filtro);
	   
		$pdf= new My_Fpdf_Pdf();
		
		$pdf->AliasNbPages();
		$pdf->AddPage();

		$pdf->Header("IMPRESIÓN DE SUBSIDIOS AL EMPLEO");

		$pdf->SetFont('Arial','B',11);
		$pdf->SetWidths(array(35, 55, 55, 40));
		$pdf->Row(array('FECHA', 'MONTO', 'REMANENTE', ''), 0, 1);
		
		$pdf->SetFont('Arial','',10);
		
		foreach($registros as $registro) {
			$pdf->Row(
				array (
					$registro->fecha, 
					$registro->monto, 
					$registro->remanente, 
					''
				), 0, 1
			);
			
			$consulta="select se.*,tp.abreviatura as impuesto,se.id_aplicacion_subsidio_empleo as id from aplicacion_subsidio_empleo se inner join tipo_impuestos tp "
				. "on (se.id_tipo_impuesto=tp.id) where id_subsidio_empleo='".$registro->id."'";
		
			$regs= My_Comun::crearQuery('SubsidioEmpleo',$consulta);
			$n_regs=count($regs);
			if($n_regs>0){
					
				for($j=0;$j<count($regs);$j++)
				{
					if($j==0)
					{
						$pdf->Row(array(
						'APLICACIONES',$regs[$j]['fecha_aplicacion'],$regs[$j]['impuesto'],$regs[$j]['monto']
					),0,1);
					}
					else{
						$pdf->Row(array(
						'',$regs[$j]['fecha_aplicacion'],$regs[$j]['impuesto'],$regs[$j]['monto']
					),0,1);
					}
					
				}
			}
		}
		
		ob_end_clean();  //Para evitar Error "Some data has ready been output"
		
		$pdf->Output();
	}*/
	
}//end class

?>