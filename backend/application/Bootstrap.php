<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap{

	protected function _initDoctrine(){
		
		//Añadir Doctrine al autoloader de Zend
		$this->getApplication()->getAutoloader()
			->pushAutoloader(array('Doctrine', 'autoload'));

		//Llamar al autoloader de Doctrine para que cargue los modelos
		spl_autoload_register(array('Doctrine', 'modelsAutoload'));

		//Obtener los parámetros definidos en el archivo appication.ini
		$doctrineConfig = $this->getOption('doctrine');
		$manager = Doctrine_Manager::getInstance();
		
		//Definir los atributos de acceso y de carga de modelos
		$manager->setAttribute(Doctrine::ATTR_AUTO_ACCESSOR_OVERRIDE, true);
		$manager->setAttribute(
			Doctrine::ATTR_MODEL_LOADING,
			$doctrineConfig['model_autoloading']
		);

		Doctrine_Core::loadModels($doctrineConfig['models_path']);

		//Crear la conexión a base de datos segun el DSN
		$conn = Doctrine_Manager::connection($doctrineConfig['dsn'],'doctrine');
		//Definir la forma nativa ENUM
		
		$conn->setAttribute(Doctrine::ATTR_USE_NATIVE_ENUM, true);
		return $conn;
	}
}