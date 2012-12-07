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
 * Drops Databases, Creates Databases, Generates Doctrine model, SQL, initializes database, load data and run 
 * all test suites
 *
 * @package    sfDoctrinePlugin
 * @subpackage Task
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfDoctrineBuildAllReloadTask.class.php 8743 2008-05-03 05:02:39Z Jonathan.Wage $
 */
class sfDoctrineBuildAllReloadTestAllTask extends sfDoctrineBaseTask
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
      new sfCommandOption('append', null, sfCommandOption::PARAMETER_NONE, 'Don\'t delete current data in the database'),
      new sfCommandOption('dir', null, sfCommandOption::PARAMETER_REQUIRED | sfCommandOption::IS_ARRAY, 'The directories to look for fixtures'),
      new sfCommandOption('force', null, sfCommandOption::PARAMETER_NONE, 'Whether to force dropping of the database'),
    ));

    $this->aliases = array('doctrine-build-all-reload-test-all');
    $this->namespace = 'doctrine';
    $this->name = 'build-all-reload-test-all';
    $this->briefDescription = 'Generates Doctrine model, SQL, initializes database, load data and run all test suites';

    $this->detailedDescription = <<<EOF
The [doctrine:build-all-reload|INFO] task is a shortcut for four other tasks:

  [./symfony doctrine:build-all-reload-test-all frontend|INFO]

The task is equivalent to:
  
  [./symfony doctrine:drop-db|INFO]
  [./symfony doctrine:build-db|INFO]
  [./symfony doctrine:build-model|INFO]
  [./symfony doctrine:insert-sql|INFO]
  [./symfony doctrine:data-load frontend|INFO]
  [./symfony test-all|INFO]

The task takes an application argument because of the [doctrine:data-load|COMMENT]
task. See [doctrine:data-load|COMMENT] help page for more information.
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $buildAllReload = new sfDoctrineBuildAllReloadTask($this->dispatcher, $this->formatter);
    $buildAllReload->setCommandApplication($this->commandApplication);

    $buildAllReloadOptions = array();
    $buildAllReloadOptions[] = '--env='.$options['env'];
    if (!empty($options['dir']))
    {
      $buildAllReloadOptions[] = '--dir=' . implode(' --dir=', $options['dir']);
    }
    if (isset($options['append']) && $options['append'])
    {
      $buildAllReloadOptions[] = '--append';
    }
    if (isset($options['force']) && $options['force'])
    {
      $buildAllReloadOptions[] = '--force';
    }
    $buildAllReload->run(array('application' => $arguments['application']), $buildAllReloadOptions);

    $testAll = new sfTestAllTask($this->dispatcher, $this->formatter);
    $testAll->setCommandApplication($this->commandApplication);
    $testAll->run();
  }
}