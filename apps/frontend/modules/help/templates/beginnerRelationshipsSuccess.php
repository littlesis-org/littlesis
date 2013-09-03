<?php slot('header_text', 'Help &raquo; Beginner  &raquo; Relationships') ?>

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

<div class="help-toc-page"> Relationships <hr></div>
<div style="padding-right: 10em">
<div class="help-toc-section"> Get Started </div>
<div class="help-toc-article"> <a href="#navigating-rel"> Navigating the Relationship Page</a> </div>
<div class="help-toc-article"> <a href="#categories"> Understanding Relationship Categories</a> </div>
<div class="help-toc-article"> <a href="#adding-rel"> Adding a New Relationship </a> </div>
<div class="help-toc-article"> <a href="#editing-rel"> Editing a Relationship</a> </div>
<div class="help-toc-article"> <a href="#q-adding-rel"> How do I know if the relationship I want to add belongs in LittleSis?</a></div>
<div class="help-toc-article"> <a href="#q-categories"> Why are some categories not available when I’m adding a new relationship?</a></div>
<br>
<div class="help-toc-section"> More on Relationships</div>
<div class="help-toc-article"> <a href="#q-board"> I’m adding a new relationship between a person and an organization where they are a Board member--what’s the correct category? </a> </div> 
<div class="help-toc-article"> <a href="#q-professor"> I’m adding a new relationship between a person and a school where they teach--what’s the correct category?</a> </div> 
<div class="help-toc-article"> <a href="#q-lobbying"> I’m adding a new relationship between a company and the government agency/official that company is lobbying--what’s the correct category?</a> </div> 
<div class="help-toc-article"> <a href="#q-transactions"> I’m adding a new Service/Transactions relationship--what goes in the blanks between the people/organizations? </a> </div> 
<div class="help-toc-article"> <a href="#q-donations"> How do I find out about a person’s donations in order to add them? </a> </div> 
<div class="help-toc-article"> <a href="#q-duplicate-rel"> I accidentally added a relationship that already exists!  What should I do? </a> </div>
<div class="help-toc-article"> <a href="#q-error-rel"> What should I do if I notice an error on a relationship? </a> </div>
<br>
<div id="advanced-rel" class="help-toc-section"> Advanced Tools </div>
<div class="help-toc-article"> <a href="#network"> Network Search </a></div>
<div class="help-toc-article"> <a href="#connections">Find Connections</a> </div>
<div class="help-toc-article"> <a href="#removing-rel"> Removing a Relationships</a> </div>
<br>
<br>
<a name="navigating-rel" class="help-anchor">text</a>
<div class="help-article-header">Navigating the Relationship Page</div>
<div class="help-article">
To view a relationship page, click any <strong>relationship title</strong> found on a person/organization’s profile.  Each title is listed below the other person/organization in the relationship and organized by the <strong><a href="#categories">relationship categories</a></strong>.  When there is more than one relationship between the same entities, a number will appear-- [+1] for example--that you can click to see the other relationships.
<hr>
<img src="<?php echo image_path('system/help/navigating-rel.png') ?>" class="help-centered-image">

<div class="help-top"><a href="#top">^back to top</a></div> 
</div>

<a name="categories" class="help-anchor">text</a>
<div class="help-article-header">Understanding Relationship Categories</div>
<div class="help-article">
There are 10 categories of relationships in LittleSis that help us organize relationships on profile pages and create advanced searches.  Analysts can create relationships between 2 people (P+P), 2 organizations (O+O) or a person and an organization (P+O), but not all categories apply.
<hr>
<ol class="num-list">
<li><strong>Position</strong> (P+O/P+P only)
<br>When a person has a place in an organizational hierarchy, usually carrying a title. Positions can be paid or unpaid. <em>Examples</em> - CEO, Director, Trustee, Chief Counsel, Professor, etc.
<li><strong>Education</strong> (P+O only)
<br>When a person attends a school or educational program as a <strong>student</strong>.
<li><strong>Membership</strong>
<br>When a person is a member of a membership organization, <strong>but doesn’t hold a position</strong>, or when an organization is a member of a larger coalition or association.  <em>Examples</em> - AFL-CIO, NRA, National Association of Manufacturers, etc. 
<li><strong>Donation/Grant</strong>
<br>A gift transfer of money, goods, or services with nothing due in return. <em>Examples</em> - political funding, contributions to charities, government grants, prizes.
<li><strong>Service/Transaction</strong> 
<br>An <strong>exchange</strong> of money, goods, or services of about equal value. <em>Examples</em> - purchases, consulting, contract work, accounting, trades, etc.
<li><strong>Lobbying</strong>
<br>When an organization <strong>directly lobbies</strong> a government agency or official.  <em>Examples</em> - organization that employs lobbyists in-house, lobbying firm hired by an organization
<li><strong>Ownership</strong> (O+O/P+O only)
<br>When a person or organization has full or partial ownership of an organization. <em>Examples</em> - sole proprietor, limited partners, shareholders, etc.
<li><strong>Hierarchy</strong> (O+O only)
<br> When an organization is the parent or child of another organization. <em>Examples</em> - foundation, lobbying arm, political arm, etc.
<li><strong>Family</strong> (P+P only)
<br>When two people are part of the same family. <br><em>Examples</em> - children, spouses, cousins, siblings, etc.
<li><strong>Social</strong> (P+P only)
<br>When two people are socially acquainted. <em>Examples</em> - friends, rivals, lovers, tennis partners, etc.
<li><strong>Professional</strong> (P+P only)
<br>When two people have a direct working or business relationship. <em>Examples</em> - co-writers, business partners, mentors, etc.
</ol>
<div class="help-top"><a href="#top">^back to top</a></div> 
</div>

