<?php

/**
 * relationship actions.
 *
 * @package    ls
 * @subpackage relationship
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 9301 2008-05-27 01:08:46Z dwhittle $
 */
class relationshipActions extends LsApiActions
{
  public function preExecute()
  {
    //setup response format
    $this->setResponseFormat();

    $this->relationship = RelationshipApi::get($this->getRequest()->getParameter('id'));

    $this->setLastModified($this->relationship);

    $this->checkExistence($this->relationship, 'Relationship');
  }


  public function executeView($request)
  {
    $this->entity1 = EntityApi::get($this->relationship['entity1_id']);
    $this->entity2 = EntityApi::get($this->relationship['entity2_id']);
    return 'Xml';
  }
  
  
  public function executeDetails($request)
  {
    $this->relationship = array_merge(
      $this->relationship, 
      RelationshipApi::getDetails(
        $this->relationship['id'], 
        $this->relationship['category_id']
      )
    );
    $this->relationship['Entity1'] = EntityApi::get($this->relationship['entity1_id']);
    $this->relationship['Entity2'] = EntityApi::get($this->relationship['entity2_id']);  
    return 'Xml';
  }  


  public function executeReferences($request)
  {
    $this->references = RelationshipApi::getReferences($this->relationship['id']);
    return 'Xml';
  }
}
