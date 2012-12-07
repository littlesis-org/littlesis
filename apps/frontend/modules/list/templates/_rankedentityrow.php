  <tr class="hover">
    <td style="width: 70px; vertical-align: top;">
      <span id="show_rank_<?php echo $object->id ?>">
        <?php echo $object->rank ?>
        <?php if (($object->LsList->is_admin == 0 && $sf_user->hasCredential('editor')) || $sf_user->hasCredential('admin')) : ?>
          <a class="text_small" href="javascript:void(0);" onclick="show_rank_form('<?php echo $object->id ?>');">edit</a>
        <?php endif; ?>
      </span>
      <span id="set_rank_<?php echo $object->id ?>" style="display: none;" class="text_small">
        <form action="<?php echo url_for($object->LsList->getInternalUrl('setRank', null, false)) ?>" method="POST">
          <?php echo input_hidden_tag('id', $object->LsList->id) ?>
          <?php echo input_hidden_tag('entity_id', $object->Entity->id) ?>
          <?php echo input_tag('rank', $object->rank, 'size=4') ?>
          <?php echo submit_tag('Go', 'class=button_small') ?>
        </form>
      </span>
    </td>
    <td>
      <?php echo entity_link($object->Entity, '') ?>
      <?php if (($object->LsList->is_admin == 0 && $sf_user->hasCredential('editor')) || $sf_user->hasCredential('admin')) : ?>
        <span class="text_small"><?php echo link_to('remove', 'list/removeEntity?id=' . $object->id, 'post=true confirm=Are you sure?') ?></span>
      <?php endif; ?>        
    </td>
    <td>
      <em style="font-size: 12px;"><?php echo excerpt($object->Entity->blurb, 40) ?></em>
    </td>
  </tr>
