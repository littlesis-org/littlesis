<?php slot('header_text', 'Your Account') ?>
<?php slot('header_link', '@account') ?>


<?php slot('rightcol') ?>
  <?php include_partial('home/profileimage', array('profile' => $profile)) ?>
  <br />
<?php end_slot() ?>

<h2>Edit Public Profile</h2>

<?php include_partial('global/formerrors', array('form' => $profile_form)) ?>

<form action="<?php echo url_for('home/editProfile') ?>" method="POST">
<?php echo input_hidden_tag('referer', $sf_request->getReferer()) ?>
<table>
  <?php include_partial('global/formspacer') ?>

  <?php include_partial('global/form', array('form' => $profile_form)) ?>
  <tr>
    <td></td>
    <td>
      <?php echo submit_tag('Save') ?>
    </td>
  </tr>
</table>
</form>