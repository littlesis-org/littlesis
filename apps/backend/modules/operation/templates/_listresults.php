<?php use_helper('Pager', 'Javascript') ?>


<div class="section" style="display: block;">
  <span class="section_title">Matches</span>
</div>
<div class="section_meta" style="display: block;"><?php echo pager_meta($list_pager) ?></div>


<div class="padded" style="display: block;">
<?php foreach ($list_pager->execute() as $list) : ?>
  <?php $list_link = str_replace("'","\'", list_link($list)) ?>
  <?php $innerHtml =  '<span class="text_big">' . $list_link . '</span> ' . input_hidden_tag('list_id', $list->id) ?>
  <?php $innerHtml = str_replace('"', '\\\'', $innerHtml) ?>

  <?php include_partial('listoneliner', array(
    'list' => $list,
    'actions' => array(array(
      'raw' => '<a href="javascript:void(0);" onclick="selectList(\'' . $innerHtml . '\', \'' . 'list' . '\');">select</a>'
    ))       
  )) ?>

<?php endforeach; ?>
</div>
