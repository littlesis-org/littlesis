<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

<?php if (has_slot('header')) : ?>
  <?php $sf_response->setTitle(get_slot('header') . ' :: ' . $sf_response->getTitle()); ?>
<?php endif; ?>

<?php include_http_metas() ?>
<?php include_metas() ?>

<?php include_title() ?>

<link rel="shortcut icon" href="/favicon.ico" />

</head>
<body style="background-color: #666;">

<div id="container">
  <div>
    <?php include_partial('global/topmenu') ?>
  </div>
  <div id="main">
    <div id="header">
      <span id="header_text">
        <?php if (has_slot('header_link')) : ?>
          <?php echo link_to(get_slot('header_text'), get_slot('header_link')) ?>
        <?php else : ?>
          <?php echo get_slot('header_text') ?>
        <?php endif; ?>
      </span>
      <span id="header_actions">
        <?php if (has_slot('header_actions')) : ?>
        <?php foreach (get_slot('header_actions') as $text => $url) : ?>
          <?php echo link_to($text, $url) ?>
        <?php endforeach; ?>
        <?php endif; ?>
      </span>
    </div>
    <div id="content">
      <?php echo $sf_content ?>
    </div>    
  </div>    
  <div id="footer">LS Beta</div>
</div>

</body>
</html>
