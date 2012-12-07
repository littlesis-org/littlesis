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
 * Generates Doctrine model, SQL and initializes the database.
 *
 * @package    sfDoctrinePlugin
 * @subpackage Task
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfDoctrineBuildAllTask.class.php 12699 2008-11-06 18:29:12Z Jonathan.Wage $
 */
class sfDoctrineBuildAllTask extends sfDoctrineBaseTask
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

    $this->aliases = array('doctrine-build-all');
    $this->namespace = 'doctrine';
    $this->name = 'build-all';
    $this->briefDescription = 'Generates Doctrine model, SQL and initializes the database';

    $this->detailedDescription = <<<EOF
The [doctrine:build-all|INFO] task is a shortcut for three other tasks:

  [./symfony doctrine:build-all|INFO]

The task is equivalent to:

  [./symfony doctrine-build-db|INFO]
  [./symfony doctrine:build-model|INFO]
  [./symfony doctrine:insert-sql|INFO]

See those three tasks help page for more information.
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $buildDb = new sfDoctrineBuildDbTask($this->dispatcher, $this->formatter);
    $buildDb->setCommandApplication($this->commandApplication);
    $buildDb->run(array('application' => $arguments['application']), array('--env='.$options['env']));

    $buildModel = new sfDoctrineBuildModelTask($this->dispatcher, $this->formatter);
    $buildModel->setCommandApplication($this->commandApplication);
    $buildModel->run(array('application' => $arguments['application']), array('--env='.$options['env']));

    $insertSql = new sfDoctrineInsertSqlTask($this->dispatcher, $this->formatter);
    $insertSql->setCommandApplication($this->commandApplication);
    $insertSql->run(array('application' => $arguments['application']), array('--env='.$options['env']));
  }
}