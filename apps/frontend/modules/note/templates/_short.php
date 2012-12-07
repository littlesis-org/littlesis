<?php use_helper('Date') ?>

<div class="note_short note_hover text_small"> 
  <?php echo user_link($note['User']) ?>
  
  <?php if ($note['is_private']) : ?>
    <?php echo image_tag('system/lock.png') ?>
  <?php endif; ?>
  
  &nbsp;<?php echo NoteTable::prepareBodyForDisplay($note['body']) ?>

  <div class="note_short_date">
    <?php echo link_to(time_ago_in_words(strtotime($note['created_at'])) . ' ago', NoteTable::getInternalUrl($note)) ?>

    <?php if (NoteTable::hasNonUsNetworks($note)) : ?>
      <?php $networks = NoteTable::getNetworks($note) ?>
      <?php $networkLinks = array() ?>
      <?php foreach ($networks as $network) : ?>
        <?php $networkLinks[] = network_link($network) ?>
      <?php endforeach; ?>    
      in <?php echo implode(', ', $networkLinks) ?>
    <?php endif; ?>
  </div>
</div>