<?php use_helper('Javascript') ?>

<?php slot('header_text', 'Add Massive') ?>


<?php slot('top') ?>
  <?php include_partial('global/topmenu') ?>
<?php end_slot() ?>

<?php if ($sf_user->hasFlash('notice')) : ?>
  <div class="form_errors"><em><?php echo $sf_user->getFlash('notice'); ?></em></div>
<?php endif; ?>

<?php echo form_tag('operation/massiveAdd', 'method=POST') ?>
  <table>
  
  <tr>
    <td class="form_label">File</td>
    <td class="form_field"><?php echo $original ?> &nbsp; <?php echo link_to('change', 'operation/upload') ?> <br />&nbsp;</td>
  </tr>
  
   <?php include_partial('global/form', array('form' => $massive_form)) ?>
   
     <tr>
    <td>
    </td>
    <td style="padding-bottom:.6em">
      <span class="form_help" ><em>Select a list of which these people are members</em></span>
    </td>

  <tr>  
  
  
   <tr>  
  
    <td class="form_label">List</td>
    <td class="form_field" id="list_field">

<?php if ($list) : ?>

      <div id="list_link">
        <?php $list_link = '<strong>' . link_to(str_replace("'","\'", $list->name), frontend_base() . '/list/view?id=' . $list->id) . '</strong>' ?>
        <span class="text_big"><?php echo $list_link ?></span>
        <a href="javascript:void(0);" onclick="changeList('list');">change</a>
        <?php echo input_hidden_tag('list_id', $list->id) ?>
      </div>
      <div id="list_search" style="display: none;">

<?php else : ?>

      <div id="list_link" style="display: none;"></div>
      <div id="list_search">
      
<?php endif; ?>


        <?php echo input_tag('list_terms', $sf_request->getParameter('list_terms'), 'id=list_input') ?>
        <?php echo input_hidden_tag('filename', $filename, 'id=filename') ?>
        <?php echo input_hidden_tag('original', $original, 'id=filename') ?>
        <?php echo submit_to_remote(
          'list_submit', 
          'Find', 
          array(
            'update' => 'list_results',
            'url' => 'operation/findList'
          ),
          array(
            'class' => 'button_small'
          )
        ) ?>
        <br />
        <br />
        <div id="list_results"></div>      
      </div>
    </td>
  </tr>
  <tr>
    <td>
    </td>
    <td style="padding-bottom:.6em">
      <span class="form_help" ><em>Select an org to which this group of people are related</em></span>
    </td>

  <tr>  
  
    <td class="form_label">Org</td>
    <td class="form_field" id="org_field">
    
<?php if ($org) : ?>

      <div id="org_link">
        <?php $org_link = '<strong>' . link_to(str_replace("'","\'", $org->name), frontend_base() . '/entity/view?id= ' . $org->id) . '</strong>' ?>
        <span class="text_big"><?php echo $org_link ?></span>
        <a href="javascript:void(0);" onclick="changeEntity('org');">change</a>
        <?php echo input_hidden_tag('org_id', $org->id) ?>
      </div>
      <div id="org_search" style="display: none;">

<?php else : ?>

      <div id="org_link" style="display: none;"></div>
      <div id="org_search">
      
<?php endif; ?>
      
        <?php echo input_tag('org_terms', $sf_request->getParameter('org_terms'), 'id=org_input') ?>
        <?php echo submit_to_remote(
          'org_submit', 
          'Find', 
          array(
            'update' => 'org_results',
            'url' => 'operation/findOrg'
          ),
          array(
            'class' => 'button_small'
          )
        ) ?>
        <br />
        <br />
        <div id="org_results"></div>      
      </div>
    </td>
  </tr>
  
   <tr>
    <td>
    </td>
    <td style="padding-bottom:.6em">
      <span class="form_help" ><em>Select a relationship category and description (must already exist in db) if you've selected an org</em></span>
    </td>

  <tr>  
  
  <tr>
    <td class="form_label">Relationship Category</td>
    <td>
      <?php foreach ($categories as $cat) : ?>
        <?php if ($cat->id != RelationshipTable::LOBBYING_CATEGORY) : ?>
          <?php echo radiobutton_tag('category_id', $cat->id) ?> <?php echo $cat->display_name ?>
          <?php if ($cat->display_name == 'Education' ) : ?>
            <?php echo ' (as student)' ?>
          <?php endif; ?>
          <br /> 
        <?php endif; ?>
      <?php endforeach; ?>
    </td>      
  </tr>
  <tr>
    <td class="form_label">
      Title/Desc
    </td>    
    <td>
       <?php echo input_tag('relationship_description', '', 'id=relationship_description') ?>
    </td>
  </tr>

  <tr>
    <td></td>  
    <td> <br /><?php echo submit_tag('Add Data', 'confirm=Are you sure you want to add this data?') ?> </td>
  </tr>
   </table>
</form>



<?php echo javascript_tag("

function selectEntity(entityLink, name)
{
  document.getElementById('org_results').innerHTML = '';
  document.getElementById('org_input').value = '';
  document.getElementById('org_search').style.display = 'none';

  var editLink = '<a href=\"javascript:void(0);\" onclick=\"changeEntity(\'' + name + '\');\">change</a>';

  document.getElementById('org_link').innerHTML = entityLink + ' ' + editLink;
  document.getElementById('org_link').style.display = 'block';
}

function changeEntity(name)
{
  document.getElementById('org_link').style.display = 'none';
  document.getElementById('org_link').innerHTML = '';
  
  document.getElementById('org_search').style.display = 'block';
}

function selectList(listLink, name)
{
  document.getElementById('list_results').innerHTML = '';
  document.getElementById('list_input').value = '';
  document.getElementById('list_search').style.display = 'none';

  var editLink = '<a href=\"javascript:void(0);\" onclick=\"changeList(\'' + name + '\');\">change</a>';

  document.getElementById('list_link').innerHTML = listLink + ' ' + editLink;
  document.getElementById('list_link').style.display = 'block';
}

function changeList(name)
{
  document.getElementById('list_link').style.display = 'none';
  document.getElementById('list_link').innerHTML = '';
  
  document.getElementById('list_search').style.display = 'block';
}

") ?>

