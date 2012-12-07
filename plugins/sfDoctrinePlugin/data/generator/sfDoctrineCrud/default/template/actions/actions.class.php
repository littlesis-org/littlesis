[?php

/**
 * <?php echo $this->getModuleName() ?> actions.
 *
 * @package    ##PROJECT_NAME##
 * @subpackage <?php echo $this->getModuleName() ?>

 * @author     ##AUTHOR_NAME##
 * @version    SVN: $Id: actions.class.php 8507 2008-04-17 17:32:20Z fabien $
 */
class <?php echo $this->getGeneratedModuleName() ?>Actions extends sfActions
{
  public function executeIndex()
  {
    $this-><?php echo $this->getSingularName() ?>List = $this->get<?php echo $this->getClassName() ?>Table()->findAll();
  }

<?php if (isset($this->params['with_show']) && $this->params['with_show']): ?>
  public function executeShow($request)
  {
    $this-><?php echo $this->getSingularName() ?> = $this->get<?php echo $this->getClassName() ?>ById(<?php echo $this->getRetrieveByPkParamsForAction(49, '$request->getParameter') ?>);
    $this->forward404Unless($this-><?php echo $this->getSingularName() ?>);
  }

<?php endif; ?>
<?php if (isset($this->params['non_atomic_actions']) && $this->params['non_atomic_actions']): ?>
  public function executeEdit($request)
  {
    $this->form = $this->get<?php echo $this->getClassName() ?>Form(<?php echo $this->getRetrieveByPkParamsForEdit(49, $this->getSingularName()) ?>);

    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getParameter('<?php echo $this->getSingularName() ?>'));
      if ($this->form->isValid())
      {
        $<?php echo $this->getSingularName() ?> = $this->form->save();

        $this->redirect('<?php echo $this->getModuleName() ?>/edit?<?php echo $this->getPrimaryKeyUrlParams() ?>);
      }
    }
  }
<?php else: ?>
  public function executeCreate()
  {
    $this->form = new <?php echo $this->getClassName() ?>Form();

    $this->setTemplate('edit');
  }

  public function executeEdit($request)
  {
    $this->form = $this->get<?php echo $this->getClassName() ?>Form(<?php echo $this->getRetrieveByPkParamsForEdit(49, $this->getSingularName()) ?>);
  }

  public function executeUpdate($request)
  {
    $this->forward404Unless($request->isMethod('post'));

    $this->form = $this->get<?php echo $this->getClassName() ?>Form(<?php echo $this->getRetrieveByPkParamsForEdit(49, $this->getSingularName()) ?>);

    $this->form->bind($request->getParameter('<?php echo $this->getSingularName() ?>'));
    if ($this->form->isValid())
    {
      $<?php echo $this->getSingularName() ?> = $this->form->save();

      $this->redirect('<?php echo $this->getModuleName() ?>/edit?<?php echo $this->getPrimaryKeyUrlParams() ?>);
    }

    $this->setTemplate('edit');
  }
<?php endif; ?>

  public function executeDelete($request)
  {
    $this->forward404Unless($<?php echo $this->getSingularName() ?> = $this->get<?php echo $this->getClassName() ?>ById(<?php echo $this->getRetrieveByPkParamsForAction(43, '$request->getParameter') ?>));

    $<?php echo $this->getSingularName() ?>->delete();

    $this->redirect('<?php echo $this->getModuleName() ?>/index');
  }
  
  private function get<?php echo $this->getClassName() ?>Table()
  {
    return Doctrine::getTable('<?php echo $this->getClassName() ?>');
  }
  
  private function get<?php echo $this->getClassName() ?>ById($id)
  {
    return $this->get<?php echo $this->getClassName() ?>Table()->find($id);
  }
  
  private function get<?php echo $this->getClassName() ?>Form($id)
  {
    $<?php echo $this->getSingularName() ?> = $this->get<?php echo $this->getClassName() ?>ById($id);
    
    if ($<?php echo $this->getSingularName() ?> instanceof <?php echo $this->getClassName() ?>)
    {
      return new <?php echo $this->getClassName() ?>Form($<?php echo $this->getSingularName() ?>);
    }
    else
    {
      return new <?php echo $this->getClassName() ?>Form();
    }
  }
}