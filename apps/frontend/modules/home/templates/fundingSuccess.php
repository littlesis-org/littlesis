<?php slot('header_text', 'Funding') ?>

<?php slot('rightcol') ?>
<?php include_component('home', 'stats')?>
<?php end_slot() ?>

<div style="float: left; padding-right: 30px;">
<?php echo link_to(image_tag('system/sunlight_500.gif', 'border=0 width=200'), 'http://sunlightfoundation.com') ?>
</div>

<div class="text_big" style="padding-top: 15px;">
LittleSis is generously funded by the <?php echo link_to('Sunlight Foundation', 'http://sunlightfoundation.com') ?>, a 501(c)(3) organization that supports, develops and deploys new Internet technologies to make information about Congress and the federal government more accessible to the American people. We owe a special thanks to <?php echo link_to('Sunlight Labs', 'http://sunlightlabs.com') ?> for keeping LittleSis safe.
</div>

<br />
<br />


<?php include_partial('home/supportourwork') ?>
