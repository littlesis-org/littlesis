<?php

class CleanupCapsNamesTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'cleanup';
    $this->name             = 'caps-names';
    $this->briefDescription = 'nameizes all-caps person names';
    $this->detailedDescription = <<<EOF
This task converts all-caps person names into their proper nameized form.
EOF;
		
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'How many entities to perform this operatio on', 5000);
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration($arguments['application'], $options['env'], true); 
    $databaseManager = new sfDatabaseManager($configuration);
    $databaseManager->initialize($configuration);      


    $db = Doctrine_Manager::connection();


    //get person entities with all-caps names
    $sql = 'SELECT e.id, e.name FROM entity e ' . 
           'WHERE e.name <> \'\' AND e.primary_ext = ? AND CAST(UPPER(e.name) AS BINARY) = CAST(e.name AS BINARY)';
           
    $stmt = $db->execute($sql, array('Person'));
    $names = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($names as $ary)
    {
      $new = PersonTable::nameizePersonName($ary['name']);

      if ($new != $ary['name'])
      {
        $sql = 'UPDATE entity SET name = ? WHERE id = ?';
        $stmt = $db->execute($sql, array($new, $ary['id']));
        
        print("Changed Entity name " . $ary['name'] . " to " . $new . "\n");      
      }
    }


    //get aliases with all-caps names
    $sql = 'SELECT a.id, a.name FROM alias a LEFT JOIN entity e ON (e.id = a.entity_id) ' . 
           'WHERE a.name <> \'\' AND a.is_primary = 1 AND e.primary_ext = ? AND ' . 
           'CAST(UPPER(a.name) AS BINARY) = CAST(a.name AS BINARY)';
           
    $stmt = $db->execute($sql, array('Person'));
    $names = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($names as $ary)
    {
      $new = PersonTable::nameizePersonName($ary['name']);

      if ($new != $ary['name'])
      {        
        $sql = 'UPDATE alias SET name = ? WHERE id = ?';
        $stmt = $db->execute($sql, array($new, $ary['id']));
        
        print("Changed Alias " . $ary['name'] . " to " . $new . "\n");      
      }
    }

    
    //DONE
    LsCli::beep();
  }
}