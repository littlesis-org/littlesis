<?php

class CleanUpBulkAddTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'cleanup';
    $this->name             = 'bulkadd';
    $this->briefDescription = 'reverse a bad bulk add';
    $this->detailedDescription = <<<EOF
If an expert user adds inaccurate or low-quality bulk data, their edits can be reversed with this task.  Specify a user id; the start and end date of the edits; the relationship category id; the entity id (for instance, of the American Museum of Natural History); and the order/position of that entity in the relationship.
NOTE: YOU MUST CLEAR THE CACHE AFTER EXECUTING THIS!
EOF;

    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'debug mode', true);
    $this->addOption('test_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'test mode', false);
    $this->addOption('entity_id', null, sfCommandOption::PARAMETER_REQUIRED, 'entity id', null);
    $this->addOption('list_id', null, sfCommandOption::PARAMETER_REQUIRED, 'list id', null);
    $this->addOption('category_id', null, sfCommandOption::PARAMETER_REQUIRED, 'relationship category id', '1');
    $this->addOption('entity_order', null, sfCommandOption::PARAMETER_REQUIRED, 'position of entity_id in relationship', '2');
    $this->addOption('username', null, sfCommandOption::PARAMETER_REQUIRED, 'user public name');
    $this->addOption('start_date', null, sfCommandOption::PARAMETER_REQUIRED, 'test mode');
    $this->addOption('end_date', null, sfCommandOption::PARAMETER_REQUIRED, 'test mode');
  }

  protected function execute($arguments = array(), $options = array())
  {
    if(!$options['username'] || !$options['start_date'] || !$options['end_date'])
    {
      echo "Task failed. Make sure you have specified options properly. Check options with 'help cleanup:bulkadd.'\n";
      die;
    }
    $databaseManager = new sfDatabaseManager($this->configuration);
    $databaseManager->initialize($this->configuration);
    if ($options['entity_id'])
    {
      $entity = Doctrine::getTable('Entity')->find($options['entity_id']);
      $user = LsDoctrineQuery::create()
                ->from('sfGuardUserProfile p')
                ->where('p.public_name = ?',$options['username'])
                ->fetchOne();
      if(!$user) 
      {
        echo "user doesn't exist. goodbye\n"; 
        die;
      }
      $accept = $this->readline("Undo bulk edits for " . $user->public_name . " on " . $entity->name . " where relationship has been created between " . $options['start_date'] . " and " . $options['end_date'] . " and category is " . $options['category_id'] . "? (y or n)");
      if ($accept != 'y')
      {
        echo "goodbye\n";
        die;
      }
      $relct = 0;
      $entityct = 0;
      $q = LsDoctrineQuery::create()
        ->from('Relationship r')
        ->where('r.category_id = ? and r.created_at > ? and r.created_at < ? and r.last_user_id = ?', array($options['category_id'],$options['start_date'],$options['end_date'],$user->user_id));
      if ($options['entity_order'] == 2)
      {
        $q->addWhere('r.entity2_id = ?',$options['entity_id']);
      }
      else
      {
        $q->addWhere('r.entity1_id = ?',$options['entity_id']);
      }
        
      $relationships = $q->execute();
      $accept = $this->readline($relationships->count() . " relationships found. proceed? (y or n)");
      if ($accept != "y")
      {
        echo "goodbye\n";
        die;
      }
      foreach($relationships as $rel)
      {
        
        if ($options['entity_order'] == 2)
        {
          $entity = Doctrine::getTable('Entity')->find($rel->entity1_id);
        }
        else $entity = Doctrine::getTable('Entity')->find($rel->entity2_id);
        
        if (!$entity) continue;
        
        if ($options['test_mode'] == false)
        {
          $rel->delete();
        }
        echo "removing " . $rel . "\n";
        $relct++;
        
        if ($entity->created_at > $options['start_date'] && $entity->created_at < $options['end_date'] && $entity->updated_at < $options['end_date'] && $entity->last_user_id == $user->user_id)
        {
          echo "\tremoving " . $entity->name . "\n";
          $entityct++;
          if ($options['test_mode'] == false)
          {
            $entity->delete();
          }
        }
      }
      echo "$relct relationships removed, $entityct entities removed.\n";
    }
    else if ($options['list_id'])
    {
      $list = Doctrine::getTable('LsList')->find($options['list_id']);
      $user = LsDoctrineQuery::create()
                ->from('sfGuardUserProfile p')
                ->where('p.public_name = ?',$options['username'])
                ->fetchOne();
      if(!$user) 
      {
        echo "user doesn't exist. goodbye\n"; 
        die;
      }
      $accept = $this->readline("Undo bulk edits for " . $user->public_name . " on " . $list->name . " where list membership has been created between " . $options['start_date'] . " and " . $options['end_date'] . "? (y or n)");
      if ($accept != 'y')
      {
        echo "goodbye\n";
        die;
      }
      $lect = 0;
      $entityct = 0;
      $q = LsDoctrineQuery::create()
        ->from('LsListEntity le')
        ->where('le.list_id = ? and le.created_at > ? and le.created_at < ? and le.last_user_id = ?', array($options['list_id'],$options['start_date'],$options['end_date'],$user->user_id));
    
      $list_entities = $q->execute();
      $accept = $this->readline($list_entities->count() . " list memberships found. proceed? (y or n)");
      if ($accept != "y")
      {
        echo "goodbye\n";
        die;
      }
      foreach($list_entities as $le)
      {
        
        $entity = Doctrine::getTable('Entity')->find($le->entity_id);
        
        if (!$entity) continue;
        
        
        
        if ($entity->created_at > $options['start_date'] && $entity->created_at < $options['end_date'] && $entity->updated_at < $options['end_date'] && $entity->last_user_id == $user->user_id)
        {
          echo "removing " . $le . " through entity deletion: \n";
          $lect++;
          echo "\tremoving " . $entity->name . "\n";
          $entityct++;
          if ($options['test_mode'] == false)
          {
            $entity->delete();
          }
        }
        else
        {
          if ($options['test_mode'] == false)
          {
            $le->delete();
          }
          echo "removing " . $le . "\n";
          $lect++;
        }
      }
      echo "$lect list entities removed, $entityct entities removed.\n";
    }
    else echo "You need to supply a list or entity id\n";
  }
  
  protected function readline($prompt="", $possible = array('y','n','b'), $lim = 5)
  {
    $response = '';
    $ct = 0;
    while (!in_array($response,$possible) && $ct < $lim)
    {
      print $prompt;
      $out = "";
      $key = "";
      $key = fgetc(STDIN);        //read from standard input (keyboard)
      while ($key!="\n")        //if the newline character has not yet arrived read another
      {
        $out.= $key;
        $key = fread(STDIN, 1);
      }
      $response = $out;
      $ct++;
    }
    return $response;
  }
  
}