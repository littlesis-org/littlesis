<?php use_helper('Pager') ?>
<?php include_partial('entity/header', array('entity' => $entity)) ?>

<?php slot('rightcol') ?>
  <?php include_partial('entity/profileimage', array('entity' => $entity)) ?>
<?php end_slot() ?>

<h2>Add Relationship</h2>

Find the person or organization you want to add a relationship to. If your search doesn't locate it, you'll have the option to create it below.
<br />
<br />

<form action="<?php echo url_for('entity/addRelationship') ?>">
<?php echo input_hidden_tag('id', $entity['id']) ?>
<?php echo input_tag('q', $sf_request->getParameter('q')) ?>&nbsp;<input class="button_small" type="submit" value="Search" />
</form>

<br />
<br />


<?php if (isset($entity_pager)) : ?>

<?php include_partial('global/section', array(
  'title' => 'Search Results',
  'pager' => $entity_pager
)) ?>

<div class="padded">
  <?php foreach ($entity_pager->execute() as $result) : ?>
    <?php include_partial('entity/oneliner', array(
      'entity' => $result,
      'profile_link' => true,
      'actions' => array(array(
          'name' => 'select',
          'url' => EntityTable::getInternalUrl($entity, 'addRelationshipCategory', array('entity2_id' => $result['id'])),
          'options' => 'class="text_big" style="font-weight: bold"'
      ))      
    )) ?>
  <?php endforeach; ?>
  <?php echo pager_noresults($entity_pager) ?>
</div>

<br />

<span class="text_big">Can't find what you're looking for? Add it now:</span>

<br />
<br />

<div id="create_form">

<?php include_partial('global/formerrors') ?>

<div class="padded">
<form action="<?php echo url_for(EntityTable::getInternalUrl($entity, 'addRelationship')) ?>" method="POST">
<?php echo input_hidden_tag('id', $entity['id']) ?>

<?php $primary = $sf_request->getParameter('primary') ?>
<?php $primary = $primary ? $primary[0] : null ?>

<table>
  <tr>
    <td class="form_label">Create a</td>
    <td class="form_field">
      <input type="radio" value="Person" name="primary[]" onclick="swapPrimaryExtension('Person');" <?php if ($primary == 'Person') { echo 'checked '; } ?>/> Person<br />
      <input type="radio" value="Org" name="primary[]" onclick="swapPrimaryExtension('Org');" <?php if ($primary == 'Org') { echo 'checked '; } ?>/> Org<br />
    </td>
  </tr>


    <?php foreach ($primary_exts as $primary) : ?>
      <tr id="<?php echo $primary ?>_extension_list" style="display: none;">
        <td class="form_label"><?php echo $primary ?> Types</td>
        <td class="form_field margin_bottom">
          <?php foreach ($tier2_defs[$primary] as $def) : ?>
            <input type="checkbox" value="1" name="extensions[<?php echo $def->name ?>]"
              <?php if (in_array($def->name, array_keys((array) $sf_request->getParameter('extensions')))) { echo ' checked '; } ?> 
              />
            <?php echo $def->display_name ?> 
            <br />
          <?php endforeach; ?>

          <?php if (count($tier3_defs[$primary])) : ?>
            <div id="tier3_<?php echo $primary ?>_exts" style="display: none;">
              <hr />
              <?php foreach ($tier3_defs[$primary] as $def) : ?>
                <input type="checkbox" value="1" name="extensions[<?php echo $def->name ?>]"
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


  <tr>
    <td class="form_label">Name</td>
    <td class="form_field">
      <?php echo input_tag('name', $sf_request->getParameter('name'), 'size=40') ?>
      <br />
      <span class="form_help">(examples: <em>Goldman Sachs Group</em> or <em>Jesse L Jackson, Jr</em>)</span>
    </td>
  </tr>

  <tr>
    <td class="form_label">Blurb</td>
    <td class="form_field">
      <?php echo input_tag('blurb', $sf_request->getParameter('blurb'), 'size=40') ?>
      <br />
      <span class="form_help">(a short sentence or phrase)</span>
    </td>
  </tr>


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
      <?php echo submit_tag('Create') ?>
    </td>
  </tr>
</table>

</form>
</div>

</div>


<?php endif; ?>


<script type="text/javascript">

function showCreateForm()
{
  document.getElementById('create_link').style.display = 'none';
  document.getElementById('create_form').style.display = 'block';
}

function swapPrimaryExtension(selectedPrimary)
{
  unselectedPrimary = (selectedPrimary == 'Person') ? 'Org' : 'Person';

  rowDisplayValue = document.all ? 'block' : 'table-row';

  document.getElementById(selectedPrimary + '_extension_list').style.display = rowDisplayValue;
  document.getElementById(unselectedPrimary + '_extension_list').style.display = 'none';
}

function showTier3Extensions(primary)
{
  document.getElementById('tier3_' + primary + '_exts').style.display = 'block';
  document.getElementById('show_tier3_' + primary + '_exts').style.display = 'none';
}

<?php $primary = $sf_request->getParameter('primary'); $primary = $primary[0] ?>
<?php if ($primary) : ?>
swapPrimaryExtension('<?php echo $primary ?>');
<?php endif; ?>

</script>