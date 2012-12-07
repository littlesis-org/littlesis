<form action="<?php echo url_for('@sf_guard_signin') ?>" method="post">
<?php echo input_hidden_tag('referer', $sf_request->getParameter('referer', isset($request_path) ? $request_path : null)) ?>
  <table style="width: auto;">
    <?php include_partial('global/form', array(
      'form' => $form, 
      'show_required' => false
    )) ?>
    <tr>
      <td></td>
      <td>
        <input type="submit" value="Login" />
        <?php if (!isset($lean) || !$lean) : ?>
          <?php echo link_to('Sign up!', '@join') ?>
          <br />
          <br />
          &nbsp;<span class="text_small"><?php echo link_to('Forgot password?', 'home/resetPassword') ?></span>
        <?php else : ?>
          <?php echo stylesheet_tag('main') ?>
          <?php echo stylesheet_tag('partials') ?>
        <?php endif; ?>
      </td>
    </tr>
  </table>
</form>