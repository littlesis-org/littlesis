<?php use_helper('Pager', 'Javascript') ?>


<div class="section" style="display: block;">
  <span class="section_title">Matches</span>
</div>
<div class="section_meta" style="display: block;"><?php echo pager_meta($entity_pager) ?></div>


<div class="padded" style="display: block;">
<?php foreach ($entity_pager->execute() as $entity) : ?>

  <?php $innerHtml =  '<span class="text_big">' . entity_link($entity) . '</span> ' . input_hidden_tag('relationship[' . strtolower($entityField) . '_id]', $entity->id) ?>
  <?php $innerHtml = str_replace('"', '\\\'', $innerHtml) ?>

  <?php include_partial('entity/oneliner', array(
    'entity' => $entity,
    'actions' => array(array(
      'raw' => '<a href="javascript:void(0);" onclick="selectEntity(\'' . $innerHtml . '\', \'' . strtolower($entityField) . '\');">select</a>'
    ))       
  )) ?>

<?php endforeach; ?>
</div>
