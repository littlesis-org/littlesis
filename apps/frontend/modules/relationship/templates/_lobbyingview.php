<?php use_helper('LsNumber') ?>

<span class="text_big">
<?php slot('share_text') ?>
<?php echo entity_link($relationship['Entity1']) ?> <?php echo (($current === NULL) ? "lobbies/lobbied" : ($current == '1' ? "lobbies" : "lobbied")) ?> <?php echo entity_link($relationship['Entity2']) ?>
<?php end_slot() ?>
<?php echo get_slot('share_text') ?>
<?php slot('share_text', RelationshipTable::formatSentenceForShare(get_slot('share_text'))) ?>
</span>

<br />
<br />

<?php $data = array(
  'Start date' => Dateable::convertForDisplay($relationship['start_date']),
  'End date' => Dateable::convertForDisplay($relationship['end_date']),
  'Is current' => LsLogic::nullOrBoolean($relationship['is_current']),
  'Amount' => $relationship['amount'],
  'Notes' => $relationship['notes'] ? nl2br($relationship['notes']) : null
) ?>

<?php if ($relationship['filings'] && $transactions = RelationshipTable::getTransactionsFromLobbyFilingByIdQuery($relationship['id'])->leftJoin('r.Entity1 e1')->leftJoin('r.Entity2 e2')->execute()) : ?>
  <?php $transactionAry = array() ?>
  <?php foreach ($transactions as $transaction) : ?>
    <?php $client = $transaction->entity2_id == $relationship['entity1_id'] ? $transaction->Entity1 : $transaction->Entity2 ?>
    <?php $transactionAry[] = entity_link($client) . ' [' . link_to('see transaction', 'relationship/view?id=' . $transaction->id) . ']' ?>
  <?php endforeach; ?>
  <?php $data['On behalf of'] = $transactionAry ?>
<?php endif; ?>


<?php include_partial('global/section', array('title' => 'Details')) ?>

<div class="padded">
<?php include_partial('global/datatable', array('data' => $data)) ?>
</div>


<?php if ($relationship['filings']) : ?>
<br />
<br />

<?php include_component('relationship', 'lobbyFilings', array(
  'relationship' => $relationship,
  'page' => $sf_request->getParameter('page', 1),
  'num' => $sf_request->getParameter('num', 10)
)) ?>
<?php endif; ?>