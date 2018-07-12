<?php
/* Description of Application_Plugin_Login */
class Application_Plugin_Default extends Zend_Controller_Plugin_Abstract {
	
	public function preDispatch(Zend_Controller_Request_Abstract $request) {
		
		$view = new Zend_View();
		$view->peticion = $request->isXmlHttpRequest();
		$view->modulo = $request->getModuleName();
		$view->controlador = $this->obtenerPermiso($request);
		$view->pagina = $request->getActionName();
		$view->accion = $this->obtenerAccion($request);
		
		$layout = Zend_Layout::getMvcInstance();
		
		if(Zend_Auth::getInstance()->hasIdentity()) {
			
			$layout->setLayout('backend');
			if($view->controlador=="login")
				header("Location: /");
		}
		else {
			$layout->setLayout('ingreso');
		}
		
		$viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
		$viewRenderer->setView($view);
		
		Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);
	}
	
	private function  obtenerPermiso($controlador) {
		
		if(!$controlador->isXmlHttpRequest() && $controlador->getModuleName()!='default') {
			
			//echo $controlador;exit;
			if(My_Comun::tienePermiso("VER_" . strtoupper(str_replace("-", "_", $controlador->getControllerName()))) == false
				&& $controlador->getControllerName() != "index" &&  $controlador->getControllerName() != "login" && $controlador->getControllerName() != "servicios")
				header("Location: /sin-permiso");
			
			return $controlador->getControllerName();
		}
		
		return $controlador->getControllerName();
	}
	
	private function  obtenerAccion($accion){
		
		if(!$accion->isXmlHttpRequest()) {
			
			switch($accion->getActionName()){
				
				case "index"    : return "ver"; break;
				case "agregar"  : return "agregar"; break;
				case "eliminar" : return "eliminar"; break;
				case "exportar" : return "ver"; break;
				default         : return $accion->getActionName(); break;   
			}
		}
		return $accion->getActionName();
	}
}

?>
