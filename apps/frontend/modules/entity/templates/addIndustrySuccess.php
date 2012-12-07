<?php include_partial('entity/header', array('entity' => $entity, 'show_actions' => true)) ?>

<?php include_partial('entity/edittabs', array('entity' => $entity)) ?>

<?php include_partial('global/section', array('title' => 'Add Industry')) ?>


<table class="list_table" style="width: 900px;">
  <tr>
    <th style="width: 300px;">Subcategory</th>
    <th>Industry</th>
    <th>Sector</th>
    <th></th>
  </tr>
<?php foreach ($categories as $category) : ?>
  <?php if (in_array($category['category_id'], OsCategoryTable::$ignoreCategories)) : ?>
    <?php continue; ?>
  <?php endif; ?>
  <?php if (in_array($category['category_id'], $existing_category_ids)) : ?>
    <?php continue; ?>
  <?php endif; ?>
    
  <tr class="hover">
    <td style="width: 200px;"><?php echo $category['category_name'] ?></td>
    <td><?php echo $category['industry_name'] ?></td>
    <td><?php echo $category['sector_name'] ?></td>
    <td style="width: 80px;">
      <?php if ($sf_user->hasCredential('editor')) : ?>
        <?php echo link_to('add', $entity->getInternalUrl('addIndustry', array('category' => $category['category_id'])), 'post=true confirm=Are you sure you want to add this industry?') ?>
      <?php endif; ?>
    </td>
  </tr>  
<?php endforeach; ?>
</table>