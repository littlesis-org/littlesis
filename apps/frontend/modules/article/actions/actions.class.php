<?php

class articleActions extends sfActions
{
  public function checkArticle($request)
  {
    //get article
    if (!$this->article = Doctrine::getTable('Article')->find($request->getParameter('id')))
    {
      $this->forward404();
    }  
  }


  public function executeLatest($request)
  {
    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 10);

    $q = LsDoctrineQuery::create()
      ->from('Article a')
      ->leftJoin('a.ArticleEntity ae')
      ->leftJoin('ae.Entity e')
      ->leftJoin('a.ArticleSource as')
      ->where('a.reviewed_at IS NOT NULL AND a.is_hidden = 0')
      ->andWhere('e.is_deleted = 0')
      ->orderBy('a.reviewed_at DESC');
      
    $this->article_pager = new LsDoctrinePager($q, $page, $num);
  }


  public function executeAdmin($request)
  {
    $page = $request->getParameter('page', 1);
    $num = $request->getParameter('num', 10);

    Doctrine::getTable('Article')->addRecordListener(new TimestampHydrationListener);

    //find indexed articles with unverified entity matches
    $q = LsDoctrineQuery::create()
      ->from('Article a')
      ->leftJoin('a.ArticleEntity ae')
      ->leftJoin('ae.Entity e')
      ->leftJoin('a.ArticleSource as')
      ->where('a.is_indexed = 1')
      ->andWhere('a.is_hidden = 0')
      ->andWhere('e.is_deleted = 0')
      ->orderBy('a.published_at DESC, a.id DESC');

    if (!$request->getParameter('all'))
    {
      $q->andWhere('a.reviewed_at IS NULL');
    }
      
    $this->article_pager = new LsDoctrinePager($q, $page, $num);
  }
  
  
  public function executeMatch($request)
  {
    if ($request->isMethod('post'))
    {
      $this->checkArticle($request);
      
      //get verified entity ids
      $oldEntities = LsDoctrineQuery::create()
        ->select('ae.entity_id')
        ->from('ArticleEntity ae')
        ->leftJoin('ae.Entity e')
        ->where('ae.article_id = ?', $this->article->id)
        ->andWhere('ae.is_verified = 1')
        ->andWhere('e.is_deleted = 0')
        ->fetchAll(PDO::FETCH_COLUMN);

      //get all submitted entity ids
      $newEntities = array_unique($request->getParameter('entity_ids', array()));
      
      //compute changes
      $verifiedEntities = array_diff($newEntities, $oldEntities);
      $unverifiedEntities = array_diff($oldEntities, $newEntities);

      //get user and time
      $userId = $this->getUser()->getGuardUser()->id;
      $time = LsDate::getCurrentDateTime();

      //save changes
      LsDoctrineQuery::create()
        ->update('ArticleEntity')
        ->set('is_verified', '?', true)
        ->set('reviewed_by_user_id', $userId)
        ->set('reviewed_at', '?', array($time))
        ->where('article_id = ?', $this->article->id)
        ->andWhereIn('entity_id', $verifiedEntities)
        ->execute();

      LsDoctrineQuery::create()
        ->update('ArticleEntity')
        ->set('is_verified', '?', false)
        ->set('reviewed_by_user_id', $userId)
        ->set('reviewed_at', '?', array($time))
        ->where('article_id = ?', $this->article->id)
        ->andWhereIn('entity_id', $unverifiedEntities)
        ->execute();

      $this->article->description = $request->getParameter('description');
      $this->article->reviewed_by_user_id = $userId;
      $this->article->reviewed_at = $time;
      $this->article->save();
      
      $this->redirect('article/admin');
    }
  }
  
  
  public function executeFeature($request)
  {
    if (!$request->isMethod('post'))
    {
      $this->forward('error', 'invalid');
    }
    
    $this->checkArticle($request);
    
    $this->article->is_featured = true;
    $this->article->save();
    
    $this->redirect('article/admin');
  }


  public function executeUnfeature($request)
  {
    if (!$request->isMethod('post'))
    {
      $this->forward('error', 'invalid');
    }
    
    $this->checkArticle($request);
    
    $this->article->is_featured = false;
    $this->article->save();
    
    $this->redirect('article/admin');
  }


  public function executeHide($request)
  {
    if (!$request->isMethod('post'))
    {
      $this->forward('error', 'invalid');
    }
    
    $this->checkArticle($request);
    
    $this->article->is_hidden = true;
    $this->article->save();
    
    $this->redirect('article/admin');
  }


  public function executeUnhide($request)
  {
    if (!$request->isMethod('post'))
    {
      $this->forward('error', 'invalid');
    }
    
    $this->checkArticle($request);
    
    $this->article->is_hidden = false;
    $this->article->save();
    
    $this->redirect('article/admin');
  }
}