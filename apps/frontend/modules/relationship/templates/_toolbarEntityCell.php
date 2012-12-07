    <td style="width: 50px; vertical-align: top;"><strong>Entity <?php echo $position ?>:</strong></td>
    <td style="width: 250px; vertical-align: top;">

<input type="hidden" id="entity<?php echo $position ?>_id" name="entity<?php echo $position ?>_id" />
<input type="hidden" id="ext<?php echo $position ?>" name="ext<?php echo $position ?>" />
<span id="entity<?php echo $position ?>_link"></span>
<input type="button" id="entity<?php echo $position ?>_clear" class="tiny-button" value="x" style="display: none;" onclick="clearEntity(<?php echo $position ?>);" />
<input type="text" id="entity<?php echo $position ?>_input" name="entity<?php echo $position ?>_input" size="20" onkeypress="return enterKey(event,'entity<?php echo $position ?>_submit');" />
<div id="entity<?php echo $position ?>_results" style="min-width: 300px; display: none; position: absolute; padding: 0.5em; background-color: #fff; border: 1px solid #ddd;"></div>
<?php include_partial('relationship/toolbarCreateForm', array('position' => $position)) ?>
<?php echo button_to_remote(
  'Search', 
  array(
    'update' => 'entity' . $position . '_results',
    'url' => 'http://littlesis.org/relationship/toolbarSearch',
    'method' => 'post',
    'with' => "'page=1&position=" . $position . "&q=' + \$F('entity" . $position . "_input')",
    'complete' => 'showSearchResults(' . $position . ')'
  ),
  array('class' => 'button_small', 'id' => 'entity' . $position . '_submit')
) ?>

    </td>