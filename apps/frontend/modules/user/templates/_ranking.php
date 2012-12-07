
<table class="datatable">
<tr>
<?php $rank = 1 ?>
<?php foreach($ranked_users as $ranked_user) : ?>
  <td style="vertical-align: bottom; padding-left: .5em">
    <?php if ($ranked_user->filename) : ?>
      <?php echo image_tag('small'.DIRECTORY_SEPARATOR.$ranked_user->filename) ?>
    <?php else : ?>
      <?php echo image_tag('system'.DIRECTORY_SEPARATOR.'user.png') ?>
    <?php endif; ?>  
  </td>
<?php endforeach; ?>
</tr>
<tr>
<?php foreach($ranked_users as $ranked_user) : ?>
  <td style="padding-top: .5em; padding-right: .9em; padding-left: .5em"> 
    <?php if ($sf_user->isAuthenticated() && $ranked_user->User->id == $sf_user->getGuardUser()->id) : ?>
      <span class="text_small entity_link"><?php echo link_to($sf_user->getGuardUser()->Profile->public_name, 'home/account') ?></span>
    <?php else : ?>
      <span class="text_small"><?php echo user_link($ranked_user->User) ?></span>
    <?php endif; ?>
    <br />
    <span class="text_small"><?php echo $ranked_user->score ?> points</span>
  </td>
  <?php $rank++ ?>  
<?php endforeach; ?>
</tr>
</table>
