<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BaseExtensionRecord extends sfDoctrineRecord
{
  public function setTableDefinition()
  {
    $this->setTableName('extension_record');
    $this->hasColumn('entity_id', 'integer', null, array('type' => 'integer', 'notnull' => true, 'notblank' => true));
    $this->hasColumn('definition_id', 'integer', null, array('type' => 'integer', 'notnull' => true));

    $this->option('collate', 'utf8_unicode_ci');
    $this->option('charset', 'utf8');
  }

  public function setUp()
  {
    $this->hasOne('Entity', array('local' => 'entity_id',
                                  'foreign' => 'id',
                                  'onDelete' => 'CASCADE',
                                  'onUpdate' => 'CASCADE'));

    $this->hasOne('ExtensionDefinition as Definition', array('local' => 'definition_id',
                                                             'foreign' => 'id',
                                                             'onUpdate' => 'CASCADE'));

    $lsversionable0 = new LsVersionable();
    $this->actAs($lsversionable0);
  }
}