<?php if ($filing_pager->getCountQuery()->count()) : ?>
<?php use_helper('LsText') ?>

<?php echo include_partial('global/section', array(
  'title' => 'Federal Contract Filings'
)) ?>

<?php include_partial('global/table', array(
  'columns' => array('Date', 'Amount', 'Goods', 'Link'),
  'pager' => $filing_pager,
  'row_partial' => 'relationship/fedspendingfilingrow',
  'alternate' => true
)) ?>

<?php endif; ?>