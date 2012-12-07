<?php use_helper('Pager', 'Javascript') ?>

<?php include_partial('entity/header', array('entity' => $entity)) ?>

<?php slot('rightcol') ?>
  <?php include_partial('entity/profileimage', array('entity' => $entity)) ?>
<?php end_slot() ?>

<h2>Add Board</h2>

<?php if (!isset($matches)) : ?>

	Start by entering the reference where board members are found (an external url).  
	<br />
	<br />
	<b>Make sure the people listed on the page you specified are board members!</b>
	
	<?php include_partial('global/formerrors', array('form' => $reference_form)) ?>
	
	<form action="<?php echo url_for($entity->getInternalUrl('addBoard', null, true)) ?>" method="POST">
	<?php echo input_hidden_tag('id', $entity->id) ?>
	<?php echo input_hidden_tag('start', 0) ?>
	
	<table>
		
		<?php include_partial('global/formspacer') ?>
	
		<?php include_partial('reference/required', array(
			'form' => $reference_form,
			'hide_bypass' => true		
		)) ?>
	
		<tr>	
			<td>
				<?php echo submit_tag('Get Data') ?>
			</td>
			<td></td>
		</tr>
	</table>
	</form>

<?php else : ?>

	Use the table below to add new board members for this organization, based on information from the provided link. <br /><br />
	
	The names in the left column were found at the link.  
  We've searched the database for each name and presented possible matches in the middle column.  
  Select the appropriate match, or "create new" where a new person needs to be created.  
	
	<?php if (isset($new_rels)) : ?>
		<table>
		<tr>
		<td>
		<?php if (isset($new_rels) && count($new_rels)) : ?>
			 These relationships were successfully created: <br />
			 </td>
				<td style="float: left">
			<?php foreach ($new_rels as $rel) : ?>
			<?php echo link_to($rel->getName(), RelationshipTable::getInternalUrl($rel), array('target' => '_new')) ?><br />
			<?php endforeach; ?>
			</td>
			</tr>
		<?php endif; ?>

		<?php if (isset($existing_rels) && count($existing_rels)) : ?>
			<tr> 
			<td>
			 These relationships already existed: <br />
			 </td>
			<td style="float:left">
			<?php foreach ($existing_rels as $rel) : ?>
			<?php echo link_to($rel->getName(), RelationshipTable::getInternalUrl($rel), array('target' => '_new')) ?><br />
			<?php endforeach; ?>
			</td>
			</tr>
		<?php endif; ?>
		</table>
	<?php endif; ?>


	<?php if ($total == 0) : ?>
		<br />
		No names found on that page.  <?php echo link_to("Try again", EntityTable::generateRoute($entity, 'addBoard')) ?>.
	<?php elseif ($start > $total) : ?>
		<br />
		All done.  <?php echo link_to("Return to " . $entity->getName(), EntityTable::getInternalUrl($entity)) ?>.
	<?php else : ?>
		<br />
		<br />
		Matches <b>
		<?php echo $start + 1 . " to " . $end  . " of " . $total . "</b> total matches found at the url you specified." ?>
		<br />
		<br />
		
		
		<form action="<?php echo url_for($entity->getInternalUrl('addBoard', null, true)) ?>" method="POST">
		<?php echo input_hidden_tag('id', $entity['id']) ?>
		<?php echo input_hidden_tag('ref_id', $ref_id) ?>
		<?php echo input_hidden_tag('start', $start + $lim) ?>

		<table class="donor-table">
			<tr class="donor-table-header">
        <td>Names Found</td>
        <td>Matches in LittleSis</td>
        <td>Create new person </td>
        <td>No Action</td>
  		</tr>
		
		<?php $count = 0 ?>
		<?php foreach ($matches as $name => $entities) : ?>
			<tr>
        <td>
          <?php echo $name ?>
        </td>

        <td>
        <?php foreach ($entities as $entity) :?>
          <?php echo radiobutton_tag('entity_' . $count, $entity['id']) ?> &nbsp;
          <?php echo link_to($entity['name'], EntityTable::getInternalUrl($entity), array('target' => '_new')) ?>
          <br />
        <?php endforeach; ?>
        </td>

        <td style="width: 200px;">
          <?php echo '<input type="radio" name="entity_' . $count . '" value="new" onclick="showNew(' . $count . ');" />' ?> &nbsp;
          create new 
          <div id ="newform-<?php echo $count ?>" style="display:none; float: left; width:200px">
            <div style="display:block; padding:5px">
              <span style="float: left; width: 40px"><label>Name</label></span>
              <span><?php echo input_tag("new_name_" . $count, EntityTable::cleanName($name)) ?></span>
            </div>
            <div style="display:block; padding:5px">
              <span style="float: left; width: 40px"><label >Blurb</label></span>
              <span><?php echo input_tag("new_blurb_" . $count) ?></span>
            </div>   
          </div>
        </td>

        <td>
          <?php echo radiobutton_tag('entity_' . $count, 0) ?> &nbsp;
        </td>	
			</tr>
			<?php $count++ ?>
		<?php endforeach; ?>
		
		</table>
		<br />
		<?php if ($total > $end) : ?>
			<?php echo submit_tag('Add & next page') ?>
			<?php echo submit_tag('Cancel') ?>
			<?php else : ?>
			<?php echo input_hidden_tag('finished', 1) ?>
			<?php echo submit_tag('Add & finish') ?>
			<?php echo submit_tag('Cancel') ?>
		<?php endif; ?>
		</form>
			
	<?php endif; ?>
	
<?php endif; ?>

<script type="text/javascript">

function showNew(num)
{
  document.getElementById('newform-' + num).style.display = 'block';
}

</script>
