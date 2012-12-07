<?php include_partial('list/header', array('list' => $list)) ?>
<?php use_helper('LsText', 'Javascript') ?>

<h2>Add Bulk</h2>

<?php use_helper('Pager', 'Javascript') ?>


<?php include_partial('global/formerrors', array('form' => array($csv_form, $reference_form))) ?>

<form action="<?php echo url_for($list->getInternalUrl('addBulk', null, true)) ?>" method="POST" enctype="multipart/form-data">

<?php echo $csv_form['_csrf_token'] ?>

<?php if (isset($matches)) : ?>

<strong><?php echo count($matches) ?> names</strong> were found in your file.<br /><br />

    <?php echo input_hidden_tag('id', $list['id']) ?>
    <?php echo input_hidden_tag('ref_id', $ref_id) ?>
    <?php echo input_hidden_tag('count', count($matches)) ?>
    <?php echo input_hidden_tag('default_type', $default_type) ?>

    <table class="donor-table">
      <tr class="donor-table-header">
        <td>Names</td>
        <td>Matches in LittleSis</td>
        <td>Create new <?php echo $default_type ?>?<br><a href="javascript:void(0);" onclick="selectAll('create_new');"  style="color: white; font-weight: normal">(select all)</a></td>
        <td>No Action<br><a href="javascript:void(0);" onclick="selectAll('no_action');" style="color: white; font-weight: normal">(select all)</td>
        <td>Rank</td>
      </tr>
    
    <?php $count = 0 ?>
    <?php foreach ($matches as $match) : ?>
      <tr>
        <td>
          <?php echo $match['name'] ?>
        </td>

        <td>
        <?php foreach ($match['search_results'] as $entity) :?>
          <?php echo radiobutton_tag('entity_' . $count, $entity['id']) ?> &nbsp;
          <?php echo link_to($entity['name'], EntityTable::getInternalUrl($entity), array('target' => '_new')) ?>
          &nbsp;
          <span style="font-size: 10px;"><?php echo excerpt($entity['blurb'], 50) ?></span>
          <br />
        <?php endforeach; ?>
        </td>

        <td style="width: 240px;">
          <?php $checked = count($match['search_results']) ? "" : "checked" ?>
          <?php $display = count($match['search_results']) ? "none" : "block" ?>
          <?php echo '<input type="radio" name="entity_' . $count . '" value="new" ' . $checked . ' onclick="showNew(' . $count . ');" class="create_new"/>' ?> &nbsp;
          create new <?php echo $default_type ?>
          <div id ="newform-<?php echo $count ?>" class="newform" style="display:<?php echo $display ?>"; float: left; width:200px">
            <div style="display:block; padding:5px">
              <span style="float: left; width: 40px"><label>Name</label></span>
              <span><?php echo input_tag("new_name_" . $count, EntityTable::cleanName($match['name'])) ?></span>
            </div>
            <div style="display:block; padding:5px">
              <span style="float: left; width: 40px"><label >Blurb</label></span>
              <span><?php echo input_tag("new_blurb_" . $count, $match['blurb']) ?></span>
            </div> <!--
            <div style="display:block; padding:5px">
              <span>Person <?php echo radiobutton_tag("new_type_" . $count, 'Person', $default_type != 'Org' ? true : false) ?></span>
              <span>Org <?php echo radiobutton_tag("new_type_" . $count, 'Org', $default_type == 'Org' ? true : false) ?></span>
            </div>-->  
            <div>
              <?php foreach ($extensions as $ext) : ?>
                <input type="checkbox" value="<?php echo $ext->name ?>" name="new_extensions_<?php echo $count?>[]"
                  
                  <?php if (isset($match['types']) && in_array($ext->name,$match['types'])) : ?>
                    checked
                  <?php endif; ?>
                  />
                <?php echo $ext->display_name ?> 
                <br />
              <?php endforeach; ?>
            </div>
          </div>
        </td>

        <td style="width: 75px">
          <?php echo radiobutton_tag('entity_' . $count, 0,"checked", array('class'=>'no_action')) ?> &nbsp;
        </td>  
        <td>
          <?php echo input_tag('entity_' . $count . '_rank', $match['rank'], array('size' => 1)) ?> &nbsp;
        </td>  
      </tr>
      <?php $count++ ?>
    <?php endforeach; ?>
    
    </table>
    <br />
    <?php echo submit_tag('Submit') ?>
    <?php echo submit_tag('Cancel') ?>
    
    </form>

<?php else : ?>

Upload a *.csv file containing the data you wish to upload.  
<br />The data should look like this if it's in a spreadsheet (saved as .csv):<br /><br />

<table style="border: 1px solid #ddd; width: 50%; margin-left: 10%">
<tr>
<th style="border: 1px solid #ddd">name
</th>
<th style="border: 1px solid #ddd">blurb</th>
<th style="border: 1px solid #ddd">rank</th>
</tr>
<tr>
<td style="border: 1px solid #ddd">Ford Foundation</td>
<td style="border: 1px solid #ddd">One of the biggest foundations in the world.</td>
<td style="border: 1px solid #ddd">2</td>
</tr>
<tr>
<td style="border: 1px solid #ddd">MacArthur Foundation</td>
<td style="border: 1px solid #ddd">Chicago-based foundation</td>
<td style="border: 1px solid #ddd">8</td>
</tr>

</table>
<br />
<table>
The <em>name</em> header/column is required; <em>blurb</em> and <em>rank</em> are optional.<br /><br />


  <?php include_partial('global/formfield', array('field' => $csv_form['file'], 'required' => true)) ?>
  
  <?php echo $csv_form['file_type']->render() ?>

<tr>
    <td class="form_label">
      Default Type*
    </td>
    <td class="form_field">Person <?php echo radiobutton_tag('default_type', 'Person') ?> Org <?php echo radiobutton_tag('default_type', 'Org') ?>
    &nbsp;
        <nobr><span class="form_help">(default type of entity you are adding)</span></nobr>
    </td>
  </tr>
  <tr><td colspan="2"><br />
  A reference link, so we know where it's coming from:<br /><br /></td></tr>
  
  <?php include_partial('reference/required', array(
    'form' => $reference_form,
    'hide_bypass' => true
  )) ?>

  <tr>  
    <td>
      <?php echo submit_tag('Begin') ?>
    </td>
    <td></td>
  </tr>
  
</table>

<?php endif; ?>

</form>


<script>

function selectAll(class_name)
{
  ary = $$("." + class_name);
  ary.each(function(s) {  
    s.checked = "checked";
  });
  if(class_name == 'create_new')
  {
    ary = $$(".newform");
    ary.each(function(b) {
      b.style.display = 'block';
    });
  }
}


function swapAll(input)
{
  ary = $$('input.confirmed-names');
  ary.each(function(s) {  
    s.checked = input.checked;
  });
}

function showCategoryFields(category)
{
  if (category)
  {
    //query for category fields
    new Ajax.Request('<?php echo url_for('relationship/toolbarCategoryFields') ?>', {
      method: 'post',
      parameters: {name: category, condensed: 0},
      onSuccess: function(transport) {
        $('category-fields').innerHTML = transport.responseText;
        $('category-fields').style.display = 'block';
      },
      onFailure: function() { alert('Something went wrong...'); }
    });    
  }
  else
  {
    $('category-fields').style.display = 'none';
    $('category-fields').innerHTML = '';
  }
}

function showNew(num)
{
  document.getElementById('newform-' + num).style.display = 'block';
}



</script>