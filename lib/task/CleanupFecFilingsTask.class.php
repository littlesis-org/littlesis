<?php

require_once(sfConfig::get('sf_root_dir') . '/lib/task/LsTask.class.php');

class CleanupFecFilings extends LsTask
{
  protected
    $db = null,
    $rawDb = null,
    $browser = null,
    $matches = array('new' => array(), 'old' => array()),
    $testMode = null,
    $debugMode = null,
    $startTime = null,
    $databaseManager = null,
    $fecImageBaseUrl = 'http://images.nictusa.com/cgi-bin/fecimg/?',
    $fecCommitteeBaseUrl = 'http://query.nictusa.com/cgi-bin/com_detail/',
    $fecSearchUrlPattern = 'http://query.nictusa.com/cgi-bin/qindcont/1/(lname|MATCHES|:%s:)|AND|(fname|MATCHES|:%s*:)';


  protected function configure()
  {
    $this->namespace        = 'opensecrets';
    $this->name             = 'cleanup-fec-filings';
    $this->briefDescription = 'Removes FecFiling records for a particular cycle and updates all the relationships tied to those filings';
    $this->detailedDescription = '';		
    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod');
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'Show debugging info', false);
    $this->addOption('cycle', null, sfCommandOption::PARAMETER_REQUIRED, 'Cycle to clean', '2012');  
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration($arguments['application'], $options['env'], true); 
    $this->databaseManager = new sfDatabaseManager($configuration);
    $this->databaseManager->initialize($configuration);
    $db = $this->databaseManager->getDatabase('main');
    $this->db = Doctrine_Manager::connection($db->getParameter('dsn'), 'main');

    $cycle = $options["cycle"];
        
    //get relationships with fec filings in the cycle     
    $sql = "SELECT DISTINCT(f.relationship_id) FROM fec_filing f WHERE f.crp_cycle = ?";
    $stmt = $this->db->execute($sql, array($cycle));
    $rel_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

    //delete fec filings in the cycle
    $sql = "DELETE FROM fec_filing WHERE crp_cycle = ?";
    $stmt = $this->db->execute($sql, array($cycle));
    
    foreach ($rel_ids as $rel_id)
    {
      if (Donation::updateRelationshipFromFecFilings($rel_id))
      {
        $this->printDebug("Updated relationship: " . $rel_id);
      } 
      else 
      {
        $this->printDebug("Deleted relationship with no fec filings: " . $rel_id);
      }
    }
  }
}