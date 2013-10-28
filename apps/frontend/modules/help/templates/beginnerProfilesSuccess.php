<?php slot('header_text', 'Help &raquo; Beginner &raquo; Profiles') ?>


<table width="100%" style="margin: auto;">
<tr>
<td></td>
<td style="padding: 3em 0 3em 0">
<?php include_partial("help/helpsearch") ?>
</td>
</tr>
<tr>
<td><?php include_component("help","helpmenu",array("current" => $this->getActionName()))?></td>
<td>


<div class="help-toc-page"> Profiles <hr></div>
<div class="help-right-col">

<div class="help-toc-section"> Get Started </div>
<div class="help-toc-article"> <a href="#navigating-profile"> Navigating the Profile Page </a> </div>
<div class="help-toc-article"> <a href="#adding-profile"> Adding a New Profile </a> </div>
<div class="help-toc-article"> <a href="#completing-profile">Completing a Profile</a></div>
<div class="help-toc-article"> <a href="#profile-pic"> Adding/Editing a Profile Picture </a> </div>
<div class="help-toc-article"> <a href="#editing-profile"> Editing a Profile </a> </div>
<div class="help-toc-article"> <a href="#q-suggestions"> Can you suggest people/orgs for me to add? </a></div>
<div class="help-toc-article"> <a href="#q-search"> How do I find information about a person/organization I want to add?</a></div>
<div class="help-toc-article"> <a href="#q-adding"> How do I know if the person/organization I want to add belongs in LittleSis? </a> </div>
<div class="help-toc-article"> <a href="#q-dates"> How do I correctly enter a date? </a> </div>
<br>
<div class="help-toc-section"> More on Profiles </div>
<div class="help-toc-article"> <a href="#profile-analysis"> Using the Profile Analysis Tabs </a> </div>
<div class="help-toc-article"> <a href="#q-error"> What should I do if I notice an error on a profile? </a> </div>
<div class="help-toc-article"> <a href="#q-missing"> Why can't I find the information I just entered? </a> </div>
<div class="help-toc-article"> <a href="#q-duplicate"> I accidentally added a profile that already exists!  What should I do? </a> </div>
<a name="advanced-profile" class="help-anchor">text</a>
<br>
<br>
<a name="navigating-profile" class="help-anchor">text</a>
<div class="help-article-header">Navigating the Profile Page </div>
<div class="help-article">
To view a profile, search for the person or organization you want to view and click their name in the search results. 
<hr>
<a href="<?php echo image_path('system/help/navigating-profile.png') ?>"><img src="<?php echo image_path('system/help/navigating-profile.png') ?>" width="500" class="help-centered-image"></a>
</div>
<div class="help-top"><a href="#top">^back to top</a></div>


<a name="adding-profile" class="help-anchor">text</a>
<div class="help-article-header">Adding a New Profile</div>
<div class="help-article">
All analysts can create a new profile for any person or organization not already profiled in LittleSis.
<hr>
<ol class="num-list">
<li>Click <strong>Add</strong> at the top of your screen and select <strong>Person</strong> or <strong>Organization</strong> from the drop-down menu.
<li>Search for the person/org you want to add to see if their profile already exists, making sure to consider spelling and possible nicknames.
<a href="<?php echo image_path('system/help/adding-profile.png') ?>"><img src="<?php echo image_path('system/help/adding-profile.png') ?>" class="help-centered-image"></a>
<li>Check all boxes that describe the <strong>Type</strong> of person/org this is.
<li>Enter their common <strong>Name</strong> (you can add other common or legal names soon).
<li>Enter a <strong>Short description</strong> that indicates how they are best known.
<li> Click <strong>Add</strong>.
<li> You’ve created a stub profile! Please continue to:
	<ul>
	<li> <strong><a href="#completing-profile">Complete the profile </a></strong> with additional basic information about the person/org.
<li> <strong><a href="#profile-pic">Add a profile picture </a></strong>.
<li> <strong><a href="/help/beginnerRelationships#adding-rel">Add relationships </a></strong> between this person/org and other entities.
</ul>
</ol></div>
<div class="help-top"><a href="#top">^back to top</a></div>


