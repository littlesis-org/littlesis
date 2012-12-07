<?php use_helper('Date') ?>

<?php include_partial('group/header', array('group' => $group)) ?>

<?php include_partial('global/section', array(
  'title' => 'Group Notes',
  'pager' => $note_pager,
  'actions' => array(
    array(
     'text' => 'write a note',
      'credential' => 'editor',
      'url' => 'home/notes?compose=1&group_id=' . $group['id']
    ),
    array(
     'text' => 'note all',
      'credential' => 'editor',
      'url' => 'home/notes?compose=1&analysts=1&group_id=' . $group['id']
    ))
)) ?>


<?php foreach ($note_pager->execute() as $note) : ?>
  <?php include_partial('note/full', array('note' => $note)) ?>    
<?php endforeach; ?>