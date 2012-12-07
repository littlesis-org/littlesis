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
 * Dumps data to the fixtures directory.
 *
 * @package    sfDoctrinePlugin
 * @subpackage Task
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfDoctrineDataDumpTask.class.php 8743 2008-05-03 05:02:39Z Jonathan.Wage $
 */
class sfDoctrineDumpDataTask extends sfDoctrineBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application name'),
      new sfCommandArgument('target', sfCommandArgument::OPTIONAL, 'The target filename'),
    ));

    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environement', 'dev'),
    ));

    $this->aliases = array('doctrine-dump-data');
    $this->namespace = 'doctrine';
    $this->name = 'data-dump';
    $this->briefDescription = 'Dumps data to the fixtures directory';

    $this->detailedDescription = <<<EOF
The [doctrine:data-dump|INFO] task dumps database data:

  [./symfony doctrine:data-dump frontend|INFO]

The task dumps the database data in [data/fixtures/%target%|COMMENT].

The dump file is in the YML format and can be reimported by using
the [doctrine:data-load|INFO] task.

  [./symfony doctrine:data-load frontend|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);

    $args = array();
    if (isset($arguments['target']))
    {
      $filename = $arguments['target'];

      if (!sfToolkit::isPathAbsolute($filename))
      {
        $dir = sfConfig::get('sf_data_dir') . DIRECTORY_SEPARATOR . 'fixtures';
        $filename = $dir . DIRECTORY_SEPARATOR . $filename;
      }
    
      $args = array('data_fixtures_path' => $filename);
    }

    $this->callDoctrineCli('dump-data', $args);
  }
}