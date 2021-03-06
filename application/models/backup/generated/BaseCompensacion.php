<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Compensacion', 'doctrine');

/**
 * BaseCompensacion
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
abstract class BaseCompensacion extends Doctrine_Record {
	
	public function setTableDefinition () {
		
		$this->setTableName('compensacion');
		$this->hasColumn('fecha', 'date', null, array(
			 'type' => 'date',
			 'notnull' => true,
			 'length' => 'null',
			 ));
		$this->hasColumn('tipo_impuesto', 'varchar', 5, array(
			 'type' => 'varchar',
			 'notnull' => true,
			 'length' => '5',
			 ));
		$this->hasColumn('periodo', 'int', 11, array(
			 'type' => 'int',
			 'notnull' => true,
			 'length' => '11',
			 ));
		$this->hasColumn('tipo_declaracion', 'varchar', 20, array(
			 'type' => 'varchar',
			 'notnull' => true,
			 'length' => '20',
			 ));
		$this->hasColumn('numero_operacion', 'varchar', 20, array(
			 'type' => 'varchar',
			 'notnull' => true,
			 'length' => '20',
			 ));
                $this->hasColumn('monto_original', 'float', 11, array(
			 'type' => 'float',
			 'notnull' => true,
			 'length' => '11',
			 ));
                $this->hasColumn('monto_aplicar', 'float', 11, array(
			 'type' => 'float',
			 'notnull' => true,
			 'length' => '11',
			 ));
                $this->hasColumn('remanente_antes', 'float', 11, array(
			 'type' => 'float',
			 'notnull' => true,
			 'length' => '11',
			 ));
                $this->hasColumn('remanente_despues', 'float', 11, array(
			 'type' => 'float',
			 'notnull' => true,
			 'length' => '11',
			 ));
                
		$this->hasColumn('ejercicio_id', 'integer', 8, array(
			 'type' => 'integer',
			 'notnull' => true,
			 'length' => '8',
			 ));
                $this->hasColumn('empresa_id', 'bigint', 20, array(
			 'type' => 'bigint',
			 'notnull' => true,
			 'length' => '20',
			 ));
		$this->hasColumn('status', 'integer', 1, array(
			 'type' => 'integer',
			 'default' => '1',
			 'notnull' => true,
			 'length' => '1',
			 ));
	}
	
	public function setUp () {
		
		parent::setUp();
		$this->hasOne('Empresa', array(
			 'local' => 'empresa_id',
			 'foreign' => 'id'));
		$this->hasOne('Ejercicio', array(
			 'local' => 'ejercicio_id',
			 'foreign' => 'id'));
		$this->hasOne('TipoImpuesto', array(
			 'local' => 'tipo_impuesto_id',
			 'foreign' => 'id'));
		
		$timestampable0 = new Doctrine_Template_Timestampable();
		$this->actAs($timestampable0);
	}
	
}