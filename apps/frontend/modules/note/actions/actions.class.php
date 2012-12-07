<?php

/**
 * note actions.
 *
 * @package    ls
 * @subpackage note
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 9301 2008-05-27 01:08:46Z dwhittle $
 */
class noteActions extends sfActions
{
  public function checkUser()
  {
    if (!$this->getUser()->isAuthenticated())
    {
      $this->forward('error', 'credential');
    }
  }


  public function checkNote($request)
  {
    $this->note = Doctrine::getTable('Note')->find($request->getParameter('id'));
    $this->forward404Unless($this->note);
  }


  public function executeSearch($request)
  {
    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 20);

    $this->query = $request->getParameter('query');

    $s = new LsSphinxClient($page, $num);
    $s->setFilter('visible_to_user_ids', array_unique(array(0, sfGuardUserTable::getCurrentUserId())));
    
    $this->note_pager = NoteTable::getSphinxPager($s, $this->query, Doctrine::HYDRATE_ARRAY);
  }


  public function executeNameSearch($request)
  {
    $this->results = array();

    if ($terms = $request->getParameter('name'))
    {
      $entities = array();
      $page = $request->getParameter('page', 1);
      $num = $request->getParameter('num', 20);

      $result = EntityTable::getSphinxHits($terms, $page, $num);
  
      if ($result['total_found'] > 0 && isset($result['matches']))
      {
        $ids = array_keys($result['matches']);

        $db = Doctrine_Manager::connection();
        $sql = 'SELECT e.*, FIELD(e.id, ' . implode(',', $ids) . ') AS field ' . 
               'FROM entity e WHERE e.id IN (' . implode(',', $ids) . ') AND e.is_deleted = 0 ' .
               'ORDER BY field';             
        $stmt = $db->execute($sql);      
        $entities = $stmt->fetchAll(PDO::FETCH_ASSOC);
      }

      foreach ($entities as $entity)
      {
        $this->results[] = array('name' => $entity['name'], 'markup' => NoteTable::getRecordMarkup($entity, $entity['name'], 'entity'));
      }
    }      
  }
  
  
  public function executeRemove($request)
  {
    if (!$request->isMethod('post'))
    {
      $this->forward('error', 'invalid');
    }

    $this->checkNote($request);
    
    if (!$this->getUser()->isAuthenticated() || ($this->getUser()->getGuardUser()->id != $this->note->user_id))
    {
      $this->forward('error', 'credentials');
    }
    
    $this->note->delete();
    
    $this->redirect($request->getReferer($this->getUser()->getGuardUser()->getInternalUrl()));
  }
}
