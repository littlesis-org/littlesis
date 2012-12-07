<?php use_helper('Date') ?>

<?php slot('header_text', $note->User->Profile->public_name) ?>
<?php slot('header_link', $note->User->getInternalUrl()) ?>

<?php slot('rightcol') ?>
  <?php include_partial('user/profileimage', array('profile' => $note->User->Profile)) ?>
<?php end_slot() ?>

<span class="text_big"><em>

Note posted <?php echo time_ago_in_words(strtotime($note->created_at)) . ' ago' ?>

<?php if (NoteTable::hasNonUsNetworks($note)) : ?>
  <?php $networks = NoteTable::getNetworksArray($note) ?>
  <?php $networkLinks = array() ?>
  <?php foreach ($networks as $network) : ?>
    <?php $networkLinks[] = network_link($network) ?>
  <?php endforeach; ?>    
  in <?php echo implode(', ', $networkLinks) ?>
<?php endif; ?>      

</em></span>

<br />
<br />

<div class="note_big">
  <?php echo NoteTable::prepareBodyForDisplay($note['body']) ?>
</div>