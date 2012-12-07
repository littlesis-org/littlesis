<?php use_helper('Form') ?>

<?php include_partial('entity/header', array('entity' => $entity, 'show_actions' => true)) ?>

<?php include_partial('entity/edittabs', array('entity' => $entity)) ?>


<h2>Edit Basic Info</h2>

<?php $pointerText = '' ?>
<?php if ($entity->primary_ext == 'Person') : ?>
  <?php $pointerText .= 'The name fields below are for this person\'s legal/official name. ' ?> 
<?php endif; ?>

<?php $pointerText .= 
'To change the way this ' . strtolower($entity->primary_ext) . '\'s name appears in on profile pages and in links, 
<strong>' . link_to('create a new primary alias', $entity->getInternalUrl('editAliases')) . '</strong>.'
?>

<?php include_partial('global/pointer', array('text' => $pointerText)) ?>

<?php include_partial('global/formerrors', array('form' => array($entity_form, $reference_form))) ?>

<form action="<?php echo url_for($entity->getInternalUrl('edit', null, true)) ?>" method="POST">
<?php echo input_hidden_tag('id', $entity->id) ?>
<?php echo $entity_form['_csrf_token'] ?>

  <table>
    <?php include_partial('reference/required', array('form' => $reference_form)) ?>

    <?php if ($show_networks) : ?>
    <tr>
      <td class="form_label">Networks</td>
      <td class="form_field margin_bottom">
        <?php foreach ($permitted_networks as $network) : ?>
          <?php echo checkbox_tag('network_ids[]', $network['id'], in_array($network['id'], $submitted_network_ids)) ?>
          <?php echo $network['name'] ?>
          <br />
        <?php endforeach; ?>

        <?php foreach ($other_networks as $network) : ?>
          <?php echo input_hidden_tag('network_ids[]', $network['id']) ?>
          <?php echo checkbox_tag('network_ids[]', $network['id'], true, 'disabled=tue') ?>
          <?php echo $network['name'] ?>
          <br />
        <?php endforeach; ?>
      </td>
    <tr>
    <?php endif; ?>

    <tr id="extension_list">
      <td class="form_label">Types</td>
      <td class="form_field margin_bottom">
        <?php foreach ($tier2_defs as $def) : ?>
          <input type="checkbox" value="1" name="extensions[<?php echo $def->name ?>]"
            <?php if (in_array($def->name, $entity_exts)) { echo ' checked '; } ?> 
            <?php if ($def->has_fields) : ?>
              onclick="updateExtensionForm(this, '<?php echo $def->name ?>');" 
            <?php endif; ?>
            <?php if ($sf_request->hasParameter('extensions[' . $def->name . ']')) : ?>
              checked
            <?php endif; ?>
            />
          <?php echo $def->display_name ?> 
          <br />
        <?php endforeach; ?>
        
        <?php if (count($tier3_defs)) : ?>
          <div id="tier3_exts" style="display: none;">
            <hr />
            <?php foreach ($tier3_defs as $def) : ?>
              <input type="checkbox" value="1" name="extensions[<?php echo $def->name ?>]"
                <?php if (in_array($def->name, $entity_exts)) { echo ' checked '; } ?> 
                <?php if ($def->has_fields) : ?>
                  onclick="updateExtensionForm(this, '<?php echo $def->name ?>');" 
                <?php endif; ?>
                <?php if ($sf_request->hasParameter('extensions[' . $def->name . ']')) : ?>
                  checked
                <?php endif; ?>
                />
              <?php echo $def->display_name ?> 
              &nbsp;&nbsp;            
            <?php endforeach; ?>
          </div>
          <a id="show_tier3_exts" href="javascript:void(0);" onclick="showTier3Extensions();">more &raquo;</a>
        <?php endif; ?>
      </td>
    </tr>
    <?php include_partial('global/formspacer') ?>

    <?php include_partial('global/form', array('form' => $entity_form)) ?>

    <?php if ($entity['primary_ext'] == 'Person') : ?>
      <?php include_partial('global/form', array('form' => $primary_ext_form)) ?>
    <?php endif; ?>

    <?php foreach ($other_ext_forms as $name => $form) : ?>
      <tbody id="<?php echo $name ?>_form"<?php if (!in_array($name, $entity_exts)) { echo ' style="display: none;"'; } ?>>
        <?php include_partial('global/form', array('form' => $form)) ?>
      </tbody>
    <?php endforeach; ?>

    <tr>
      <td></td>
      <td class="form_submit">
        <input type="submit" value="Save" />
        </form>
        <?php echo button_to('Remove', $entity->getInternalUrl('remove'), 'post=true confirm=Are you sure you want to remove this ' . strtolower($entity->getPrimaryExtension()) . '?') ?>
        <?php echo button_to('Cancel', $entity->getInternalUrl()) ?>
      </td>
    </tr>
  </table>
</form>

<script>

function updateExtensionForm(checkbox, name)
{
  extensionForm = document.getElementById(name + '_form');

  rowGroupDisplayValue = document.all ? 'block' : 'table-row-group';

  if (checkbox.checked)
  {
    extensionForm.style.display = rowGroupDisplayValue;
  }
  else
  {
    extensionForm.style.display = 'none';  
  }  
}

function showTier3Extensions()
{
  document.getElementById('tier3_exts').style.display = 'block';
  document.getElementById('show_tier3_exts').style.display = 'none';
}

<?php if (array_intersect(array_keys((array) $sf_request->getParameter('extensions')), ExtensionDefinitionTable::getNamesByTier(3))) : ?>
showTier3Extensions();
<?php endif; ?>

</script>