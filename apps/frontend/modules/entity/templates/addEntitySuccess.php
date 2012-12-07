<?php use_helper('Form') ?>

<?php slot('header_text', $header) ?>

<span class="text_big">
Before adding a new <?php echo strtolower($entity->getPrimaryExtension(true)) ?>, 
check to make sure <?php echo $entity->hasExtension('Person') ? "they don't" : "it doesn't" ?> 
already exist in the database!
</span>
<br />
<br />

<?php include_partial('global/formerrors', array('forms' => array($entity_form, $reference_form))) ?>

<form action="<?php echo url_for('entity/' . $sf_request->getParameter('action')) ?>" method="POST">
  <table>
  
    <?php include_partial('reference/required', array(
      'form' => $reference_form,
      'hide_bypass' => true
    )) ?>

    <tr id="extension_list">
      <td class="form_label">Types</td>
      <td class="form_field margin_bottom">
        <?php foreach ($tier2_defs as $def) : ?>
          <input type="checkbox" value="1" name="extensions[<?php echo $def->name ?>]"
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
    <?php include_partial('global/form', array('form' => $primary_ext_form)) ?>
    <?php include_partial('global/form', array('form' => $entity_form)) ?>
    <?php foreach ($other_ext_forms as $name => $form) : ?>
      <tbody id="<?php echo $name ?>_form" style="display: none;">
        <?php include_partial('global/form', array('form' => $form)) ?>
      </tbody>
    <?php endforeach; ?>

    <tr>
      <td></td>
      <td class="form_submit">
        <input type="submit" value="Save" />
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

