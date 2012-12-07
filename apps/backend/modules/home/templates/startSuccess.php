<?php slot('header_text', 'Administration') ?>

<?php echo link_to('Users', 'user/list') ?><br />
<br />
<?php echo link_to('Groups', 'sfGuardGroup/index') ?><br />
<br />
<?php echo link_to('Modifications', 'modification/list') ?><br />
<br />
<?php echo link_to('Operations', 'operation/index') ?><br />
<br />
<?php echo link_to('API Users', 'api/users') ?><br />
<br />
<?php echo link_to('API Log', 'api/log') ?><br />
<br />
<?php echo link_to('Remove Cache', 'cache/remove') ?><br />
<br />
<?php echo link_to('Cache Keys', 'cache/keys') ?></br />