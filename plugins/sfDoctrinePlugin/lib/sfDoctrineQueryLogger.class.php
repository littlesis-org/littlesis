<?php
/*
 * This file is part of the sfDoctrinePlugin package.
 * (c) 2006-2007 Jonathan H. Wage <jonwage@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfDoctrine query logger. Logs queries to file and web debug toolbar
 *
 * @package    sfDoctrinePlugin
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfDoctrineQueryLogger.class.php 8938 2008-05-14 01:52:35Z Jonathan.Wage $
 */
class sfDoctrineQueryLogger extends Doctrine_EventListener
{
  protected $connection = null;
  protected $encoding = 'UTF8';

  /**
   * Log a query before it is executed
   *
   * @param Doctrine_Event $event
   * @return void
   */
  public function preExecute(Doctrine_Event $event)
  {
    $this->sfLogQuery('{sfDoctrine Execute} executeQuery : ', $event);
  }

  /**
   * Add the time after a query is executed
   *
   * @param Doctrine_Event $event
   * @return void
   */
  public function postExecute(Doctrine_Event $event)
  {
    $this->sfAddTime();
  }

  /**
   * Add the time after a query is prepared
   *
   * @param Doctrine_Event $event
   * @return void
   */
  public function postPrepare(Doctrine_Event $event)
  {
    $this->sfAddTime();
  }

  /**
   * Before a query statement is executed log it
   *
   * @param Doctrine_Event $event
   * @return void
   */
  public function preStmtExecute(Doctrine_Event $event)
  {
    $this->sfLogQuery('{sfDoctrine Statement} executeQuery : ', $event);
  }

  /**
   * postStmtExecute
   *
   * @param Doctrine_Event $event
   * @return void
   */
  public function postStmtExecute(Doctrine_Event $event)
  {
    $this->sfAddTime();
  }

  /**
   * Log a query before it is executed
   *
   * @param Doctrine_Event $event
   * @return void
   */
  public function preQuery(Doctrine_Event $event)
  {
    $this->sfLogQuery('{sfDoctrine Query} executeQuery : ', $event);
  }

  /**
   * Post query add the time
   *
   * @param string $Doctrine_Event
   * @return void
   */
  public function postQuery(Doctrine_Event $event)
  {
    $this->sfAddTime();
  }

  /**
   * Log a Doctrine_Query
   *
   * @param string $message
   * @param string $event
   * @return void
   */
  protected function sfLogQuery($message, $event)
  {
    $message .= $event->getQuery();

    if ($params = $event->getParams())
    {
      $message .= ' - ('.implode(', ', $params) . ' )';
    }

    if (sfContext::hasInstance())
    {
      sfContext::getInstance()->getLogger()->log($message);
    }

    $sqlTimer = sfTimerManager::getTimer('Database (Doctrine)');
  }

  /**
   * Add the time to the log
   *
   * @return void
   */
  protected function sfAddTime()
  {
    sfTimerManager::getTimer('Database (Doctrine)')->addTime();
  }
}