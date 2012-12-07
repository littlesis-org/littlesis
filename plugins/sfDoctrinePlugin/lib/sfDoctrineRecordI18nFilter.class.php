<?php

/*
 * This file is part of the symfony package.
 * (c) Jonathan H. Wage <jonwage@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfDoctrineRecordI18nFilter implements access to the translated properties for
 * the current culture from the internationalized model.
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfDoctrineRecordI18nFilter.class.php 11878 2008-09-30 20:14:40Z Jonathan.Wage $
 */
class sfDoctrineRecordI18nFilter extends Doctrine_Record_Filter
{
  public function init()
  {
  }

  public function filterSet(Doctrine_Record $record, $name, $value)
  {
    return $record['Translation'][sfDoctrineRecord::getDefaultCulture()][$name] = $value;
  }

  public function filterGet(Doctrine_Record $record, $name)
  {
    return $record['Translation'][sfDoctrineRecord::getDefaultCulture()][$name];
  }
}