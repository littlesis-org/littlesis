<?php if ($sf_user->isAuthenticated()) : ?>

<?php $text = (!isset($static_header) && isset($user)) ? ' to ' . $user->getProfile()->public_name : (isset($record) ? ' about ' . $record->getName() : '') ?>

<?php 
$params = array('compose' => 1);
if (isset($user))
{
  $params['user_id'] = $user->id;
}
elseif (isset($record))
{
  $class = get_class($record);
  $class = ($class == 'LsList') ? 'List' : $class;
  $params[strtolower($class) . '_id'] = $record->id;
}
?>

<div class="new_note_container">
<span class="new_note_header">
<?php echo link_to('Write a Note' . $text . ' &raquo;', 'home/notes?' . http_build_query($params)) ?>
</span>
</div>

<?php endif; ?>