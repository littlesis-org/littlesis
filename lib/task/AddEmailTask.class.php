<?php

class AddEmailTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'add';
    $this->name             = 'email';
    $this->briefDescription = 'reverse a bad bulk add';
    $this->detailedDescription = <<<EOF

EOF;

    $this->addArgument('application', null, 'The application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('debug_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'debug mode', true);
    $this->addOption('test_mode', null, sfCommandOption::PARAMETER_REQUIRED, 'test mode', false);
    $this->addOption('file_name',null,sfCommandOption::PARAMETER_REQUIRED,'file name',null);
    $this->addOption('safe_mode',null,sfCommandOption::PARAMETER_REQUIRED,'safe mode',true);
  }

  protected function execute($arguments = array(), $options = array())
  {
  	$databaseManager = new sfDatabaseManager($this->configuration);
    $databaseManager->initialize($this->configuration);
    $db = Doctrine_Manager::connection();
        
  	$str = file_get_contents($options['file_name']);
  	$lines = explode("\n",$str);
  	foreach ($lines as $line)
  	{
  		$arr = explode(",",$line);
  		$entity = Doctrine::getTable('Entity')->find($arr[0]);
  		if (strlen($arr[1]) > 0)
  		{
				$email = $entity->addEmail($arr[1]);
				if ($email)
				{
					if ($options['safe_mode'] == true)
					{
						$resp = $this->readline("Do name (" . $entity->name . ") and email (" . $arr[1] . ") match?");
					}
					else $resp = 'y';
					if ($resp == 'y')
					{
						$email->save();
					} 
				}
				else echo "email failed to save\n";
			}
			else echo "entity skipped\n";
  	}
  	
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