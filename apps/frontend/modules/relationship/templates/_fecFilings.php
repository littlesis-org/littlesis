<?php if ($filing_pager->getCountQuery()->count()) : ?>

<?php echo include_partial('global/section', array(
  'title' => 'Contributions',
  'pointer' => 'This relationship is based on the following campaign contributions reported by the Federal Elections Commission'
)) ?>

<?php include_partial('global/table', array(
  'columns' => array('Date', 'Amount', 'FEC ID', 'Link'),
  'pager' => $filing_pager,
  'row_partial' => 'relationship/fecfilingrow',
  'alternate' => true
)) ?>

<?php endif; ?>