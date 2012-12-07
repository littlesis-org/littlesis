<?php
/*
 * This file is part of the sfDoctrinePlugin package.
 * (c) 2006-2007 Jonathan H. Wage <jonwage@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Default Doctrine_Record listener
 * 
 * @package    sfDoctrinePlugin
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfDoctrineRecordListener.class.php 8743 2008-05-03 05:02:39Z Jonathan.Wage $
 */
class sfDoctrineRecordListener extends Doctrine_Record_Listener
{
  /**
   * preInsert
   *
   * @param string $Doctrine_Event 
   * @return void
   */
  public function preInsert(Doctrine_Event $event)
  {
    if ($event->getInvoker()->getTable()->hasColumn('created_at'))
    {
      $event->getInvoker()->created_at = date('Y-m-d H:i:s', time());
    }
    
    if ($event->getInvoker()->getTable()->hasColumn('updated_at'))
    {
      $event->getInvoker()->updated_at = date('Y-m-d H:i:s', time());
    }
  }
  
  /**
   * preUpdate
   *
   * @param string $Doctrine_Event 
   * @return void
   */
  public function preUpdate(Doctrine_Event $event)
  {
    if ($event->getInvoker()->getTable()->hasColumn('updated_at'))
    {
      $event->getInvoker()->updated_at = date('Y-m-d H:i:s', time());
    }
  }
}