<form action="<?php echo url_for($action) ?>">

Look for users matching <?php echo input_tag('user_search', $sf_request->getParameter('user_search')) ?>&nbsp;
<?php echo submit_tag('Go', 'class=button_small') ?>
</form>
