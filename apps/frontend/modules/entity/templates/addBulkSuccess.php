<?php use_helper('Javascript') ?>

<?php include_partial('entity/header', array('entity' => $entity)) ?>


<?php use_helper('LsText', 'Javascript') ?>

<?php if ($entity['summary']) : ?>
    <div class="padded">
    <?php include_partial('global/excerpt', array('text' => $entity['summary'], 'id' => 'summary', 'less' => true, 'length' => 500)) ?>
    </div>
<?php endif; ?>
  
<h2>Add Bulk</h2>

<?php use_helper('Pager', 'Javascript') ?>

<?php include_partial('global/formerrors', array('form' => array($add_bulk_form, $reference_form))) ?>

<form action="<?php echo url_for($entity->getInternalUrl('addBulk', null, true)) ?>" method="POST" enctype="multipart/form-data">

<?php echo $reference_form['_csrf_token'] ?>
<?php echo $add_bulk_form['_csrf_token'] ?>
<input type="hidden" name="ext" id="ext" value="<?php echo $entity->primary_ext ?>">

<!-- FIRST SCREEN - CHOOSE UPLOAD &C. -->

<?php if (!$sf_request->isMethod('post') || (!isset($matches) && !isset($confirm_names))) : ?>

<div class="bulk_subheader">1. Pick a reference link (or add one)</div>

<table>
  <?php include_partial('reference/required', array(
    'form' => $reference_form,
    'hide_bypass' => true
  )) ?>
</table>

<!--  CHOOSE Add type  -->

<div class="bulk_subheader">2. Choose a method of adding data & set options</div>

<table>
<tr>
<td class="bulk_form_label">Bulk add method*</td>
<td>
<input type="radio" name="add_method" value="upload" onclick="showForm(this.value);" <?php echo $sf_request->getParameter('add_method') == 'upload' ? "checked" : ""?>>&nbsp;Upload from file<br>
<input type="radio" name="add_method" value="scrape" onclick="showForm(this.value);" <?php echo $sf_request->getParameter('add_method') == 'scrape' ? "checked" : ""?>>&nbsp;Scrape from reference link (url provided above)<br>
<input type="radio" name="add_method" value="text" onclick="showForm(this.value);" <?php echo $sf_request->getParameter('add_method') == 'text' ? "checked" : ""?>>&nbsp;Add names to text box<br>
<?php if($entity->summary && $entity->summary != "") :?>
  <input type="radio" name="add_method" value="summary" onclick="showForm(this.value);" <?php echo $sf_request->getParameter('add_method') == 'summary' ? "checked" : ""?>>&nbsp;Parse summary<br>
<?php endif; ?>
<input type="radio" name="add_method" value="db_search" onclick="showForm(this.value);" <?php echo $sf_request->getParameter('add_method') == 'db_search' ? "checked" : ""?>>&nbsp;Search the database for name matches in entity summaries<br>
</td>
</tr>
</table>
<!-- upload spreadsheet -->

<div id="upload_form" style="display: <?php echo $sf_request->getParameter('add_method') == 'upload' ? "block" : "none"?>">
<table>
<tr><td class="bulk_form_label">Upload a file*</td><td>
<input size="30" type="file" name="file" id="file" />                        &nbsp;
  <nobr><span class="form_help">(must be in .csv format. the first row must be a header row including a <i>name</i> field and at least 2 rows)</span>
<br /><br />The data should look like this if it's in a spreadsheet (saved as .csv):<br /><br />

<table style="border: 1px solid #ddd; width: 70%; margin-left: 10%">
<tr>
<th style="border: 1px solid #ddd">name
</th>
<th style="border: 1px solid #ddd">blurb</th>
<th style="border: 1px solid #ddd">description1</th>
<th style="border: 1px solid #ddd">is_current</th>
<th style="border: 1px solid #ddd">is_board</th>
<th style="border: 1px solid #ddd">start_date</th>
<th style="border: 1px solid #ddd">end_date</th>
</tr>
<tr>
<td style="border: 1px solid #ddd">Ford Foundation</td>
<td style="border: 1px solid #ddd">One of the biggest foundations in the world.</td>
<td style="border: 1px solid #ddd">Director</td>
<td style="border: 1px solid #ddd">1</td>
<td style="border: 1px solid #ddd">1</td>
<td style="border: 1px solid #ddd">2004</td>
<td style="border: 1px solid #ddd"></td>
</tr>
<tr>
<td style="border: 1px solid #ddd">MacArthur Foundation</td>
<td style="border: 1px solid #ddd">Chicago-based foundation</td>
<td style="border: 1px solid #ddd">Chairman</td>
<td style="border: 1px solid #ddd">1</td>
<td style="border: 1px solid #ddd">1</td>
<td style="border: 1px solid #ddd">2009</td>
<td style="border: 1px solid #ddd"></td>
</tr>

