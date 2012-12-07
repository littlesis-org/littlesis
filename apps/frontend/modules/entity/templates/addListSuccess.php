<?php include_partial('entity/header', array('entity' => $entity, 'show_actions' => true)) ?>

<h2>Add to a List</h2>

Select a popular list to add this <?php echo strtolower($entity['primary_ext']) ?> to:
<br />
<br />

<div class="padded">
<?php foreach ($popular_lists as $popular_list) : ?>
  <?php include_partial('list/oneliner', array(
    'list' => $popular_list,
    'actions' => array(array(
      'name' => 'add',
      'url' => EntityTable::generateRoute($entity, 'addList', array('list_id' => $popular_list->id)),
      'options' => 'post=true'
    ))
  )) ?>
<?php endforeach; ?>
</div>
<br />
<br />


Or find another list:
<br />
<br />

<form action="<?php echo url_for('entity/addList') ?>">
<?php echo input_hidden_tag('id', $sf_request->getParameter('id')) ?>
<?php echo input_tag('add_list_terms', $sf_request->getParameter('add_list_terms')) ?> <?php echo submit_tag('Search', 'class=button_small') ?>
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
        'url' => EntityTable::generateRoute($entity, 'addList', array('list_id' => $list->id)),
        'options' => 'post=true'
      ))
    )) ?>
  <?php endforeach; ?>
</div>

<?php endif; ?>

