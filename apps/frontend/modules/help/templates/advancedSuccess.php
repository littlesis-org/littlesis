<?php slot('header_text', 'Help &raquo; Advanced') ?>


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
<div class="help_main_header"><?php echo link_to("Profiles","help/advancedProfiles") ?></div>
<ul class="help_section_links">
<li><?php echo link_to("Removing a Profile","help/advancedProfiles#removing-profile") ?>
<li><?php echo link_to("Merging Duplicate Profiles","help/advancedProfiles#merging-profile") ?>

<br><li><?php echo link_to("see all","help/advancedProfiles") ?>
</ul>
</div>
</td>
<td>
<div class="help_main_section">
<div class="help_main_header"><?php echo link_to("Relationships","help/advancedRelationships") ?></div>
<ul class="help_section_links">
<li><?php echo link_to("Network Search","help/advancedRelationships#network") ?>
<li><?php echo link_to("Find Connections","help/advancedRelationships#connections") ?>
<li><?php echo link_to("Removing a Relationship","help/advancedRelationships#removing-rel") ?>


<br><li><?php echo link_to("see all","help/advancedRelationships") ?>
</ul>
</div>
</td>
</tr>
<tr>
<td>
<div class="help_main_section">
<div class="help_main_header"><?php echo link_to("Lists","help/advancedLists") ?></div>
<ul class="help_section_links">
<li><?php echo link_to("Adding a New List","help/advancedLists#adding-list") ?>
<li><?php echo link_to("How do I know if the list I want to add belongs in LittleSis?","help/advancedLists#q-adding-list") ?>
<li><?php echo link_to("Adding List Members in Bulk","help/advancedLists#bulk-member") ?>
<li><?php echo link_to("Matching a List’s Related Donors","help/advancedLists#donations-member") ?>
<li><?php echo link_to("Removing a List","help/advancedLists#removing-list") ?>

<br><li><?php echo link_to("see all...","help/advancedLists") ?>
</ul>
</div>
</td>
<td>

<div class="help_main_section">
<div class="help_main_header"><?php echo link_to("Add Bulk","help/addBulk") ?></div>
<ul class="help_section_links">
<li><?php echo link_to("Adding Relationships in Bulk","help/addBulk#adding-bulk") ?>
<li><?php echo link_to("Add Bulk Methods: Text Box","help/addBulk#bulk-methods-text") ?>
<li><?php echo link_to("Add Bulk Processing: all at once","help/addBulk#bulk-processing-all") ?>
<li><?php echo link_to("Adding Relationships with the LittleSis Bookmarklet","help/addBulk#bookmarklet") ?>

<br><li><?php echo link_to("see all...","help/addBulk") ?></a>
</ul>
</div>

</td>
</tr>
<tr>
<td colspan=2>
<div class="help_main_section">
<div class="help_main_header"><?php echo link_to("Match Donations","help/matchDonations") ?></div>
<ul class="help_section_links">
<li><?php echo link_to("Matching a Person with Donor Records from OpenSecrets","help/matchDonations#donations-person") ?>
<li><?php echo link_to("Matching an Organization’s Related Donors","help/matchDonations#donations-org") ?>
<li><?php echo link_to("Why can't I click the Match Donations button on some profiles?","help/matchDonations#q-donations-button") ?>
<li><?php echo link_to("What if a record’s employer doesn’t match or is blank, but the address is similar to another matching record?","help/matchDonations#q-match-address") ?>
<li><?php echo link_to("What if there are multiple employers listed on a record, but only one matches the data in LittleSis?","help/matchDonations#q-match-address") ?>
<li><?php echo link_to("What if the person’s LittleSis profile doesn’t include their current employer?","help/matchDonations#q-no-employer") ?>
<li><?php echo link_to("Is there a way to filter a long list of records to quickly find matches?","help/matchDonations#q-filter") ?>

<br><li><?php echo link_to("see all...","help/matchDonations") ?>
</ul>
</div>
</td>

</tr>
</table>


</td>

</tr>
</table>
