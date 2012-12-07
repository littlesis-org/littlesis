<form action="<?php echo url_for($action) ?>">

<?php foreach ($hidden_params as $param) : ?>
  <?php echo input_hidden_tag($param, $sf_request->getParameter($param)) ?>
<?php endforeach; ?>

Look between <?php echo input_tag('start', $sf_request->getParameter('start')) ?> and <?php echo input_tag('end', $sf_request->getParameter('end')) ?> 
<?php echo submit_tag('Go', 'class=button_small') ?>
</form>
