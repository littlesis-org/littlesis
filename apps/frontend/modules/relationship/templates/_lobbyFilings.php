<?php if ($filing_pager->getCountQuery()->count()) : ?>

<?php echo include_partial('global/section', array(
  'title' => 'LDA Disclosure Filings'
)) ?>

<?php include_partial('global/table', array(
  'columns' => array('Date', 'Amount', 'Agencies Lobbied', 'Issues', 'Lobbyists', 'Link'),
  'pager' => $filing_pager,
  'row_partial' => 'relationship/lobbyfilingrow',
  'alternate' => true
)) ?>

<?php endif; ?>