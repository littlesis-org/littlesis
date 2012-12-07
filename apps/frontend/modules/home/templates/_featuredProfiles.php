<?php if (count($featured_ids)) : ?>

<?php
if (@!$hide_header) 
{
  include_partial('global/section', array(
    'title' => 'Featured Profiles',
    'pointer' => 'People and organizations in the news'
  ));
}
?>

<?php foreach ($featured_ids as $id) : ?>
  <?php include_component('entity', 'mini', array('id' => $id)) ?>
<?php endforeach; ?>

<br />
<br />
<?php endif; ?>
