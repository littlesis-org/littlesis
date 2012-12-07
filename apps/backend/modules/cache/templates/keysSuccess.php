<?php slot('header_text', 'Cache Keys') ?>

<?php echo link_to('Frontend', 'cache/keys') ?> &nbsp <?php echo link_to('API', 'cache/keys?app=api') ?>

<br />
<br />

<?php if ($using_memcache) : ?>

  <?php if (isset($metakeys)) : ?>
    <strong><?php echo count($metakeys) ?> meta-keys:</strong><br />
    <br />
    
    <?php foreach ($metakeys as $key) : ?>
      <?php echo $key ?><br />
    <?php endforeach ?>
  <?php endif; ?>

  <br />

  <strong><?php echo count($keys) ?> keys:</strong><br />
  <br />

  <?php foreach ($keys as $key) : ?>
    <?php echo $key ?><br />
  <?php endforeach ?>

<?php else : ?>

  <strong>Not using Memcache as backend!</strong>

<?php endif; ?>