Dear <?php echo $user->Profile->name_first ?>,

Congratulations, you're now a LittleSis analyst!  This means you can make edits and take part in the LittleSis community.

Before you can log in with the email address and password you provided, you must confirm your email address by following this link:
http://littlesis.org/confirm?code=<?php echo $user->Profile->confirmation_code
?>

Once you log in, "edit" and "add relationship" buttons will appear next to the name on each profile page. You will also be able to add new individuals and organizations to the database.

Use the help pages as a reference for getting started: http://littlesis.org/help

You can reply to this email with any questions or if you are interested in a training. 

Thank you,
the LittleSis team