</table>
<br />
The <em>name</em> header/column is required; <em>blurb</em> is optional; other fields vary based on what type of relationship you're adding.<br /><br />

  
</td></tr></table>
</div>

<!-- OR scrape from ref url -->

<div id="scrape_summary_form" style="display: <?php echo in_array($sf_request->getParameter('add_method'),array('summary','scrape')) ? "block" : "none"?>">
<table>
<tr>
<td class="bulk_form_label">Entities to look for</td>
<td>
    <input type="radio" name="entity_types" value="people" />&nbsp;people&nbsp;&nbsp;
      <input type="radio" name="entity_types" value="orgs" />&nbsp;orgs&nbsp;&nbsp;
      <input type="radio" name="entity_types" value="all" checked="true"/>&nbsp;both<br />&nbsp;<span class="form_help">(algorithms will look for names of people, or names of organizations, or both)</span>
</td></tr></table>
</div>

<!-- OR add from text box -->

<div id="text_form" style="display: <?php echo $sf_request->getParameter('add_method') == 'text' ? "block" : "none"?>">
<table>
<tr><td class="bulk_form_label">
Enter names*</td>
<td><?php echo textarea_tag('manual_names', $sf_request->getParameter('manual_names'), 'size=60x8') ?>&nbsp;
<span class="form_help">(one per line)</span>
</td>
</tr></table>
</div>

  
</table>








<?php else : ?>

<input type="hidden" name="ref_id" id="ref_id" value="<?php echo $ref_id ?>">



<!-- CONFIRM SCRAPE NAMES -->

<?php if (isset($confirm_names)) : ?>
<div class="bulk_subheader">3. Confirm names found at reference link</div> 
Reference link: <?php echo reference_link($reference) ?><br><br>
<input type="hidden" name="names" value='<?php echo addslashes(serialize($names)) ?>'>


<div id="parsed_text" style="display: none"><?php echo isset($text) ? $text : "" ?></div>
<a href="javascript:void(0);" onclick="showParsedText();" id="show_parsed">show parsed text...</a><br><br>

<?php echo checkbox_tag('select_all', 'select all', true, 'onChange="swapAll(this,\'confirmed-names\');"') ?> select all<br> <br>

<?php foreach ($names as $name) : ?>
  <?php echo checkbox_tag('confirmed_names[]', $name, true, 'class=confirmed-names') ?> 
  <?php echo $name ?>
  <br />
<?php endforeach; ?>

<br />

Enter any additional names below, one per line, that weren't identified from the page:

<br />
<br />
<?php echo textarea_tag('manual_names', $sf_request->getParameter('manual_names'), 'size=60x8') ?>
<br />
<br />






<!-- ADD RELATIONSHIPS  -->


<?php else : ?>

<?php if (isset($db_search)) : ?>

<?php include_partial('bulkfromdb',array('entity' => $entity, 'matches' => $matches, 'category_name' => $category_name, 'form_schema' => $form_schema, 'ref_id' => $ref_id, 'field_names' => $field_names, 'order' => $order, 'extensions' => $extensions, 'add_method' => $add_method)) ?>

<?php else : ?>

<?php include_partial('bulkatonce',array('entity' => $entity, 'matches' => $matches, 'category_name' => $category_name, 'form_schema' => $form_schema, 'default_type' => $default_type, 'ref_id' => $ref_id, 'field_names' => $field_names, 'order' => $order, 'extensions' => $extensions)) ?>

<?php endif; ?>
<?php endif; ?>
<?php endif; ?>





<!--  CHOOSE RELATIONSHIP DEFAULTS -->


<?php if (!$sf_request->isMethod('post') || !isset($matches)) : ?>

<?php $display = ($sf_request->isMethod('post') && !in_array($sf_request->getParameter('add_method'),array('summary','scrape'))) || isset($confirm_names) ? 'block' : 'none' ?>

<div id="default_choices" style="display: <?php echo $display ?>">
<table><tr><td class="bulk_form_label">
How do you want to add this data?*</td>
<td>
<?php echo radiobutton_tag('verify_method', 'enmasse', '', 'onclick="showDefaultForm(this.value);"') ?>&nbsp;all entries at once&nbsp;<span class="form_help">(fast, high stakes, less flexible. everything on one screen.)</span> <br/>

