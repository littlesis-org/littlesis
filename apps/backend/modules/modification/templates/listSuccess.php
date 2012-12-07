<?php slot('header_text', 'Modifications') ?>
<?php slot('header_link', 'modification/list') ?>

<form action="<?php echo url_for('modification/list') ?>">

<?php echo input_hidden_tag('user_id', $sf_request->getParameter('user_id')) ?>
<?php echo input_hidden_tag('object_id', $sf_request->getParameter('object_id')) ?>
<?php echo input_hidden_tag('object_model', $sf_request->getParameter('object_model')) ?>

Look between <?php echo input_tag('start', $sf_request->getParameter('start')) ?> and <?php echo input_tag('end', $sf_request->getParameter('end')) ?> 
<br />
<br />
<input type="checkbox" value="1" name="model[Reference]"> Reference&nbsp;&nbsp;
<input type="checkbox" value="1" name="model[Entity]">Entity&nbsp;&nbsp;
<input type="checkbox" value="1" name="model[Relationship]">Relationship&nbsp;&nbsp;
<input type="checkbox" value="1" name="model[Address]"> Address&nbsp;&nbsp;
<input type="checkbox" value="1" name="model[Image]"> Image&nbsp;&nbsp;
<br />
<br />
<input type="checkbox" value="1" name="is_delete"> Is delete?&nbsp;&nbsp;
<input type="checkbox" value="1" name="is_create"> Is create?&nbsp;&nbsp;
<input type="checkbox" value="1" name="distinct"> Distinct user&nbsp;&nbsp;
Users matching <?php echo input_tag('user', $sf_request->getParameter('user')) ?>&nbsp;
<?php echo submit_tag('Go', 'class=button_small') ?>
</form>


<br />
<br />


<?php //echo link_to('show users only', 'modification/list?users_only=1') ?>

<br />
<br />

<?php include_partial('global/table', array(
  'columns' => array('Date', 'User', 'Object', 'Model', 'Action', 'Changes', 'Options'),
  'pager' => $modification_pager,
  'row_partial' => 'modification/listrow'
)) ?>