<a name="completing-profile" class="help-anchor">text</a>
<div class="help-article-header">Completing a Profile
<hr></div>
<div class="help-article">
<strong>Aliases</strong>
<br>Add other common or legal names for the entity to improve search results.
<ol class="num-list">
<li> Click <strong>Add</strong> next to Aliases to create a new name.
<li> Click <strong>Primary</strong> next to their most commonly-used name to display it on their profile. (eg. Bank of America is also known as BoA)
</ol>
<strong>Custom Fields</strong>
<br>Add basic info about the entity not covered in the Basic tab. 
<br><div class="pointer_box"><table><tr><td><img width="60" src="http://s3.amazonaws.com/littlesis/images/system/finger.gif" alt="Finger"/></td>
<td>
Does the information describe a relationship (eg. Bank of America’s CEO)? <strong><a href="/help/beginnerRelationships#adding-rel">Add a new relationship</a></strong> instead of a custom field.</td></tr></table></div>
<ol class="num-list">
<li>Click <strong>Add</strong>.
<li>Enter your source of the additional information.
<li>Enter the new Field Name (eg. employer_id_number) and and its Value, or the info that completes this new field (eg. what is Bank of American’s EIN?)
</ol>
<strong>Industries</strong>
<br>Add the entity’s industry to their profile. 
<ol class="num-list">
<li> Click <strong>Update from OpenSecrets</strong> to search automatically for their relevant industries.  If no results are found, follow the next step.
<li>Click <strong>Add</strong> to see a list of all industries.
<li> Select the relevant one(s) by clicking <strong>Add</strong> again next to its name.
</ol>
<p><strong>Networks</strong>
<br>Check any geographic power networks this entity is in.  Your <strong><a href="/help/account#networks">default network</a></strong> will be automatically checked.</p>
<strong>Basic</strong>
<br>Add vital stats about the entity.  <strong>Always edit this tab last!</strong>  The information you enter won’t be saved when you move to another tab.
<ol class="num-list">
<li> Reference the source of any basic information you are about to add. 
<ul> 
<li>My info comes from a <strong><a href="/help/sources#new-source">new source</a></strong>.
<li>My info comes from an <strong><a href="/help/sources#existing-source">existing source</a></strong>.
</ul> 
<li> Enter the information from your source.
<li> Click <strong>Save</strong> to finish.
</ol></div>
<div class="help-top"><a href="#top">^back to top</a></div>



<a name="profile-pic" class="help-anchor">text</a>
<div class="help-article-header">Adding/Editing a Profile Picture
<hr></div>
<div class="help-article">
<ol class="num-list">
<li>Open a new tab and search for an image of the person or organization.  
<li>Copy the URL of the image by right-clicking on it and selecting <strong>Copy image URL</strong>.
<li>Return to the LittleSis tab and click the picture/placeholder.
	<ul>
<li>If the profile already has a picture, click <strong>Upload</strong> to add a new one.  
</ul>
<li>Paste the image URL in the <strong>Remote URL</strong> box.
<li>Give the image a <strong>Title</strong> that will make it easy for other users to identify.
<li>If the profile already has a picture, check the <strong>Featured</strong> box to use this new one instead.
<li>Click <strong>Upload</strong> to finish.
</ol>
<div class="pointer_box"><table><tr><td><img width="60" src="http://s3.amazonaws.com/littlesis/images/system/finger.gif" alt="Finger"/></td>
<td>You can upload a picture from your computer by clicking <strong>Choose file</strong>, but it needs to be owned by you or have a Creative Commons license.  If so, check the <strong>Is free</strong> box to continue.</td></tr></table></div>
</div>
<br>
<div class="help-top"><a href="#top">^back to top</a></div>


