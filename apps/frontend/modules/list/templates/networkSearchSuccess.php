<?php use_helper('Pager') ?>

<?php include_partial('list/header', array('list' => $list, 'show_actions' => true)) ?>

<h2>Search Network</h2>

The following search returns <strong>Entity X</strong>s with the most <strong>Entity Y</strong>s such that:

<br />
<br />

<?php echo list_link($list) ?> &nbsp;&larr;Member&rarr;&nbsp;
<strong>Entity Y</strong> &nbsp;&larr;Relationship&rarr;&nbsp;
<strong>Entity X</strong>

<br />
<br />

<form action="<?php echo url_for(LsListTable::getInternalUrl($list, 'networkSearch')) ?>" method="GET">

<table style="width: auto;">
  <tr>
    <td>Relationship:</td>
    <td>
      <?php foreach ($categories as $id => $name) : ?>
        <?php echo checkbox_tag('cat_ids[]', $id, in_array($id, explode(',', $cat_ids))) ?><?php echo $name ?>
      <?php endforeach; ?>
    </td>
  </tr>
  <tr>
    <td>Order:</td>
    <td>
      <?php echo select_tag('order', options_for_select(array('', 1, 2), $order)) ?>
      <span class="text_small">(order of Entity Y in the Relationship)</span>
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
    'row_partial' => 'list/networksearchrow',
    'base_object' => $list
  )) ?>
<?php endif; ?>