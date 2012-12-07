<?php use_helper('Javascript') ?>


<?php include_partial('entity/header', array('entity' => $entity)) ?>

<?php slot('rightcol') ?>
  <?php include_partial('entity/profileimage', array('entity' => $entity)) ?>
<?php end_slot() ?>

<h2>Add Relationship</h2>

<?php include_partial('global/formerrors', array('form' => $reference_form)) ?>

<form action="<?php echo url_for($entity->getInternalUrl('addRelationshipCategory', null, true)) ?>" method="POST">
<?php echo input_hidden_tag('id', $entity->id) ?>
<?php echo input_hidden_tag('entity2_id', $entity2->id) ?>

<table>
  <tr>
    <td class="form_label">With <?php echo $entity2->getPrimaryExtension() ?></td>
    <td class="form_field text_big">
      <?php echo entity_link($entity2) ?>
    </td>    
  </tr>

  <tr>
    <td class="form_label">
      Category
    </td>
    <td>
      <?php foreach ($categories as $cat) : ?>
        <?php if ($cat->id != RelationshipTable::LOBBYING_CATEGORY) : ?>
          <?php echo radiobutton_tag('category_id', $cat->id, $cat->id == $sf_request->getParameter('category_id')) ?> <?php echo $cat->display_name ?>
          <?php if ($cat->display_name == 'Education' ) : ?>
            <?php echo ' (as student)' ?>
          <?php endif; ?> 
        <?php else : ?>
          <input type="radio" name="category_id" id="category_id_<?php echo RelationshipTable::LOBBYING_CATEGORY ?>" value="<?php echo RelationshipTable::LOBBYING_CATEGORY ?>" onclick="swapLobbyingOptions(this);" <?php if ($cat->id == $sf_request->getParameter('category_id')) { echo ' checked'; } ?> /> Lobbying
        <?php endif; ?>
        <br />
      <?php endforeach; ?>    

      <br />

      <span class="text_big">
        <?php echo link_to('Explain categories &raquo;', 'help/relationshipCategories', array(
          'popup' => array('Relationship Categories', 'width=600,height=400,left=200,top=200,scrollbars=yes')      
        )) ?>
      </span>
    </td>
  </tr>

  <tbody id="lobbying_options" style="display: none;">
    <tr>
      <td></td>
      <td>
        <br />
        <span class="text_big">Which kind of lobbying relationship do you want to create?</span>
        <br />
        <br />
      </td>
    </tr>
    <tr>
      <td></td>
      <td>
        <input type="radio" name="lobbying_scenario" value="direct" /> <?php echo $entity->getName() ?> lobbied <?php echo $entity2->getName() ?> <em>directly</em>
        <br />
        <input type="radio" name="lobbying_scenario" value="direct_reverse" /> <?php echo $entity2->getName() ?> lobbied <?php echo $entity->getName() ?> <em>directly</em>
        <br />
        <input type="radio" name="lobbying_scenario" value="service" /> <?php echo $entity->getName() ?> <em>hired</em> <?php echo $entity2->getName() ?> to lobby a politican or government body
        <br />
        <input type="radio" name="lobbying_scenario" value="service_reverse" /> <?php echo $entity2->getName() ?> <em>hired</em> <?php echo $entity->getName() ?> to lobby a politican or government body   
        <br />
        <br />
        If the lobbying between <?php echo $entity->getName() ?> and <?php echo $entity2->getName() ?> was performed by a third party lobbying firm or lobbyist, you should add two separate relationships: one between <?php echo $entity->getName() ?> and the third party, and another between <?php echo $entity2->getName() ?> and the third party.
      </td>
    </tr>
  </tbody>


  <?php include_partial('global/formspacer') ?>

  <?php include_partial('reference/required', array(
    'form' => $reference_form,
    'hide_bypass' => true
  )) ?>
  
  <?php include_partial('global/formspacer', array('text' => "After adding the relationship you will be taken to an edit page to provide more details.")) ?>
  <?php include_partial('global/formspacer') ?>
  
  <tr>
    <td></td>
    <td>
      <?php echo submit_tag('Add') ?>
    </td>
  </tr>
</table>
</form>

<?php echo javascript_tag('

function swapLobbyingOptions(radio)
{
  options = document.getElementById(\'lobbying_options\');

  if (radio.checked)
  {
    options.style.display = \'table-row-group\';
  }
  else
  {
    options.style.display = \'none\';
  }
}

') ?>

<?php if ($sf_request->getParameter('category_id') == RelationshipTable::LOBBYING_CATEGORY) : ?>
<?php echo javascript_tag('
document.getElementById(\'lobbying_options\').style.display = \'table-row-group\';
') ?>
<?php endif; ?>