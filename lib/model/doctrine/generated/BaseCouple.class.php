<?php

abstract class BaseCouple extends sfDoctrineRecord
{
  public function setTableDefinition()
  {
    $this->setTableName('couple');
    $this->hasColumn('partner1_id', 'integer', null, array('type' => 'integer', 'notnull' => true));
    $this->hasColumn('partner2_id', 'integer', null, array('type' => 'integer', 'notnull' => true));

    $this->option('collate', 'utf8_unicode_ci');
    $this->option('charset', 'utf8');
  }

  public function setUp()
  {
    $this->hasOne('Entity as Partner1', array('local' => 'partner1_id',
                                           'foreign' => 'id',
                                           'onDelete' => 'DELETE',
                                           'onUpdate' => 'CASCADE'));

    $this->hasOne('Entity as Partner2', array('local' => 'partner2_id',
                                           'foreign' => 'id',
                                           'onDelete' => 'DELETE',
                                           'onUpdate' => 'CASCADE'));

    $extension0 = new Extension();
    $this->actAs($extension0);
  }
}