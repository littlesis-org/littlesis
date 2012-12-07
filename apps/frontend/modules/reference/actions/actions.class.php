<?php

class referenceActions extends sfActions
{
  public function checkObject($request)
  {
    $this->object = Doctrine::getTable($request->getParameter('model'))->find($request->getParameter('id'));  
    $this->forward404Unless($this->object);  
  }


  public function checkReference($request)
  {
    $this->reference = Doctrine::getTable('Reference')->find($request->getParameter('id'));
    $this->forward404Unless($this->reference);  
  }


  public function executeList($request)
  {
    $this->checkObject($request);
  }
  
  
  public function executeAdd($request)
  {
    $this->checkObject($request);
    
    $this->reference_form = new ReferenceForm;

    $formClass = get_class($this->object) . 'Form';
    $form = new $formClass($this->object);
    $this->fields = $form->getFieldsWithLabels();
    
    if ($request->isMethod('post'))
    {
      $params = $request->getParameter('reference');      
      $this->reference_form->bind($params);

      $fields = array_keys($request->getParameter('fields'));

      if ($this->reference_form->isValid())
      {
        $ref = $this->object->addReference(
          $params['source'],
          $params['excerpt'],
          $fields,
          $params['name'],
          $params['source_detail'],
          $params['publication_date']
        );
        
        LsCache::clearRecordCache($this->object);

        $this->redirect('reference/list?model=' . get_class($this->object) . '&id=' . $this->object->id);
      }    
    }
  }
  
  
  public function executeEdit($request)
  {
    $this->checkReference($request);

    $this->reference_form = new ReferenceForm($this->reference);
    $this->object = $this->reference->getObject();

    $formClass = get_class($this->object) . 'Form';
    $form = new $formClass($this->object);
    $this->fields = $form->getFieldsWithLabels();
    unset($this->fields['_csrf_token']);
    
    if ($request->isMethod('post'))
    {
      $params = $request->getParameter('reference');      
      $this->reference_form->bind($params);

      $newFields = (array) array_keys($request->getParameter('fields'));
      
      if ($this->reference_form->isValid())
      {
        $this->reference_form->updateObject();
        $ref = $this->reference_form->getObject();
        
        $ref->setFieldsArray($newFields);
        $ref->save();

        LsCache::clearRecordCache($this->object);
        
        $this->redirect('reference/list?model=' . get_class($this->object) . '&id=' . $this->object->id);
      }    
    }    
  }
  
  
  public function executeRemove($request)
  {
    if (!$request->isMethod('post'))
    {
      $this->redirect('error/invalid');
    }


    $this->checkReference($request);
    
    $object = $this->reference->getObject();
    $this->reference->delete();

    LsCache::clearRecordCache($object);

    $this->redirect('reference/list?model=' . get_class($object) . '&id=' . $object->id);
  }
  
  
  public function executeAddExcerpt($request)
  {
    if (!$request->isMethod('post'))
    {
      $this->redirect('error/invalid');
    }


    if (!$excerpt = $request->getParameter('excerpt'))
    {
      $this->redirect('error/invalid');    
    }

    $this->checkReference($request);

    $this->reference->addExcerpt($excerpt);
    $this->reference->save();
    
    $this->redirect('reference/list?model=' . $this->reference->object_model . '&id=' . $this->reference->object_id);
  }
  
  
  public function executeRemoveExcerpt($request)
  {
    $excerpt = Doctrine::getTable('ReferenceExcerpt')->find($request->getParameter('id'));
    $this->forward404Unless($excerpt);
    
    $ref = $excerpt->Reference;
    $excerpt->delete();
    
    $this->redirect('reference/list?model=' . $ref->object_model . '&id=' . $ref->object_id);
  }
}