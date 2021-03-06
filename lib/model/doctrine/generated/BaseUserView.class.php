<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BaseUserView extends sfDoctrineRecord
{
  public function setTableDefinition()
  {
    $this->setTableName('user_view');
    $this->hasColumn('user_id', 'integer', null, array('type' => 'integer', 'notnull' => true));
    $this->hasColumn('is_visible', 'boolean', null, array('type' => 'boolean', 'notnull' => true, 'default' => true));

    $this->option('collate', 'utf8_unicode_ci');
    $this->option('charset', 'utf8');
  }

  public function setUp()
  {
    $this->hasOne('sfGuardUser as User', array('local' => 'user_id',
                                               'foreign' => 'id',
                                               'onDelete' => 'CASCADE',
                                               'onUpdate' => 'CASCADE'));

    $timestampable0 = new Doctrine_Template_Timestampable();
    $objectable0 = new Objectable();
    $this->actAs($timestampable0);
    $this->actAs($objectable0);
  }
}