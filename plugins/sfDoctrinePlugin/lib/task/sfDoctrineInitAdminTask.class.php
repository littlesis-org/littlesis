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
 * Initializes a Doctrine admin module.
 *
 * @package    sfDoctrinePlugin
 * @subpackage Task
 * @author     2006-2007 Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfDoctrineInitAdminTask.class.php 8743 2008-05-03 05:02:39Z Jonathan.Wage $
 */
class sfDoctrineInitAdminTask extends sfDoctrineBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application name'),
      new sfCommandArgument('module', sfCommandArgument::REQUIRED, 'The module name'),
      new sfCommandArgument('model', sfCommandArgument::REQUIRED, 'The model class name'),
    ));

    $this->addOptions(array(
      new sfCommandOption('theme', null, sfCommandOption::PARAMETER_REQUIRED, 'The theme name', 'default'),
    ));

    $this->aliases = array('doctrine-init-admin');
    $this->namespace = 'doctrine';
    $this->name = 'init-admin';
    $this->briefDescription = 'Initializes a Doctrine admin module';

    $this->detailedDescription = <<<EOF
The [doctrine:init-admin|INFO] task generates a Doctrine admin module:

  [./symfony doctrine:init-admin frontend article Article|INFO]

The task creates a [%module%|COMMENT] module in the [%application%|COMMENT] application
for the model class [%model%|COMMENT].

The created module is an empty one that inherit its actions and templates from
a runtime generated module in [%sf_app_cache_dir%/modules/auto%module%|COMMENT].

The generator can use a customized theme by using the [--theme|COMMENT] option:

  [./symfony doctrine:init-admin --theme="custom" frontend article Article|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $properties = parse_ini_file(sfConfig::get('sf_config_dir').'/properties.ini', true);

    $constants = array(
      'PROJECT_NAME' => isset($properties['name']) ? $properties['name'] : 'symfony',
      'APP_NAME'     => $arguments['application'],
      'MODULE_NAME'  => $arguments['module'],
      'MODEL_CLASS'  => $arguments['model'],
      'AUTHOR_NAME'  => isset($properties['author']) ? $properties['author'] : 'Your name here',
      'THEME'        => $options['theme'],
    );

    $moduleDir = sfConfig::get('sf_apps_dir').'/'.$arguments['application'].'/modules/'.$arguments['module'];

    // create module structure
    $finder = sfFinder::type('any')->ignore_version_control()->discard('.sf');
    $dirs = sfProjectConfiguration::getActive()->getGeneratorSkeletonDirs('sfDoctrineAdmin', $options['theme']);
    foreach ($dirs as $dir)
    {
      if (is_dir($dir))
      {
        $this->getFileSystem()->mirror($dir, $moduleDir, $finder);
        break;
      }
    }

    // customize php and yml files
    $finder = sfFinder::type('file')->name('*.php', '*.yml');
    $this->getFileSystem()->replaceTokens($finder->in($moduleDir), '##', '##', $constants);
  }
}