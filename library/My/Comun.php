<?php

/**
 * My_Comun
 **/
class My_Comun {
	
	function __construct(){}
	
	// rutas
	const CORREO       = 'danny_ramirez@avansys.com.mx';
	const EMAIL        = 'envioskaluz@solucionesoftware.com.mx';
	const PASS         = 'Kaluz017';
	const URL_SISTEMA  = 'http://heza.solucionesoftware.com.mx';
	const REPLYTO      = 'no-reply@solucionesoftware.com.mx';
	const PASS_REPLYTO = '';
	const SMTP         = 'mail.solucionesoftware.com.mx';
	const SISTEMA      = 'Heza consultoria integral';
	
	/**
	 * Mensaje
	 **/
	public static function mensaje ($numero) {
	
		switch($numero) {
			case "-5"   : return "El registro no pudo ser guardado porque está duplicado."; break;
			case "1"    : return "El registro fue eliminado"; break;
			case "2"    : return "El registro fue deshabilitado"; break;
			case "3"    : return "Ocurrió un error al tratar de deshabilitar el registro, inténtelo de nuevo"; break;
			case "4"    : return "El registro fue habilitado"; break;
			case "5"    : return "Ocurrió un error al tratar de habilitar el registro, inténtelo de nuevo"; break;
			case "6"    : return "El País que no se puede eliminar porque dependen estados de el"; break;
			case "7"    : return "El registro no pudo ser eliminado ya que tiene información relacionada; por el momento, solo fue DESHABILITADO"; break;
			default     : return "No se encontró la descripción del error. ".$numero; break;    
		}
	}
	
	/**
	 * aleatorio
	 **/
	function aleatorio ($maximo) {
		
		$permitidos = "1234567890abcdefghijklmnopqrstuvwxyz";
		$i = 1;
		$_aleatorio = "";
		
		while($i <= $maximo){
			$_aleatorio .= $permitidos{mt_rand(0, strlen($permitidos))};
			$i++;
		}
		
		return $_aleatorio;
	}
	
	/**
	 * tienePermiso
	 **/
	public static function tienePermiso ($permiso) {
		
		$permisos = explode("|", Zend_Auth::getInstance()->getIdentity()->permisos);
		//print_r($permiso);
		
		if(in_array($permiso, $permisos))
			return true;
		else
			return false;
	}
	
	/**
	 * crearQuery
	 **/
	public static function crearQuery ( $modelo, $query = null ) {
		
		$con = Doctrine_Manager::getInstance()->connection();
		
		if(is_null($query)) {
			$q = Doctrine_Query::create()->from($modelo);
			return $q;
		}
		else {
			$q = $con->execute($query)->fetchAll();
			return $q;
		}
	}
	
	/**
	 * @function     obtener
	 * @author:      
	 * @contact:     
	 * @description: 
	 * @version:     1.0
	 * @copyright:   Avansys
	 **/
	public static function obtener($modelo, $campo, $valor, $final = "", $orden = "") {
		
		$filtro = $campo." = '".$valor."' ";
		
		if($final != "")
			$filtro .= $final;
		
		$q = Doctrine_Query::create()->from($modelo)->where($filtro);
		
		if($orden != "")
			$q->orderBy($orden);
		
		return $q->execute()->getFirst();
	}
	
	/**
	 * obtenerFiltro
	 **/
	public static function obtenerFiltro ($modelo, $filtro, $orden = "") {
		
		$q = Doctrine_Query::create()->from($modelo)->where($filtro);
		
		if( $orden != "" )
			$q->orderBy($orden);
		
		// echo $q->getSqlQuery(); //exit;
		
		return $q->execute();
	}
	
	/**
	 * guardar
	 **/
	public static function guardar($modelo, $datos = array(), $id = 0, $bitacora = array()) {
		
		$tabla = Doctrine_Core::getTable($modelo);
		
		if(!is_numeric($id) || $id==0 || $id == "0") {
			//print_r($datos);exit;
			$Modelo = new $modelo(); 
			unset($datos['id']);
		}
		else {
			$Modelo = $tabla->findOneById((int)$id);
		}
		
		foreach($datos AS $campo) {
			if(!is_null($campo)) {
				$campo = str_replace(array("'", '"'), array("´", "´"), $campo);
			}
		}
		
		$Modelo->fromArray($datos);
		
		try{
			$Modelo->save();
			
			foreach($bitacora as $bitacora_) {
				
				if($bitacora_["id"] == "")
					Bitacora::guardar($Modelo->id, $modelo, $bitacora_["agregar"], $Modelo[$bitacora_["campo"]]);
				else{
					$registro = My_Comun::obtener($bitacora_["modelo"], "id", $bitacora_["id"]);
					Bitacora::guardar($Modelo->id, $modelo, $bitacora_["editar"], $registro[$bitacora_["campo"]]);
				}
			}
			
			return $Modelo->id;
		}
		catch(Doctrine_Connection_Exception $e) {
			
			if($e->getPortableCode()==-5) {
				
				$m=$e->getMessage();
				preg_match_all('/".*?"/', str_replace("'", "\"", $m), $matches);
				$campo=explode("_",$matches[0][1]);
				
				return "El registro no pudo ser insertado porque el valor <b>".$matches[0][0]."</b> está repetido.";
			}
			else {
				try {
					return My_Comun::mensaje($e->getPortableCode()); 
				}
				catch(Exception $e1) {
					return My_Comun::mensaje(-100); 
				}
			}
		}
	}
	
