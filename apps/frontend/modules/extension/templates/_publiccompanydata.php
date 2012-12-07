<?php $data = array(
  'Ticker' => $entity->ticker,
  //'SEC CIK' => $entity->sec_cik
) ?>

<?php include_partial('global/datatable', array(
  'data' => $data,
  'label_width' => isset($label_width) ? $label_width : '80px'
)) ?>