<?php use_helper('LsNumber') ?>

<span class="text_big">

<?php slot('share_text') ?>

<?php if (RelationshipTable::areSameDescriptions($relationship)) : ?>
  <?php echo entity_link($relationship['Entity1']) ?> and <?php echo entity_link($relationship['Entity2']) ?> were/are <?php echo LsLanguage::pluralize($relationship['description1']) ?>
<?php else : ?>
  <?php echo entity_link($relationship['Entity1']) ?> and <?php echo entity_link($relationship['Entity2']) ?> did/do business
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
  'Amount' => readable_number($relationship['amount'], '$'),
  'Goods' => $relationship['goods'],
  'Notes' => $relationship['notes'] ? nl2br($relationship['notes']) : null
)) ?>

<?php if ($relationship['filings'] && $lobbyings = RelationshipTable::getLobbyingsFromLobbyFilingById($relationship['id'])) : ?>
  <?php $agencyAry = array() ?>
  <?php foreach ($lobbyings as $lobbying ) : ?> 
    <?php if (in_array($lobbying->entity1_id, array($relationship['entity1_id'], $relationship['entity2_id']))) : ?>
      <?php $agency = $lobbying->Entity2 ?>
    <?php else : ?>
      <?php $agency = $lobbying->Entity1 ?>
    <?php endif ; ?>
    <?php $agencyAry[] = entity_link($agency) . ' [' . link_to('see lobbying', 'relationship/view?id=' . $lobbying->id) . ']' ?>
  <?php endforeach; ?>
  <?php $data['Agencies lobbied'] = $agencyAry ?>
<?php endif; ?>

<?php include_partial('global/section', array('title' => 'Details')) ?>

<div class="padded">
<?php include_partial('global/datatable', array('data' => $data)) ?>
</div>


<?php if ($relationship['is_lobbying'] && $relationship['filings']) : ?>
<br />
<br />
<?php include_component('relationship', 'lobbyFilings', array(
  'relationship' => $relationship,
  'page' => $sf_request->getParameter('page', 1),
  'num' => $sf_request->getParameter('num', 10)
)) ?>
<?php endif; ?>


<?php if ($relationship['filings']) : ?>
<br />
<br />
<?php include_component('relationship', 'fedspendingFilings', array(
  'relationship' => $relationship,
  'page' => $sf_request->getParameter('page', 1),
  'num' => $sf_request->getParameter('num', 10)
)) ?>
<?php endif; ?>
