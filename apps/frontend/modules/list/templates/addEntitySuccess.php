<?php include_partial('list/header', array('list' => $list)) ?>

<h2>Add Person or Organization</h2>

Find a person or organization to add to this list.
<br />
<br />

<form action="<?php echo url_for($list->getInternalUrl('addEntity', null, true)) ?>">
<?php echo input_hidden_tag('id', $sf_request->getParameter('id')) ?>
<?php echo input_tag('q', $sf_request->getParameter('q')) ?> <?php echo submit_tag('Go', 'class=button_small') ?>
</form>

<br />
<br />


<?php if (isset($results_pager)) : ?>

<?php include_partial('global/section', array(
  'title' => 'Results',
  'pager' => $results_pager
)) ?>

<div class="padded">
  <?php foreach ($results_pager->execute() as $entity) : ?>
    <?php include_partial('entity/oneliner', array(
      'entity' => $entity,
      'profile_link' => true,
      'actions' => array(array(
        'name' => 'add',
        'url' => $list->getInternalUrl('addEntity', array('entity_id' => $entity['id'])),
        'options' => 'class="text_big" style="font-weight: bold" post=true'
      ))
    )) ?>
  <?php endforeach; ?>
</div>

<?php endif; ?>

