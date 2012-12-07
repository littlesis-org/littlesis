<?php
/*
 * This file is part of the sfDoctrinePlugin package.
 * (c) 2006-2007 Jonathan H. Wage <jonwage@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfDoctrine exception class
 *
 * @package    sfDoctrinePlugin
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfDoctrineException.class.php 8743 2008-05-03 05:02:39Z Jonathan.Wage $
 */
class sfDoctrineException extends sfException
{
  /**
   * Class constructor.
   *
   * @param string The error message.
   * @param int    The error code.
   */
  public function __construct($message = null, $code = 0)
  {
    $this->setName('sfDoctrineException');
    parent::__construct($message, $code);
  }
}