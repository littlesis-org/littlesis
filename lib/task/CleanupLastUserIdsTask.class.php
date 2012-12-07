<?php

class CleanupLastUserIdsTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'cleanup';
    $this->name             = 'last-user-ids';
    $this->briefDescription = 'sets last_user_id field for versionable records ';
    $this->detailedDescription = <<<EOF
This task is for setting the last_user_id field for versionable records that haven't been updated since the last_user_id field began being automatically set.
EOF;
		
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'How many records to perform this operation on', 10000);
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration($arguments['application'], $options['env'], true); 
    $databaseManager = new sfDatabaseManager($configuration);
    $databaseManager->initialize($configuration);      

    $db = Doctrine_Manager::connection();
    $models = array('Entity', 'Relationship', 'LsList');

    foreach ($models as $model)    
    {
      $modelAlias = strtolower($model);
      $updateAlias = ($model == 'LsList') ? 'ls_list' : $modelAlias;
      
      //get records to update
      $q = LsDoctrineQuery::create()
        ->select('id')
        ->from($model . ' ' . $modelAlias)
        ->where($modelAlias . '.last_user_id IS NULL')
        ->limit($options['limit'])
        ->setHydrationMode(Doctrine::HYDRATE_NONE);


      if (!count($rows = $q->execute()))
      {
        //nothing to update, go to next model
        continue;
      }


      foreach ($rows as $row)
      {
        $id = $row[0];

        //get last_user_id
        $result = LsDoctrineQuery::create()
          ->select('m.user_id')
          ->from('Modification m')
          ->where('m.object_model = ? AND m.object_id = ?', array($model, $id))
          ->orderBy('m.id DESC')
          ->setHydrationMode(Doctrine::HYDRATE_NONE)
          ->fetchOne();
          
        if ($lastUserId = $result[0])
        {
          $query = 'UPDATE ' . $updateAlias . ' SET last_user_id=? WHERE id=?';

          //use PDO for speed
          $db->execute($query, array($lastUserId, $id));
        }
        else
        {
          throw new Exception("Couldn't find last_user_id for " . $model . ' #' . $id);
        }
      }
      
      //only update records of one model at a time
      break;
    }


       
    //DONE
    LsCli::beep();
  }
}