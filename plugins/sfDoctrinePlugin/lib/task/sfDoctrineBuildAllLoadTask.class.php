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
 * Creates Databases, Generates Doctrine model, SQL, initializes database, and load data.
 *
 * @package    sfDoctrinePlugin
 * @subpackage Task
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfDoctrineBuildAllLoadTask.class.php 8774 2008-05-05 06:25:14Z Jonathan.Wage $
 */
class sfDoctrineBuildAllLoadTask extends sfDoctrineBaseTask
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

    $this->aliases = array('doctrine-build-all-load');
    $this->namespace = 'doctrine';
    $this->name = 'build-all-load';
    $this->briefDescription = 'Generates Doctrine model, SQL, initializes database, and load data';

    $this->detailedDescription = <<<EOF
The [doctrine:build-all-load|INFO] task is a shortcut for four other tasks:

  [./symfony doctrine:build-all-load frontend|INFO]

The task is equivalent to:

  [./symfony doctrine-build-db|INFO]
  [./symfony doctrine:build-model|INFO]
  [./symfony doctrine:insert-sql|INFO]
  [./symfony doctrine:data-load frontend|INFO]

The task takes an application argument because of the [doctrine:build-db|COMMENT], 
[doctrine:insert-sql|COMMENT], and [doctrine:data-load|COMMENT] task. See 
[doctrine:data-load|COMMENT] help page for more information.
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $buildAll = new sfDoctrineBuildAllTask($this->dispatcher, $this->formatter);
    $buildAll->setCommandApplication($this->commandApplication);
    $buildAll->run(array('application' => $arguments['application']), array('--env='.$options['env']));

    $loadData = new sfDoctrineLoadDataTask($this->dispatcher, $this->formatter);
    $loadData->setCommandApplication($this->commandApplication);

    $loadDataOptions = array();
    $loadDataOptions[] = '--env='.$options['env'];
    if (!empty($options['dir']))
    {
      $loadDataOptions[] = '--dir=' . implode(' --dir=', $options['dir']);
    }
    if (isset($options['append']) && $options['append'])
    {
      $loadDataOptions[] = '--append';
    }

    $loadData->run(array('application' => $arguments['application']), $loadDataOptions);
  }
}