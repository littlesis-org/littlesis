<?php use_helper('Date') ?>

<table class="note_full note_hover" >
  <tr>
      
    <?php if (isset($new) && $new) : ?>
      <td class="note_new">&nbsp;</td>
    <?php endif; ?>
    
    <?php if (isset($reply) && $reply) : ?>
      <td class="note_reply">&nbsp;</td>
    <?php endif; ?>


    <td class="note_pic">
      <?php echo user_pic($note['User'], 'profile', array('width' => 40)) ?>
    </td>

    <td class="note_body">
      <?php echo user_link($note['User']) ?>
  
      <?php if ($note['is_private']) : ?>
        <?php echo image_tag('system/lock.png') ?>
      <?php endif; ?>
  
      &nbsp;<?php echo NoteTable::prepareBodyForDisplay($note['body']) ?>

      <div class="note_full_date">
        <?php echo link_to(time_ago_in_words(strtotime($note['created_at'])) . ' ago', NoteTable::getInternalUrl($note)) ?>
      
        <?php if (NoteTable::hasNonUsNetworks($note)) : ?>
          <?php $networks = NoteTable::getNetworksArray($note) ?>
          <?php $networkLinks = array() ?>
          <?php foreach ($networks as $network) : ?>
            <?php $networkLinks[] = network_link($network) ?>
          <?php endforeach; ?>    
          in <?php echo implode(', ', $networkLinks) ?>
        <?php endif; ?>      
      </div>
    </td>

    <?php if ($sf_user->isAuthenticated()) : ?>
      <td class="note_actions">
      <?php if ($sf_user->getGuardUser()->id == $note['User']['id']) : ?>
        <?php echo link_to(image_tag('system/trash.gif', 'border=0'), 'note/remove?id=' . $note['id'], 'confirm=Are you sure you want to delete this note? post=true') ?>
      <?php else : ?>
        <?php echo link_to(image_tag('system/reply.gif', 'border=0'), 'home/notes?user_id=' . $note['user_id'] . '&compose=1' . ($note['is_private'] ? '&private=1' : '')) ?>
      <?php endif; ?>
      </td>    
    <?php endif; ?>
  </tr>
</table>