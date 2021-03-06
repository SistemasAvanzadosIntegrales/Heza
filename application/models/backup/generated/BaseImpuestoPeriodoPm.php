<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('SubsidioEmpleo', 'doctrine');

/**
 * BaseSubsidioEmpleo
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
abstract class BaseImpuestoPeriodoPm extends Doctrine_Record {
	
	/**
	 * setTableDefinition
	 **/
	public function setTableDefinition() {
		
		$this->setTableName('impuesto_periodo_pm');
		
		$this->hasColumn('id_empresa', 'integer', 20, array(
			 'type' => 'integer',
			 'notnull' => true,
			 'length' => '20',
			 ));
		$this->hasColumn('id_ejercicio', 'integer', 20, array(
			 'type' => 'integer',
			 'notnull' => true,
			 'length' => '20',
			 ));
		$this->hasColumn('id_periodo', 'integer', 20, array(
			 'type' => 'integer',
			 'notnull' => true,
			 'length' => '20',
			 ));
		$this->hasColumn('status', 'integer', 20, array(
			 'type' => 'integer',
			 'notnull' => true,
			 'length' => '20',
			 ));
		$this->hasColumn('isr_ingreso_periodo', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('isr_anticipo_cliente', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('isr_otros_ingresos', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('isr_producto_financiero', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('isr_ingreso_nominal', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('isr_ingreso_acumulabe', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('isr_utilidad_fiscal', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('isr_acumulables_inventario', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('isr_anticipos_distribuidos', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('isr_deduccion_inversiones', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('isr_ptu_pagada', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('isr_perdida_anteriores', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('isr_base_provisional', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('isr_periodo', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('isr_pagos_anteriores', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('isr_retenido', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('isr_impuesto_pagar', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('isr_compensacion', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('isr_pago_provisional', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('isr_impuesto_favor', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('iva_ingresos_16', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('iva_ingresos_15', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('iva_ingresos_11', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('iva_ingresos_0', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('iva_ingresos_exentos', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('iva_ingresos_otra_base', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('iva_total_ingresos', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('iva_trasladado_16', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('iva_trasladado_15', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('iva_trasladado_11', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('iva_total_trasladado', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('iva_gravable_16', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('iva_gravable_15', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('iva_gravable_11', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('iva_gravable_0', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('iva_gravable_exento', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('iva_total_base_acreditable', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('iva_acreditable_16', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('iva_acreditable_15', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('iva_acreditable_11', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('iva_total_acreditable', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('iva_coeficiente_acreditamiento', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('iva_acreditable_antes_retenciones', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('iva_retenido_mes', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('iva_retenido_mes_anterior', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('iva_acreditable', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('iva_cargo_antes_compensaciones', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('iva_favor', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('iva_compensaciones', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('iva_cargo', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('iva_favor_acumulado', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('retencion_salarios', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('retencion_isr_honorarios', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('retencion_asimilados', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('retencion_dividendos', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('retencion_intereses', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('retencion_pagos_extranjero', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('retencion_venta_acciones', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('retencion_venta_partes_sociales', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('retencion_isr_arrendamiento', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('total_impuestos', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('subsidio_empleo', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('compensaciones_otros', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
		$this->hasColumn('impuestos_pagar', 'decimal', 18, array(
			 'type' => 'decimal',
			 'notnull' => true,
			 'length' => '18',
			'scale' =>2
			 ));
	}
	
	/**
	 * setUp
	 **/
	public function setUp() {
		parent::setUp();
		$this->hasOne('Empresa', array(
			 'local' => 'id_empresa',
			 'foreign' => 'id'));
	}
}