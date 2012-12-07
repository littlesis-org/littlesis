<?php use_helper('Form', 'Pager') ?>

<?php slot('header_text', 'Advanced Search') ?>
<?php slot('rightcol', '') ?>

<span class="text_big">
Search for people and organizations profiled in LittleSis, using the form below to narrow the results.
Leaving the form empty will return all profiles.
</span>
<br />
<br />


<?php if (isset($results_pager)) : ?>
  <div id="show_search_form"><a href="javascript:void(0);" onclick="showSearchForm();">Modify search &raquo;</a><br /><br /></div>
<?php endif; ?>

<div id="advanced_search_form"<?php if (isset($results_pager)) { echo ' style="display: none;"'; } ?>>

<?php if (isset($errors)) : ?>
  <?php include_partial('global/formerrors', array('form' => $entity_form)) ?>
<?php endif; ?>


<form action="<?php echo url_for('search/advanced') ?>">
  <table>
    <tr id="primary_extensions">
      <td class="form_label">Searching for</td>
      <td class="form_field margin_bottom">
        <?php foreach ($primary_exts as $primary) : ?>
          <input type="radio" value="<?php echo $primary ?>" name="primary[]"
            <?php if ($request_primary == $primary) { echo 'checked'; } ?>
            onclick="swapPrimaryExtension(this.value);" /> <?php echo $primary ?><br />
        <?php endforeach; ?>
      </td>
    </tr>

    <?php foreach ($primary_exts as $primary) : ?>
      <tr id="<?php echo $primary ?>_extension_list" style="display: none;">
        <td class="form_label"><?php echo $primary ?> Types</td>
        <td class="form_field margin_bottom">
          <?php foreach ($tier2_defs[$primary] as $def) : ?>
            <input type="checkbox" value="1" name="extensions[<?php echo $def->name ?>]"
              <?php if (in_array($def->name, array_keys((array) $sf_request->getParameter('extensions')))) { echo ' checked '; } ?> 
              <?php if ($def->has_fields) : ?>
                onclick="updateExtensionForm(this, '<?php echo $def->name ?>');" 
              <?php endif; ?>
              />
            <?php echo $def->display_name ?> 
            <br />
          <?php endforeach; ?>

          <?php if (count($tier3_defs[$primary])) : ?>
            <div id="tier3_<?php echo $primary ?>_exts" style="display: none;">
              <hr />
              <?php foreach ($tier3_defs[$primary] as $def) : ?>
                <input type="checkbox" value="1" name="extensions[<?php echo $def->name ?>]"
                  <?php if ($def->has_fields) : ?>
                    onclick="updateExtensionForm(this, '<?php echo $def->name ?>');" 
                  <?php endif; ?>
                  />
                <?php echo $def->display_name ?> 
                &nbsp;&nbsp;            
              <?php endforeach; ?>
            </div>
            <a id="show_tier3_<?php echo $primary ?>_exts" href="javascript:void(0);" onclick="showTier3Extensions('<?php echo $primary ?>');">more &raquo;</a>
          <?php endif; ?>

        </td>
      </tr>
    <?php endforeach; ?>
    
    <?php include_partial('global/formspacer') ?>

    <?php foreach ($primary_exts as $primary) : ?>
      <tbody id="<?php echo $primary ?>_form" style="display: none;">
        <?php include_partial('global/form', array('form' => $extension_forms[$primary]['primary'], 'show_required' => false)) ?>
      </tbody>
    <?php endforeach; ?>

    <?php include_partial('global/form', array('form' => $entity_form, 'show_helps' => false)) ?>

    <?php foreach ($primary_exts as $primary) : ?>
      <table id="<?php echo $primary ?>_table" style="display: none;">
        <?php foreach ($extension_forms[$primary]['other'] as $name => $form) : ?>
          <tbody id="<?php echo $name ?>_form" style="display: none;">
            <?php include_partial('global/form', array('form' => $form)) ?>
          </tbody>
        <?php endforeach; ?>
      </table>
    <?php endforeach; ?>

  <table>
    <tr>
      <td class="form_label"></td>
      <td class="form_submit">
        <input type="submit" value="Search" />
      </td>
    </tr>
  </table>
</form>

<br />
<br />

</div>


<?php if (isset($results_pager)) : ?>

<?php include_partial('global/section', array(
  'title' => 'Results',
  'pager' => $results_pager
)) ?>

<div class="padded">
  <?php foreach ($results_pager->execute() as $entity) : ?>
    <?php include_partial('entity/oneliner', array('entity' => $entity)) ?>
  <?php endforeach; ?>
</div>

<?php endif; ?>


<script>

function showSearchForm()
{
  document.getElementById('advanced_search_form').style.display = 'block';
  document.getElementById('show_search_form').style.display = 'none';
}

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

function swapPrimaryExtension(selectedPrimary)
{
  if (selectedPrimary == 'Person')
  {
    unselectedPrimary = 'Org';
  }
  else
  {
    unselectedPrimary = 'Person';
  }

  rowDisplayValue = document.all ? 'block' : 'table-row';
  rowGroupDisplayValue = document.all ? 'block' : 'table-row-group';
  inlineTableDisplayValue = document.all ? 'block' : 'inline-table';

  document.getElementById(selectedPrimary + '_extension_list').style.display = rowDisplayValue;
  document.getElementById(unselectedPrimary + '_extension_list').style.display = 'none';

  document.getElementById(selectedPrimary + '_form').style.display = rowGroupDisplayValue;
  document.getElementById(unselectedPrimary + '_form').style.display = 'none';

  document.getElementById(selectedPrimary + '_table').style.display = inlineTableDisplayValue;
  document.getElementById(unselectedPrimary + '_table').style.display = 'none';
}

function showTier3Extensions(primary)
{
  document.getElementById('tier3_' + primary + '_exts').style.display = 'block';
  document.getElementById('show_tier3_' + primary + '_exts').style.display = 'none';
}

<?php if ($request_primary) : ?>
swapPrimaryExtension('<?php echo $request_primary ?>');
<?php endif; ?>

</script>