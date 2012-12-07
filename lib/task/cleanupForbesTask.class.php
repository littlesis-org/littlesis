<?php

class cleanupForbesTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'cleanup';
    $this->name             = 'forbes';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [cleanup:forbes|INFO] task does things.
Call it with:

  [php symfony cleanup:forbes|INFO]
EOF;
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('test_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'Test mode', false);
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'debug mode', true);
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    $databaseManager->initialize($this->configuration);
    /*$q = LsDoctrineQuery::create()
              ->from('LsListEntity le')
              ->where('le.list_id = ? and le.entity_id < ?', array(7,28220))
              ->execute();
     foreach($q as $le)
     {
       $e = Doctrine::getTable('Entity')->find($le->entity_id);
       echo $e->name . "\n";
       foreach($e->getReferencesByFields() as $ref)
       {
         if ($ref->name == 'Forbes.com')
         {
           $ref->delete();
           echo '  deleted ref ' . $ref->name . "\n";
           break;
         } 
       }
       $le->delete();
       echo "  deleted list_entity \n\n";
     }*/
     $list = Doctrine::getTable('LsList')->findOneByName('Forbes Largest Private Companies (2007)');
     echo $list->name;
     $list_entities = LsDoctrineQuery::create()
                 ->from ('LsListEntity le')
                 ->where('le.list_id = ?', $list->id)
                 ->orderBy('le.entity_id DESC')
                 ->execute();
                 
      foreach($list_entities as $list_entity)
      {
      
        $es = LsDoctrineQuery::create()
                  ->select('e.*')
                  ->from('Entity e')
                  ->leftJoin('e.LsListEntity le')
                  ->where('le.list_id = ? and le.rank = ?',array($list->id,$list_entity->rank))
                  ->addWhere('e.id < ?', $list_entity->entity_id)
                  ->execute();
                  
         foreach($es as $e)
         {       
           if ($e->getRelationshipsQuery(null, null, null, null, null, 'entity')->count() == 0)
           {
             if (!$options['test_mode'])
             {
               $e->delete();
             }
             echo "deleted $e->name\n";
           }
           else echo "  $e->name has relationships\n";
         }
      
      }

              
  }
}