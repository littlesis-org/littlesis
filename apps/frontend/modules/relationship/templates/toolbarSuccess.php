<?php use_helper('Javascript') ?>


<?php if (isset($entity1)) : ?>
  <?php slot('header_text', 'Add Relationships') ?>

  <div style="padding: 1em; background: #eee; border: 1px solid #ccc;">

  <?php if (isset($ref)) : ?>
    This process creates relationships between <?php echo entity_link($entity1, null) ?> and other entities
    appearing on the page <strong><?php echo link_to($ref->name, $ref->source, array('absolute' => 1)) ?></strong>.
    <br />
    <br />
  <?php endif; ?>
  
  <strong>There are <?php echo count($sf_user->getAttribute('toolbar_names')) ?> entity names remaining in queue.</strong> 
  &nbsp;
  <?php echo button_to('Skip', 'relationship/toolbar', 'post=true class=button_small name=commit') ?>
  <?php echo button_to('Clear', 'relationship/toolbar', 'post=true class=button_small name=commit') ?>

  </div>
  
  <br />

  <?php include_partial('global/formerrors') ?>
  
<?php else : ?>

<div style="float: right;">

  Logged in as <strong><?php echo link_to($sf_user->getGuardUser()->Profile->public_name, 'home/notes', array('absolute'=>1)) ?></strong>
  [ <?php echo link_to('Logout', '@sf_guard_signout', array('absolute' => 1)) ?> ]

</div>

<?php endif; ?>


<?php if (@$created_rel) : ?>

  <div>
    Relationship created! <?php echo link_to($created_rel->getName(), RelationshipTable::getInternalUrl($created_rel), array('target' => '_new', 'absolute' => 1)) ?>
  </div>
  
  <br />
  
<?php endif; ?>


<form action="<?php echo url_for('relationship/toolbar', 1) ?>" method="POST" onsubmit="return validateRelationship();">
<?php echo input_hidden_tag('is_switched', isset($is_switched) ? $is_switched : 0, 'id=is_switched') ?>

<?php if (isset($entity1)) : ?>
  <?php echo input_hidden_tag('is_bulk', 1) ?>
<?php endif; ?>

<table style="font-size: 12px; width: auto;">
  <tr>

    <?php include_partial('relationship/toolbarEntityCell', array('position' => 1)) ?>

    <td id="switch" style="width: 100px; visibility: hidden;">
      <a href="javascript:void(0);" onclick="switchEntities();">&larr;switch&rarr;</a>
    </td>

    <?php include_partial('relationship/toolbarEntityCell', array('position' => 2)) ?>

    <td id="category_field" style="width: 200px; vertical-align: top;">
      <strong>Category:</strong> <select id="category_name" name="category_name" onchange="showCategoryFields(this.value);"></select>     
    </td>

  </tr>
</table>

<div id="existing_relationships" style="display: none; margin-top: 0.2em;"></div>
<div id="category_fields" style="display: none; margin-top: 0.2em;"></div>
<div id="source_fields" style="display: none; margin-top: 0.4em;">
<strong>Source URL:</strong> <input type="text" id="reference_source" name="reference_source" size="50" value="<?php echo $sf_request->getParameter('url') ?>" />
&nbsp;
<strong>Source Name:</strong> <input type="text" id="reference_name" name="reference_name" size="50" value="<?php echo $sf_request->getParameter('title') ?>" />
<br />
<input type="submit" value="Submit" class="button_small" style="margin-top: 0.5em;" />
</div>

</form>



<script>

function showSearchResults(position)
{
  //hide entity creation form
  create = $('entity' + position + '_create');
  create.style.display = 'none';

  results = $('entity' + position + '_results');
  results.style.display = 'block';
}

function selectEntity(position, id, ext)
{
  results = $('entity' + position + '_results');
  results.style.display = 'none';

  entity = $('entity_' + id + '_link');
  linkText = entity.innerHTML;

  setEntity(position, id, ext, linkText);
}

function setEntity(position, id, ext, linkText)
{
  hidden = $('entity' + position + '_id');
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
    request = new Ajax.Request('<?php echo url_for('relationship/toolbarCategories', 1) ?>?ext1=' + ext1 + '&ext2=' + ext2, {
      method: 'post',
      onSuccess: function(transport) {
        var response = transport.responseText;
        $('category_name').innerHTML = response;
        $('category_field').style.display = 'table-cell';
        $('switch').style.visibility = 'visible';

        <?php if (isset($category)) : ?>
          $('category_name').value = '<?php echo $category ?>';
          $('category_name').onchange();
        <?php endif; ?>
      },
      onFailure: function() { alert('Something went wrong...'); }      
    });
  }  
}

function clearEntity(position)
{
  hidden = $('entity' + position + '_id');
  hidden.value = '';

  hiddenExt = $('ext' + position);
  hiddenExt.value = '';

  results = $('entity' + position + '_results');
  results.innerHTML = '';
  results.style.display = 'none';
  
  input = $('entity' + position + '_input');
  input.style.display = 'inline';
  
  submit = $('entity' + position + '_submit');
  submit.style.display = 'inline';
  
  link = $('entity' + position + '_link');
  link.innerHTML = '';
  
  clear = $('entity' + position + '_clear');
  clear.style.display = 'none';
  
  $('existing_relationships').style.display = 'none';
  $('category_fields').style.display = 'none';
  $('source_fields').style.display = 'none';
  $('category_name').innerHTML = '';
  $('switch').style.visibility = 'hidden';
}

