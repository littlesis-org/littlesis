<tr>
  <td style="width: 30%;"><?php echo reference_link($object) ?></td>
  <td style="width: 30%;" class="text_small">
    <?php foreach ($object->Excerpt as $excerpt) : ?>
      <em><span class="pointer" title="<?php echo $excerpt->body ?>"><?php echo excerpt($excerpt->body, 20) ?></span></em>
      <?php if ($sf_user->hasCredential('editor')) : ?>
        <?php echo link_to('remove', 'reference/removeExcerpt?id=' . $excerpt->id) ?>
      <?php endif; ?>
      <br />
    <?php endforeach; ?>

    <?php if ($sf_user->hasCredential('contributor')) : ?>
      <a id="show_excerpt_form_<?php echo $object->id ?>" href="javascript:void(0);" onclick="show_excerpt_form(<?php echo $object->id ?>);">add</a>
    <?php endif; ?>

    <div id="excerpt_form_<?php echo $object->id ?>" style="display: none;">
    <form action="<?php echo url_for('reference/addExcerpt') ?>" method="POST">
    <?php echo input_hidden_tag('id', $object->id) ?>
    <?php echo textarea_tag('excerpt', null, 'size=30x5') ?>
    <br />
    <?php echo submit_tag('Add') ?>
    </form>
    </div>

  </td>
  <td><?php echo implode(', ', $object->getFieldsArray()) ?></td>

  <?php if ($sf_user->hasCredential('editor')) : ?>
    <td>
        <?php echo link_to('edit', 'reference/edit?id=' . $object->id) ?>
    </td>
  <?php endif; ?>


<!--
  <td><?php echo time_ago_in_words(strtotime($object->created_at)) ?> ago</td>
-->
</tr>