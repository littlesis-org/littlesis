<?php use_helper('Date') ?>

<?php slot('header_text', 'Analyst Notes') ?>
<?php slot('header_link', '@notes') ?>

You can filter the note history by entering search terms below.
<br />
<br />

<form action="<?php echo url_for('@notes') ?>">
<?php echo input_tag('query', $sf_request->getParameter('query')) ?> 
<?php echo submit_tag('Search', 'class=button_small') ?>
</form>

<br />
<br />

<?php include_partial('global/section', array(
  'title' => 'Note History',
  'pointer' => 'A history of notes by analysts' . ($query ? ' containing <strong>' . $query . '</strong>' : ''),
  'pager' => $note_pager
)) ?>

<?php foreach ($note_pager->execute() as $note) : ?>
  <?php include_partial('note/full', array('note' => $note)) ?>
<?php endforeach; ?>