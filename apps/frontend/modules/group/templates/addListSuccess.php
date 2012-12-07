<?php include_partial('group/header', array('group' => $group)) ?>


<h2>Add a List</h2>

Search for a research list to add to this group:<br />
<br />

<form action="<?php echo url_for($group->getInternalUrl('addList')) ?>">
<?php echo input_hidden_tag('name', $sf_request->getParameter('name')) ?>
<?php echo input_tag('q', $sf_request->getParameter('q')) ?> <?php echo submit_tag('Search', 'class=button_small') ?>
</form>

<br />
<br />


<?php if (isset($results_pager)) : ?>

<?php include_partial('global/section', array(
  'title' => 'Results',
  'pager' => $results_pager
)) ?>

<div class="padded">
  <?php foreach ($results_pager->execute() as $list) : ?>
    <?php include_partial('list/oneliner', array(
      'list' => $list,
      'actions' => array(array(
        'name' => 'add',
        'url' => sfGuardGroupTable::getInternalUrl($group, 'addList', array('list_id' => $list->id)),
        'options' => 'post=true'
      ))
    )) ?>
  <?php endforeach; ?>
</div>

<?php endif; ?>

