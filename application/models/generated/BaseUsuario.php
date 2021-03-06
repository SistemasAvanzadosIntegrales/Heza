<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Usuario', 'doctrine');

/**
 * BaseUsuario
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $nombre
 * @property string $correo_electronico
 * @property string $contrasena
 * @property string $permisos
 * @property integer $status
 * @property Doctrine_Collection $Bitacora
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseUsuario extends Doctrine_Record {
	
	public function setTableDefinition () {
		
		$this->setTableName('usuario');
		
		$this->hasColumn('nombre', 'string', 200, array(
			 'type' => 'string',
			 'notnull' => true,
			 'length' => '200',
			 ));
		$this->hasColumn('correo_electronico', 'string', 100, array(
			 'type' => 'string',
			 'notnull' => true,
			 'length' => '100',
			 ));
		$this->hasColumn('contrasena', 'string', 20, array(
			 'type' => 'string',
			 'notnull' => true,
			 'length' => '20',
			 ));
		$this->hasColumn('permisos', 'string', null, array(
			 'type' => 'string',
			 'notnull' => true,
			 'length' => '',
			 ));
		$this->hasColumn('status', 'integer', 1, array(
			 'type' => 'integer',
			 'default' => '1',
			 'notnull' => true,
			 'length' => '1',
			 ));
	}
	
	public function setUp() {
		
		parent::setUp();
		$this->hasMany('Bitacora', array(
			 'local' => 'id',
			 'foreign' => 'usuario_id'));
		
		$timestampable0 = new Doctrine_Template_Timestampable();
		$this->actAs($timestampable0);
	}
	
}