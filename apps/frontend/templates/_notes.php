<?php use_helper('Date') ?>

<?php $actions = array(array(
  'text' => 'see all',
  'url' => $more_uri ? $more_uri : $record->getInternalUrl('notes')
)) ?>

<?php 
$model = isset($model) ? $model : get_class($record);

if ($sf_user->isAuthenticated())
{
  $class = ($model == 'LsList') ? 'List' : $model;
  $actions[] = array(
     'text' => 'write a note',
      'url' => 'home/notes?compose=1&' . strtolower($class) . '_id=' . $record['id']
    );
  $actions[] = array(
     'text' => 'note all',
      'credential' => 'editor',
      'url' => 'home/notes?compose=1&analysts=1&' . strtolower($class) . '_id=' . $record['id']
  );
}
?>

<?php include_partial('global/section', array(
  'title' => 'Notes',
  'pointer' => 'Analyst notes about ' . (isset($name) ? $name : $record['name']),
  'actions' => $actions
)) ?>

<?php foreach (NoteTable::getNotesByModelAndIdQuery($model, $record['id'])->limit(5)->execute() as $note) : ?>
  <?php include_partial('note/short', array('note' => $note, 'record' => $record)) ?>
<?php endforeach; ?>