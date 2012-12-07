<?php use_helper('Pager', 'Javascript') ?>


<div class="section" style="display: block;">
  <span class="section_title">Matches</span>
</div>
<div class="section_meta" style="display: block;"><?php echo pager_meta($entity_pager) ?></div>


<div class="padded" style="display: block;">
<?php foreach ($entity_pager->execute() as $entity) : ?>
 
   <?php $entity_link = str_replace("'","\'", entity_link($entity)) ?>

  <?php $innerHtml =  '<span class="text_big">' . $entity_link . '</span> ' . input_hidden_tag('org_id', $entity->id) ?>
  <?php $innerHtml = str_replace('"', '\\\'', $innerHtml) ?>

  <?php include_partial('entityoneliner', array(
    'entity' => $entity,
    'actions' => array(array(
      'raw' => '<a href="javascript:void(0);" onclick="selectEntity(\'' . $innerHtml . '\', \'' . 'org' . '\');">select</a>'
    ))       
  )) ?>

<?php endforeach; ?>
</div>
