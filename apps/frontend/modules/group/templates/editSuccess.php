<?php include_partial('group/header', array('group' => $group)) ?>

<h2>Edit Group</h2>

<?php include_partial('global/formerrors', array('form' => $group_form)) ?>

<form action="<?php echo url_for($group->getInternalUrl('edit')) ?>" method="POST">
<?php echo input_hidden_tag('name', $sf_request->getParameter('name')) ?>
<table class="form_table" width="500">
  <?php include_partial('global/form', array(
    'form' => $group_form,
    'label_width' => '120px'
  )) ?>
  <tr>
    <td></td>
    <td><?php echo submit_tag('Save') ?>
  </tr>
</table>
</form>

<script type="text/javascript">
tinyMCE.init({
  theme : "advanced",
  mode: "exact",
  elements: "sf_guard_group_description",
  height: 250,
  width: 600,
});
</script> 