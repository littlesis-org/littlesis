<ul>
<?php foreach ($results as $result) : ?>
  <li onclick="displayMarkup('<?php echo str_replace("'", "\'", $result['markup']) ?>');"><?php echo $result['name'] ?></li>
<?php endforeach; ?>
</ul>