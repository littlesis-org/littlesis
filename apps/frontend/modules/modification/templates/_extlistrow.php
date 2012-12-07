<?php $defIdField = $object->is_create ? 'new_value' : 'old_value' ?>
<?php $def = Doctrine::getTable('ExtensionDefinition')->find($object->Field[0]->$defIdField) ?>

<tr>
  <td><?php echo $object->created_at ?></td>
  <td><?php echo user_link($object->User) ?></td>
  <td><?php echo $object->getType() ?></td>
  <td><?php echo $def->display_name ?></td>
</tr>