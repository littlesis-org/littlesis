<?php echo $note_author ?> has written you a <?php echo $note_is_private ? 'private' : 'public' ?> note:

"<?php echo $note_body ?>"


You can reply to <?php echo $note_author ?> using this link (you will need to log in first):
<a href="http://littlesis.org/home/notes?compose=1&user_id=<?php echo $note_author_id ?>">http://littlesis.org/home/notes?compose=1&user_id=<?php echo $note_author_id ?></a>

To stop receiving these notifications, edit your account settings here:
<a href="http://littlesis.org/home/settings">http://littlesis.org/home/settings</a>