<?php echo radiobutton_tag('verify_method', 'onebyone', '', 'onclick="showDefaultForm(this.value);"') ?>&nbsp;one entry at a time&nbsp;<span class="form_help">(slower and more flexible.)</span> <br/>
</td>
</tr>
</table>


<div id="enmasse" style="display:none">

<div class="bulk_subheader"><?php echo isset($confirm_names) ? "4." : "3."?> Choose relationship defaults (required)</div>

<table>
  <tr>
    <td class="bulk_form_label">
      Default Entity Type*
    </td>
    <td>Person <?php echo radiobutton_tag('default_type', 'Person', '', 'onclick="setCategories(this.value,\'all\');"') ?> Org <?php echo radiobutton_tag('default_type', 'Org', '', 'onclick="setCategories(this.value,\'all\');"') ?>
    &nbsp;
        <nobr><span class="form_help">(default type of entity you are adding)</span></nobr>
    </td>
  </tr>
  
  <tr>
  <td class="bulk_form_label">
      Relationship Order*
  </td>
  <td>
  <input type="hidden" name="order" id="order" value="<?php echo ($entity->primary_ext == "Person" ? "1" : "2")?>">
  <div id="order_2" style="display: <?php echo ($entity->primary_ext == "Person" ? "none" : "block")?>">
  <strong>Entities in file</strong> &rarr; have positions at / give to / do business with / are members of &rarr; <strong><?php echo link_to($entity['name'], EntityTable::getInternalUrl($entity), array('target' => '_new')) ?><br />
  <a href="javascript:void(0);" onclick="switchEntities();">(switch)</a></strong>
  </div>
  <div id="order_1" style="display: <?php echo ($entity->primary_ext == "Person" ? "block" : "none")?>">
  <strong><?php echo link_to($entity['name'], EntityTable::getInternalUrl($entity), array('target' => '_new')) ?></strong> &rarr; has positions at / gives to / does business with / is a member of &rarr; <strong>entities in file<br />
  <a href="javascript:void(0);" onclick="switchEntities();">(switch)</a></strong>
  </div>
    
  </td>
  </tr>
  
 <tr>
  <td class="bulk_form_label">
      Category*
    </td>
    <td id="category_field" style="vertical-align: top;">
      <select id="relationship_category_all" name="relationship_category_all" onchange="showCategoryFields(this.value, 'all');"></select>     
    </td>
 </tr>
 <tr>
 <td class="bulk_form_label">Category fields</td>
 <td><div id="category_fields_all" class="form_help" style="display: block; margin-top: 0.2em;">(add a default entity type and select a category to show fields. defaults won't overwrite spreadsheet data.)</div><br>
 </td>
 </tr>
</table>
</div>

<div id="onebyone" style="display:none">

<div class="bulk_subheader"><?php echo isset($confirm_names) ? "4." : "3."?>  Choose relationship defaults (optional)</div>

You can optionally set default values for the relationships you're creating:

<table>
<tr><td class="bulk_form_label">
Category</td><td> <?php echo select_tag('relationship_category_one', $categories, "onchange=showCategoryFields(this.value,'one')") ?></td></tr>
 <tr>
 <td class="bulk_form_label">Category fields</td>
 <td><div id="category_fields_one" class="form_help" style="display: block; margin-top: 0.2em;">(add a default entity type and select a category to show fields. defaults won't overwrite spreadsheet data.)</div><br>
 </td>
 </tr>
</table>

</div>
</div>

<?php if (isset($confirm_names)) : ?>
  <?php echo submit_tag('Continue') ?>
<?php else : ?>
  <?php echo submit_tag('Begin') ?>
<?php endif; ?>

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

function swapAll(input,class_name)
{
  ary = $$("." + class_name);
  ary.each(function(s) { 
    if (input.checked)
    {
      s.checked = true;
    }
    else 
    {
      s.checked = false;
    }
  });
}

function showNew(num)
{
  document.getElementById('newform-' + num).style.display = 'block';
}

function showCategoryFields(category,type)
{
  if (category)
  {
    //query for category fields
    new Ajax.Request('<?php echo url_for('relationship/toolbarCategoryFields') ?>', {
      method: 'post',
      parameters: {name: category},
      onSuccess: function(transport) {
        $('category_fields_' + type).innerHTML = transport.responseText;
        $('category_fields_' + type).style.display = 'block';
        $('source_fields').style.display = 'block';
      },
      onFailure: function() { alert('Something went wrong...'); }
    });
  } 
}

function setCategories(default_ext,type)
{
  if ($('order').value == '1')
  {
    ext1 = $('ext').value;
    ext2 = default_ext;
  }
  else
  {
    ext2 = $('ext').value;
    ext1 = default_ext;
  }
  
  request = new Ajax.Request('<?php echo url_for('relationship/toolbarCategories') ?>?ext1=' + ext1 + '&ext2=' + ext2, {
    method: 'post',
    onSuccess: function(transport) {
      var response = transport.responseText;
      $('relationship_category_' + type ).update(response);
      $('category_field').style.display = 'table-cell';

      <?php if (isset($category)) : ?>
        $('relationship_category_' + type).value = '<?php echo $category ?>';
        $('relationship[category]').onchange();
      <?php endif; ?>
    },
    onFailure: function() { alert('Something went wrong...'); }      
  });

}

function switchEntities()
{
  
  if ($('order').value == '1')
  {
    $('order').value = '2';
    $('order_2').style.display = 'block';
    $('order_1').style.display = 'none';
  }
  else
  {
    $('order').value = '1';
    $('order_1').style.display = 'block';
    $('order_2').style.display = 'none';
  }
  if ($('default_type_Person').checked)
  {
    setCategories('Person','all');
  }
  else if ($('default_type_Org').checked)
  {
    setCategories('Org','all');
  }
}


function setEntity(position, id, ext, linkText)
{
  hidden = $('entity_id');
  hidden.value = id;

  hiddenExt = $('ext' + position);
  hiddenExt.value = ext;
  
  input = $('entity' + position + '_input');
  input.style.display = 'none';
  
  submit = $('entity' + position + '_submit');
  submit.style.display = 'none';

  link = $('entity' + position + '_link');
  link.innerHTML = linkText;
    
  clear = $('entity' + position + '_clear');
  clear.style.display = 'inline';

  otherHidden = $('entity' + (3-position) + '_id');
  
  if (otherHidden.value)
  {
    ext1 = $('ext1').value;
    ext2 = $('ext2').value;
    request = new Ajax.Request('<?php echo url_for('relationship/toolbarCategories') ?>?ext1=' + ext1 + '&ext2=' + ext2, {
      method: 'post',
      onSuccess: function(transport) {
        var response = transport.responseText;
        $('relationship[category]').innerHTML = response;
        $('category_field').style.display = 'table-cell';
        $('switch').style.visibility = 'visible';

        <?php if (isset($category)) : ?>
          $('relationship[category]').value = '<?php echo $category ?>';
          $('relationship[category]').onchange();
        <?php endif; ?>
      },
      onFailure: function() { alert('Something went wrong...'); }      
    });
  }  
}

function showForm(form_type)
{
  if(form_type != 'scrape' && form_type != 'summary')
  {
    $('default_choices').style.display = 'block';  
  }
  else
  {
    $('default_choices').style.display = 'none';
  } 
  if(form_type == 'summary' || form_type == 'scrape')
  {
    form_type = 'scrape_summary';
  }
  if (form_type != 'db_search')
  {
    $(form_type + '_form').style.display = 'block';
  }
  forms = new Array('upload','scrape_summary','text');
  for (i=0; i< forms.length; i++)
  {
    if(forms[i] != form_type)
    {
      $(forms[i] + '_form').style.display = 'none';
    }
  }
}

function showDefaultForm(form)
{
  $(form).style.display = 'block';
  if (form == 'enmasse')
  {
    $('onebyone').style.display = 'none';
  }
  else
  {
    $('enmasse').style.display = 'none';
  }
}

function showParsedText()
{
  if ($('parsed_text').style.display == 'block')
  {
    $('parsed_text').style.display = 'none';
    $('show_parsed').innerHTML = 'show parsed text';
  }
  else
  {
    $('parsed_text').style.display = 'block';
    $('show_parsed').innerHTML = 'hide parsed text';
  }
}

function checkExisting(input, row)
{

  //check if there's a similar existing relationship
  if ($F('order') == 1)
  {
    id1 = $F('entity_id');
    id2 = input.value;
  }
  else
  {
    id2 = $F('entity_id');
    id1 = input.value;
  }
  category = $F('category_name');

  new Ajax.Request('<?php echo url_for('relationship/toolbarCheckExisting') ?>', {
    method: 'post',
    parameters: {entity1_id: id1, entity2_id: id2, name: category},
    onSuccess: function(transport) {
      if (transport.responseText.strip())
      {
        $('existing_relationships_' + row).innerHTML = transport.responseText + '<br>';
        $('existing_relationships_' + row).style.display = 'block';
        $('existing_relationships_' + row).style.backgroundColor = 'white';
      }
      else
      {
        $('existing_relationships_' + row).innerHTML = '';
        $('existing_relationships_' + row).style.display = 'none';        
      }
    },
    onFailure: function() { alert('Something went wrong...'); }
  });
}



</script>