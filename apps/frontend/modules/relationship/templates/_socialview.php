<span class="text_big">

<?php slot('share_text') ?>

<?php if (RelationshipTable::areSameDescriptions($relationship)) : ?>
  <?php echo entity_link($relationship['Entity1']) ?> and <?php echo entity_link($relationship['Entity2']) ?> <?php echo (($current === NULL) ? "are/were" : ($current == '1' ? "are" : "were")) ?> <?php echo LsLanguage::pluralize($relationship['description1']) ?>
<?php else : ?>
  <?php echo entity_link($relationship['Entity1']) ?> and <?php echo entity_link($relationship['Entity2']) ?> <?php echo (($current === NULL) ? "have/had" : ($current == '1' ? "have" : "had")) ?> a social relationship
<?php endif; ?>

<?php end_slot() ?>
<?php echo get_slot('share_text') ?>
<?php slot('share_text', RelationshipTable::formatSentenceForShare(get_slot('share_text'))) ?>

</span>

<br />
<br />

<?php $data = array() ?>

<?php if (!RelationshipTable::areSameDescriptions($relationship)) : ?>
  <?php if ($desc1 = $relationship['description1']) : ?>
    <?php $data[ucfirst($desc1)] = entity_link($relationship['Entity1']) ?>
  <?php endif; ?>
  
  <?php if ($desc2 = $relationship['description2']) : ?>
    <?php $data[' ' . ucfirst($desc2)] = entity_link($relationship['Entity2']) ?>
  <?php endif; ?>
<?php endif; ?>
  
<?php $data = array_merge($data, array(
  'Start date' => Dateable::convertForDisplay($relationship['start_date']),
  'End date' => Dateable::convertForDisplay($relationship['end_date']),
  'Is current' => LsLogic::nullOrBoolean($relationship['is_current']),
  'Notes' => $relationship['notes'] ? nl2br($relationship['notes']) : null
)) ?>

<?php include_partial('global/section', array('title' => 'Details')) ?>

<div class="padded">
<?php include_partial('global/datatable', array('data' => $data)) ?>
</div>