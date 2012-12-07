<?php

/**
 * error actions.
 *
 * @package    ls
 * @subpackage error
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 9301 2008-05-27 01:08:46Z dwhittle $
 */
class errorActions extends sfActions
{
  public function postExecute()
  {
	  $this->setLayout('layout');
  }

	public function executeCredentials()
	{
	}

	public function executeInvalid()
	{
	}

	public function execute404()
	{
	  $this->setLayout('layout');
	}
	
	public function executeInternal()
	{
	}
	
	public function executeMaintenance()
	{	
	}
}