function switchEntities()
{
  id1 = $('entity1_id').value;
  id2 = $('entity2_id').value;
  
  if (!id1 || !id2)
  {
    return;
  }
  
  link1 = $('entity1_link').innerHTML;
  link2 = $('entity2_link').innerHTML;

  $('entity1_link').innerHTML = link2;
  $('entity2_link').innerHTML = link1;
  
  input1 = $('entity1_input').value;
  input2 = $('entity2_input').value;

  $('entity1_id').value = id2;
  $('entity2_id').value = id1;

  $('entity1_input').value = input2;
  $('entity2_input').value = input1;

  $('is_switched').value = 1 - $('is_switched').value;
}

function showCreateEntityForm(position)
{
  //hide search results
  $('entity' + position + '_results').style.display = 'none';

  //copy search text to name field
  input = $('entity' + position + '_input')
  name = $('entity' + position + '_name');  
  $('entity' + position + '_name').value = input.value.replace(/\.(?!(\w\w\w)($| ))/g, "$1");
  
  //show creation form
  $('entity' + position + '_create').style.display = 'block';
  $('entity' + position + '_name').focus();
}

function clearCreateEntityForm(position)
{
  $('entity' + position + '_name').value = '';
  $('entity' + position + '_person').checked = false;
  $('entity' + position + '_org').checked = false;
  $('entity' + position + '_blurb').checked = false;
}

function createEntity(position)
{
  name = $('entity' + position + '_name').value;

  person = $('entity' + position + '_person').checked;
  org = $('entity' + position + '_org').checked;
  ext = person ? 'Person' : (org ? 'Org' : '');

  blurb = $('entity' + position + '_blurb').value;
  //list_id = $('entity' + position + '_list').value;

  //validate
  if (!name || !ext)
  {
    alert('You must complete the form');
    return;
  }
  
  request = new Ajax.Request('<?php echo url_for('relationship/toolbarCreate', 1) ?>', {
    method: 'post',
    parameters: {'name': name, 'ext': ext, 'blurb': blurb /*, 'list_id': list_id, 'position': position */},
    onSuccess: function(transport) {
      data = transport.responseText.evalJSON();
      $('entity' + position + '_create').style.display = 'none';
      clearCreateEntityForm(position);
      setEntity(position, data.id, data.ext, data.link);
    },
    onFailure: function() { alert('Something went wrong...'); }    
  });
}

function showCategoryFields(category)
{
  if (category)
  {
    //query for category fields
    new Ajax.Request('<?php echo url_for('relationship/toolbarCategoryFields', 1) ?>', {
      method: 'post',
      parameters: {name: category},
      onSuccess: function(transport) {
        $('category_fields').innerHTML = transport.responseText;
        $('category_fields').style.display = 'block';
        $('source_fields').style.display = 'block';
      },
      onFailure: function() { alert('Something went wrong...'); }
    });
    
    //check if there's a similar existing relationship
    id1 = $F('entity1_id');
    id2 = $F('entity2_id');

    new Ajax.Request('<?php echo url_for('relationship/toolbarCheckExisting', 1) ?>', {
      method: 'post',
      parameters: {entity1_id: id1, entity2_id: id2, name: category},
      onSuccess: function(transport) {
        if (transport.responseText.strip())
        {
          $('existing_relationships').innerHTML = transport.responseText;
          $('existing_relationships').style.display = 'block';
        }
        else
        {
          $('existing_relationships').innerHTML = '';
          $('existing_relationships').style.display = 'none';        
        }
      },
      onFailure: function() { alert('Something went wrong...'); }
    });
  }
  else
  {
    $('existing_relationships').style.display = 'none';        
    $('existing_relationships').innerHTML = '';
    $('category_fields').style.display = 'none';
    $('category_fields').innerHTML = '';
    $('source_fields').style.display = 'none';
  }
}

function hideResults(position)
{
  results = $('entity' + position + '_results');
  results.style.display = 'none';
  results.style.innerHTML = '';
}

function enterKey(e, buttonId)
{
  var key;     
  if (window.event)
  {
    key = window.event.keyCode; //IE
  }
  else
  {
    key = e.which; //firefox     
  }

  if (key == 13)
  {
    if (typeof(buttonId) != 'undefined')
    {
      $(buttonId).click();
    }
    
    return false;
  }
  
  return true;
}

function validateRelationship()
{
  refSource = $F('reference_source');
  refName = $F('reference_name');

  if (!refSource.match(/^http(s?)\:\/\/.{2,}\..{3,}/i))
  {
    alert('You must enter a valid source URL');
    return false;
  }

  if (!refSource || !refName)
  {
    alert('You must complete the source fields');
    return false;
  }
  
  return true;
}

<?php if (isset($entity1)) : ?>
  var bulkEntityLink = '<?php echo str_replace("'", "\'", entity_link($entity1)) ?>';
  <?php echo "setEntity(" . ($is_switched ? '2' : '1') . ", " . $entity1['id'] . ", '" . $entity1['primary_ext'] . "', bulkEntityLink);" ?>
<?php endif; ?>

<?php if (isset($entity2_name)) : ?>
  $('entity<?php echo $is_switched ? 1 : 2 ?>_input').value = "<?php echo $entity2_name ?>";
  $('entity<?php echo $is_switched ? 1 : 2 ?>_submit').click();
<?php endif; ?>

</script>