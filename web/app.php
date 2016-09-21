<?php

require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');

$server = $_SERVER['SERVER_NAME'];

if ($server == 'lsapi.local')
{
  $app = 'api';
  $env = 'dev';
  $debug = false;
}
else
{
  $app = 'frontend';
  $env = 'prod';
  $debug = false;
}

$configuration = ProjectConfiguration::getApplicationConfiguration($app, $env, $debug);
sfContext::createInstance($configuration)->dispatch();
