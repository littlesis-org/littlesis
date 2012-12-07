<?php if (!$sf_user->isAuthenticated() || $sf_user->getGuardUser()->Profile->enable_pointers) : ?>
<div class="pointer_box">
<table>
  <tr>
    <td>
      <?php echo image_tag('system/finger.gif', 'width=60') ?>
    </td>
    <td class="text">
      <?php echo $text ?>
    </td>
  </tr>
</table>
</div>
<div class="hide_pointer text_small">
  <?php if ($sf_user->isAuthenticated()) : ?>
    <?php echo link_to('hide pointers', 'home/hidePointers') ?>
  <?php endif; ?>
</div>
<?php endif; ?>
