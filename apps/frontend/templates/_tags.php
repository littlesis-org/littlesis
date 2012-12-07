<?php $visibleOnly = $sf_user->hasCredential('admin') ? false : true ?>


<div class="section">
<span class="section_title">Tags</span>
<?php if ($sf_user->hasCredential('contributor')) : ?>
  <span class="section_actions">
		<span id="add_tag_link"><a onclick="showAddTagForm();">add</a></span>
  </span>
<?php endif; ?>
</div>

<?php if (!$sf_user->isAuthenticated() || $sf_user->getProfile()->enable_pointers) : ?>
<div class="section_pointer">
  Useful keywords for this page
</div>
<?php endif; ?>

<div class="padded">

<?php foreach ($object->getTagsQuery(true, $visibleOnly)->execute() as $tag) : ?>
  <strong><?php echo link_to($tag->getName(), 'tag/view?name=' . $tag->getName()) ?></strong>
  <?php if ($sf_user->hasCredential('editor')) : ?>
    (<span class="text_small"><?php echo link_to('untag', 'tag/untag?name=' . $tag->getName() . '&object_id=' . $object->id . '&object_model=' . get_class($object), 'post=true') ?></span>)
  <?php endif; ?>
  <br />
<?php endforeach; ?>

<div id="add_tag_form" style="display: none;">
  <form action="<?php echo url_for('tag/addTag') ?>" method="POST">
  <?php echo input_hidden_tag('object_id', $object->id) ?>
  <?php echo input_hidden_tag('object_model', get_class($object)) ?>
  <?php echo input_tag('name', NULL, 'size=18 id=add_tag') ?>&nbsp;
  <?php echo submit_tag('Add') ?>
  </form>
</div>	

</div>


<script type="text/javascript">

showAddTagForm = function()
{
	document.getElementById('add_tag_link').style.display = 'none';
	document.getElementById('add_tag_form').style.display = 'block';
	document.getElementById('add_tag').focus();
}

</script>