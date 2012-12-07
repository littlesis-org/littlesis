id,name
<?php foreach ($entities as $entity) : ?>
<?php echo $entity['id'] ?>,<?php echo $entity['name'] . "\n" ?>
<?php endforeach; ?>