	/**
	 * registrosGrid
	 **/
	public static function registrosGrid($modelo, $filtro) {
		
		### Incializamos el arreglo de registros
		$registros = array();
		
		### Recibimos los parámetros de paginación y ordenamiento.
		if (isset($_POST['page'])      != ""){ $page      = $_POST['page']; }
		if (isset($_POST['sortname'])  != ""){ $sortname  = $_POST['sortname']; }
		if (isset($_POST['sortorder']) != ""){ $sortorder = $_POST['sortorder']; }
		if (isset($_POST['qtype'])     != ""){ $qtype     = $_POST['qtype']; }
		if (isset($_POST['query'])     != ""){ $query     = $_POST['query']; }
		if (isset($_POST['rp'])        != ""){ $rp        = $_POST['rp']; }
		
		$alias = $modelo;
		
		### Codificamos el filtro para evitar problemas con IE      
		$filtro = ( My_Utf8::is_utf8($filtro) ) ? $filtro : utf8_encode($filtro);
		
		### Creamos la consulta con el filtro pero sin parámetros de paginación para obtener el total de registros.
		//echo $filtro; //exit;
		$q = My_Comun::crearQuery($modelo)->where($filtro);
		//echo $q->getSqlQuery(); exit;
		
		$registros['total'] = $q->count();
		$paginas = ceil($registros['total'] / $rp);
		if($page > $paginas)
			$page = 1;
			
		### Completamos la consulta con los datos de paginación y ordenamiento
		$offset  = ($page-1) * $rp;
		$filtro .= ($qtype != '' && $query != '') ? " AND $alias.{$qtype} = '$query'" : '';
		$order   = "$alias.{$sortname} $sortorder";
		
		### Ejecutamos la consulta
		$q = $q->orderBy($order)->limit($rp)->offset($offset);
		//echo $q->getSqlQuery(); exit;
		
		$registros['registros']=$q->execute();
		$registros['pagina']=$page;
		
		return $registros;
	}
	
	/**
	 * registrosGrid
	 **/
	public static function registrosGridQuery($consulta) {
		
		$registros = array();
		
		// Parámetros de paginación y ordenamiento que vienen del POST del JS donde se carga el DataGrid.
		if(isset($_POST['page'])      != ""){ $page = $_POST['page']; }
		if(isset($_POST['sortname'])  != ""){ $sortname = $_POST['sortname']; }
		if(isset($_POST['sortorder']) != ""){ $sortorder = $_POST['sortorder']; }
		if(isset($_POST['qtype'])     != ""){ $qtype = $_POST['qtype']; }
		if(isset($_POST['query'])     != ""){ $query = $_POST['query']; }
		if(isset($_POST['rp'])        != ""){ $rp = $_POST['rp']; }
		
		// Ejecutar la consulta sin parámetros de paginación ni ordenamiento; para obtener el total de registros.
		$q = My_Comun::crearQuery(null, $consulta);
		
		$registros['total'] = count($q);
		
		$paginas = ceil($registros['total'] / $rp);
		if($page > $paginas)
			$page = 1;
			
		// Completar la consulta con los parámetros de paginación y ordenamiento
		$consulta .= " order by ".$sortname." ".$sortorder;
		$offset    = ($page - 1) * $rp;
		$consulta .= " limit ".$offset.", ".$rp;
		
		$registros['registros'] = My_Comun::crearQuery(null, $consulta);
		$registros['pagina'] = $page;  
		
		return $registros;
	}
	
