<?php slot('header_text', 'Help &raquo; Advanced &raquo; Lists') ?>





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




<div class="help-toc-page"> Lists</div>
<hr>
<div class="help-toc-article"><a href="#adding-list">Adding a New List</a> </div>
<div class="help-toc-article"><a href="#q-adding-list">How do I know if the list I want to add belongs in LittleSis?</a> </div>
<div class="help-toc-article"><a href="#bulk-member">Adding List Members in Bulk</a> </div>
<div class="help-toc-article"><a href="#donations-member">Matching a List’s Related Donors</a> </div>
<div class="help-toc-article"><a href="#removing-list">Removing a List</a> </div>
<a name="adding-list" class="help-anchor">text</a><div class="help-article-header">Adding a New List</div>
<div class="help-article">
<em>This tool is only available to advanced analysts.</em>
<hr>
<ol class="help-num-list">
<li>Click <strong>Add</strong> at the top of your screen and select <strong>List</strong> from the drop-down menu.
<li>Search for the list you want to add to see if it already exists, making sure to consider spelling and other possible names.
<li><strong><a href="/help/beginner/sources/">Reference the source</a></strong> of the list you are adding.
<li>Enter the list name.
<li>Optionally add a description to explain the list to other users and indicate whether the list members should be ranked.
<li>Click <strong>Add</strong> to finish.  
</ol>
<div class="help-top"><a href="#top">^back to top</a></div> 
</div>
<a name="q-adding-list" class="help-anchor">text</a><div class="help-article-header">How do I know if the list I want to add belongs in LittleSis?</div>
<hr>
<div class="help-article">
Lists are people and organizations that belong on a page together because they share a common thread of power, but aren't part of a formal organization.  Most new lists include a number of people or organizations who are already profiled in LittleSis.  You might create a list in order to:
<ul>
<li>Bring useful lists compiled by the media or watchdog groups into LittleSis for further analysis.
<li>Highlight potentially important relationships that don’t fit in the relationship categories, like the guest list of a high-powered event or the visitor’ log of a government official.
<li>Organize a research project you’re working on in LittleSis.    
</ul>
Browse existing <strong><a href="/lists">lists</a></strong> to get a better sense of what kinds of lists work in LittleSis.  If you’re working on a research project with other analysts, it might help to create a <strong><a href="/html_links.htm#joining-group/">research group </a></strong> where analysts can share findings and keep track of important lists, people, and organizations.  <strong><a href="/contact">Contact us</a></strong> to create a new research group. 
<div class="help-top"><a href="#top">^back to top</a></div> 
</div>
<a name="bulk-member" class="help-anchor">text</a><div class="help-article-header">Adding List Members in Bulk</div>
<div class="help-article">
<em>This tool is only available to advanced analysts.</em>
<hr>
<ol class="help-num-list">
<li>Search for the list (eg. Buffalo Bills) you want to add members to.
<li>Click its name in the search results.
<li>Click <strong>Add bulk</strong> in the list page header.
<li>Get your list of names saved on a <strong><a href="https://www.google.com/#bav=on.2,or.r_cp.r_qf.&fp=fd37ca4320edeeb3&q=spreadsheet+in+CSV-format">spreadsheet in CSV-format</a></strong> arranged like the example on the page.
<li>Click <strong>Choose File</strong> to select your spreadsheet.
<li>Select a <strong>Default Type</strong> to indicate whether you are adding a list of people or organizations.  You can’t bulk add a list containing both people and orgs.
<li><strong><a href="/help/beginner/sources/">Reference the source</a></strong> of the list members you are adding. 
<li>Click <strong>Begin</strong> to look at the list of names found in your spreadsheet.
<img src="<?php echo image_path('system/help/bulk-member.png') ?>" class="help-centered-image">
<li>For each name, the <strong>Matches in LittleSis</strong> column shows existing profiles with a similar name to the person/org you’re adding.   
<li>Click each name to open its profile in a new tab and verify if it’s a matching profile.
<ul>
<li>If there is a correct match, click the circle next to it. (eg. Ralph Wilson)
<li>If no correct matches exist, click the circle next to <strong>Create new Person/Org</strong> and complete the form. (eg. Stevie Johnson)
</ul>
<br><img width="60" src="http://s3.amazonaws.com/littlesis/images/system/finger.gif" alt="Finger"/> 
To skip a name--don’t add them to LittleSis or add a relationship--click the circle under <strong>No Action</strong>.</br>
<li>Add each person/org’s ranking in the last column if the list is ranked.
<li>Click <strong>Submit</strong> to finish. 
</ol>
<div class="help-top"><a href="#top">^back to top</a></div> 
</div>
<a name="donations-member" class="help-anchor">text</a><div class="help-article-header">Matching a List’s Related Donors
</div>
<div class="help-article">
<em>This tool is only available to advanced analysts.</em>
<hr>
<ol class="help-num-list">
<li>Search for the <strong>list of people</strong> (eg. 100 Most Influential People in US Defense) for which you want to match donation data from OpenSecrets. 
<li>Click its name in the search results.
<li>Click <strong>Match related donors</strong> in the Expert Tools section of the list page.
<li>The name at the top is the first person on the list you’ll be matching donation data for (eg. Dr. James R Schlesinger, Nixon’s Secretary of Defense).
<img src="<?php echo image_path('system/help/donations-member1.png') ?>" class="help-centered-image">
<li>The person’s LittleSis profile summary are on the left hand side and a list of donor records from OpenSecrets with the same name are on the right hand side.
<br><img width="60" src="http://s3.amazonaws.com/littlesis/images/system/finger.gif" alt="Finger"/> 
You can also see which analyst last reviewed this donation data and when.  If the data has been updated in OpenSecrets since then, that date will appear highlighted in pink. </br>
<li>Check all donor records that list the same employer as the person’s LittleSis profile.
<li>Scroll to the bottom of the page to see how many more list members have possible matches. 
<img src="<?php echo image_path('system/help/donations-member2.png') ?>" class="help-centered-image">
<li>Click <strong>Verify and Match Another</strong> to continue with other list members or click Verify to finish.
<li>Repeat steps 5-8 for each list member until complete.
</ol>
Within a few minutes, your matches will update the list’s <strong><a href="#list-analysis">analysis tabs</a></strong> and generate new relationships between these people and their donation recipients, displayed on the profile pages of both entities.  
<div class="help-top"><a href="#top">^back to top</a></div> 
</div>
<a name="removing-list" class="help-anchor">text</a><div class="help-article-header">Removing a List</div>
<div class="help-article">
<em>This tool is only available to advanced analysts.</em>
<hr>
<ol class="help-num-list">
<li>Search for the list you want to remove.
<li>Click its name in the search results.
<li>Click <strong>Remove</strong> in the list page header.
<li>Verify that you want to remove the list in the dialog box that appears.
</ol>
<div class="help-top"><a href="#top">^back to top</a></div> 
</div>


</div>

</td>
</tr>
</table>