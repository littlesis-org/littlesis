<?php include_partial('entity/header', array('entity' => $entity, 'show_actions' => true)) ?>

<?php include_partial('entity/modificationtabs', array('entity' => $entity)) ?>

<?php include_partial('global/section', array(
  'title' => 'Relationship Modifications',
  'pointer' => 'Relationships with ' . $entity->name . ' that were recently added or removed'
)) ?>

<?php $holder = new sfParameterHolder ?>
<?php $holder->set('fromMerges', array()) ?>
<?php include_partial('global/table', array(
  'columns' => array('Date', 'User', 'Action', 'Relationship'),
  'pager' => $modification_pager,
  'row_partial' => 'modification/relationshiplistrow',
  'holder' => $holder
)) ?>