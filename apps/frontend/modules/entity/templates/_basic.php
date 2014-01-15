<?php use_helper('LsJavascript', 'Pager', 'LsText') ?>


<?php include_partial('entity/header', array('entity' => $entity, 'show_actions' => true)) ?>



<!-- DUPLICATE NOTIFICATION -->
<?php if ($sf_user->isAuthenticated() && !$sf_user->hasCredential('merger') && !cache('duplicates')) : ?>
  <?php include_component('entity', 'possibleDuplicates', array('entity' => $entity)) ?>
  <?php cache_save() ?>
<?php endif; ?>



<!-- IMAGE, TAGS, ETC IN RIGHT COLUMN -->
<?php include_partial('entity/leftcol', array('entity' => $entity)) ?>


<!-- BASIC INFO -->

<?php slot('header_subtext') ?>
<?php if ($entity['blurb'] && $entity['blurb'] != '') : ?>  

  <div id="entity_blurb_container">

  <?php if ($sf_user->hasCredential('editor')) : ?>
    <div id="entity_blurb" onmouseover="showBlurbEdit();" onmouseout="hideBlurbEdit();">
  <?php else : ?>
    <div id="entity_blurb">
  <?php endif; ?>

  <span class="entity_blurb"><?php echo excerpt($entity['blurb'], 90) ?></span>
  
  <?php if ($sf_user->hasCredential('editor')) : ?>
    <a href="javascript:void(0);" id="entity_blurb_edit" style="display: none;" onClick="showEditBlurbForm('<?php echo str_replace("'", "\'", $entity['blurb']) ?>');">
      <?php echo image_tag('system/edit-pencil.png') ?>
    </a>
  <?php endif; ?>
  </div>
  </div>
<?php elseif ($sf_user->hasCredential('editor')) : ?>
  <div id="entity_blurb_container">
  <div id="entity_blurb">
    <a href="javascript:void(0);" onClick="showEditBlurbForm('');">[add a short description]</a>
  </div>
  </div>
<?php endif; ?>
<?php end_slot(); ?>

<?php if ($entity['summary']) : ?>
<span class="profile_summary">
  <?php include_partial('global/excerpt', array('text' => $entity['summary'], 'id' => 'summary', 'less' => true)) ?>
  <br />
  <br />
</span>
<?php endif; ?>


<?php if (false && $sf_user->hasCredential('editor')) : ?>

<?php sfContext::getInstance()->getResponse()->addJavascript(sfConfig::get('sf_prototype_web_dir').'/js/prototype'); ?>

<script>

function showEditBlurbForm(val)
{
  document.getElementById('entity_blurb').innerHTML = '\
<input size="80" maxlength="200" type="text" id="entity_blurb_input"  value="' + val + '" id="entity_blurb" onkeypress="return enterKey(event,\'edit_blurb_submit\');" /> \
<input type="submit" id="edit_blurb_submit" onClick="submitEditBlurbForm();"> \
';
}


function submitEditBlurbForm()
{
  blurb_input = document.getElementById('entity_blurb_input');
  new Ajax.Request('<?php echo url_for('entity/editBlurbInline') ?>', {
    method: 'post',
    parameters: {blurb: blurb_input.value, id: <?php echo $entity['id'] ?>},
    onSuccess: function(transport) {
      var response = transport.responseText;
      document.getElementById('entity_blurb_container').innerHTML = response;
    },
    onFailure: function() { alert('Something went wrong...'); }
  });
}

function enterKey(e, buttonId)
{
  var key;     
  if (window.event)
  {
    key = window.event.keyCode; //IE
  }
  else
  {
    key = e.which; //firefox     
  }

  if (key == 13)
  {
    if (typeof(buttonId) != 'undefined')
    {
      $(buttonId).click();
    }
    
    return false;
  }
  
  return true;
}

</script>


<script type="text/javascript">

function showBlurbEdit()
{
  document.getElementById('entity_blurb_edit').style.display = 'inline';
}

function hideBlurbEdit()
{
  document.getElementById('entity_blurb_edit').style.display = 'none';
}

</script>



<?php endif; ?>
