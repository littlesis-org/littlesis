<table class="entity_oneliner">
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

    <td style="vertical-align: bottom">
      <?php if (isset($profile_link) && $profile_link) : ?>
        <span class="text_big">
          <strong><?php echo $entity->name ?></strong>
        </span>
        &nbsp;
        <?php echo entity_link($entity, '', false, 'view profile &raquo;') ?>
        &nbsp;
        <em><?php echo $entity->blurb ?></em>
      <?php else : ?>
        <?php echo entity_link($entity) ?>&nbsp;
        <em><?php echo $entity->blurb ?></em>
      <?php endif; ?>  
      
    </td>
    
  </tr>
</table>