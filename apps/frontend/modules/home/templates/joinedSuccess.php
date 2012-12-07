<?php slot('header_text', 'Welcome!') ?>

<span class="text_big">
Congratulations, you're now a LittleSis analyst!

<?php if ($sf_request->getParameter('conf')) : ?>
  <?php echo link_to('Start using LittleSis', '@homepage') ?>.
<?php else : ?>
  <br />
  <br />
  But you need to confirm your email address before you can try on your analyst hat. 
  Check your email inbox shortly for a confirmation link.
<?php endif; ?>

</span>