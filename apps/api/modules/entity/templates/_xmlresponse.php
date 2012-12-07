<Response>
  <Meta>
    <ExecutionTime><?php echo (microtime(true) - $GLOBALS['startTime']) ?></ExecutionTime>
  </Meta>
  <Data>
<?php foreach ($data_parts as $xml) : ?>
    <?php echo $xml ?>
<?php endforeach; ?>
  </Data>
</Response>