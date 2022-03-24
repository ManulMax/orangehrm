<?php

/**
 * BaseAvailableGroupField
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property int                  $reportGroupId                   Type: integer, primary key
 * @property int                  $groupFieldId                    Type: integer, primary key
 * @property ReportGroup          $ReportGroup                     
 * @property GroupField           $GroupField                      
 *  
 * @method int                    getReportgroupid()               Type: integer, primary key
 * @method int                    getGroupfieldid()                Type: integer, primary key
 * @method ReportGroup            getReportGroup()                 
 * @method GroupField             getGroupField()                  
 *  
 * @method AvailableGroupField    setReportgroupid(int $val)       Type: integer, primary key
 * @method AvailableGroupField    setGroupfieldid(int $val)        Type: integer, primary key
 * @method AvailableGroupField    setReportGroup(ReportGroup $val) 
 * @method AvailableGroupField    setGroupField(GroupField $val)   
 *  
 * @package    orangehrm
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseAvailableGroupField extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('ohrm_available_group_field');
        $this->hasColumn('report_group_id as reportGroupId', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             ));
        $this->hasColumn('group_field_id as groupFieldId', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('ReportGroup', array(
             'local' => 'report_group_id',
             'foreign' => 'reportGroupId',
             'onDelete' => 'cascade'));

        $this->hasOne('GroupField', array(
             'local' => 'group_field_id',
             'foreign' => 'groupFieldId',
             'onDelete' => 'cascade'));
    }
}