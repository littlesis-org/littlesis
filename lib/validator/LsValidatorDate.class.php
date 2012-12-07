<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * LsValidatorDate validates a date
 *
 * @package    symfony
 * @subpackage validator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfValidatorDate.class.php 9897 2008-06-26 15:09:49Z fabien $
 */
class LsValidatorDate extends sfValidatorBase
{
  protected function configure($options = array(), $messages = array())
  {
  }


  /**
   * @see sfValidatorBase
   */
  protected function doClean($value)
  {
    if (preg_match('#^\d{4}-\d{2}-\d{2}$#', Dateable::convertForDb($value)))
    {
      $clean = Dateable::convertForDb($value);
    }
    else
    {
      throw new sfValidatorError($this, 'invalid');
    }
    
    return $clean;
  }
}
