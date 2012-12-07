<?php use_helper('Javascript') ?>
<span class="invite_box">

<span id="invite_link">
  <strong><a href="javascript:void(0);" onclick="showForm();">invite friends!</a></strong>
</span>
<span id="invite_form" class="invite_form" style="display: none">
<form style="display: inline;">
Email: 
<?php echo input_tag('email','',array('size' => '15', 'onkeypress' => 'return event.keyCode!=13')) ?>
       
<?php echo submit_to_remote(
  'invite_submit', 
  'Invite', 
  array(
    'update' => 'invite_result',
    'url' => 'home/invite'
  ),
  array(
    'class' => 'button_small'
  )
) ?>

</span>
</form>
<span id="invite_result" class="invite_result">
</span>
</span>

<?php echo javascript_tag("

function showForm()
{
  document.getElementById('invite_link').style.display = 'none';
  document.getElementById('invite_form').style.display = 'inline';
}


") ?>

