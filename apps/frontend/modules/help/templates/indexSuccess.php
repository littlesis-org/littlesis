<?php slot('header_text', 'LittleSis Help') ?>


<table width="80%" style="margin: auto">
<tr>

<td colspan="3" style="padding: 2em 0 2em 0; text-align: center">
<?php include_partial("help/helpsearch") ?>
</td>
</tr>
<tr>
<td width="23%">
<div class="help_home_section">
<div class="help_home_header">New to LittleSis?</div>

<ul class="help_home_list">
<li>•&nbsp;&nbsp;<?php echo link_to("start exploring the power elite","list/list") ?>
<li>•&nbsp;&nbsp;<?php echo link_to("sign up as an analyst to edit","home/join#signup") ?>
<li>•&nbsp;&nbsp;<?php echo link_to("learn more about the site","home/about") ?>
<li>•&nbsp;&nbsp;<?php echo link_to("check out recent edits","modification/latest") ?>
</ul>
</td>
<td width="23%">
<div class="help_home_section">
<div class="help_home_header"><?php echo link_to("Beginner Analyst","help/beginner")?></div>
<?php //echo image_tag('help/koch1.png') ?>
<ul class="help_home_list">
<li>•&nbsp;&nbsp;<?php echo link_to("creating and editing profile pages","help/beginnerProfiles")?>
<li>•&nbsp;&nbsp;<?php echo link_to("connect the dots with relationships","help/beginnerRelationships") ?>
<li>•&nbsp;&nbsp;<?php echo link_to("using sources as you go","help/sources") ?>
<li>•&nbsp;&nbsp;<?php echo link_to("and much more!","help/beginner") ?>
</ul>
</td>
<td width="23%">
<div class="help_home_section">

<div class="help_home_header"><?php echo link_to("Advanced Analyst","help/advanced")?></div>
<?php //echo image_tag('help/koch2.png') ?>
<ul class="help_home_list">
<li>•&nbsp;&nbsp;<?php echo link_to("analyze ties with network search","help/advancedRelationships#network") ?>
<li>•&nbsp;&nbsp;<?php echo link_to("add relationships en masse","help/addBulk") ?>
<li>•&nbsp;&nbsp;<?php echo link_to("import political contributions","help/matchDonations") ?> 
<li>•&nbsp;&nbsp;<?php echo link_to("and much more!","help/advanced") ?>
</ul>
</td>
</tr>
</table>