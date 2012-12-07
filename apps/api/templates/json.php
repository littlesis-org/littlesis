<?php ob_start() ?>
<?php echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" ?>
<Response>
  <Meta>
    <ExecutionTime><?php echo LsApi::getResponseTime() ?></ExecutionTime>
  </Meta>
  <Data>
    <?php echo $sf_content ?>
  </Data>
</Response>
<?php $xml = ob_get_contents() ?>
<?php ob_end_clean() ?>
<?php echo Zend_Json::fromXml($xml) ?>