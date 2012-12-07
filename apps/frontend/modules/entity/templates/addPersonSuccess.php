<?php use_helper('Form') ?>

<?php slot('header_text', 'Add New Person') ?>

<span class="text_big">
Before adding a new person, check to make sure they don't already exist in the database!
</span>
<br />
<br />

<?php include_partial('global/formerrors', array('form' => $entity_form)) ?>

<form action="<?php echo url_for('entity/addPerson') ?>" method="POST">

<table>
  <tr id="person_extension_list">
    <td class="form_label">Types</td>
    <td class="form_field margin_bottom">
      <?php foreach ($tier2_defs as $def) : ?>
        <input type="checkbox" value="1" name="extensions[<?php echo $def->name ?>]"
          <?php if (in_array($def->name, array_keys((array) $sf_request->getParameter('extensions')))) { echo ' checked '; } ?> 
          />
        <?php echo $def->display_name ?> 
        <br />
      <?php endforeach; ?>

      <?php if (count($tier3_defs)) : ?>
        <div id="tier3_person_exts" style="display: none;">
          <hr />
          <?php foreach ($tier3_defs as $def) : ?>
            <input type="checkbox" value="1" name="extensions[<?php echo $def->name ?>]"
              <?php if (in_array($def->name, array_keys((array) $sf_request->getParameter('extensions')))) { echo ' checked '; } ?> 
              />
            <?php echo $def->display_name ?> 
            &nbsp;&nbsp;            
          <?php endforeach; ?>
        </div>
        <a id="show_tier3_person_exts" href="javascript:void(0);" onclick="showTier3Extensions('person');">more &raquo;</a>
      <?php endif; ?>

    </td>
  </tr>

  <?php include_partial('global/form', array('form' => $entity_form)) ?>


  <?php if (isset($networks)) : ?>
  <tr>
    <td class="form_label">Networks</td>
    <td class="form_field margin_bottom">
      <?php foreach ($networks as $network) : ?>
        <?php echo checkbox_tag('network_ids[]', $network['id'], $network['id'] == sfGuardUserTable::getHomeNetworkId()) ?>
        <?php echo $network['name'] ?>
        <br />
      <?php endforeach; ?>
    </td>
  <tr>
  <?php endif; ?>
  

  <tr>
    <td></td>
    <td>
      <?php echo submit_tag('Add') ?>
    </td>
  </tr>
</table>

</form>



<script type="text/javascript">

function showTier3Extensions(primary)
{
  document.getElementById('tier3_' + primary + '_exts').style.display = 'block';
  document.getElementById('show_tier3_' + primary + '_exts').style.display = 'none';
}

<?php foreach ($tier3_defs as $def) : ?>
  <?php if (in_array($def->name, array_keys((array) $sf_request->getParameter('extensions')))) : ?>
    showTier3Extensions('person');
    <?php break ?>
  <?php endif; ?>
<?php endforeach; ?>

</script>