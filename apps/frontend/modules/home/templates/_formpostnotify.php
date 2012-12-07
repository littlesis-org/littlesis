<?php use_helper('LsText') ?>
Name: <?php echo $user->Profile->getFullname() ?>

Email: <?php echo $user->username ?>

Banned: <?php echo $banned ? 'yes' : 'no' ?>


Recent form posts:
<?php foreach ($posts as $post) : ?>
  <?php echo $post->created_at ?> :: <?php echo $post->module ?>/<?php echo $post->action ?><?php echo $post->params ? excerpt('?' . $post->params) : '' ?> 


<?php endforeach; ?>