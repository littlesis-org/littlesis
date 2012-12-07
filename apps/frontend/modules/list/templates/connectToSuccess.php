<?php use_helper('Pager') ?>
<?php include_partial('list/header', array('list' => $list)) ?>


<h2>Connect To</h2>

<?php if (isset($entity)) : ?>

<span style="font-size: 14px;">
Displaying connections to <?php echo entity_link($entity) ?> 
</span>
<?php echo button_to('Start Over', $list->getInternalUrl('connectTo'), 'class=button_small') ?>

<?php else : ?>

Search for the person or organization you want to connect this list to.
<br />
<br />

<form action="<?php echo url_for($list->getInternalUrl('connectTo')) ?>">
<?php echo input_hidden_tag('id', $list['id']) ?>
<?php echo input_tag('q', $sf_request->getParameter('q')) ?>&nbsp;<input class="button_small" type="submit" value="Search" />
</form>

<?php endif; ?>

<br />
<br />


<?php if (isset($matches_pager)) : ?>

<br />

<?php include_partial('global/section', array(
  'title' => 'Connections',
)) ?>

<?php include_partial('global/table', array(
  'columns' => array('List Member and Relationships'),
  'pager' => $matches_pager,
  'row_partial' => 'list/connecttorow',
  'base_object' => $entity
)) ?>


<?php elseif ($sf_request->getParameter('entity_id')) : ?>

<strong>No connections found.</strong>

<?php endif; ?>




<?php if (isset($entity_pager)) : ?>

<?php include_partial('global/section', array(
  'title' => 'Search Results',
  'pager' => $entity_pager
)) ?>

<div class="padded">
  <?php foreach ($entity_pager->execute() as $entity) : ?>
    <?php include_partial('entity/oneliner', array(
      'entity' => $entity,
      'profile_link' => true,
      'actions' => array(array(
        'name' => 'select',
        'url' => $list->getInternalUrl('connectTo', array(
          'entity_id' => $entity['id']
        )),
        'options' => 'class="text_big" style="font-weight: bold"'
      ))      
    )) ?>
  <?php endforeach; ?>
  <?php echo pager_noresults($entity_pager) ?>
</div>


<?php endif; ?>