	/**
	 * registrosGridArray
	 **/
	public static function registrosGridArray($array) {
		
		$registros = array();
		
		// Parámetros de paginación y ordenamiento que vienen del POST del JS donde se carga el DataGrid.
		if(isset($_POST['page'])      != ""){ $page = $_POST['page']; }
		if(isset($_POST['sortname'])  != ""){ $sortname = $_POST['sortname']; }
		if(isset($_POST['sortorder']) != ""){ $sortorder = $_POST['sortorder']; }
		if(isset($_POST['qtype'])     != ""){ $qtype = $_POST['qtype']; }
		if(isset($_POST['query'])     != ""){ $query = $_POST['query']; }
		if(isset($_POST['rp'])        != ""){ $rp = $_POST['rp']; }
		
		$registros['total'] = count($array);
		
		$paginas = ceil($registros['total'] / $rp);
		if($page > $paginas)
			$page = 1;
		
		$registros['registros'] = $array;
		$registros['pagina']    = $page;
		
		return $registros;
	}
	
	/**
	 * armarGrid
	 **/
	public static function armarGrid($registros, $grid) {
		
		if(count($grid) > 0) {
			$columnas=array_keys($grid[0]);
		}
		
		$xml = '<rows><page>'.$registros['pagina'].'</page><total>'.$registros['total'].'</total>';
		
		foreach($grid AS $row){
			
			if(array_key_exists("id", $row))
				$xml .= '<row id="'.$row['id'].'">';
			else
				$xml .= '<row id="0">';
			
			foreach($columnas AS $k => $v) {
				if($v != 'id'){
					$xml.='<cell><![CDATA['.$row[$v].']]></cell>';
				}
			}
			$xml .= '</row>';
		}
		
		echo $xml.="</rows>";
	}
	
	/**
	 * @function     eliminar
	 * @author:      
	 * @contact:     
	 * @description: 
	 * @version:     1.0
	 * @copyright:   Avansys
	 **/
	public static function eliminar($modelo, $id, $bitacora = array()) {
		
		//Verificamos si el registro ya está deshabilitado para entonces habilitarlo
		$registro = My_Comun::obtener($modelo, "id", $id);
		
		if( $registro->status == 0 ) {
			
			$q = Doctrine_Query::create()->update($modelo)->set("status", "1")->where("id = ".$id);
			$q->execute();
			
			foreach($bitacora AS $bitacora_) {
				Bitacora::guardar($bitacora_["id"], $bitacora_["modelo"], $bitacora_["habilitar"], $registro[$bitacora_["campo"]]);
			}
			
			return My_Comun::mensaje(4);
		}
		else {
			try {
				foreach($bitacora as $bitacora_){
					$registro = My_Comun::obtener($bitacora_["modelo"], "id", $bitacora_["id"]);
				}
				
				$q = Doctrine_Query::create()->delete($modelo)->where("id = ".$id);
				$q->execute();
				
				foreach($bitacora as $bitacora_){
					Bitacora::guardar($bitacora_["id"], $bitacora_["modelo"], $bitacora_["eliminar"], $registro[$bitacora_["campo"]]);
				}
				
				return My_Comun::mensaje(1);
			}
			catch (Exception $e) {
				
				//echo $e->getMessage();
				
				if($e->getPortableCode()=="-3") { // Error de integridad referencial
					try {
						$q = Doctrine_Query::create()->update($modelo)->set("status", "0")->where("id = ".$id);
						$q->execute();
						
						foreach($bitacora as $bitacora_) {
							Bitacora::guardar($bitacora_["id"], $bitacora_["modelo"], $bitacora_["deshabilitar"], $registro[$bitacora_["campo"]]);
						}
						
						return My_Comun::mensaje(7);
					}
					catch (Exception $e1) {
						//echo $e1->getMessage();
						return My_Comun::mensaje(3);  
					}
				}
				else {
					return My_Comun::mensaje($e->getPortableCode()); 
				}
			}
		}
	}
	
	/**
	 * deshabilitar
	 **/
	public static function deshabilitar($modelo, $id, $bitacora = array()){
		
		try {
			$q = Doctrine_Query::create()->update($modelo)->set("status", "0")->where("id = ".$id);
			$q->execute();  
			
			foreach($bitacora as $bitacora_){
				$registro = My_Comun::obtener($bitacora_["modelo"], "id", $bitacora_["id"]);
				
				Bitacora::guardar($bitacora_["id"], $bitacora_["modelo"], $bitacora_["deshabilitar"], $registro[$bitacora_["campo"]]);
			}
			
			return My_Comun::mensaje(2);
		}
		catch (Exception $e) {
			return My_Comun::mensaje(3); 
		}
	}
	
