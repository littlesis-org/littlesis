<?php slot('header_text', 'Welcome to LittleSis') ?>

<h3>Getting Started</h3>

<ul>
  <li><?php echo link_to('Login', '@sf_guard_signin') ?></li>
  <li><?php echo link_to('Add a new person', 'entity/addPerson') ?></li>
  <li><?php echo link_to('Add a new organization', 'entity/addOrg') ?></li>
  <li><?php echo link_to('See recent edits', 'modification/latest') ?></li>
  <li><?php echo link_to('Read the site guide', 'home/guide') ?></li>
  <li><?php echo link_to('Watch howto videos', 'home/videos') ?></li>
</ul>
