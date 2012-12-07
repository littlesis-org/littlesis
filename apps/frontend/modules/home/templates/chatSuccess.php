<?php
  $sf_response->addJavascript('/yshout/js/jquery.js');
  $sf_response->addJavascript('/yshout/js/yshout.js');
  $sf_response->addStylesheet('/yshout/css/light.yshout.css');
  $path = $sf_request->getUriPrefix() . $sf_request->getRelativeUrlRoot() . '/yshout/';
?>

<?php slot('header_text', 'Analyst Chat') ?>



<?php slot('usercol') ?>

<?php include_partial('global/section', array(
  'title' => 'Recent Chatters',
  'pointer' => 'Analysts who have viewed this page in the past five minutes'
)) ?>
<table>
<?php foreach ($users as $u) : ?>
  <tr>
    <td style="width: 50px; padding-top: 5px;">
      <?php echo user_pic($u, 'profile', array('width' => 40)) ?>
    </td>
    <td style="vertical-align: middle;">   
      <span class="text_big"><?php echo user_link($u) ?></span>
    </td>
  </tr>
<?php endforeach; ?>
</table>
<?php end_slot() ?>



<script type="text/javascript">
  new YShout({
    yPath: '<?php echo $path ?>',
    log: <?php echo $room ?>, 
    prefs: {
      publicName: '<?php echo $sf_user->getProfile()->public_name ?>', 
      truncate: 10,
      messageLength: 500,
      timestamp: 24,
      defaultMessage: ''
    }
  });

  refresh = function() {
    if (!$('#ys-input-message').val()) {
      location.reload();
    }
  };

  setTimeout('refresh()', 300000);  //refresh every five minutes
</script>

<div id="yshout"></div>