<?php use_helper('LsText') ?>

<?php include_partial('industry/header', array('category' => $category)) ?>

<span class="text_big">

<?php include_partial('global/datatable', array('data' => array(
  'Sector' => link_to($sector, 'industry/list?sector=' . $sector),
  'Industry' => $industry,
  'Category' => category_link($category)
))) ?>

<br />

<?php if ($type == 'people') : ?>

<?php include_partial('global/section', array('title' => 'People')) ?>

  <?php include_partial('global/table', array(
    'pager' => $person_pager,
    'row_partial' => 'industry/listrow'
  )) ?>

<?php elseif ($type == 'orgs') : ?>

<?php include_partial('global/section', array('title' => 'Orgs')) ?>

  <?php include_partial('global/table', array(
    'pager' => $org_pager,
    'row_partial' => 'industry/listrow'
  )) ?>

<?php else: ?>

  <strong><?php echo link_to('People (' . $person_count . ')', '@categoryPeople?category=' . $category['category_id']) ?></strong>
  <br />
  <br />
  <strong><?php echo link_to('Orgs (' . $org_count . ')', '@categoryOrgs?category=' . $category['category_id']) ?></strong>

<?php endif; ?>

</span>