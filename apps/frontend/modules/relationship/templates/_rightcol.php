<?php slot('rightcol') ?>

<?php if ($sf_user->isAuthenticated() || !cache('rightcol', 86400)) : ?>
  <?php include_partial('global/modifications', array(
    'object' => $relationship,
    'more_uri' => RelationshipTable::getInternalUrl($relationship, 'modifications')
  )) ?>
  <?php include_component('reference', 'list', array(
    'object' => $relationship,
    'model' => 'Relationship',
    'more_uri' => RelationshipTable::getInternalUrl($relationship, 'references')
  )) ?>
  <?php if (!$sf_user->isAuthenticated()) : ?>
    <?php cache_save() ?>
  <?php endif; ?>
<?php endif; ?>

<br />  
<?php include_component('note', 'recordSample', array(
  'record' => $relationship,
  'more_uri' => RelationshipTable::getInternalUrl($relationship, 'notes'),
  'model' => 'Relationship',
  'name' => 'this relationship'
)) ?>

<?php end_slot() ?>
