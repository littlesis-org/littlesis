<?php slot('header_text', 'Help &raquo; Advanced &raquo; Add Bulk') ?>




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





<div class="help-toc-page"> Add Bulk </div>
<hr>
<div class="help-toc-article"><a href="#adding-bulk">Adding Relationships in Bulk</a> </div>
<div class="help-toc-article"><a href="#bookmarklet">Adding Relationships with the LittleSis Bookmarklet</a> </div>
<br>
<div class="help-toc-section"> Add Bulk Methods </div>
<div class="help-toc-article"><a href="#bulk-methods-file">Upload from file</a> </div>
<div class="help-toc-article"><a href="#bulk-methods-link">Scrape from reference link</a> </div>
<div class="help-toc-article"><a href="#bulk-methods-text">Add names to text box</a> </div>
<div class="help-toc-article"><a href="#bulk-methods-summary">Parse summary</a> </div>
<div class="help-toc-article"><a href="#bulk-methods-search">Search the database for name matches in entity summaries</a> </div>
<br>
<div class="help-toc-section"> Add Bulk Processing </div>
<div class="help-toc-article"><a href="#bulk-processing-one">One entry at a time time</a> </div>
<div class="help-toc-article"><a href="#bulk-processing-all">All entries at once</a> </div>
<a name="adding-bulk" class="help-anchor">text</a><div class="help-article-header">Adding Relationships in Bulk</div>
<div class="help-article">
<em>This tool is only available to advanced analysts.</em>
<hr>
<ol class="help-num-list">
<li>Search for the person or organization (eg. Columbia Law School) whose relationships you want to add in bulk.
<li>Click their name in the search results.
<li>Click <strong>Add Bulk</strong> in their profile page header.
<li><li><strong><a href="/help/beginner/sources/">Reference the source</a></strong> of the new information you are about to add.
<li>Select a method of adding relationships in bulk and adjust its related options.
<ul>
<li><strong><a href="#bulk-methods-file">Upload from file</a></strong> 
<li><strong><a href="#bulk-methods-link">Scrape from reference link</a></strong>
<li><strong><a href="#bulk-methods-text">Add names to text box</a></strong>
<li><strong><a href="#bulk-methods-summary">Parse summary</a></strong>
<li><strong><a href="#bulk-methods-search">Search the database for name matches in entity summaries</a></strong>
</ul>
</ol>
<div class="help-top"><a href="#top">^back to top</a></div> 
</div>
<a name="bookmarklet" class="help-anchor">text</a><div class="help-article-header">Adding Relationships with the LittleSis Bookmarklet</div>
<div class="help-article">
<em>This tool is only available to advanced analysts.</em>
<br>Online articles and blog posts often contain lots of important information about powerful people and organizations and their relationships.  The LittleSis bookmarklet helps you easily add new people/orgs and relationships to LittleSis as you read about them in the news.  You can also use it just to search for people/orgs you read about to learn more.  
<hr>
<strong>Get the Bookmarklet</strong>
<br>Drag this link to your browser’s bookmarks toolbar (don’t click):
<br><strong><a href="javascript:(function(){var%20jqLoader={go:function(){if(!(window.jQuery&amp;&amp;window.jQuery.fn.jquery=='1.3.2')){var%20s=document.createElement('script');s.setAttribute('src','http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js');s.setAttribute('type','text/javascript');document.getElementsByTagName('head')[0].appendChild(s)}this.ok()},ok:function(){if(typeof(window.jQuery)!=='undefined'&amp;&amp;window.jQuery.fn.jquery=='1.3.2'){this.init()}else{setTimeout((function(){jqLoader.ok()}),100)}},init:function(){$.getScript('http://littlesis.org/js/bookmarklet.js',function(){$('body').addLittleSisToolbar()})}};jqLoader.go()})()"> LittleSis Bookmarklet</a></strong> 
<p><strong> Use the Bookmarklet</strong></p>
<ol class="help-num-list">
<li>Make sure you are logged into LittleSis.
<li>While reading a page with info you want to add to LittleSis, click <strong> LittleSis Bookmarklet</strong> on your bookmarks toolbar.  The bookmarklet will open at the top of the page.
<img src="<?php echo image_path('system/help/bookmarklet.png') ?>" class="help-centered-image">
<li>Enter a name as <strong>Entity 1</strong> and click <strong>Search</strong> to look for the person/org in LittleSis.
<li>Click each name in the drop-down to open its profile in a new tab and verify if it’s a matching profile.
<ul>
<li>If there is a correct match, click <strong>Select</strong> next to it.
<li>If no correct matches exist, click <strong>Create New</strong> and complete the form.
</ul>
<br><img width="60" src="http://s3.amazonaws.com/littlesis/images/system/finger.gif" alt="Finger"/> 
If you just want to add a new person/org to LittleSis--not add a relationship--click Refresh on your browser to exit the bookmarklet and continue reading.</br>  
<li>To add a new relationship, enter another name as <strong>Entity 2</strong> and click <strong>Search</strong>. 
<li>Click each name in the drop-down to open its profile in a new tab and verify if it’s a matching profile.
<ul>
<li>If there is a correct match, click <strong>Select</strong> next to it.
<li>If no correct matches exist, click <strong>Create New</strong> and complete the form.
</ul>
<il>Select the relationship <strong>Category</strong> from the drop-down menu.
<li>Make sure the relationship order is correct as you would read a sentence about the relationship (eg. Steve Cohen has a position at SAC Capital Partners).  Click <strong>Switch</strong> to change if necessary.
<li>Click the links to open any <strong>Similar Relationships</strong> in a new tab and verify if the relationship already exists in LittleSis.  If so, click Refresh to exit the bookmarklet and continue reading.
<li>Complete the relationship description.
<li>The bookmarklet automatically copies the web page you’re reading as the source. 
<li>Click <strong>Submit</strong> to finish.
</ol>
<div class="help-top"><a href="#top">^back to top</a></div> 
</div>
<a name="bulk-methods-file" class="help-anchor">text</a><div class="help-article-header">Add Bulk Methods: File</div>
<div class="help-article"
<em>This tool is only available to advanced analysts.</em>
<br>Ideal for adding a list of names and descriptive information from a website, email, document, etc.  If you have a list of names only, use the <strong><a href="#bulk-methods-text">Text Box method</a></strong>.
<hr>
<ol class="help-num-list">
<li>Get your list of names saved on a <strong><a href="https://www.google.com/#bav=on.2,or.r_cp.r_qf.&fp=fd37ca4320edeeb3&q=spreadsheet+in+CSV-format">spreadsheet in CSV-format</a></strong> arranged like the example on the page.
<li>Click <strong>Choose File</strong> to select your spreadsheet.
<li>Select a process for adding new relationships in bulk:
<ul>
<li><strong><a href="#bulk-processing-one">One entry at a time (recommended)</a></strong> 
<li><strong><a href="#bulk-processing-all">All entries at once</a></strong>
</ul>
</ol>
<div class="help-top"><a href="#top">^back to top</a></div> 
</div>
<a name="bulk-methods-link" class="help-anchor">text</a><div class="help-article-header">Add Bulk Methods: Link</div>
<div class="help-article"
<em>This tool is only available to advanced analysts.</em>
<br>Ideal for adding names (eg. board members, staff) directly from an organization’s website.  If the list is short, use the <strong><a href="#bulk-methods-text">Text Box method</a></strong> to manually copy and paste relevant names rather than rely on a bot.
<hr>
<ol class="help-num-list">
<li>Make sure the source URL you’ve selected/entered will take the bot to the exact page containing all the information you want to add.
<li>Select what kind of entities you want the bot to look for on the page.
<li>Click <strong>Begin</strong> to continue.
<li>Uncheck any names found that you <strong>do not want</strong> to add as relationships.
<li>Enter any additional names that the bot missed in the box, one per line.
<li>Select a process for adding new relationships in bulk:
<ul>
<li><strong><a href="#bulk-processing-one">One entry at a time (recommended)</a></strong> 
<li><strong><a href="#bulk-processing-all">All entries at once</a></strong>
</ul>
</ol>
<div class="help-top"><a href="#top">^back to top</a></div> 
</div>
<a name="bulk-methods-text" class="help-anchor">text</a><div class="help-article-header">Add Bulk Methods: Text Box</div>
<div class="help-article"
<em>This tool is only available to advanced analysts.</em>
<br>Ideal for adding a list of names from a website, email, document, etc.  If you have other descriptive information to include about the people/orgs on your list, use the File method (below).
<hr>
<ol class="help-num-list"> 
<li>Copy and paste your list of names into the text box, one per line.
<li>Select a process for adding new relationships in bulk:
<ul>
<li><strong><a href="#bulk-processing-one">One entry at a time (recommended)</a></strong> 
<li><strong><a href="#bulk-processing-all">All entries at once</a></strong>
</ul>
</ol>
<div class="help-top"><a href="#top">^back to top</a></div> 
</div>
<a name="bulk-methods-summary" class="help-anchor">text</a><div class="help-article-header">Add Bulk Methods: Summary</div>
<div class="help-article"
<em>This tool is only available to advanced analysts.</em>
<br>Ideal for adding names found throughout a person’s LittleSis bio.  If the list is short, it’s often more effective to use the Text Box method (below) to manually copy and paste relevant names.
<hr>
<ol class="help-num-list">
<li>Select what kind of entities you want the bot to look for on the page.
<li>Click <strong>Begin</strong> to continue.
<li>Uncheck any names found that you <strong>do not want</strong> to add as relationships.
<li>Enter any additional names that the bot missed in the box, one per line.
<li>Select a process for adding new relationships in bulk:
<ul>
<li><strong><a href="#bulk-processing-one">One entry at a time (recommended)</a></strong> 
<li><strong><a href="#bulk-processing-all">All entries at once</a></strong>
</ul>
</ol>
<div class="help-top"><a href="#top">^back to top</a></div> 
</div>
<a name="bulk-methods-search" class="help-anchor">text</a><div class="help-article-header">Add Bulk Methods: Search</div>
<div class="help-article"
<em>This tool is only available to advanced analysts.</em>
<br>Ideal for finding people in LittleSis whose bios mention a particular organization and adding relationships between the two entities based on the description in the bio.
<hr>
<ol class="help-num-list">
<li>Select <strong>All entries at once</strong>.
<li>Select <strong>Person</strong> as the Default Entity Type
<li>Enter the relationship <strong>Category</strong> and <strong>Order</strong> that you’re hoping to find.  You’ll only be able to add relationships that fit that description. 
<li>Click <strong>Begin</strong> to continue.
<li>For each name, the <strong>Matches in LittleSis</strong> column shows the people and their bios that mention the organization to which you’re adding relationships in bulk.  No person listed here already has a relationship with that organization on their profile.
<img src="<?php echo image_path('system/help/bulk-methods.png') ?>" class="help-centered-image">
<ul>
<li>To add a new relationship, click the circle next to a name and enter its description in the Relationship Fields based on the description in the bio. 
<li>To skip a name, do nothing. 
</ul>
<li> Click <strong>Submit</strong> to finish.
</ol>
<div class="help-top"><a href="#top">^back to top</a></div> 
</div>
<a name="bulk-processing-one" class="help-anchor">text</a><div class="help-article-header">Add Bulk Processing: One Entry</div>
<div class="help-article">
<em>This tool is only available to advanced analysts.</em>
<br>Used to display and add each name from your list one-by-one.  Ideal for analysts new to the Add Bulk tool or without much time to finish the task.
<hr>
<ol class="help-num-list">
<li>Enter the relationship description that fits most of your names as the default (optional).
<li>Click <strong>Begin</strong> or <strong>Continue</strong> to continue.
<img src="<?php echo image_path('system/help/bulk-processing1.png') ?>" class="help-centered-image">
<li>The first name will come up as <strong>Entity 2</strong> with a drop-down showing existing profiles with a similar name.  
<li>Click each name in the drop-down to open its profile in a new tab and verify if it’s a matching profile.
<ul>
<li>If there is a correct match, click <strong>Select</strong> next to it.
<li>If no correct matches exist, click <strong>Create New</strong> and complete the form.
</ul>
<br><img width="60" src="http://s3.amazonaws.com/littlesis/images/system/finger.gif" alt="Finger"/> 
To skip a name--don’t add them to LittleSis or add a relationship--click <strong>Skip</strong> to continue to the next name</br>
<li>Select or change the relationship Category from the drop-down menu.
<li>Make sure the relationship order is correct as you would read a sentence about the relationship (eg. David A Barrett has a position at Columbia Law School).  Click Switch to change if necessary.
<li>Click the links to open any Similar Relationships in a new tab and verify if the relationship already exists in LittleSis.  If so, click Skip to continue to next name.
<li>Complete the relationship description (the fields will auto-complete with your default description but can be edited).
<li> Click <strong>Submit</strong> to finish.
</ol>
<div class="help-top"><a href="#top">^back to top</a></div> 
</div>
<a name="bulk-processing-all" class="help-anchor">text</a><div class="help-article-header">Add Bulk Processing: All Entries</div>
<div class="help-article">
<em>This tool is only available to advanced analysts.</em>
<br>Used to display and add every name from your list all on one page.  Ideal for analysts with lots of Add Bulk experience who have plenty of time to complete the task.
<hr> 
<ol class="help-num-list"
<li>Enter the relationship description that fits most of your names as the default.  All fields can be edited later <strong>except Category and Order</strong>, which must be the same for all entries. 
<li>Click <strong>Begin</strong> or <strong>Continue</strong> to continue.
<img src="<?php echo image_path('system/help/bulk-processing2.png') ?>" class="help-centered-image">
<li>For each name, the <strong>Matches in LittleSis</strong> column shows existing profiles with a similar name to the person/org you’re adding.  
<li>Click each name to open its profile in a new tab and verify if it’s a matching profile.
<ul>
<li>If there is a correct match, click the circle next to it. (eg. Steven B Epstein) 
<li>If no correct matches exist, click the circle next to <strong>Create new Person/Org</strong> and complete the form.  (eg. Sheila A Abdus-Salaam)
<br><img width="60" src="http://s3.amazonaws.com/littlesis/images/system/finger.gif" alt="Finger"/> 
To skip a name--don’t add them to LittleSis or add a relationship--click the circle under <strong>No Action</strong>.</br>
<li>The relationship fields auto-fill with the defaults you entered earlier--verify their accuracy (and edit if necessary) for each name. 
<li> Click <strong>Submit</strong> to finish.
</ol>
<div class="help-top"><a href="#top">^back to top</a></div> 
</div>


</div>

</td>
</tr>
</table>