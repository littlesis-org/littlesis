<?php use_helper('Pager') ?>

<?php include_partial('entity/header', array('entity' => $entity)) ?>
<?php $primary = $entity->getPrimaryExtension() ?>

<h2>Merge With Another <?php echo $primary ?></h2>

<?php include_partial('global/warning', array(
  'message' => 'Merging will transfer all info to the selected ' . strtolower($primary) . ' and remove this one permanently!'
)) ?>

Find the <?php echo strtolower($primary) ?> you want to merge this <?php echo strtolower($primary) ?> with.
<br />
<br />

<form action="<?php echo url_for($entity->getInternalUrl('merge', null, true)) ?>">
<?php echo input_tag('q', $sf_request->getParameter('q')) ?>&nbsp;<input type="submit" value="Search" class="button_small" />
</form>

<br />
<br />


<?php if (count($similar_entities)) : ?>
<?php include_partial('global/section', array(
  'title' => 'Possible Matches'
)) ?>

<div class="padded">
  <?php foreach ($similar_entities as $match) : ?>
    <?php include_partial('entity/oneliner', array(
      'entity' => $match,
      'merge_link' => true,
      'actions' => array(array(
          'name' => 'merge',
          'url' => $entity->getInternalUrl('merge', array('keep_id' => $match->id)),
          'options' => 'post=true confirm=Are you sure?'
      ))       
    )) ?>
  <?php endforeach; ?>
</div>

<br />
<br />

<?php endif; ?>


<?php if (isset($match_pager)) : ?>
<?php include_partial('global/section', array(
  'title' => 'Search Results',
  'pager' => $match_pager
)) ?>

<div class="padded">
  <?php foreach ($match_pager->execute() as $match) : ?>
    <?php if ($match['id'] == $entity['id']) : ?>
      <?php continue ?>
    <?php endif; ?>

    <?php include_partial('entity/oneliner', array(
      'entity' => $match,
      'merge_link' => true,
      'actions' => array(array(
          'name' => 'merge',
          'url' => EntityTable::getInternalUrl($entity, 'merge', array('keep_id' => $match['id'])),
          'options' => 'post=true confirm=Are you sure?'
      ))
    )) ?>
  <?php endforeach; ?>
</div>

<?php endif; ?>