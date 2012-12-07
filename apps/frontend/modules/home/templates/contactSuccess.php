<?php slot('header_text', isset($flag_url) ? 'Flag for Review ' : 'Contact Us') ?>
<?php slot('header_link', '@contact') ?>

<div class="pointer_box">
  Please only use this form to send a message to the LittleSis team. We <strong>DO NOT</strong> have contact info for the people and organizations profiled in LittleSis.
</div>

<br />

<?php if (isset($sent)) : ?>

Thank you for your message. We'll get back to you shortly.

<?php else : ?>

  <?php if (isset($flag_url)) : ?>
    Please contact us regarding problems or inaccuracies at the following URL:
    <br />
    <br />
    <?php echo '<a href="' . $flag_url . '">' . $flag_url . '</a>' ?>
    <br />
    <br />
    <br />
  <?php endif; ?>
<?php include_partial('global/formerrors', array('form' => $contact_form)) ?>

<form action="<?php echo url_for('@contact') ?>" method="POST" enctype="multipart/form-data">
<?php echo $contact_form['_csrf_token'] ?>
<?php if (isset($flag_url)) : ?>
  <?php echo '<input type="hidden" name="flag_url" value="' . $flag_url . '">' ?>
<?php endif; ?>

<table>
<?php if (!$sf_user->isAuthenticated()) : ?>
  <?php include_partial('global/formfield', array('field' => $contact_form['name'], 'required' => true)) ?>
  <?php include_partial('global/formfield', array('field' => $contact_form['email'], 'required' => true)) ?>
<?php endif; ?>
  <?php include_partial('global/formfield', array('field' => $contact_form['subject'], 'required' => true)) ?>
  <?php include_partial('global/formfield', array('field' => $contact_form['message'], 'required' => true)) ?>
  <?php include_partial('global/formfield', array('field' => $contact_form['file'])) ?>
<?php if (!$sf_user->isAuthenticated()) : ?>
  <?php include_partial('global/formfield', array('field' => $contact_form['captcha'], 'required' => true)) ?>
<?php endif; ?>
  <tr>
    <td></td>
    <td>
      <?php echo submit_tag('Send') ?>
    </td>
  </tr>
</table>
</form>


<?php endif; ?>