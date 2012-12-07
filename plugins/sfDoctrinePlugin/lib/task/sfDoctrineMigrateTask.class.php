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
 * Inserts SQL for current model.
 *
 * @package    sfDoctrinePlugin
 * @subpackage Task
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfDoctrineMigrateTask.class.php 8915 2008-05-13 01:23:45Z Jonathan.Wage $
 */
class sfDoctrineMigrateTask extends sfDoctrineBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application name'),
      new sfCommandArgument('version', sfCommandArgument::OPTIONAL, 'The version to migrate to', null),
    ));

    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
    ));

    $this->aliases = array('doctrine-migrate');
    $this->namespace = 'doctrine';
    $this->name = 'migrate';
    $this->briefDescription = 'Migrates database to current/specified version';

    $this->detailedDescription = <<<EOF
The [doctrine:migrate|INFO] task migrates database to current/specified version

  [./symfony doctrine:migrate|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);

    $this->callDoctrineCli('migrate', array('version' => $arguments['version']));
  }
}