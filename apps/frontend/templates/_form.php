<?php $validator_schema = $form->getValidatorSchema() ?>
<?php $show_required = isset($show_required) ? $show_required : true ?>
<?php $show_helps = isset($show_helps) ? $show_helps : true ?>
<?php $formSchema = $form->getFormFieldSchema() ?>
<?php $widgetSchema = $form->getWidgetSchema() ?>
<?php $helps = $widgetSchema->getHelps() ?>
<?php foreach ($formSchema as $name => $field) : ?>
  <?php if (isset($field_names) && !in_array($name, $field_names)) : ?>
    <?php continue; ?>
  <?php endif; ?>

  <?php if ($field->isHidden()) : ?>
    <?php echo $field ?>
  <?php else : ?>
    <?php $required = (isset($validator_schema[$name]) && $options = $validator_schema[$name]->getOptions()) ? $options['required'] : false ?>
    <?php include_partial('global/formfield', array(
      'field' => $field, 
      'label_width' => isset($label_width) ? $label_width : '120px',
      'form_label_class' => isset($form_label_class) ? $form_label_class : null,
      'form_field_class' => isset($form_field_class) ? $form_field_class : null,
      'required' => ($show_required && $required) ? true : false,
      'help' => isset($helps[$name]) ? $helps[$name] : null,
      'show_helps' => $show_helps
    )) ?>
  <?php endif; ?>
<?php endforeach; ?>