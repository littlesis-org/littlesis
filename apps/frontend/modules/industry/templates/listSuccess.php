<?php use_helper('LsText') ?>

<?php slot('header_text', 'Industries') ?>
<?php slot('header_link', '@industries') ?>

<span class="text_big">

<?php $sector_links = array() ?>
<?php foreach ($sectors as $sector) : ?>
 <?php $sector_links[] = link_to($sector, 'industry/list?sector=' . $sector) ?>
<?php endforeach; ?>
<?php echo join(' &nbsp;|&nbsp; ', $sector_links) ?>
<br />

<?php $category_name = null; ?>
<?php $industry_name = null; ?>
<?php $sector_name = null; ?>

<?php foreach ($categories as $category) : ?>
  <?php if (in_array($category['category_id'], OsCategoryTable::$ignoreCategories)) : ?>
    <?php continue; ?>
  <?php endif; ?>
    
  <?php if ($category['sector_name'] != $sector_name) : ?>
    <h1><?php echo $category['sector_name'] ?></h1>
  <?php endif; ?>

  <?php if ($category['industry_name'] != $industry_name) : ?>
    <h3><?php echo $category['industry_name'] ?></h3>
  <?php endif; ?>

  <?php echo category_link($category) ?><br />

  <?php $category_name = $category['category_name']; ?>
  <?php $industry_name = $category['industry_name']; ?>
  <?php $sector_name = $category['sector_name']; ?>
<?php endforeach; ?>

</span>