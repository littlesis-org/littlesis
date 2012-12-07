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
 * Loads data from fixtures directory.
 *
 * @package    sfDoctrinePlugin
 * @subpackage Task
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfDoctrineDataLoadTask.class.php 8743 2008-05-03 05:02:39Z Jonathan.Wage $
 */
class sfDoctrineLoadDataTask extends sfDoctrineBaseTask
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
    ));

    $this->aliases = array('doctrine-load-data');
    $this->namespace = 'doctrine';
    $this->name = 'data-load';
    $this->briefDescription = 'Loads data from fixtures directory';

    $this->detailedDescription = <<<EOF
The [doctrine:data-load|INFO] task loads data fixtures into the database:

  [./symfony doctrine:data-load frontend|INFO]

The task loads data from all the files found in [data/fixtures/|COMMENT].

If you want to load data from other directories, you can use
the [--dir|COMMENT] option:

  [./symfony doctrine:data-load --dir="data/fixtures" --dir="data/data" frontend|INFO]

If you don't want the task to remove existing data in the database,
use the [--append|COMMENT] option:

  [./symfony doctrine:data-load --append frontend|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);

    $arguments = array();
    if (isset($options['append']) && $options['append'])
    {
      $arguments['append'] = $options['append'];
    }

    if (isset($options['dir']) && $options['dir'])
    {
      $arguments['data_fixtures_path'] = $options['dir'];
    }

    $this->callDoctrineCli('load-data', $arguments);
  }
}