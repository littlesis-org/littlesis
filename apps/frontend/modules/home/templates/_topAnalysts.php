<?php foreach ($users as $user) : ?>
  <table style="border-bottom: 1px dotted #EEEEEE;">
    <tr>
      <td style="padding: 0.5em; width: 40px;">
        <?php echo user_pic($user->User, 'profile', array('width' => 40)) ?>
      </td>
      <td style="padding: 0.5em;">
        <span class="text_big"><?php echo user_link($user->User) ?></span>
        <br />
        <span style="color: #666;"><?php echo $user->score ?> points</span>
      </td>
    </tr>
  </table>
<?php endforeach; ?>