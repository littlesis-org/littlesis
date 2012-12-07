<?php $current_action = sfContext::getInstance()->getActionName() ?>

<?php $tabs = array() ?>
<?php $tab_actions = array(
  'Basic' => 'modifications',
  'Relationships' => 'relationshipModifications',
  'Images' => 'imagesModifications'
) ?>

<?php foreach ($tab_actions as $text => $action) : ?>
  <?php if ($action == $current_action) : ?>
    <?php $tabs[$text] = null ?>
  <?php else : ?>
    <?php $tabs[$text] = $entity->getInternalUrl($action) ?>
  <?php endif; ?>
<?php endforeach; ?>

<?php include_partial('global/contenttabs', array('tabs' => $tabs)) ?>
