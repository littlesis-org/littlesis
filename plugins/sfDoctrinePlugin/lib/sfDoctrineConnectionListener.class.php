<?php
/*
 * This file is part of the sfDoctrinePlugin package.
 * (c) 2006-2007 Jonathan H. Wage <jonwage@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Standard connection listener
 *
 * @package    sfDoctrinePlugin
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfDoctrineConnectionListener.class.php 8743 2008-05-03 05:02:39Z Jonathan.Wage $
 */
class sfDoctrineConnectionListener extends Doctrine_EventListener
{
  public function __construct($connection, $encoding)
  {
    $this->connection = $connection;
    $this->encoding = $encoding;
  }

  public function postConnect(Doctrine_Event $event)
  {
    $this->connection->setCharset($this->encoding);
    $this->connection->setDateFormat();
  }
}