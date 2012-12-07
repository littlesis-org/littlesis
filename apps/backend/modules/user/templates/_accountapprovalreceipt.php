Dear <?php echo $user->Profile->name_first ?>,

<?php if ($new) : ?>
Your account has been activated. Please follow this link to confirm your email address and log in with the email and password you provided:

http://littlesis.org/confirm?code=<?php echo $user->Profile->confirmation_code ?>


<?php endif; ?><?php if ($analyst) : ?>You are now a LittleSis analyst!  This means you can make edits and contributions to the database.

Once you log in, "edit" and "add relationship" buttons will appear next to the name on each profile page.  You will also be able to add new individuals and organizations to the database.

Please read the analyst how-to (http://littlesis.org/howto) before making edits.

<?php endif; ?><?php if ($new) : ?>Note: support for IE6 browsers is currently unstable.

<?php endif; ?>
Thank you,
LittleSis Admin