<?php if ($sf_user->isAuthenticated()) : ?>

<?php use_helper('Javascript') ?>

<div class="new_note_container">

<span class="new_note_header"><a href="javascript:void(0);" onclick="swapNewNote();">
<?php $str = isset($static_header) ? '' : (isset($user) ? ' to ' . $user->getProfile()->public_name : (isset($record) ? ' about ' . $record->getName() : '')) ?>
<?php echo 'Write a Note' . $str . ' &raquo;' ?>
</a></span>

<div id="new_note" style="display: none;">

<br />

<div style="padding: 0.5em; background-color: #f8f8f8; border: 1px dotted #ddd;">
<li>Alert other analysts using <strong>@kevin</strong>. 
<li>Link to pages using <strong>@entity:14305[Cheney]</strong> or <strong>@list:1[Fortune 1000]</strong> or <strong>@rel:23[CEO]</strong>. 
<li>HTML will not be rendered, though you can include links.
</div>

<br />

<?php include_partial('global/formerrors', array('form' => $form)) ?>

<form action="<?php echo url_for('home/notes') ?>" method="POST">
<?php echo $form['_csrf_token'] ?>

<div class="note_form">



<script type="text/javascript">
function displayMarkup(markup)
{
  document.getElementById('markup').innerHTML = markup;
  document.getElementById('copy-link').style.display = 'inline';
}

function copyMarkup()
{
  field = document.getElementById('note-body');
  markup = document.getElementById('markup').innerHTML;
  field.value = field.value + ' ' + markup;
}
</script>


<?php if ($sf_user->hasCredential('contributor')) : ?>
Find an entity: 
<?php echo input_auto_complete_tag(
  'name', 
  null,
  'note/nameSearch',
  array('autocomplete' => 'off', 'onkeypress' => 'return event.keyCode!=13'),
  array('use_style' => 'true')
) ?>
&nbsp;&nbsp;
<span id="markup" style="font-weight: bold;"></span> 
<a href="javascript:void(0);" id="copy-link" style="display: none;" class="text_small" onclick="copyMarkup();">copy</a>
<br />
<br />
<?php endif; ?>

<?php 
$initialValue = '';
if (isset($user))
{
  $initialValue = NoteTable::getUserMarkup($user);
}
elseif (isset($record))
{
  if (isset($analysts))
  {
    $handles = array();
    foreach ($analysts as $analyst)
    {
      $handles[] = NoteTable::getUserMarkup($analyst);
    }
    $initialValue = "To all " . NoteTable::getRecordMarkup($record, $record->getName()) . " analysts (" . implode(", ",$handles) . "): \n";
  }
  else
  {
    $initialValue = NoteTable::getRecordMarkup($record, $record->getName());
  }
}
?>

<?php if (isset($networks)) : ?>
  <?php foreach ($networks as $network) : ?>
    <?php $isChecked = isset($network_ids) ? in_array($network['id'], $network_ids) : ($network['id'] == sfGuardUserTable::getHomeNetworkId()) ?>
    <?php echo checkbox_tag('network_ids[]', $network['id'], $isChecked) ?>
    <?php echo $network['name'] ?>
    <br />
  <?php endforeach; ?>
  <br />
<?php endif; ?>

<?php echo textarea_tag('note[body]', $sf_request->getParameter('note[body]', $initialValue), 'id=note-body') ?>

<?php echo submit_tag('Post') ?>&nbsp;&nbsp;

<?php echo checkbox_tag('note[is_private]', true, (isset($private) && $private)) ?> Private 
&nbsp;<span class="form_help">(private notes can only be seen by you and alerted analysts)</span>

</div>

</form>

</div>

</div>


<script type="text/javascript">

function swapNewNote()
{
  newNote = $('new_note');

  if (newNote.visible())
  {
    <?php echo visual_effect('BlindUp', 'new_note', array('duration' => 0.3)) ?>
  }
  else
  {
    <?php echo visual_effect('BlindDown', 'new_note', array('duration' => 0.3)) ?>
  }
}

<?php if (isset($compose) || $form->getErrorSchema()->getErrors()) : ?>
swapNewNote();
<?php endif; ?>

</script>

<?php endif; ?>