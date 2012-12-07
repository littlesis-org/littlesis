<?php use_helper('LsText', 'Javascript') ?>

<div style="float: right;"><input type="button" class="tiny-button" value="x" onclick="hideResults(<?php echo $position ?>);" /></div>

<?php foreach ($entities as $entity) : ?>
  <strong><a href="javascript:void(0);" onclick="selectEntity(<?php echo $position ?>, <?php echo $entity['id'] ?>, '<?php echo $entity['primary_ext'] ?>');">select</a></strong> 
  &nbsp;
  <span id="entity_<?php echo $entity['id'] ?>_link"><?php echo link_to($entity['name'], EntityTable::getInternalUrl($entity), array('target' => '_new', 'absolute' => 1)) ?></span> 
  &nbsp;
  <span style="font-size: 10px;"><?php echo excerpt($entity['blurb'], 50) ?></span>
  <br />
<?php endforeach; ?>

<?php if (!count($entities)) : ?>
  <strong>Nothing found.</strong>
  <br />
<?php endif; ?>

<hr />

<?php if (isset($total) && ($total > $page * count($entities))) : ?>
  <strong><?php echo link_to_remote(
    'more', 
    array(
      'update' => 'entity' . $position . '_results',
      'url' => 'http://littlesis.org/relationship/toolbarSearch',
      'method' => 'post',
      'with' => "'page=" . ($page + 1) . "&position=" . $position . "&q=' + \$F('entity" . $position . "_input')",
      'complete' => 'showSearchResults(' . $position . ')'
    )
  ) ?></strong> | 
<?php endif; ?>

<strong><a href="javascript:void(0);" onclick="showCreateEntityForm(<?php echo $position ?>);">create new</a></strong>