<a name="adding-rel" class="help-anchor">text</a>
<div class="help-article-header">Adding a New Relationship</div>
<div class="help-article">
All analysts can add relationships between different people and organizations on LittleSis.
<ol class="num-list">
<li>Search for the organization or person (eg. Larry Summers, former Director of the National Economic Council) whose relationship you want to add.
<li>Click their name to go to their profile.
<li>Look over their profile to see if the relationship you want to add already exists.
<li>Click <strong>Add Relationship</strong> in the profile page header.
<li>Search for the person or organization you want to relate this person to, making sure to consider spelling and possible nicknames.
<li>Click <strong>Select</strong> next to their name in the search results.
<li>If the person/org you’re looking for doesn’t come up, fill out the form and click <strong>Create</strong> at the bottom of the page to add them instantly.
<li>Select the <strong><a href="#categories">category</a></strong> that describes this relationship.
<li>Reference the source of the new information you are about to add.
<ul>
<li>My info comes from new source.
<li>My info comes from an existing source.
</ul>
<li>Click Add to continue.
<li>Describe the relationship with as much detail as your source provides.  Keep in mind that relationships are the most important part of LittleSis, so <strong>the more detail the better</strong>.
<li>Click Save to finish.
</ol>
<div class="help-top"><a href="#top">^back to top</a></div> 
</div>

<a name="editing-rel" class="help-anchor">text</a>
<div class="help-article-header">Editing a Relationship</div>
<div class="help-article">
All analysts can edit any relationship in LittleSis to add new information or correct errors.
<hr>
<ol class="num-list">
<li>Find the relationship you want to edit on a person/organization’s profile page.
<img src="<?php echo image_path('system/help/editing-rel.png') ?>" class="help-centered-image">
<li>Click the <strong>relationship title</strong>.
<li>Click <strong>Edit</strong> in the relationship page header.
<li>Reference the source of the new information you are about to add/change.
<ul>
<li>My info comes from a new source.
<li>My info comes from an existing source.
<li>Only fixing a typo? Check the Just cleaning up box.
</ul>
<li>Make changes and click Save to finish.
</ol>
<div class="help-top"><a href="#top">^back to top</a></div> 
</div>

<a name="q-adding-rel" class="help-anchor">text</a>
<div class="help-article-header">How do I know if the relationship I want to add belongs in LittleSis?</div>
<hr>
<div class="help-article">
LittleSis tracks influence and power with respect to policy-making and the public sphere. We try to keep relationships limited to people and organizations with significant access, influence, and wealth.  
<br><img width="60" src="http://s3.amazonaws.com/littlesis/images/system/finger.gif" alt="Finger"/> 
Most relationships are important, but at least add the ones that link people or organizations already in the database.

<div class="help-top"><a href="#top">^back to top</a></div> 
</div>

<a name="q-categories" class="help-anchor">text</a>
<div class="help-article-header">Why are some categories not available when I’m adding a new relationship?</div>
<hr>
<div class="help-article"> 
Analysts can create relationships between 2 people (P+P), 2 organizations (O+O) or a person and an organization (P+O), but not all categories apply.  Only the categories that apply to the type of relationship you’re creating will be available to select.  

<div class="help-top"><a href="#top">^back to top</a></div> 
</div>

