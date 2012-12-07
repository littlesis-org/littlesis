<?php use_helper('Pager') ?>
<?php include_partial('entity/header', array('entity' => $entity, 'show_actions' => true)) ?>

<?php include_partial('entity/edittabs', array('entity' => $entity)) ?>

<h2>Change Parent</h2>

<span class="text_big">Current Parent:</span>

<?php if ($entity->Parent->exists()) : ?>
  <?php echo entity_link($entity->Parent) ?> 
<?php else : ?>
  NONE
<?php endif; ?>

<br />
<br />

Find a new parent for this organization.
<br />
<br />

<form action="<?php echo url_for($entity->getInternalUrl('changeParent')) ?>">
<?php echo input_hidden_tag('id', $entity->id) ?>
<?php echo input_tag('parent_terms', $sf_request->getParameter('parent_terms')) ?>&nbsp;<input class="button_small" type="submit" value="Search" />
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
          'name' => 'set parent',
          'url' => $entity->getInternalUrl('changeParent', array('parent_id' => $result->id)),
          'options' => 'post=true class="text_big" style="font-weight: bold"'
      ))      
    )) ?>
  <?php endforeach; ?>
  <?php echo pager_noresults($entity_pager) ?>
</div>

<?php endif; ?>