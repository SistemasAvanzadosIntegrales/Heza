<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('TipoEmpresa', 'doctrine');

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
abstract class BaseTablaAsimilados extends Doctrine_Record {
    
  public function setTableDefinition() {
    $this->setTableName('tabla_asimilados');
    $this->hasColumn('id', 'int', 20, array(
      'type' => 'int',
      'notnull' => false,
      'length' => '20',
    ));
    $this->hasColumn('limite_inferior', 'decimal', 18, array(
      'type' => 'decimal',
      'notnull' => true,
      'length' => '18',
      'scale' => 2
    ));
    $this->hasColumn('limite_superior', 'decimal', 18, array(
      'type' => 'decimal',
      'notnull' => true,
      'length' => '18',
      'scale' => 2
    ));
    $this->hasColumn('cuota_fija', 'decimal', 18, array(
      'type' => 'decimal',
      'notnull' => true,
      'length' => '18',
      'scale' => 2
    ));
    $this->hasColumn('porcentaje', 'decimal', 18, array(
      'type' => 'decimal',
      'notnull' => true,
      'length' => '18',
      'scale' => 4
    ));
  }

  public function setUp() {
    $timestampable = new Doctrine_Template_Timestampable();
    $this->actAs($timestampable);
  }
}