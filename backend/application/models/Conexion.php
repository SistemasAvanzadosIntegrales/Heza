<?php 
/**
 * Conexion
 **/
class Conexion {
	
	/**
	 * abreConexion
	 **/
	public static function abreConexion($user, $pass, $database, $serverName) {
		
		$serverName = $serverName;
		$connectionInfo = array("Database" => $database, "UID" => $user, "PWD" => $pass);
		$conn = sqlsrv_connect( $serverName, $connectionInfo);
		
		if( $conn ) {
			// echo "Conexión establecida.<br />";
			return $conn;
		}
		else {
			echo "Conexión no se pudo establecer.<br />";
			die( print_r( sqlsrv_errors(), true));
			// return else;
		}
	}
}
?>