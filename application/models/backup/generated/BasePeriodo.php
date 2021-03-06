<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Ejercicio', 'doctrine');

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
abstract class BasePeriodo extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('periodo');
        $this->hasColumn('periodo', 'integer', 8, array(
             'type' => 'integer',
             'notnull' => true,
             'length' => '1',
             ));
        
        $this->hasColumn('id_ejercicio', 'integer', 8 , array(
             'type' => 'integer',
             'notnull' => true,
             'length' => '12',
             ));
        
    }

    public function setUp()
    {
        parent::setUp();
        // $this->hasOne('Ejercicio', array(
             // 'local' => 'id_ejercicio',
             // 'foreign' => 'id'));
        
        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}