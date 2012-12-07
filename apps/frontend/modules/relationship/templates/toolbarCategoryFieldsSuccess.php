<?php foreach ($field_names as $field_name) : ?>
  <strong><?php echo $form_schema[$field_name]->renderLabel() ?>:</strong> 
  <?php echo $form_schema[$field_name]->render() ?>
  &nbsp;
  <?php if ($field_name == 'is_current') : ?>
    <hr style="height: 0; border: 0;" />
  <?php endif; ?>
  <?php if (!$condensed) : ?>
    <br />
  <?php endif; ?>  
<?php endforeach; ?>
