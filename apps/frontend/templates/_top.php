<div id="topcontent">
<div id="login">

<?php if ($sf_user->isAuthenticated()) : ?>
  
  Logged in as <strong><?php echo link_to($sf_user->getProfile()->public_name, 'home/notes') ?></strong>
  in <strong><?php echo network_link(Doctrine::getTable('LsList')->find($sf_user->getProfile()->home_network_id)) ?></strong>.
  
  <?php if ($sf_user->isAuthenticated() && $sf_user->getProfile()->unread_notes == 1) : ?>
    You have <strong><?php echo link_to('1 new note!', 'home/notes') ?></strong>
  <?php elseif ($sf_user->isAuthenticated() && $sf_user->getProfile()->unread_notes > 0) : ?>
    You have <strong><?php echo link_to($sf_user->getProfile()->unread_notes . ' new notes!', 'home/notes') ?></strong>
  <?php endif; ?>

    [ <?php echo link_to('Logout', '@sf_guard_signout') ?> / <?php echo link_to('Settings', 'home/settings') ?> ]



<?php else : ?>

  <?php $queryStr = http_build_query($sf_request->getGetParameters()) ?>
  <?php $referer = substr($sf_request->getPathInfo(), 1) . ($queryStr ? '?' . $queryStr : '') ?>
  
  <strong><?php echo link_to('Login', '@sf_guard_signin' . ($referer ? '?referer=' . $referer : '')) ?></strong> or <strong class="text_big"><?php echo link_to('Sign Up', 'home/join') ?></strong> to edit

<?php endif; ?>

</div>


<div id="logo">
  <?php echo link_to(image_tag('system' . DIRECTORY_SEPARATOR . 'logo-lean2-subtitle.png', 'border=0 alt=LittleSis'), '@homepage') ?>
</div>
</div>