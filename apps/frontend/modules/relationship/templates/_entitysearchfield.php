  <tr>  
    <td class="form_label"><?php echo $label ?></td>
    <td class="form_field" id="<?php echo $name ?>_field">

<?php if (isset($params[$id_name]) && $entityId = $params[$id_name]) : ?>
  <?php if ($entity = Doctrine::getTable('Entity')->find($entityId)) : ?>
    <?php $relationship->$relation_name = $entity ?>
  <?php endif; ?>
<?php endif; ?>

<?php if ($relationship->$id_name) : ?>

      <div id="<?php echo $name ?>_link">
        <span class="text_big"><?php echo entity_link($relationship->$relation_name) ?></span>
        <a href="javascript:void(0);" onclick="changeEntity('<?php echo $name ?>');">change</a>
        <?php echo input_hidden_tag('relationship[' . $id_name . ']', $relationship->$id_name) ?>
      </div>
      <div id="<?php echo $name ?>_search" style="display: none;">

<?php else : ?>

      <div id="<?php echo $name ?>_link" style="display: none;"></div>
      <div id="<?php echo $name ?>_search">

<?php endif; ?>

        <?php echo input_tag(strtolower($relation_name) . '_terms', $sf_request->getParameter(strtolower($relation_name) . '_terms'), 'id=' . $name . '_input') ?>
        <?php echo submit_to_remote(
          $name . '_submit', 
          'Find', 
          array(
            'update' => $name . '_results',
            'url' => 'relationship/findEntity?entity_field=' . $relation_name
          ),
          array(
            'class' => 'button_small'
          )
        ) ?>
        <br />
        <br />
        <div id="<?php echo $name ?>_results"></div>      
      </div>
    </td>
  </tr>