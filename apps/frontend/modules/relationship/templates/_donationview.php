<?php use_helper('LsNumber') ?>

<span class="text_big">
<?php slot('share_text') ?>
<?php echo entity_link($relationship['Entity1']) ?> <?php echo (($current === NULL) ? "gives/gave" : ($current == '1' ? "gives" : "gave")) ?> money to <?php echo entity_link($relationship['Entity2']) ?>
<?php end_slot() ?>
<?php echo get_slot('share_text') ?>
<?php slot('share_text', RelationshipTable::formatSentenceForShare(get_slot('share_text'))) ?>
</span>

<br />
<br />

<?php $data = array(
  'Type' => $relationship['description1'],
  'Start date' => Dateable::convertForDisplay($relationship['start_date']),
  'End date' => Dateable::convertForDisplay($relationship['end_date']),
  'Is current' => LsLogic::nullOrBoolean($relationship['is_current']),
  'Amount' => readable_number($relationship['amount'], '$'),
  'Goods' => $relationship['goods'],
  'Notes' => $relationship['notes'] ? nl2br($relationship['notes']) : null
) ?>

<?php include_partial('global/section', array('title' => 'Details')) ?>

<div class="padded">
<?php include_partial('global/datatable', array('data' => $data)) ?>
</div>

<?php if ($relationship['filings']) : ?>
<br />
<br />

<?php include_component('relationship', 'fecFilings', array(
  'relationship' => $relationship,
  'page' => $sf_request->getParameter('page', 1),
  'num' => $sf_request->getParameter('num', 10)
)) ?>
<?php endif; ?>