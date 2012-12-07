<?php

/**
 * lookup actions.
 *
 * @package    ls
 * @subpackage lookup
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 9301 2008-05-27 01:08:46Z dwhittle $
 */
class lookupActions extends sfActions
{
  public function preExecute()
  {
    $request = $this->getRequest();

    //setup response format
    $this->format = $request->getParameter('format', 'xml');        
    $this->getResponse()->setContentType( ($this->format == 'json') ? 'application/json' : 'application/xml' );
    $this->setLayout( ($this->format == 'json') ? 'json' : 'xml' );
  }


  public function executeEntityTypes($request)
  {
    $this->types = array();
    
    foreach (ExtensionDefinitionTable::$extensionNames as $id => $name)
    {
      $this->types[] = array(
        'id' => $id,
        'name' => $name,
        'fields' => implode(',', ExtensionDefinitionTable::$extensionFields[$id])
      );
    }
    
    return 'Xml';
  }


  public function executeRelationshipCategories($request)
  {
    $this->categories = array();
    
    foreach (RelationshipCategoryTable::$categoryNames as $id => $name)
    {
      $this->categories[] = array(
        'id' => $id, 
        'name' => $name, 
        'fields' => implode(',', RelationshipCategoryTable::$categoryFields[$id])
      );
    }
    
    return 'Xml';
  }
}
