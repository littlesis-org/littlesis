<?php slot('rightcol') ?>


<?php include_partial('global/modifications', array('object' => $entity, 'more_uri' => EntityTable::getInternalUrl($entity, 'modifications'))) ?>


<?php if (!cache('leftcol_profileimage', 86400)) : ?>
  <?php include_partial('entity/profileimage', array('entity' => $entity)) ?>
  <br />
  <?php cache_save() ?>
<?php endif; ?>


<?php if ($sf_user->isAuthenticated() || !cache('leftcol_references', 86400)) : ?>
  <?php include_component('reference', 'list', array(
    'object' => $entity, 
    'model' => 'Entity', 
    'more_uri' => EntityTable::getInternalUrl($entity, 'references')
  )) ?>

  <?php if (!$sf_user->isAuthenticated()) : ?>
    <?php cache_save() ?>
  <?php else : ?>
     <?php echo "find more on: " . link_to('google','http://www.google.com/#q=' . urlencode($entity['name']), "target='_blank'") ?><br>
  <?php endif; ?>
    <br />
<?php endif; ?>


<?php if (!cache('leftcol_stats', 86400)) : ?>
  <?php include_partial('entity/vitalstats', array('entity' => $entity)) ?>
  <br />
  <?php cache_save() ?>
<?php endif; ?>

<?php if ($sf_user->hasCredential('admin')) : ?>
  <?php include_partial('entity/adminlinks', array('entity' => $entity)) ?>
  <br />
  
<?php endif; ?>

<?php include_partial('entity/expertlinks', array('entity' => $entity)) ?>

<?php if ($sf_user->isAuthenticated() || !cache('leftcol_lists', 86400)) : ?>
  <?php include_partial('global/lists', array('entity' => $entity)) ?>
  <br />
  <?php if (!$sf_user->isAuthenticated()) : ?>
    <?php cache_save() ?>
  <?php endif; ?>
<?php endif; ?>



<?php include_component('note', 'recordSample', array(
  'record' => $entity,
  'model' => 'Entity',
  'more_uri' => EntityTable::getInternalUrl($entity, 'notes')
)) ?>
<br />


<?php if ($sf_user->hasCredential('editor') || $sf_user->hasCredential('merger')) : ?>

  <?php if ($sf_user->hasCredential('merger') && !cache('similarEntities')) : ?>
    <?php include_component('entity', 'similarEntities', array('entity' => $entity)) ?>
    <?php cache_save() ?>
  <?php endif; ?>
  
  <?php /* TOO SLOW FOR NOW
  <?php if ($sf_user->hasCredential('editor') && !cache('watchers')) : ?>
    <?php include_component('entity', 'watchers', array('entity' => $entity)) ?>
    <?php cache_save() ?>
  <?php endif; ?>
  */ ?>

<?php endif; ?>


<?php end_slot() ?>
