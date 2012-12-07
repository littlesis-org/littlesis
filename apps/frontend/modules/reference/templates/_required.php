<script>

function show_reference_more()
{
  rowGroupDisplayValue = document.all ? 'block' : 'table-row-group';

  document.getElementById('show_reference_more').style.display = 'none';
  document.getElementById('reference_more').style.display = rowGroupDisplayValue;
  document.getElementById('hide_reference_more').style.display = 'inline';
}

function hide_reference_more()
{
  document.getElementById('hide_reference_more').style.display = 'none';
  document.getElementById('reference_more').style.display = 'none';
  document.getElementById('show_reference_more').style.display = 'inline';
}

function show_new_source()
{
  rowGroupDisplayValue = document.all ? 'block' : 'table-row-group';

  document.getElementById('show_new_source').style.display = 'none';
  document.getElementById('new_source').style.display = rowGroupDisplayValue;
}

</script>

<?php echo $form['_csrf_token'] ?>

  <tbody class="required_reference">
    <tr>
      <td style="width: 150px;">&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td></td>
      <td>
        <span class="text_big">Where is this information coming from?</span>
        <br />
        <br />
      </td>
    </tr>


<?php if (!isset($hide_bypass)) : ?>
    <tr>
      <td class="form_label"><?php echo isset($bypass_text) ? $bypass_text : 'Just cleaning up' ?></td>
      <td class="form_field">
        <?php echo $form['nosource'] ?>
      </td>
    </tr>

<script>
nosource = document.getElementById('reference_nosource');
nosource.onclick = function()
{
  fieldNames = new Array('source', 'existing_source', 'name', 'source_detail', 'publication_date', 'excerpt');

  for (var i in fieldNames)
  {
    if (field = document.getElementById('reference_' + fieldNames[i]))
    {
      field.disabled = (nosource.checked == true) ? true : false;
    }
  }
}
</script>
<?php endif; ?>


<?php if (isset($form['existing_source'])) : ?>

    <tr>
      <td class="form_label">Existing source</td>
      <td class="form_field">
        <?php echo $form['existing_source'] ?>
      </td>
    </tr>
    <tr>
      <td></td>
      <td>
        <a id="show_new_source" href="javascript:void(0);" onclick="show_new_source();">Or enter a new source &raquo;</a>        
      </td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
  </tbody>

  <tbody id="new_source" class="required_reference" style="display: none;">
    <?php include_partial('global/formfield', array(
      'field' => $form['source'], 
      'required' => true
    )) ?>
    
    <?php include_partial('global/formfield', array(
      'field' => $form['name'],
      'required' => true
    )) ?>
    <tr>
      <td></td>
      <td>
        <a id="show_reference_more" href="javascript:void(0);" onclick="show_reference_more();">More &raquo;</a>
        <a id="hide_reference_more" style="display: none;" href="javascript:void(0);" onclick="hide_reference_more();">Less &laquo;</a>
      </td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
  </tbody>

<?php else : ?>

    <?php include_partial('global/formfield', array(
      'field' => $form['source'], 
      'required' => true
    )) ?>
    
    <?php include_partial('global/formfield', array(
      'field' => $form['name'],
      'required' => true
    )) ?>

   <tr>
      <td></td>
      <td>
        <a id="show_reference_more" href="javascript:void(0);" onclick="show_reference_more();">More &raquo;</a>
        <a id="hide_reference_more" style="display: none;" href="javascript:void(0);" onclick="hide_reference_more();">Less &laquo;</a>
      </td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
  </tbody>


<?php endif; ?>

   
  <tbody id="reference_more" class="required_reference" style="display: none;">
    <?php include_partial('global/formfield', array(
      'field' => $form['source_detail'],
      'help' => $form->getWidgetSchema()->getHelp('source_detail')
    )) ?>
    <?php include_partial('global/formfield', array(
      'field' => $form['publication_date'],
      'help' => $form->getWidgetSchema()->getHelp('publication_date')
    )) ?>
    <?php include_partial('global/formfield', array(
      'field' => $form['excerpt'],
      'help' => $form->getWidgetSchema()->getHelp('excerpt')
    )) ?>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
   </tbody>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  
<?php if ($sf_request->getParameter('reference[nosource]')) : ?>
<script type="text/javascript">
nosource.onclick();
</script>
<?php endif; ?>

<?php if ($sf_request->getParameter('reference[source]')) : ?>
<script type="text/javascript">
show_new_source();
</script>
<?php endif; ?>