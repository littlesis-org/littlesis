<?php use_helper('Date', 'LsText') ?>

<?php slot('header_text', 'Recent Updates') ?>
<?php slot('header_link', 'modification/latest') ?>

<span class="text_big">
Below are the profiles that have been most recently updated by a LittleSis analyst or a bot.
</span>
<br />
<br />

<?php include_partial('global/section', array('title' => 'Updated Profiles')) ?>

<?php include_partial('global/table', array(
  'columns' => array('Modified By', 'Name', 'About'),
  'pager' => $entity_pager,
  'row_partial' => 'entity/updatedlistrow'
)) ?>