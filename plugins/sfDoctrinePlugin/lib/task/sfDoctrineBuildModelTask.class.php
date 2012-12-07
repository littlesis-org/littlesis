<?php
/*
 * This file is part of the sfDoctrinePlugin package.
 * (c) 2006-2007 Jonathan H. Wage <jonwage@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/sfDoctrineBaseTask.class.php');

/**
 * Create classes for the current model.
 *
 * @package    sfDoctrinePlugin
 * @subpackage Task
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfDoctrineBuildModelTask.class.php 12699 2008-11-06 18:29:12Z Jonathan.Wage $
 */
class sfDoctrineBuildModelTask extends sfDoctrineBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application name'),
    ));

    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
    ));

    $this->aliases = array('doctrine-build-model');
    $this->namespace = 'doctrine';
    $this->name = 'build-model';
    $this->briefDescription = 'Creates classes for the current model';

    $this->detailedDescription = <<<EOF
The [doctrine:build-model|INFO] task creates model classes from the schema:

  [./symfony doctrine:build-model|INFO]

The task read the schema information in [config/doctrine/*.yml|COMMENT]
from the project and all installed plugins.

You mix and match YML and XML schema files. The task will convert
YML ones to XML before calling the Doctrine task.

The model classes files are created in [lib/model|COMMENT].

This task never overrides custom classes in [lib/model|COMMENT].
It only replaces files in [lib/model/generated|COMMENT].
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $config = $this->getCliConfig();

   	$pluginSchemaDirectories = glob(sfConfig::get('sf_plugins_dir') . DIRECTORY_SEPARATOR . '*' .DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'doctrine'); 

   	$pluginSchemas = sfFinder::type('file')->name('*.yml')->in($pluginSchemaDirectories);

   	$tmpPath = sfConfig::get('sf_cache_dir') . DIRECTORY_SEPARATOR . 'tmp';

   	if ( ! file_exists($tmpPath))
   	{
   	  Doctrine_Lib::makeDirectories($tmpPath);
   	}

   	foreach ($pluginSchemas as $schema)
   	{
   	  $schema = str_replace('/', DIRECTORY_SEPARATOR, $schema);
   	  $plugin = str_replace(sfConfig::get('sf_plugins_dir') . DIRECTORY_SEPARATOR, '', $schema);
   	  $e = explode(DIRECTORY_SEPARATOR, $plugin);
   	  $plugin = $e[0];
   	  $name = basename($schema);

   	  $tmpSchemaPath = $tmpPath . DIRECTORY_SEPARATOR . $plugin . '-' . $name;

   	  $models = Doctrine_Parser::load($schema, 'yml');
      if(!isset($models['package'])) 
      {
        $models['package'] = $plugin . '.lib.model.doctrine'; 
      }
   	  Doctrine_Parser::dump($models, 'yml', $tmpSchemaPath);
   	}

    $import = new Doctrine_Import_Schema();
    $import->setOption('generateBaseClasses', true);
    $import->setOption('generateTableClasses', true);
    $import->setOption('packagesPath', sfConfig::get('sf_plugins_dir'));
    $import->setOption('packagesPrefix', 'Plugin');
    $import->setOption('suffix', '.class.php');
    $import->setOption('baseClassesDirectory', 'generated');
    $import->setOption('baseClassName', 'sfDoctrineRecord');

    $import->importSchema(array($tmpPath, $config['yaml_schema_path']), 'yml', $config['models_path']);

    $this->dispatcher->notify(new sfEvent($this, 'command.log', array($this->formatter->formatSection('doctrine', 'Generated models successfully'))));
  }
}