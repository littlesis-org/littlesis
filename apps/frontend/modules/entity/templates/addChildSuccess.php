<?php use_helper('Pager') ?>
<?php include_partial('entity/header', array('entity' => $entity, 'show_actions' => true)) ?>

<?php include_partial('entity/edittabs', array('entity' => $entity)) ?>

<h2>Add Child</h2>

Find the child organization you want to add as a subgroup of this one. 
(Children of other organizations will not appear in the search results.)
<br />
<br />

<form action="<?php echo url_for('entity/addChild') ?>">
<?php echo input_hidden_tag('id', $entity->id) ?>
<?php echo input_tag('child_terms', $sf_request->getParameter('child_terms')) ?>&nbsp;<input class="button_small" type="submit" value="Search" />
</form>

<br />
<br />


<?php if (isset($entity_pager)) : ?>

<?php include_partial('global/section', array(
  'title' => 'Search Results',
  'pager' => $entity_pager
)) ?>

<div class="padded">
  <?php foreach ($entity_pager->execute() as $result) : ?>
    <?php include_partial('entity/oneliner', array(
      'entity' => $result,
      'profile_link' => true,
      'actions' => array(array(
          'name' => 'add child',
          'url' => $entity->getInternalUrl('addChild', array('child_id' => $result->id)),
          'options' => 'post=true class="text_big" style="font-weight: bold"'
      ))      
    )) ?>
  <?php endforeach; ?>
  <?php echo pager_noresults($entity_pager) ?>
</div>

<?php endif; ?>