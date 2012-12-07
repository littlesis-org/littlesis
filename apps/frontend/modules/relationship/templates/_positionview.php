<?php use_helper('LsNumber') ?>

<span class="text_big">
<?php slot('share_text') ?>
<?php echo entity_link($relationship['Entity1']) ?> <?php echo (($current === NULL) ? "has/had" : ($current == '1' ? "has" : "had")) ?> a position 
<?php echo RelationshipTable::getDisplayDescription($relationship) ? '(' . RelationshipTable::getDisplayDescription($relationship) . ')' : '' ?> 
<?php echo $relationship['Entity2']['primary_ext'] == 'Person' ? 'under' : 'at' ?> <?php echo entity_link($relationship['Entity2']) ?>
<?php end_slot() ?>
<?php echo get_slot('share_text') ?>
<?php slot('share_text', RelationshipTable::formatSentenceForShare(get_slot('share_text'))) ?>
</span>

<br />
<br />

<?php $data = array(
  'Title' => $relationship['description1'],
  'Start date' => Dateable::convertForDisplay($relationship['start_date']),
  'End date' => Dateable::convertForDisplay($relationship['end_date']),
  'Is current' => LsLogic::nullOrBoolean($relationship['is_current']),
  'Board member' => LsLogic::nullOrBoolean($relationship['is_board']),
  'Executive' => LsLogic::nullOrBoolean($relationship['is_executive']),
  'Employee' => LsLogic::nullOrBoolean($relationship['is_employee']),
  'Compensation' => readable_number($relationship['compensation'], '$'),
  'Notes' => $relationship['notes'] ? nl2br($relationship['notes']) : null
) ?>

<?php include_partial('global/section', array('title' => 'Details')) ?>

<div class="padded">
<?php include_partial('global/datatable', array('data' => $data)) ?>
</div>