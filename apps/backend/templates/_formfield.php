<?php $prefix = str_replace('[%s]', '', $field->getParent()->getWidget()->getNameFormat()) ?>
<?php preg_match('/' . $prefix . '_([^"]+)">/', $field->renderLabel(), $match) ?>
<?php $fieldName = $match[1] ?>
<tr>
  <td class="<?php echo isset($form_label_class) ? $form_label_class : 'form_label' ?>
             <?php if ($field->hasError() || $sf_request->hasError($fieldName)) { echo ' form_label_error'; } ?>"
      <?php echo isset($label_width) ? 'style="width: ' . $label_width . ';"' : '' ?>>
    <?php echo $field->renderLabel() . ((isset($required) && $required) ? '*' : null) ?>
  </td>
  <td class="<?php echo isset($form_field_class) ? $form_field_class : 'form_field' ?>">
    <?php echo $field->render() ?>
    <?php if (!isset($show_helps) || @$show_helps) : ?>
      <?php $help = (@$help) ? $help : $field->getParent()->getWidget()->getHelp($fieldName) ?>
      <?php if ($help) : ?>
        &nbsp;
        <nobr><span class="form_help">(<?php echo $help ?>)</span></nobr>
      <?php endif; ?>
    <?php endif; ?>
  </td>
</tr>
