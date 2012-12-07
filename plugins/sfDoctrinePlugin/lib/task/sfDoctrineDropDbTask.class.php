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
 * Drops database for current model.
 *
 * @package    sfDoctrinePlugin
 * @subpackage Task
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfDoctrineDropDbTask.class.php 8743 2008-05-03 05:02:39Z Jonathan.Wage $
 */
class sfDoctrineDropDbTask extends sfDoctrineBaseTask
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
      new sfCommandOption('force', null, sfCommandOption::PARAMETER_NONE, 'Whether to force dropping of the database')
    ));

    $this->aliases = array('doctrine-drop-db');
    $this->namespace = 'doctrine';
    $this->name = 'drop-db';
    $this->briefDescription = 'Drops database for current model';

    $this->detailedDescription = <<<EOF
The [doctrine:drop-db|INFO] task drops the database:

  [./symfony doctrine:drop-db|INFO]

The task read connection information in [config/doctrine/databases.yml|COMMENT]:
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    $this->callDoctrineCli('drop-db', array('force' => $options['force']));
  }
}