<?php

/**
 * operation actions.
 *
 * @package    ls
 * @subpackage operation
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 9301 2008-05-27 01:08:46Z dwhittle $
 */
class operationActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex($request)
  {

  }
  
  public function executeUpload($request)
  {

    if ($request->isMethod('post'))
    {
      $original = $request->getFileName('file');
      $fileName = sha1($original . time());
      $request->moveFile('file', sfConfig::get('sf_root_dir') . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $fileName);
      $this->redirect('operation/massiveAdd?filename=' . $fileName . '&original=' . $original);
    }
    
  }
  
  public function executeMassiveAdd($request)
  {
    $this->massive_form = new MassiveForm;
    
    if (!$request->getParameter('filename'))
    {
       $this->redirect('operation/upload');
    }
    else if ($request->isMethod('post'))
    {
      $this->massive_form->bind($request->getParameter('massive'));
      
      if ($this->massive_form->isValid())
      {
        $filename = sfConfig::get('sf_root_dir') . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $request->getParameter('filename');
        $org_id = $request->getParameter('org_id');
        $list_id = $request->getParameter('list_id');
        $category_id = $request->getParameter('category_id');
        $relationship_description = $request->getParameter('relationship_description');
        $source_url = $request->getParameter('massive[source]');
        $source_name = $request->getParameter('massive[name]');
        
        $q = LsQuery::getByModelAndFieldsQuery('Relationship',array('category_id' => $category_id, 'description1' => $relationship_description))->fetchOne();
        
        if ((!$category_id && !$org_id) || $category_id && !$relationship_description || $category_id && $q)
        {      
          $scraper = new MassAddScraper(false,false,sfContext::getInstance()->getConfiguration(), $this->getUser());
          $scraper->setShowTime(false);
          $scraper->disableBeep();
          $scraper->setOptions($filename, $source_url, $source_name, $list_id, $org_id, $category_id, $relationship_description);
          $scraper->run();    
          $errors = $scraper->getErrors();
          $edits = $scraper->getEdits();
          $this->filename = $request->getParameter('filename');
          $this->original = $request->getParameter('original');
          $this->getUser()->setAttribute('edits',$edits);
          $this->getUser()->setAttribute('errors',$errors);
          $this->redirect('operation/massiveReview');
        }
        else 
        {
           $this->getUser()->setFlash('notice', 'Problems with relationship section. If org is set, make sure category is set.  Also, title/description must already exist in db for specific category.');
        }
      }
    }
    $this->filename = $request->getParameter('filename');
    $this->original = $request->getParameter('original');
    $this->org = isset($org_id) ? Doctrine::getTable('Entity')->find($org_id) : null;
    $this->list = isset($list_id) ? Doctrine::getTable('LsList')->find($list_id) : null;
    $this->category_id = isset($category_id) ? $category_id : null;
    $this->categories = RelationshipCategoryTable::getByExtensionsQuery('Person', 'Org')->execute();  
  
  }
  
  public function executeMassiveReview($request)
  {
    $edits = $this->getUser()->getAttribute('edits');
    $errors = $this->getUser()->getAttribute('errors');
    
    if ($edits)
    {
      foreach($edits as &$edit)
      {
        foreach($edit as $k => &$v)
        {
          if ($k == 'Similar Names')
          {
            foreach($v as &$n)
            {
              $n = Doctrine::getTable('Entity')->find($n);
            }
          }
          else if ($k == 'New Person' || $k == 'Existing Person' || $k == 'New Org' || $k == 'Existing Org')
          {
            $v = Doctrine::getTable('Entity')->find($v);
          }
          else if ($k == 'New Relationship')
          {
            $v = Doctrine::getTable('Relationship')->find($v);
          }
        }
      } 
    }
    $this->edits = $edits;
    $this->errors = $errors;
  }
  
  public function executeFindOrg($request)
 {

    $terms = $request->getParameter('org_terms');
    $terms = preg_replace('/\s{2,}/', ' ', $terms);
    $terms = explode(' ', $terms);

    //search for query that excludes the current Entity1
    $q = EntityTable::getSimpleSearchQuery($terms, array('Org'));

    $num = $request->getParameter('num', 10);
    $page = $request->getParameter('page', 1);

    $this->entity_pager = new LsDoctrinePager($q, $page, $num);  
    
    return $this->renderPartial('operation/entityresults');
  }
  
  public function executeFindList($request)
  {

    $terms = $request->getParameter('list_terms');
    $terms = preg_replace('/\s{2,}/', ' ', $terms);
    $terms = explode(' ', $terms);

    //search for query that excludes the current Entity1
    $q = LsListTable::getSimpleSearchQuery($terms);

    $num = $request->getParameter('num', 10);
    $page = $request->getParameter('page', 1);

    $this->list_pager = new LsDoctrinePager($q, $page, $num);  
    
    return $this->renderPartial('operation/listresults');
  }
  
}
