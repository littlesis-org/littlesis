<?php use_helper('Pager') ?>

<?php include_partial('list/header', array('list' => $list, 'show_actions' => true)) ?>


<h2>Search for a Member</h2>

<form action="<?php echo url_for($list->getInternalUrl('findMember', null, true)) ?>">
<?php echo input_hidden_tag('id', $list->id) ?>
<?php echo input_tag('member_search_terms', $sf_request->getParameter('member_search_terms')) ?> 
<?php echo submit_tag('Search', 'class=button_small') ?>
</form>

<br />
<br />


<?php include_partial('global/section', array(
  'title' => 'Results',
  'pager' => $member_pager
)) ?>

<div class="padded">
  <?php foreach ($member_pager->execute() as $entity) : ?>
    <?php include_partial('entity/oneliner', array('entity' => $entity)) ?>
  <?php endforeach; ?>
</div>