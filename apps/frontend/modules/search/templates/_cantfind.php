<br />

<?php $pointerText = 'Can\'t find something that should be on LittleSis? ' ?>

<br />
<?php if (!$sf_user->isAuthenticated()) : ?>
    <?php $queryStr = http_build_query($sf_request->getGetParameters()) ?>
    <?php $referer = substr($sf_request->getPathInfo(), 1) . ($queryStr ? '?' . $queryStr : '') ?>
    <?php $pointerText .= '<strong>' . link_to('Login', '@sf_guard_signin?referer=' . $referer) . '</strong> or <strong>' .  link_to('sign up', 'home/join') . '</strong> to add it yourself!' ?>
<?php else : ?>
  <?php if ($sf_user->hasCredential('contributor')) : ?>
    <?php $pointerText .= 'Add it now: ' ?>
    <?php $pointerText .= '&nbsp;<strong>' . button_to('New Person','entity/addPerson') . '</strong> &nbsp;<strong>' . button_to('New Organization','entity/addOrg') . '</strong>' ?>
  <?php else : ?>
    <?php $pointerText .= '<br /><br />Please <strong>' . link_to('contact us','home/contact') . '</strong> to become an analyst and add it yourself!' ?>
  <?php endif; ?>
<?php endif; ?>            

<?php if (strlen($pointerText)) : ?>
  <?php echo '<div class="pointer_box padded"><span class ="text_big">' . $pointerText . '</span></div>' ?>
<?php endif; ?>