<?php

/*
* This file is part of the symfony package.
* (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

/**
* Permission management.
*
* @package    symfony
* @subpackage plugin
* @author     Fabien Potencier <fabien.potencier@symfony-project.com>
* @version    SVN: $Id: actions.class.php 7634 2008-02-27 18:01:40Z fabien $
*/
class sfGuardPermissionActions extends autosfGuardPermissionActions
{
  protected function updatesfGuardPermissionFromRequest()
  {
    $sf_guard_permission = $this->getRequestParameter('sf_guard_permission');

    if (isset($sf_guard_permission['name']))
    {
      $this->sf_guard_permission->setName($sf_guard_permission['name']);
    }
    if (isset($sf_guard_permission['description']))
    {
      $this->sf_guard_permission->setDescription($sf_guard_permission['description']);
    }
    if (!empty($sf_guard_permission['module_name']))
    {
      $this->sf_guard_permission->setModuleName($sf_guard_permission['module_name']);
    }
    if (!empty($sf_guard_permission['action_name']))
    {
      $this->sf_guard_permission->setActionName($sf_guard_permission['action_name']);
    }
  }
}
