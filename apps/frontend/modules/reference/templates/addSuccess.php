<?php slot('header_text', $object->getName()) ?>
<?php slot('header_link', strtolower(get_class($object)) . '/view?id=' . $object->id) ?>


<h2>Add Reference</h2>

<?php include_partial('global/formerrors', array('form' => $reference_form)) ?>

<form action="<?php echo url_for('reference/add') ?>" method="POST">
<?php echo input_hidden_tag('model', get_class($object)) ?>
<?php echo input_hidden_tag('id', $object->id) ?>
<?php echo $reference_form['_csrf_token'] ?>

<table>
  <?php include_partial('global/form', array('form' => $reference_form, 'field_names' => array('source', 'name', 'source_detail', 'publication_date', 'excerpt'))) ?>
  <tr>
    <td class="form_label">Fields</td>
    <td>
      <?php $half = round(count($fields) / 2) ?>
      <?php $fields1 = array_slice($fields, 0, $half) ?>
      <?php $fields2 = array_slice($fields, $half) ?>

      <table style="width: auto;">
        <tr>
          <td>
            <?php foreach ($fields1 as $name => $label) : ?>
              <input type="checkbox" name="fields[<?php echo $name ?>]" value="1" /> <?php echo $label ?><br />
            <?php endforeach; ?>
          </td>
          <td style="padding-left: 1em;">
            <?php foreach ($fields2 as $name => $label) : ?>
              <input type="checkbox" name="fields[<?php echo $name ?>]" value="1" /> <?php echo $label ?><br />
            <?php endforeach; ?>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <?php include_partial('global/formspacer') ?>
  
  <tr>
    <td></td>
    <td>
      <?php echo submit_tag('Add') ?>
      </form>
      <?php echo button_to('Cancel', 'reference/list?model=' . get_class($object) . '&id=' . $object->id) ?>
    </td>
  </tr>
</table>

</form>