<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Empresa', 'doctrine');

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
abstract class BaseEmpresa extends Doctrine_Record {
	
	/**
	 * setTableDefinition
	 **/
	public function setTableDefinition() {
		
		$this->setTableName('empresa');
		$this->hasColumn('nombre', 'string', 100, array(
			 'type' => 'string',
			 'notnull' => true,
			 'length' => '100',
			 ));
		$this->hasColumn('razon_social', 'string', 100, array(
			 'type' => 'string',
			 'notnull' => true,
			 'length' => '100',
			 ));
		$this->hasColumn('nombre_bd_contpaq', 'string', 200, array(
			 'type' => 'string',
			 'notnull' => true,
			 'length' => '30',
			 ));
		$this->hasColumn('usuario_bd_contpaq', 'string', 30, array(
			 'type' => 'string',
			 'notnull' => true,
			 'length' => '30',
			 ));
		$this->hasColumn('pass_bd_contpaq', 'string', 30, array(
			 'type' => 'string',
			 'notnull' => true,
			 'length' => '30',
			 ));
		$this->hasColumn('server_bd_contpaq', 'string', 200, array(
			 'type' => 'string',
			 'notnull' => true,
			 'length' => '30',
			 ));
		$this->hasColumn('coeficiente_utilidad', 'float', 18, array(
			 'type' => 'float',
			 'notnull' => true,
			 'length' => '18',
			 ));
		$this->hasColumn('tasa', 'integer', 11, array(
			 'type' => 'integer',
			 'notnull' => true,
			 'length' => '11',
			 ));
		$this->hasColumn('isr_retenido', 'string', 30, array(
			 'type' => 'string',
			 'notnull' => true,
			 'length' => '30',
			 ));
		$this->hasColumn('retencion_salarios', 'integer', 11, array(
			 'type' => 'integer',
			 'notnull' => true,
			 'length' => '11',
			 ));
		$this->hasColumn('retencion_isr_honorarios', 'integer', 11, array(
			 'type' => 'integer',
			 'notnull' => true,
			 'length' => '11',
			 ));
		$this->hasColumn('retencion_asimilados', 'integer', 11, array(
			 'type' => 'integer',
			 'notnull' => true,
			 'length' => '11',
			 ));
		$this->hasColumn('retencion_dividendos', 'integer', 11, array(
			 'type' => 'integer',
			 'notnull' => true,
			 'length' => '11',
			 ));
		$this->hasColumn('retencion_intereses', 'integer', 11, array(
			 'type' => 'integer',
			 'notnull' => true,
			 'length' => '11',
			 ));
		$this->hasColumn('retencion_pagos_extranjero', 'integer', 11, array(
			 'type' => 'integer',
			 'notnull' => true,
			 'length' => '11',
			 ));
		$this->hasColumn('retencion_venta_acciones', 'integer', 11, array(
			 'type' => 'integer',
			 'notnull' => true,
			 'length' => '11',
			 ));
		$this->hasColumn('retencion_venta_partes_sociales', 'integer', 11, array(
			 'type' => 'integer',
			 'notnull' => true,
			 'length' => '11',
			 ));
		$this->hasColumn('retencion_isr_arrendamiento', 'integer', 11, array(
			 'type' => 'integer',
			 'notnull' => true,
			 'length' => '11',
			 ));
		$this->hasColumn('tipo_empresa_id', 'integer', 1, array(
			 'type' => 'integer',
			 'notnull' => true,
			 'length' => '1',
			 ));
		$this->hasColumn('status', 'integer', 1, array(
			 'type' => 'integer',
			 'default' => '1',
			 'notnull' => true,
			 'length' => '1',
			 ));
	}
	
	/**
	 * setUp
	 **/
	public function setUp() {
		
		parent::setUp();
		$this->hasOne('TipoEmpresa', array(
			 'local' => 'tipo_empresa_id',
			 'foreign' => 'id'));
		
		$timestampable0 = new Doctrine_Template_Timestampable();
		$this->actAs($timestampable0);
	}
}