<a name="editing-profile" class="help-anchor">text</a>
<div class="help-article-header">Editing a Profile</div>
<div class="help-article">All analysts can edit the basic information displayed on any person or organization’s profile in LittleSis.
<hr></div>
<div class="help-article">
<ol class="num-list">
<li>Search for the person (eg. Ben Bernanke, Fed chairman) or organization you want to edit.
<li>Click their name to go to their profile.
<li>Click <strong>Edit</strong> in the profile page header.
</ol>
<strong>Aliases</strong>
<br>Add other common or legal names for the entity to improve search results.
<ol class="num-list">
<li> Click <strong>Add</strong> next to Aliases to create a new name.
<li> Click <strong>Primary</strong> next to their most commonly-used name to display it on their profile. (eg. Benjamin Bernanke is commonly known as Ben Bernanke.)
</ol>
<strong>Custom Fields</strong>
<br>Add basic info about the entity not covered in the Basic tab. 
<br><div class="pointer_box"><table><tr><td><img width="60" src="http://s3.amazonaws.com/littlesis/images/system/finger.gif" alt="Finger"/></td>
<td>Does the information describe a relationship (eg. Ben Bernanke’s spouse)? <strong><a href="/help/beginnerRelationships#adding-rel">Add a new relationship</a></strong> instead of a custom field.</td></tr></table></div>
<ol class="num-list">
<li>Click <strong>Add</strong>.
<li>Enter your source of the additional information.
<li>Enter the new Field Name (eg. net_worth) and and its Value, or the info that completes this new field (eg. what is Ben Bernanke’s net worth?)
</ol>
<strong>Industries</strong>
<br>Add the entity’s industry to their profile. 
<ol class="num-list">
<li> Click <strong>Update from OpenSecrets</strong> to search automatically for their relevant industries.  If no results are found, follow the next step.
<li>Click <strong>Add</strong> to see a list of all industries.
<li> Select the relevant one(s) by clicking <strong>Add</strong> again next to its name.
</ol>
<p><strong>Networks</strong>
<br>Check any geographic power networks this entity is in.  Your <strong><a href="/help/account#networks">default network</a></strong> will be automatically checked.</p>
<strong>Basic</strong>
<br>Add vital stats about the entity. <strong>Always edit this tab last!</strong>  The information you enter won’t be saved when you move to another tab.
<ol class="num-list">
<li> Reference the source of any basic information you are about to add.<ul> <li>My info comes from a <strong><a href="/help/sources#new-source">new source</a></strong>.
 <li>My info comes from an <strong><a href="/help/sources#existing-source">existing source</a></strong> </ul> 
<li> Enter the information from your source.
<li> Click <strong>Save</strong> to finish.
</ol></div>
<div class="help-top"><a href="#top">^back to top</a></div>


<a name="q-suggestions" class="help-anchor">text</a>
<div class="help-article-header">Can you suggest people/orgs for me to add?
<hr></div>
<div class="help-article">
LittleSis tracks influence and power with respect to policy-making and the public sphere. There are many powerful people and organizations with stub profiles on LittleSis, meaning they have fewer than one relationship.  Check out the <strong><a href="/help/research_guide/">list of stub profiles</a></strong> to begin <strong><a href="/help/beginnerRelationships#adding-rel">adding new relationships</a></strong> that will make their profiles more complete. 
</div>
<p><div class="help-top"><a href="#top">^back to top</a></div>



<a name="q-search" class="help-anchor">text</a>
<div class="help-article-header">How do I find information about a person/organization I want to add?
<hr></div>
<div class="help-article">
Start with a simple Google search.  The best information is usually found in articles or bios written by others, but sometimes a person’s “official" bio on the websites of organizations in which they have positions can be just as juicy.  Sites like Wikipedia that aggregate information can provide interesting information, but anything you find there must be <strong><a href="/help/sources#q-appropriate">supported with an original source</a></strong>.
<p>For more tips, try our <strong><a href="/help/tutorial">practice tutorial</a></strong> or check out our <strong><a href="/help/research">research guide</a></strong>, which highlights some of our favorite sources and how to use them. </p>
</div>
<div class="help-top"><a href="#top">^back to top</a></div>



<a name="q-adding" class="help-anchor">text</a>
<div class="help-article-header">How do I know if a person/organization that I want to add belongs in LittleSis?
<hr></div>
<div class="help-article">
LittleSis tracks influence and power with respect to policy-making and the public sphere. We try to keep profiles limited to people and organizations with significant access, influence, and wealth.  
<br><div class="pointer_box"><table><tr><td><img width="60" src="http://s3.amazonaws.com/littlesis/images/system/finger.gif" alt="Finger"/></td>
<td>Only add profiles that are linked to a person or organization already in the database.</td></tr></table></div>
<p>The site's core data is almost all relevant to investigating power on a national level, but individuals and organizations who belong to networks of influence at the state or local level may be worth including if they have ties to existing data.  If you wish to tackle the project of mapping the power structures in your local city by using LittleSis, <strong><a href="/contact">contact us</a></strong> about setting up a new group or network.</p>
</div>
<div class="help-top"><a href="#top">^back to top</a></div>



<a name="q-dates" class="help-anchor">text</a>
<div class="help-article-header">How do I correctly enter a date?
<hr></div>
<div class="help-article">
Enter dates in the format <strong>yyyy-mm-dd</strong>.  The year is required, but a specific month or day are optional.
<br><em>Examples</em> - 1999 would be 1999-00-00; May 1968 would be 1968-05-00</div>
<br>
<div class="help-top"><a href="#top">^back to top</a></div>



