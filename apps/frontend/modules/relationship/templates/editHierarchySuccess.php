<?php use_helper('Javascript') ?>
<?php $params = (array) $sf_request->getParameter('relationship') ?>

<?php slot('header_text', 'Edit Hierarchy') ?>

<?php include_partial('global/formerrors', array('form' => array($category_form, $reference_form))) ?>

<form action="<?php echo url_for('relationship/edit') ?>" method="POST">
<?php echo input_hidden_tag('id', $relationship->id) ?>
<?php echo $category_form['_csrf_token'] ?>

<table>
  <?php include_partial('reference/required', array('form' => $reference_form)) ?>
  
  <tr>
    <td class="form_label">Child</td>
    <td id="entity1_field" class="form_field text_big"><?php echo entity_link($entity1) ?></td>
  </tr>

  <tr>
    <td></td>
    <td class="form_field">
      <a href="javascript:void(0);" onclick="switchEntities();">switch &uarr;&darr;</a>
      <?php echo input_hidden_tag('switch', 0, 'id=switch') ?>
    </td>
  </tr>

  <tr>
    <td class="form_label">Parent</td>
    <td id="entity2_field" class="form_field text_big"><?php echo entity_link($entity2) ?></td>
  </tr>

  <tr>
    <td class="form_label"></td>
    <td id="entity1_field" class="form_field text_big">      
      <?php echo entity_link($entity1) ?>
      is
      <?php echo $category_form['description1']->render() ?>
      of 
      <?php echo entity_link($entity2) ?>
    </td>
  </tr>

  <tr>
    <td class="form_label"></td>
    <td id="entity2_field" class="form_field text_big">
      <?php echo entity_link($entity2) ?>
      is
      <?php echo $category_form['description2']->render() ?>
      of 
      <?php echo entity_link($entity1) ?>
    </td>
  </tr>

  <?php include_partial('global/form', array(
    'form' => $category_form,
    'field_names' => array('start_date', 'end_date', 'is_current', 'notes')
  )) ?>

  <?php include_partial('global/formspacer') ?>

  <tr>
    <td></td>
    <td>
      <?php echo submit_tag('Save') ?>
      </form>
      <?php echo button_to('Remove', 'relationship/remove?id=' . $relationship->id, 'post=true confirm=Are you sure you want to remove this relationship?') ?>
      <?php echo button_to('Cancel', 'relationship/view?id=' . $relationship->id) ?>
    </td>
  </tr>
</table>

<?php include_partial('relationship/entitysearchscript') ?>


<?php echo javascript_tag("

function switchEntities()
{
  var entity1Field = document.getElementById('entity1_field').innerHTML;
  var entity2Field = document.getElementById('entity2_field').innerHTML;
  var switchLink = document.getElementById('switch');

  document.getElementById('entity1_field').innerHTML = entity2Field;
  document.getElementById('entity2_field').innerHTML = entity1Field;
  
  if (switchLink.value == 0)
  {
    switchLink.value = 1;
  }
  else
  {
    switchLink.value = 0;
  }
}

") ?>


<?php if ($sf_request->getParameter('switch')) : ?>
<?php echo javascript_tag("

switchEntities();

") ?>
<?php endif; ?>