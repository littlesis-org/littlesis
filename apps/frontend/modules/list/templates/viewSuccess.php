<?php include_partial('list/basic', array('list' => $list)) ?>

<!-- TABS -->

<?php include_partial('list/membertabs', array('list' => $list)) ?>

<div id="member_tabs_content">
<?php if (!cache('list_tabs_content', 86400)) : ?>
  <?php use_helper('LsText') ?>

  <?php include_partial('global/section', array('title' => 'Members')) ?>

  <?php if ($list->is_ranked) : ?>

  <?php include_partial('global/table', array(
    'columns' => array('Rank', 'Name', 'About'),
    'pager' => $list_entity_pager,
    'row_partial' => 'list/rankedentityrow'
  )) ?>

  <?php else : ?>

  <?php include_partial('global/table', array(
    'columns' => array('Name', 'About'),
    'pager' => $list_entity_pager,
    'row_partial' => 'list/entityrow'
  )) ?>

  <?php endif; ?>
<?php endif; ?>
</div>

<script type="text/javascript">
function show_rank_form(id)
{
  span = document.getElementById('set_rank_' + id);
  span.style.display = 'inline';

  link = document.getElementById('show_rank_' + id);
  link.style.display = 'none';
}
</script>