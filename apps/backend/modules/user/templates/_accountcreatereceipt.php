Dear <?php echo $user->Profile->name_first ?>,

Congratulations, you're now a LittleSis analyst!  This means you can make edits and contributions to profile pages, and take part in the LittleSis community.

<?php if (isset($password)) : ?>

Please save the following login information:
Email: <?php echo $user->Profile->email ?>

Password: <?php echo $password ?>

<?php endif; ?>

<?php if (!$is_invited) : ?>

***********************
Before you can log in, you must confirm your email address by following this link:
http://littlesis.org/confirm?code=<?php echo $user->Profile->confirmation_code
?>

***********************

<?php endif; ?>

Once you log in, "edit" and "add relationship" buttons will appear next to the name on each profile page.  You will also be able to add new individuals and organizations to the database.

Please read the analyst how-to (http://littlesis.org/howto) before making edits.

As always, feel free to contact us (http://littlesis.org/contact) with any questions.


Thank you,
LittleSis Admin