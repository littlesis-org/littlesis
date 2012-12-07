<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

<?php if (has_slot('header_text')) : ?>
  <?php $sf_response->setTitle($sf_response->getTitle() . ' &raquo; ' . htmlentities(get_slot('header_text'))); ?>
<?php else : ?>
  <?php $sf_response->setTitle($sf_response->getTitle() . ' &raquo; Profiling the powers that be'); ?>
<?php endif; ?>

<?php include_http_metas() ?>
<?php include_metas() ?>
<?php use_stylesheet('front_layout') ?>

<title><?php echo html_entity_decode($sf_response->getTitle()) ?></title>

<link rel="shortcut icon" href="/favicon.ico" />
</head>

<body>

<noscript>
<div style="position: absolute; background-color: #fff; width: 100%; height: 110%; z-index: 100;">
<p style="text-align: center; margin-top: 200px; font-size: 26px;">
You must enable JavaScript in your browser to use Little Sister!<br />
<br />
<?php echo link_to('Instructions', 'http://www.google.com/support/bin/answer.py?answer=23852') ?>
</p>
</div>
</noscript>


<div id="front_container">
  <div id="front_content">

    <div id="front_leftcol">
      <?php include_slot('leftcol') ?>
    </div>
    
    <div id="front_rightmain">
      <?php echo $sf_content ?>
    </div>  

  </div>
</div>


<?php include_partial('global/analytics') ?>


</body>
</html>
