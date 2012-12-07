<?php include_partial('user/header', array('user' => $profile->User, 'show_actions' => false)) ?>

<h2>Edit Settings</h2>

<?php include_partial('global/formerrors', array('form' => $settings_form)) ?>

<form action="<?php echo url_for('home/editSettings') ?>" method="POST">
<table>
  <?php include_partial('global/form', array('form' => $settings_form, 'label_width' => '200px')) ?>
  <tr>
    <td></td>
    <td>
      <?php echo submit_tag('Save') ?>
    </td>
  </tr>
</table>
</form>