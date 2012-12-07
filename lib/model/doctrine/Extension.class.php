<?php

class Extension extends Doctrine_Template
{
  public function setTableDefinition()
  {
    $this->hasColumn('entity_id', 'integer', null, array('notnull' => true, 'unique' => true));
  }

  public function setUp()
  {
    $this->hasOne('Entity', array(
      'local' => 'entity_id',
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
    return $this->getInvoker()->Entity->name;
  }

    
  public function getExtensionDefinition()
  {
    return Doctrine::getTable('ExtensionDefinition')->findOneByName(get_class($this->getInvoker()));
  }


  public function getRelationAliasByFieldName($fieldName)
  {
    $object = $this->getInvoker();
    $table = $object->getTable();
    
    foreach ($table->getRelations() as $name => $relation)
    {
      if ($relation->getLocalFieldName() == $fieldName)
      {
        return $name;
      }
    }
    
    return null;
  }
}