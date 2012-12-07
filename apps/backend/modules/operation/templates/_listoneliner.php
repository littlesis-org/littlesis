<?php use_helper('LsText') ?>

<table class="list_oneliner">
  <tr>

    <?php if (isset($actions)) : ?>
    <td class="actions">
      <?php foreach ($actions as $action) : ?>
        <?php if (isset($action['raw'])) : ?>
          <?php echo $action['raw'] ?>
        <?php else : ?>
          <?php echo link_to($action['name'], $action['url'], 'class=small' . (isset($action['options']) ? ' ' . $action['options'] : '')) ?>
          <br />
        <?php endif; ?>
      <?php endforeach; ?>
    </td>
    <?php endif; ?>

    <td>
      <span class="list_link text_big"><?php echo list_link($list) ?></span>&nbsp;
      <em><?php echo excerpt($list->description, 100) ?></em>
    </td>
    
  </tr>
</table>