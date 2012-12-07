<?php

class RelationshipCategoryTemplate extends Doctrine_Template
{
  public function setTableDefinition()
  {
    $this->hasColumn('relationship_id', 'integer', null, array('notnull' => true, 'unique' => true));
  }

  public function setUp()
  {
    $this->hasOne('Relationship', array(
      'local' => 'relationship_id',
      'foreign' => 'id',
      'onDelete' => 'CASCADE',
      'onUpdate' => 'CASCADE'
    ));

    //$versionable = new LsVersionable();
    //$this->actAs($versionable);
    $this->addListener(new ReferenceableHydrationListener);
  }
  
  public function getName()
  {
  	return $this->getInvoker()->Relationship->getName()	;
  }
}