<br>
<?php include_partial('global/warning', array(
  'message' => 'This is high stakes.  You are potentially creating <strong>' . count($matches) . ' new ' . $category_name . ' relationships</strong>. Proceed with caution!'
)) ?>

<br><br><br>


<strong>Please process each of the following <?php echo count($matches) ?> names</strong>:<br />
<li>Select "no action" if you don't want to create a relationship between <?php echo $entity->name ?> and the name found.</li>
<li>Modify relationship fields as you see fit.</li><br><br>

    <?php echo input_hidden_tag('id', $entity['id']) ?>
    <?php echo input_hidden_tag('ref_id', $ref_id) ?>
    <?php echo input_hidden_tag('count', count($matches)) ?>
    <?php echo input_hidden_tag('category_name', $category_name) ?>
    <?php echo input_hidden_tag('entity_id', $entity->id) ?>
    <?php echo input_hidden_tag('order', $order) ?>
    <?php echo input_hidden_tag('add_method', $add_method) ?>
    
    <table class="donor-table">
      <tr class="donor-table-header">
        <td width="60%">Matches in LittleSis</td>
        <td width="30%">Relationship fields</td>
        <td width="10%">No Action<br><a href="javascript:void(0);" onclick="selectAll('no_action');" style="color: white; font-weight: normal">(select all)</a>
      </tr>
    
    <?php $count = 0 ?>
    <?php foreach ($matches as $match) : ?>
      <tr>
        <td>
        
        <div id="existing_relationships_<?php echo $count?>"></div>
        <?php foreach ($match['search_results'] as $entity) :?>
          <?php echo radiobutton_tag('entity_' . $count, $entity['id'], "", array("onclick" => "checkExisting(this, $count);")) ?> &nbsp;
          <?php echo link_to($entity['name'], EntityTable::getInternalUrl($entity), array('target' => '_new')) ?>
          &nbsp;
          <span style="font-size: 11px;"><?php echo excerpt($entity['blurb'], 50) ?></span>
          <br />
          <span style="font-size: 11px;"><?php echo $entity['summary'] ?></span>
        <?php endforeach; ?>
        </td>
        <td class="form_help">
          <strong><?php echo $category_name . " fields:<br>" ?></strong>
          <?php foreach($field_names as $field_name) : ?>
            
            <strong><?php echo $form_schema[$field_name]->renderLabel() ?>:</strong> 
            
            <?php if(get_class($form_schema[$field_name]->getWidget()) == 'LsWidgetFormSelectRadio') : ?>
              <?php $field = $form_schema[$field_name]->getWidget()->render("relationship[" . $field_name ."]", isset($match[$field_name]) ? $match[$field_name] : $form_schema[$field_name]->getValue()) ?>
            <?php else : ?>
              <?php $field = $form_schema[$field_name]->render(array("value" => isset($match[$field_name]) ? $match[$field_name] : $form_schema[$field_name]->getValue())) ?>
            <?php endif; ?>           
            <?php $field = str_replace('="relationship','="relationship_' . $count, $field) ?>
            <?php echo $field ?><br />
            
          <?php endforeach; ?>
         
        </td> 
        </td>  
        <td>
          <?php echo radiobutton_tag('entity_' . $count, 0, "checked", array('class'=>'no_action')) ?> &nbsp;
        </td>  
        
      </tr>
      <?php $count++ ?>
    <?php endforeach; ?>
    
    </table>
    <br />
    <?php echo submit_tag('Submit') ?>
    <?php echo submit_tag('Cancel') ?>
    
    </form>
