<?php use_helper('Date') ?>

<?php slot('header_text', $network['name'] . ' Notes') ?>
<?php slot('header_link', LsListTable::getNetworkInternalUrl($network, 'notes')) ?>

Showing analyst notes in the <strong><?php echo network_link($network) ?></strong> network. You can filter the note history by entering search terms below.
<br />
<br />

<form action="<?php echo url_for(LsListTable::getNetworkInternalUrl($network, 'notes')) ?>">
<?php echo input_tag('query', $sf_request->getParameter('query')) ?> 
<?php echo submit_tag('Search', 'class=button_small') ?>
</form>

<br />
<br />

<?php include_partial('global/section', array(
  'title' => 'Note History',
  'pointer' => 'A history of notes in the ' . $network['name'] . ' network' . ($query ? ' containing <strong>' . $query . '</strong>' : ''),
  'pager' => $note_pager
)) ?>

<?php foreach ($note_pager->execute() as $note) : ?>
  <?php include_partial('note/full', array('note' => $note)) ?>
<?php endforeach; ?>