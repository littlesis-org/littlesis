<?php slot('header_text', 'Help &raquo; Beginner') ?>


<table width="100%" style="margin: auto">
<tr>
<td></td>
<td style="padding: 3em 0 3em 0">
<?php include_partial("help/helpsearch") ?>

</td>
</tr>
<tr>
<td><?php include_component("help","helpmenu",array("current" => $this->getActionName()))?></td>
<td>

<table>
<tr>
<td width="50%">
<div class="help_main_section">
<div class="help_main_header"><?php echo link_to("Profiles","help/beginnerProfiles") ?></div>
<ul class="help_section_links">
<li><?php echo link_to("Adding a New Person or Organization","help/beginnerProfiles#adding-profile") ?>
<li><?php echo link_to("Editing a Profile Page","help/beginnerProfiles#editing-profile") ?>
<li><?php echo link_to("Adding/Editing a Profile Picture","help/beginnerProfiles#profile-pic") ?>
<li><?php echo link_to("Using the Analysis Tabs","help/beginnerProfiles#profile-analysis") ?>

<br><li><?php echo link_to("see all...","help/beginnerProfiles") ?>
</ul>
</div>
</td>
<td>
<div class="help_main_section">
<div class="help_main_header"><?php echo link_to("Relationships","help/beginnerRelationships") ?></div>
<ul class="help_section_links">
<li><?php echo link_to("Understanding Relationship Categories","help/beginnerRelationships#categories") ?>
<li><?php echo link_to("Adding a New Relationship","help/beginnerRelationships#adding-rel") ?>
<li><?php echo link_to("Editing a Relationship","help/beginnerRelationships#editing-rel") ?>
<li><?php echo link_to("How do I know if the relationship I want to add belongs in LittleSis?","help/beginnerRelationships#q-adding-rel") ?>

<br><li><?php echo link_to("see all...","help/beginnerRelationships") ?>
</ul>
</div>
</td>
</tr>
<tr>
<td>
<div class="help_main_section">
<div class="help_main_header"><?php echo link_to("Sources","help/sources") ?></div>
<ul class="help_section_links">
<li><?php echo link_to("Referencing Data with a New Source","help/sources#new-source") ?>
<li><?php echo link_to("Referencing Data with an Existing Source","help/sources#existing-source") ?>
<li><?php echo link_to("Adding a New Source Link","help/sources#source-link") ?>
<li><?php echo link_to("Editing a Source","help/sources#editing-source") ?>
<li><?php echo link_to("Appropriate Sources","help/sources#q-appropriate") ?>

<br><li><?php echo link_to("see all...","help/sources") ?>
</ul>
</div>
</td>
<td>
<div class="help_main_section">
<div class="help_main_header"><?php echo link_to("Lists","help/beginnerLists") ?></div>
<ul class="help_section_links">
<li><?php echo link_to("Editing a List","help/beginnerLists#editing-list") ?>
<li><?php echo link_to("Adding a List Member from a List","help/beginnerLists#adding-member") ?>
<li><?php echo link_to("Adding a List Member from a Profile","help/beginnerLists#member-profile") ?>
<li><?php echo link_to("Using the List Analysis Tabs","help/beginnerLists#list-analysis") ?>

<br><li><?php echo link_to("see all...","help/beginnerLists") ?>
</ul>
</div>
</td>
</tr>
<tr>
<td>
<div class="help_main_section">
<div class="help_main_header"><?php echo link_to("Connect","help/connect") ?></div>
<ul class="help_section_links">
<li><?php echo link_to("Joining/Leaving a Research Group","help/connect#joining-group") ?>
<li><?php echo link_to("Writing a Research Group Note","help/connect#group-note") ?>
<li><?php echo link_to("How do I add a new research group?","help/connect#q-group") ?>
<li><?php echo link_to("Writing a Note","help/connect#writing-note") ?>

<br><li><?php echo link_to("see all...","help/connect") ?>
</ul>
</div>
</td>
<td>
<div class="help_main_section">
<div class="help_main_header"><?php echo link_to("Account","help/account") ?></div>
<ul class="help_section_links">
<li><?php echo link_to("Editing My Public Info","help/account#public-info") ?>
<li><?php echo link_to("Managing My Settings","help/account#settings") ?>
<li><?php echo link_to("Understanding Permission Levels","help/account#levels") ?>
<li><?php echo link_to("How do I know my current permission level?","help/account#q-current-level") ?>

<br><li><?php echo link_to("see all...","help/account") ?>
</ul>
</div>
</td>
</tr>
</table>


</td>

</tr>
</table>