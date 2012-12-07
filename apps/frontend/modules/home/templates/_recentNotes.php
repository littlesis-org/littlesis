<?php $actions = array(
  array(
    'text' => 'more',
    'url' => isset($more) ? $more : '@notes'
  )
) ?>

<?php 
if ($sf_user->isAuthenticated())
{
  $actions[] = array(
    'text' => 'write a note',
    'url' => isset($write) ? $write : 'home/notes?compose=1'
  );
}
?>

<?php 
if (@!$hide_header)
{
  include_partial('global/section', array(
    'title' => 'Analyst Notes',
    'pointer' => 'Recent notes by LittleSis analysts',
    'actions' => $actions
  )) ;
}
?>

<?php foreach ($notes as $note) : ?>
  <?php include_partial('note/full', array('note' => $note)) ?>
<?php endforeach; ?>