<a name="profile-analysis" class="help-anchor">text</a>
<div class="help-article-header">Using the Profile Analysis Tabs</div>
<div class="help-article">
These tabs automatically analyze relationship data added by users and display it in ways that may be helpful for researchers, journalists and others. They are an easy way to filter complex networks and can save users a lot of manual tallying, but are not meant to offer an authoritative list of a person's or organization's closest affiliates.  Relationships are merely counted, not weighted by importance or supported by original research. 
<hr>
<em>Organizations</em>
<a href="<?php echo image_path('system/help/analysis-org-interlocks.png') ?>"><img src="<?php echo image_path('system/help/analysis-org-interlocks.png') ?>" class="help-centered-image"></a>
<p>The <strong>Interlocks</strong> tab shows other organizations in which this organization’s (eg. Booz Allen Hamilton) people--leadership and staff--have positions.  The orgs with most common people are shown first.</p>
<a href="<?php echo image_path('system/help/analysis-org-giving.png') ?>"><img src="<?php echo image_path('system/help/analysis-org-giving.png') ?>" class="help-centered-image"></a>
<p>The <strong>Giving</strong> tab shows the politicians and organizations to which this organization’s people have made donations, in order by total amount donated.</p>
<p>The <strong>Political</strong> tab graphs the political donation data of this organization’s people by party, politicians and political orgs supported, as well as showing the biggest individual donors.</p>
<a href="<?php echo image_path('system/help/analysis-org-schools.png') ?>"><img src="<?php echo image_path('system/help/analysis-org-schools.png') ?>" class="help-centered-image"></a>
<p>The <strong>Schools</strong> tab shows the universities and colleges attended by this organization’s people.  The schools with the most common people are shown first.</p>
<p><em>People</em>
<a href="<?php echo image_path('system/help/analysis-person-interlocks.png') ?>"><img src="<?php echo image_path('system/help/analysis-person-interlocks.png') ?>" class="help-centered-image"></a>
<p>The <strong>Interlocks</strong> tab shows the other people that have positions in the same organizations as this person (eg. Sheryl Sandberg, Facebook COO).  The people with the most common orgs are shown first.</p>
<a href="<?php echo image_path('system/help/analysis-person-giving.png') ?>"><img src="<?php echo image_path('system/help/analysis-person-giving.png') ?>" class="help-centered-image"></a>
<p>The <strong>Giving</strong> tab shows the other people that made donations to the same politicians or organizations as this person.  The people with the most common donation recipients are shown first.</p>
<p>The <strong>Political</strong> tab graphs this person’s political donation data by party, politicians and political orgs supported.</p>
</div>
<div class="help-top"><a href="#top">^back to top</a></div>



<a name="q-error" class="help-anchor">text</a>
<div class="help-article-header">What should I do if I notice an error on a profile?
<hr></div>
<div class="help-article">
Use the references to try to find the correct information and make the change(s).  Otherwise you can click the <strong>Flag</strong> button in the profile header and complete the form to notify us of a problem pertaining to that profile.  If the error seems to be systemic—occurring on more than one profile— please <strong><a href="/contact">contact us</a></strong>.  
<p>Because we collected most of the core data on the site using <strong><a href="/guide">automated processes</a></strong>, and because the site includes data on nearly 50,000 individuals and organizations—too much for us to verify by hand—there are some errors in the data.  Thanks for helping us sort them out!</p>
</div>
<div class="help-top"><a href="#top">^back to top</a></div>



<a name="q-missing" class="help-anchor">text</a>
<div class="help-article-header">Why can't I find the information I just entered?
<hr></div>
<div class="help-article">
Occasionally the appearance of new data is delayed--sorry about that!  Click <strong>History</strong> in the header of any profile, relationship page or list to see a list of data modifications. Please <strong><a href="/contact">contact us</a></strong> if your data hasn’t appeared after a few minutes.
</div>
<br>
<div class="help-top"><a href="#top">^back to top</a></div>



<a name="q-duplicate" class="help-anchor">text</a>
<div class="help-article-header">I accidentally added a profile that already exists!  What should I do?
<hr></div>
<div class="help-article">
Click the <strong>Flag</strong> button in the header of the duplicate profile and complete the form to notify us of the problem.  We will make the correction as soon as possible.  
<p>Advanced analysts can <strong><a href="/help/advancedProfiles#removing-profile">delete</a></strong> or <strong><a href="/help/advancedProfiles#merging-profile">merge</a></strong> the duplicate profiles.  To check your level, click your username and click <strong>Settings</strong> on your dashboard header. </p>
</div>
<div class="help-top"><a href="#top">^back to top</a></div>



</div>

</td>
</tr>
</table>