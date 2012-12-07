<div id="entity<?php echo $position ?>_create" style="width: 350px; display: none; position: absolute; padding: 0.5em; background-color: #fff; border: 1px solid #ddd;">

  <div style="float: right; font-size: 10px;"><input type="button" class="tiny-button" value="x" onclick="$('entity<?php echo $position ?>_create').style.display = 'none'; clearCreateEntityForm(<?php echo $position ?>);" /></div>

  <strong>New Entity</strong>
  
  <table style="font-size: 12px; margin-top: 0.4em;">
    <tr>
      <td>Name*</td>
      <td class="form_field">
        <input type="text" id="entity<?php echo $position ?>_name" size="30" onkeypress="return enterKey(event, 'entity<?php echo $position ?>_create_submit');" />
      </td>
    </tr>
    <tr>
      <td>Type*</td>
      <td class="form_field">
        Person <input type="radio" id="entity<?php echo $position ?>_person" name="entity<?php echo $position ?>_ext" value="Person" />
        Org <input type="radio" id="entity<?php echo $position ?>_org" name="entity<?php echo $position ?>_ext" value="Org" />
      </td>
    </tr>
    <tr>
      <td>Description</td>
      <td class="form_field">
        <input type="text" id="entity<?php echo $position ?>_blurb" size="40" onkeypress="return enterKey(event, 'entity<?php echo $position ?>_create_submit');" />
      </td>
    </tr>
    <!--
    <tr>
      <td>List ID</td>
      <td class="form_field">
        <input type="text" id="entity<?php echo $position ?>_list" size="5" onkeypress="return enterKey(event, 'entity<?php echo $position ?>_create_submit');" value="<?php echo $sf_user->getAttribute('list' . $position . '_id') ?>" />
      </td>      
    </tr>
    -->
    <tr>
      <td></td>
      <td>
        <input type="button" id="entity<?php echo $position ?>_create_submit" class="button_small" value="Create" onclick="createEntity(<?php echo $position ?>);" />
      </td>
    </tr>
  </table>
  
</div>