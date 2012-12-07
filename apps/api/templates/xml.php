<?php echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" ?>
<Response>
  <Meta>
    <?php if (has_slot('total')) : ?>
    <TotalCount><?php echo get_slot('total') ?></TotalCount>
    <?php endif; ?>
    <?php if (has_slot('num_results')) : ?>
    <ResultCount>
    <?php foreach (get_slot('num_results') as $field => $num) : ?>
      <?php printf("<%s>%s</%s>", $field, $num, $field) ?>
    <?php endforeach; ?>
    </ResultCount>
    <?php endif; ?>
    <?php if (has_slot('params') && count(get_slot('params'))) : ?>
    <Parameters>
    <?php foreach (get_slot('params') as $key => $value) : ?>
      <?php printf("<%s>%s</%s>", $key, $value, $key) ?>      
    <?php endforeach; ?>
    </Parameters>
    <?php endif; ?>
  </Meta>
  <Data>
    <?php echo $sf_content ?>
  </Data>
</Response>