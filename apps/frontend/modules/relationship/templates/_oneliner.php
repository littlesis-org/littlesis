<?php $categoryName = RelationshipCategoryTable::$categoryNames[$relationship['category_id']] ?>
<?php $text = get_partial(
  'relationship/' . strtolower($categoryName) . 'oneliner', 
  array(
    'relationship' => $relationship,
    'profiled_entity' => isset($profiled_entity) ? $profiled_entity : null,
    'related_entity' => isset($related_entity) ? $related_entity : null
  )
) ?>

<?php if ($relationship['notes']) { $text .= '<span><strong>*</strong></span>'; } ?>
  
<span><?php echo link_to(trim($text), 'relationship/view?id=' . $relationship['id'], array('class' => 'relationship', 'title' => 'view details')) ?></span>