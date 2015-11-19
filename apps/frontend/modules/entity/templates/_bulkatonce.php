<br>



<strong>Please process each of the following <?php echo count($matches) ?> names</strong>:<br />
<li>Select "no action" if you don't want to create a relationship between <?php echo $entity->name ?> and the name found.</li>
<li>If the entity already exists in the database, select the appropriate match; otherwise, select "create new."</li>
<li>Modify relationship fields as you see fit.</li><br><br>

    <?php echo input_hidden_tag('id', $entity['id']) ?>
    <?php echo input_hidden_tag('ref_id', $ref_id) ?>
    <?php echo input_hidden_tag('count', count($matches)) ?>
    <?php echo input_hidden_tag('category_name', $category_name) ?>
    <?php echo input_hidden_tag('default_type', $default_type) ?>
    <?php echo input_hidden_tag('entity_id', $entity->id) ?>
    <?php echo input_hidden_tag('order', $order) ?>
    
    <table class="donor-table">
      <tr class="donor-table-header">
        <td width="20%">Names</td>
        <td width="30%">Matches in LittleSis</td>
        <td width="20%">Create new <?php echo $default_type ?>?<br><a href="javascript:void(0);" onclick="selectAll('create_new');"  style="color: white; font-weight: normal">(select all)</a></td>
        <td width="20%">Relationship fields</td>
        <td width="10%">No Action<br><a href="javascript:void(0);" onclick="selectAll('no_action');" style="color: white; font-weight: normal">(select all)</a>
      </tr>
    
    <?php $count = 0 ?>
    <?php foreach ($matches as $match) : ?>
      <tr>
        <td>
          <strong><?php echo $match['name'] ?></strong>
        </td>

        <td>
        
        <div id="existing_relationships_<?php echo $count?>"></div>
        <?php foreach ($match['search_results'] as $entity) :?>
          <?php echo radiobutton_tag('entity_' . $count, $entity['id'], "", array("onclick" => "checkExisting(this, $count);")) ?> &nbsp;
          <?php echo link_to($entity['name'], EntityTable::getInternalUrl($entity), array('target' => '_new')) ?>
          &nbsp;
          <span style="font-size: 10px;"><?php echo excerpt($entity['blurb'], 50) ?></span>
          <br />
        <?php endforeach; ?>
        </td>

        <td style="width: 200px;">
          <?php $checked = count($match['search_results']) ? "" : "checked" ?>
          <?php $display = count($match['search_results']) ? "none" : "block" ?>
          <?php echo '<input type="radio" name="entity_' . $count . '" value="new" ' . $checked . ' onchange="showNew(' . $count . ');" class="create_new"/>' ?>
          create new <?php echo $default_type ?>
          <div id ="newform-<?php echo $count ?>" class="newform" style="display:<?php echo $display ?>"; float: left; width:200px">
            <div style="display:block; padding:5px">
              <span style="float: left; width: 40px"><label>Name</label></span>
              <span>
                <input type="text" id="new_name_<?php echo $count ?>" name="new_name_<?php echo $count ?>" value="<?php echo EntityTable::cleanName($match['name']) ?>" />
              </span>
            </div>
            <div style="display:block; padding:5px">
              <span style="float: left; width: 40px"><label >Blurb</label></span>
              <span><?php echo input_tag("new_blurb_" . $count, isset($match['blurb']) ? $match['blurb'] : "") ?></span>
            </div>
            <div style="display:block; padding:5px">
              <span style="float: left; width: 40px"><label >Summary</label></span>
              <span><?php echo input_tag("new_summary_" . $count, isset($match['summary']) ? $match['summary'] : "") ?></span>
            </div> 
            <!-- 
<div style="display:block; padding:5px">
              <span>Person <?php echo radiobutton_tag("new_type_" . $count, 'Person', $default_type != 'Org' ? true : false) ?></span>
              <span>Org <?php echo radiobutton_tag("new_type_" . $count, 'Org', $default_type == 'Org' ? true : false) ?></span>
            </div>
 -->  
            <div>
              <?php foreach ($extensions as $ext) : ?>
                <input type="checkbox" value="<?php echo $ext->name ?>" name="new_extensions_<?php echo $count?>[]"
                  
                  <?php if (isset($match['types']) && in_array($ext->name,$match['types'])) : ?>
                    checked
                  <?php endif; ?>
                  />
                <?php echo $ext->display_name ?> 
                <br />
              <?php endforeach; ?>
            </div>
          </div>
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
          <?php echo '<input type="hidden" name="relationship_' . $count . '[ref_source]" value="' . $match['reference_link'] . '" />' ?>
          <?php echo '<input type="hidden" name="relationship_' . $count . '[ref_name]" value="' . $match['reference_name'] . '" />' ?>
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
