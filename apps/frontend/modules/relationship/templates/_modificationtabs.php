<?php $current_action = $sf_request->getParameter('action') ?>

<?php $tabs = array() ?>
<?php $tab_actions = array(
  'Basic' => 'modifications'
) ?>

<?php foreach ($tab_actions as $text => $action) : ?>
  <?php if ($action == $current_action) : ?>
    <?php $tabs[$text] = null ?>
  <?php else : ?>
    <?php $tabs[$text] = 'relationship/' . $action . '?id=' . $relationship->id ?>
  <?php endif; ?>
<?php endforeach; ?>

<?php include_partial('global/contenttabs', array('tabs' => $tabs)) ?>
