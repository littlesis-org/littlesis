<?php

class frontendConfiguration extends sfApplicationConfiguration
{
  public function configure()
  {
    ProjectConfiguration::configureDirs();
    ProjectConfiguration::configureCache();
  }
}
