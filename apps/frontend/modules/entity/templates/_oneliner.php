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
          <strong><?php echo $entity['name'] ?></strong>
        </span>
        &nbsp;
        <?php echo entity_link($entity, '', false, 'view profile &raquo;', array('target' => '_blank')) ?>
        &nbsp;
        <?php if (isset($search)) : ?>
          <?php $terms = explode(' ', str_replace('*', '', $search)) ?>
          <em><?php echo highlight_matches($entity['blurb'], $terms) ?></em>
          <?php if ($summary = excerpt_matches($entity['summary'], $terms)) : ?>
            <div class="search-summary"><?php echo highlight_matches($summary, $terms) ?></div>
          <?php endif; ?>
        <?php else : ?>
          <em><?php echo $entity['blurb'] ?></em>
        <?php endif; ?>
      <?php elseif (isset($merge_link) && ($entity['primary_ext'] == 'Person')) : ?>
        <?php echo entity_link($entity) ?>&nbsp;
        <em><?php echo $entity['blurb'] ?></em>
        <?php echo PersonTable::getRelatedOrgSummary($entity) ?>
      <?php else : ?>
        <?php echo entity_link($entity) ?>&nbsp;
        <?php if (isset($search)) : ?>
          <?php $terms = explode(' ', str_replace('*', '', $search)) ?>
          <em><?php echo highlight_matches($entity['blurb'], $terms) ?></em>
          <?php if ($summary = excerpt_matches($entity['summary'], $terms)) : ?>
            <div class="search-summary"><?php echo highlight_matches($summary, $terms) ?></div>
          <?php endif; ?>
        <?php else : ?>
          <em><?php echo $entity['blurb'] ?></em>
        <?php endif; ?>
      <?php endif; ?>  
      
    </td>
    
  </tr>
</table>