<a name="q-board" class="help-anchor">text</a>
<div class="help-article-header">I’m adding a new relationship between a person and an organization where they are a Board member--what’s the correct category? </div>
<hr>
<div class="help-article"> 
The correct category for this relationship is <strong>Position</strong>.  Any relationship where the person has a title in the organizational hierarchy (ie. Board Member) should be categorized as Position.  The same is true for members of advisory boards, councils, committees, etc.
<p>The Membership category is used only to describe the relationship between a person and the organization where they are a member, without a title or significant responsibilities. </p>

<div class="help-top"><a href="#top">^back to top</a></div> 
</div>

<a name="q-professor" class="help-anchor">text</a>
<div class="help-article-header">I’m adding a new relationship between a person and a school where they teach--what’s the correct category?</div>
<hr>
<div class="help-article"> 
The correct category for this relationship is <strong>Position</strong>.  Any relationship where the person has a title in the organizational hierarchy (ie. Professor) should be categorized as Position.
<p>The Education category is used only to describe the relationship between a person and the school they attended as a student</p>

<div class="help-top"><a href="#top">^back to top</a></div> 
</div>

<a name="q-lobbying" class="help-anchor">text</a>
<div class="help-article-header">I’m adding a new relationship between a company and the government agency/official that company is lobbying--what’s the correct category?
</div>
<hr>
<div class="help-article"> 
If the company is lobbying <strong>directly</strong>--meaning the lobbyist is an employee of the company--the correct category is <strong>Lobbying</strong>.  
<p>If the company is lobbying <strong>indirectly</strong> through a firm they’ve hired--meaning the lobbyist is an employee of that firm, not the company--you may need to add up to three different relationships. Once you’ve added each relationship, each relationship page displays all the information.</p>
<img src="<?php echo image_path('system/help/q-lobbying.png') ?>" class="help-centered-image">
<ul>
<li>The relationship between the lobbyist and the firm should be categorized as Position. 
<li>The relationship between the company and the firm should be categorized as Service/Transaction
<li>The relationship between the firm and the government agency/official should be categorized as Lobbying.
</ul>
Check out our <strong><a href="/help/research_guide/">research guide on LDA disclosure filings</a></strong> to learn more about how to research lobbying activity. 

<div class="help-top"><a href="#top">^back to top</a></div> 
</div>

<a name="q-transactions" class="help-anchor">text</a>
<div class="help-article-header">I’m adding a new Service/Transactions relationship--what goes in the blanks between the people/organizations? </div>
<hr>
<div class="help-article"> 
<img src="<?php echo image_path('system/help/q-transactions.png') ?>" class="help-centered-image">
Use those spaces to name or describe the service exchanged--for example, one person/organization is often a client of another--to give users looking at their profiles a better idea of what the relationship is about.  It’s also fine to leave them blank if you don’t have much information about the service exchanged.

<div class="help-top"><a href="#top">^back to top</a></div> 
</div>

<a name="q-donations" class="help-anchor">text</a>
<div class="help-article-header">How do I find out about a person’s donations in order to add them? </div>
<hr>
<div class="help-article"> 
Advanced analysts can easily <strong><a href="/html_links.htm#donations-person/">find and add federal political donations</a></strong> made by people in LittleSis by matching their names to donors in OpenSecrets.  To check your level, click your username and click <strong>Settings</strong> on your dashboard header. 

<div class="help-top"><a href="#top">^back to top</a></div> 
</div>

<a name="q-duplicate-rel" class="help-anchor">text</a>
<div class="help-article-header">I accidentally added a relationship that already exists!  What should I do?</div>
<hr>
<div class="help-article"> 
Click the <strong>Flag</strong> button in the header of the duplicate relationship and complete the form to notify us of the problem.  We will make the correction as soon as possible.

<div class="help-top"><a href="#top">^back to top</a></div> 
</div>

<a name="q-error-rel" class="help-anchor">text</a>
<div class="help-article-header">What should I do if I notice an error on a relationship?</div>
<hr>
<div class="help-article"> 
Use the references to try to find the correct information and make the change(s).  Otherwise you can click the <strong>Flag</strong> button in the relationship header and complete the form to notify us of a problem pertaining to that profile.  If the error seems to be systemic—occurring on more than one profile— please <strong><a href="/contact">contact us</a></strong>.  
<p>Because we collected most of the core data on the site using <strong><a href="/guide/">automated processes</a></strong>, and because the site includes data on nearly 50,000 individuals and organizations—too much for us to verify by hand—there are some errors in the data.  Thanks for helping us sort them out!</p>

<div class="help-top"><a href="#top">^back to top</a></div> 
</div>





</div>

</td>
</tr>
</table>