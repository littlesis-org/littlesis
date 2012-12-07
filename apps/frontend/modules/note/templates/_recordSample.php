<?php use_helper('Date') ?>


<?php include_partial('global/section', array(
  'title' => 'Notes',
  'pointer' => 'Analyst notes about ' . (isset($name) ? $name : $record['name']),
  'actions' => $actions
)) ?>

<?php foreach ($notes as $note) : ?>
  <?php include_partial('note/short', array('note' => $note, 'record' => $record)) ?>
<?php endforeach; ?>