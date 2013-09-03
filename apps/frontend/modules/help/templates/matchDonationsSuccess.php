<?php slot('header_text', 'Help &raquo; Advanced &raquo; Match Donations') ?>



<table width="100%" style="margin: auto">
<tr>
<td></td>
<td style="padding: 3em 0 3em 0">
<?php include_partial("help/helpsearch") ?>
</div>
</td>
</tr>
<tr>
<td><?php include_component("help","helpmenu",array("current" => $this->getActionName()))?></td>
<td>








<div class="help-toc-page"> Match Donations</div>
<hr>
<div class="help-toc-article"><a href="#donations-person">Matching a Person with Donor Records from OpenSecrets</a> </div>
<div class="help-toc-article"><a href="#donations-org">Matching an Organization’s Related Donors</a> </div>
<div class="help-toc-article"><a href="#q-donations-button">Why can't I click the Match Donations button on some profiles?
</a> </div>
<div class="help-toc-article"><a href="#q-match-address">What if a record’s employer doesn’t match or is blank, but the address is similar to another matching record?</a> </div>
<div class="help-toc-article"><a href="#q-no-employer">What if the person’s LittleSis profile doesn’t include their current employer?</a> </div>
<div class="help-toc-article"><a href="#q-filter">Is there a way to filter a long list of records to quickly find matches?</a> </div>
<a name="donations-person" class="help-anchor">text</a><div class="help-article-header">Matching a Person with Donation Data from OpenSecrets</div>
<div class="help-article">
<em>This tool is only available to advanced analysts.</em>
<hr>
<ol class="help-num-list">
<li>Search for the person for whom you want to find donations.
<li>Click their name in the search results.
<li>Click <strong>Match Donations</strong> in the their profile page header.
<li>The person’s LittleSis profile summary are on the left hand side and a list of donor records from OpenSecrets with the same name are on the right hand side.
<br><img width="60" src="http://s3.amazonaws.com/littlesis/images/system/finger.gif" alt="Finger"/> 
You can also see which analyst last reviewed this donation data and when.  If the data has been updated in OpenSecrets since then, that date will appear highlighted in pink.</br> 
<li>Check all donor records that list the same employer as the person’s LittleSis profile.
<li>Click <strong>Verify</strong> to finish.
</ol>
<p>Within a few minutes, your matches will update the person’s <strong><a href="/html_links.htm#profile-analysis/">analysis tabs</a></strong> and generate new relationships between the person and their donation recipients, displayed on the profile pages of both entities.</p>
<div class="help-top"><a href="#top">^back to top</a></div> 
</div>
<a name="donations-org" class="help-anchor">text</a><div class="help-article-header">Matching an Organization’s Related Donors</div>
<div class="help-article">
Quickly match the people with positions in an organization to their donor records from OpenSecrets.
<em>This tool is only available to advanced analysts.</em>
<hr>
<ol class="help-num-list">
<li>Search for the organization (eg. Wells Fargo) for which you want to find donations.
<li>Click its name in the search results.
<li>Click <strong>Match related donors</strong> in the Expert Tools section of its profile page.
<li>The name at the top is the first person with a position in the organization you’ll be matching donation data for.
<img src="<?php echo image_path('system/help/donations-org1.png') ?>" width="600" class="help-centered-image">
<li>The person’s LittleSis profile summary are on the left hand side and a list of donor records from OpenSecrets with the same name are on the right hand side.
<br><img width="60" src="http://s3.amazonaws.com/littlesis/images/system/finger.gif" alt="Finger"/> 
You can also see which analyst last reviewed this donation data and when.  If the data has been updated in OpenSecrets since then, that date will appear highlighted in pink.</br> 
<li>Check all donor records that list the same employer as the person’s LittleSis profile.
<li>Scroll to the bottom of the page to see how many more people with positions in the org have possible matches. 
<img src="<?php echo image_path('system/help/donations-org2.png') ?>" class="help-centered-image">
<li>Click Verify and Match Another to continue with other people or click Verify to finish.
<li>Repeat steps 5-8 for each person with a position in the organization.
<p>Within a few minutes, your matches will update the organization’s <strong><a href="/html_links.htm#profile-analysis/">analysis tabs</a></strong> and generate new relationships between these people and their donation recipients, displayed on the profile pages of both entities. </p>
<div class="help-top"><a href="#top">^back to top</a></div> 
</div>
<a name="q-donations-button" class="help-anchor">text</a><div class="help-article-header">Why can't I click the Match Donations button on some profiles?</div>
<div class="help-article">
<hr>
If there are no donor records from OpenSecrets with similar names to the person in LittleSis, you won’t be able to click the Match Donations button.
<br>
<div class="help-top"><a href="#top">^back to top</a></div> 
</div>
<a name="q-match-address" class="help-anchor">text</a><div class="help-article-header">What if a record’s employer doesn’t match or is blank, but the address is similar to another matching record?</div>
<div class="help-article">
<hr>
<img src="<?php echo image_path('system/help/q-match-address1.png') ?>" class="help-centered-image"
<strong>Verify if the record has the same full address as a matching record:</strong>
<ol class="help-num-list">
<li>Find a record listing the same employer as the person’s LittleSis profile.
<li>Click its <strong>Source Link</strong> (on the far right) to open the source in a new tab.  
<li>Look over the source for the donor’s full address.  
<li>Now find a record listing a different employer than the person’s LittleSis profile or no employer and click its <strong>Source Link</strong>.  
<li>Do the full addresses match?  If so, check this record.  
</ol>
<br><img width="60" src="http://s3.amazonaws.com/littlesis/images/system/finger.gif" alt="Finger"/> 
If the source links are broken, don’t check the record as a match and continue to the next record.</br>
<p>One record may also include some employers that match and others that don’t.  In this case, we also need to verify if each donation in the record has the same full address. </p>
<img src="<?php echo image_path('system/help/q-match-address2.png') ?>" class="help-centered-image">
<ol class="help-num-list">
<li>Click the down arrow next to the record check box to view each donation that was merged into one record by OpenSecrets.
<li>Each individual donation in the record will be displayed with its own checkbox.  Check those that list the same employers as the LittleSis profile.  
<li>Click the <strong>Source Link</strong> (on the far right) of one of the matching donations to open the source in a new tab.
<li>Look over the source for the donor’s full address.  
<li>Now find a donation listing a different employer than the person’s LittleSis profile and click its <strong>Source Link</strong>. 
<li>Do the full addresses match?  If so, check this record with a different employer.
<br><img width="60" src="http://s3.amazonaws.com/littlesis/images/system/finger.gif" alt="Finger"/> 
If the source links are broken, don’t check the donation as a match.</br>
<li>Continue checking the source links of other donations with different or blank employers in the record to see if the full addresses match.
</ol>
<br>You can also do a Google search in a new tab to verify if the person profiled in LittleSis has also worked for the other employer(s) listed.  If so,  <strong><a href="/html_links.htm#adding-rel/">add a new relationship</a></strong> when you’re done matching donation data.
<br>
<div class="help-top"><a href="#top">^back to top</a></div> 
</div>
<a name="q-no-employer" class="help-anchor">text</a><div class="help-article-header">What if the person’s LittleSis profile doesn’t include their current employer?</div>
<div class="help-article">
<hr>
You can often do a Google search to update the profile with the person’s current employer, but in some cases the person is retired or self-employed and matching won’t really be possible.  If you’re matching people in a list or organization, click <strong>Skip and Match Another</strong> to move on to the next name.
<br>
<div class="help-top"><a href="#top">^back to top</a></div> 
</div>
<a name="q-filter" class="help-anchor">text</a><div class="help-article-header">Is there a way to filter a long list of records to quickly find matches?</div>
<div class="help-article">
<hr>
The easiest way to do this is using your web browser’s <strong><a href="https://www.google.com/webhp?hl=en&tab=ww#fp=9a975e048e4bd6d6&hl=en&q=ctrl%2Bf">Find function (Ctrl+F)</a></strong> and entering one word from the name of the person’s current employer.
<br>
<div class="help-top"><a href="#top">^back to top</a></div> 
</div>






</div>

</td>
</tr>
</table>
