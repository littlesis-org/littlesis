<?php use_helper('Pager') ?>

<?php include_partial('entity/header', array('entity' => $entity, 'show_actions' => true)) ?>

<h2>Search Network</h2>

The following search returns <strong>Entity X</strong>s with the most <strong>Entity Y</strong>s such that:

<br />
<br />

<?php echo entity_link($entity) ?> &nbsp;&larr;Relationship 1&rarr;&nbsp;
<strong>Entity Y</strong> &nbsp;&larr;Relationship 2&rarr;&nbsp;
<strong>Entity X</strong>

<br />
<br />

<form action="<?php echo url_for(EntityTable::getInternalUrl($entity, 'networkSearch')) ?>" method="GET">

<table style="width: auto;">
  <tr>
    <td style="width:90px;">Relationship 1:</td>
    <td>
      <?php foreach ($categories as $id => $name) : ?>
        <?php echo checkbox_tag('cat1_ids[]', $id, in_array($id, explode(',', $cat1_ids))) ?><?php echo $name ?>
      <?php endforeach; ?>
    </td>
  </tr>
  <tr>
    <td>Order 1:</td>
    <td>
      <?php echo select_tag('order1', options_for_select(array('', 1, 2), $order1)) ?>
      <span class="text_small">(order of <?php echo $entity['name'] ?> in Relationship 1)</span>
    </td>
  </tr>
  <tr>
    <td>Is current? 1:</td>
    <td>
      <?php echo checkbox_tag('past1', '1', strlen($past1)) ?> only current relationships <span class="text_small">(exclude past relationships between <?php echo entity_link($entity) ?> and Entity Y)</span>
    </td>
  </tr>
  <tr>
    <td>Relationship 2:</td>
    <td>
      <?php foreach ($categories as $id => $name) : ?>
        <?php echo checkbox_tag('cat2_ids[]', $id, in_array($id, explode(',', $cat2_ids))) ?><?php echo $name ?>
      <?php endforeach; ?>
    </td>
  </tr>
  <tr>
    <td>Order 2:</td>
    <td>
      <?php echo select_tag('order2', options_for_select(array('', 1, 2), $order2)) ?>
      <span class="text_small">(order of Entity Y in Relationship 2)</span>
    </td>
  </tr>
  <tr>
    <td>Is current? 2:</td>
    <td>
      <?php echo checkbox_tag('past2', '1', strlen($past2)) ?> only current relationships <span class="text_small">(exclude past relationships between Entity Y and Entity X)</span>
    </td>
  </tr>
  <tr>
    <td>Entity X Type:</td>
    <td>
      	<?php foreach ($extensions['primary'] as $e) : ?>
	        <?php echo checkbox_tag('ext2_ids[]', $e['id'], in_array($e['id'], explode(',', $ext2_ids))) ?><?php echo $e['display_name'] ?>
        <?php endforeach; ?>
        <span class="text_small">(do not choose Person or Org if you want to pick more specific types)</span>
        <br>
        <div id="show_link" style="display:<?php echo strlen($ext2_ids) ? "none" : "inline"?>"><a class="pointer" onclick="
  document.getElementById('show_link').style.display = 'none';
  document.getElementById('additional_types').style.display = 'inline';
">more types&nbsp;&raquo;</a></div>
<div id="additional_types" style="display:<?php echo strlen($ext2_ids) ? "inline" : "none"?>">
        <strong>Person types:</strong>
				<?php foreach ($extensions['person'] as $e) : ?>
	        <?php echo checkbox_tag('ext2_ids[]', $e['id'], in_array($e['id'], explode(',', $ext2_ids))) ?><?php echo $e['display_name'] ?>
        <?php endforeach; ?>
        <br>
        <strong>Org types:</strong>
        <?php foreach ($extensions['org'] as $e) : ?>
	        <?php echo checkbox_tag('ext2_ids[]', $e['id'], in_array($e['id'], explode(',', $ext2_ids))) ?><?php echo $e['display_name'] ?>
        <?php endforeach; ?>
        <br>
        <a class="pointer" onclick="
  document.getElementById('additional_types').style.display = 'none';
  document.getElementById('show_link').style.display = 'inline';
">&laquo;&nbsp;less</a>
				</div>
    </td>
  </tr>
  <tr>
    <td></td>
    <td></td>
  </tr>
  <tr>
    <td colspan="2"><?php echo submit_tag('Search') ?></td>
  </tr>
</table>
</form>

<br />
<br />

<?php if (isset($entity_pager)) : ?>
  <?php include_partial('global/section', array(
    'title' => 'Search Results'
  )) ?>
  
  <?php include_partial('global/table', array(
    'columns' => array('Indirect Link', 'Linked Through'),
    'pager' => $entity_pager,
    'row_partial' => 'entity/networksearchrow',
    'base_object' => $entity
  )) ?>
<?php endif; ?>

