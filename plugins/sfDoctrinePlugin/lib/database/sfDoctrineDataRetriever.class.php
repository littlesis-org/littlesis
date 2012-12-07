<?php
/*
 * This file is part of the sfDoctrinePlugin package.
 * (c) 2006-2007 Jonathan H. Wage <jonwage@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package    sfDoctrinePlugin
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfDoctrineDataRetriever.class.php 8743 2008-05-03 05:02:39Z Jonathan.Wage $
 */
class sfDoctrineDataRetriever
{
  static public function retrieveObjects($class, $peer_method = 'findAll')
  {
    if (!$peer_method)
    {
      $peer_method = 'findAll';
    }

    $table = Doctrine::getTable($class);

    return call_user_func(array($table, $peer_method));
  }
}