<?php include_partial('entity/header', array('entity' => $entity, 'show_actions' => true)) ?>

<?php include_partial('entity/edittabs', array('entity' => $entity)) ?>

<?php include_partial('global/pointer', array('text' => 
'The primary alias is how this ' . strtolower($entity->primary_ext) . '\'s name will appear
on profile pages and in links. To change the primary alias, add a new alias and make it primary, 
or make an existing alias primary.'
)) ?>

<div class="section">
<span class="section_title">Aliases</span>
<?php if ($sf_user->hasCredential('contributor')) : ?>
<span class="section_actions"><a href="#" onclick="showAddForm();">add</a></span>
<?php endif; ?>
</div>

<?php if (!$sf_user->isAuthenticated() || $sf_user->getProfile()->enable_pointers) : ?>
<div class="section_pointer">
  <?php echo $entity->name ?> is also known as:
</div>
<?php endif; ?>

<div class="padded" id="add_form" style="display: none;">
<?php include_partial('global/formerrors') ?>
<form action="<?php echo url_for($entity->getInternalUrl('addAlias')) ?>" method="POST">
<?php echo input_hidden_tag('id', $entity->id) ?>
<?php echo input_tag('alias', $sf_request->getParameter('alias'), 'size=30') ?> 

<?php if ($sf_user->hasCredential('admin')) : ?>
  &nbsp;context:&nbsp;
  <?php echo input_tag('context', $sf_request->getParameter('context'), 'size=20') ?> 
<?php endif; ?>

<?php echo submit_tag('Add', 'class=button_small') ?>
</form>
</div>

<div class="padded">
<?php foreach ($aliases as $alias) : ?>
  <span class="text_big">
    <?php echo $alias->name ?>
  </span>
  <?php if ($sf_user->hasCredential('admin') && $alias->context) : ?>
    <strong>[<?php echo $alias->context ?>]</strong> 
  <?php endif ; ?>
  <?php if ($alias->is_primary) : ?>
    <strong>[PRIMARY]</strong>
  <?php else : ?>
    <?php if ($sf_user->hasCredential('editor')) : ?>
      <?php echo link_to('primary', 'entity/makePrimaryAlias?id=' . $alias->id, 'post=true') ?>
    <?php endif; ?>
    <?php if ($sf_user->hasCredential('deleter')) : ?>
      <?php echo link_to('remove', 'entity/removeAlias?id=' . $alias->id, 'post=true confirm=Are you sure?') ?>
    <?php endif; ?>
  <?php endif; ?>
  <br />
<?php endforeach; ?>
</div>


<script>
function showAddForm()
{
  document.getElementById('add_form').style.display = 'block';
}
<?php if ($sf_request->hasErrors()) : ?>
showAddForm();
<?php endif; ?>
</script>