	/**
	 * habilitar
	 **/
	public static function habilitar($modelo, $id, $bitacora = array(), $extra = "") {
			
		try {
			$q = Doctrine_Query::create()->update($modelo)->set("status", "1")->where("id = ".$id);
			$q->execute();  
			
			foreach($bitacora as $bitacora_) {
				$registro = My_Comun::obtener($bitacora_["modelo"], "id", $bitacora_["id"]);
				Bitacora::guardar($bitacora_["id"], $bitacora_["modelo"], $bitacora_["habilitar"], $registro[$bitacora_["campo"]]);
			}
			
			return My_Comun::mensaje(4);
		}
		catch (Exception $e){
			//echo $e1->getMessage();
			return My_Comun::mensaje(5); 
		}
	}
	
	/**
	 * FileSizeConvert
	 **/
	public static function FileSizeConvert($bytes) {
		
		$bytes = floatval($bytes);
		$arBytes = array(
			0 => array(
				"UNIT" => "TB",
				"VALUE" => pow(1024, 4)
			),
			1 => array(
				"UNIT" => "GB",
				"VALUE" => pow(1024, 3)
			),
			2 => array(
				"UNIT" => "MB",
				"VALUE" => pow(1024, 2)
			),
			3 => array(
				"UNIT" => "KB",
				"VALUE" => 1024
			),
			4 => array(
				"UNIT" => "B",
				"VALUE" => 1
			),
		);
		
		foreach($arBytes as $arItem) {
			
			if($bytes >= $arItem["VALUE"]){
				$result = $bytes / $arItem["VALUE"];
				$result = str_replace(".", "," , strval(round($result, 2)))." ".$arItem["UNIT"];
				break;
			}
		}
		
		return $result;
	}
	
	/**
	 * correo
	 **/
	public static function correo($titulo, $cuerpo, $de, $de_nombre, $para, $para_nombre, $copia = "", $adjunto = "") {
		
		$config = array('auth'=>'login',
						'username' => My_Comun::EMAIL,
						'password' => My_Comun::PASS,
						'port' => 587);
		
		$transport = new Zend_Mail_Transport_Smtp(My_Comun::SMTP, $config);
		
		$cuerpo_ = "
				<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
				<html xmlns=\"http://www.w3.org/1999/xhtml\">
					<head><title>Heza Consultoria Integral</title></head>
					<body>
						<table style=\"width: 100%; font-family: Tahoma, Arial; font-size: 12px; border: none 0; border-spacing: 0; border-collapse: collapse; padding: 0;\" border=\"0\">
							<tr><td style=\"height:3px; background-color: #009999;\"></td></tr>
							<tr>
								<td align=\"left\" style=\"padding: 10px;height:130px;\">
								<img src='http://heza/public/imagenes/logo-heza.png' alt=\"Heza\" title=\"Heza\" /></td>
							</tr>
							<tr><td style=\"height:3px; background-color: #009999;\"></td></tr>
							<tr>
								<td align=\"left\" valign=\"middle\" style=\"padding:10px; color:#14374A; font-size:18px;\">
									".$titulo."
								</td>
							</tr>
							<tr>
								<td align=\"left\" valign=\"top\" style=\"padding:20px; color:#444444; font-size:12px;\">
									".$cuerpo."
								</td>
							</tr>
							<tr>
								<td align=\"left\" valign=\"top\" style=\"color:#444444; font-size:10px;\">
									Este correo electrónico ha sido enviado de la pagina de Heza consultoria integral
								</td>
							</tr>
							<tr><td style=\"height:3px; background-color: #009999;\"></td></tr>
						</table>
					</body>
				</html>";
		
		$mail = new Zend_Mail();
		$mail->setBodyHtml(utf8_decode($cuerpo_));
		$mail->setFrom($de,  utf8_decode($de_nombre));
		$mail->addTo($para, utf8_decode($para_nombre));	
		$mail->setSubject(utf8_decode($titulo));
		
		if($copia != "") {
			
			$copia_ = explode(",", $copia);
			for($i = 0; $i <= count($copia_) - 1; $i++)
				$mail->addCc($copia_[$i], utf8_decode("Copia automática"));
		}
		
		if($adjunto != "") {
			
			$adjunto_ = explode(",", $adjunto);
			for($i = 0; $i <= count($adjunto_) - 1; $i++){
				$at = new Zend_Mime_Part(file_get_contents($adjunto_[$i]));
				$at->type = "application/pdf";
				$at->disposition = Zend_Mime::DISPOSITION_INLINE;
				$at->encoding = Zend_Mime::ENCODING_BASE64;
				$at->filename = "archivo".$i.".pdf";
				$mail->addAttachment($at);
			}
		}
		
		try {
			$mail->send($transport);
		} catch(Exception $e){
			echo("$e error");
		}
		
		return 1;
	}
}

?>