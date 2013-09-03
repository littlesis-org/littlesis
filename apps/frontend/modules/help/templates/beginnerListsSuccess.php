<?php slot('header_text', 'Help &raquo; Beginner &raquo; Lists') ?>

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


<div class="help-toc-page">Lists<hr></div>
<div style="padding-right: 10em">
<div class="help-article">Lists are people and organizations that belong on a page together because they share a common thread of power, but aren't part of a formal organization.</div><br>
<div class="help-toc-section">Get Started</div>
<div class="help-toc-article"> <a href="#editing-list"> Editing a List </a> </div>
<div class="help-toc-article"> <a href="#adding-member"> Adding a List Member</a></div>
<div class="help-toc-article"> <a href="#ranking-member">Editing the Rank of a List Member</a> </div>
<div class="help-toc-article"> <a href="#member-profile">Adding/Removing a List Member through their Profile </a> </div>
<div class="help-toc-article"> <a href="#list-analysis"> Using the List Analysis Tabs</a> </div>
<br>
<div id="advanced-lists" class="help-toc-section"> Advanced Tools </div>
<div class="help-toc-article"> <a href="#adding-list">Adding a New List</a> </div>
<div class="help-toc-article"> <a href="#q-adding-list">How do I know if the list I want to add belongs in LittleSis?</a> </div>
<div class="help-toc-article"> <a href="#bulk-member">Adding List Members in Bulk</a> </div>
<div class="help-toc-article"> <a href="#donations-member">Matching a List’s Related Donors</a> </div>
<div class="help-toc-article"> <a href="#removing-list">Removing a List</a> </div>
<br>
<br>
<a name="editing-list" class="help-anchor">text</a>
<div class="help-article-header"> Editing a List</div>
<div class="help-article">
Editing a list allows you to change its name and description and indicate whether it’s ranked.  You can also <strong><a href="#member-list">add</a></strong> and <strong><a href="#removing-member">remove</a></strong> list members and <strong><a href="#ranking-member">change their ranking</a></strong>.
<hr>
<ol class="num-list">
<li>Search for the list you want to edit.
<li>Click its name in the search results.
<li>Click <strong>Edit</strong> in the header of the list page
<li>Reference the source of the new information you are about to add.
<ul>
<li>My info comes from a new source.
<li>My info comes from an existing source.
<li>Only fixing a typo? Check the Just cleaning up box.
</ul>
<li>Make changes and click Save to finish.
</ol>
<div class="help-top"><a href="#top">^back to top</a></div> 
</div>
<a name="adding-member" class="help-anchor">text</a>
<div class="help-article-header">Adding a List Member from the List</div>
<hr>
<div class="help-article">
<ol class="num-list">
<li>Search for the list you want to add a member to.
<li>Click its name in the search results.
<li>Click <strong>Add member</strong> in the header.
<li>Search for the name of the person/org you want to add 
<li>Click <strong>Add</strong> next to their name in the search results.
</ol>
<div class="help-top"><a href="#top">^back to top</a></div> 
</div>
<a name="ranking-member" class="help-anchor">text</a>
<div class="help-article-header"> Editing the Rank of a List Member</div>
<hr>
<div class="help-article">
<ol class="num-list">
<li>Search for the list in which you want to change a member’s ranking.
<li>Click its name in the search results.
<li>The list page displays the names of all list members in ranking order. Use the page navigation buttons to find the member you want to remove.
<li>Click <strong>Edit</strong> next to their rank and change the number.
</ol>
<div class="help-top"><a href="#top">^back to top</a></div> 
</div>
<a name="member-profile" class="help-anchor">text</a>
<div class="help-article-header">Adding/Removing a List Member through their Profile</div>
<hr>
<div class="help-article">
<ol class="num-list">
<li>Search for the person/org (eg. Hillary Clinton) you want to add to a list.
<li>Click their name in the search results.
<img src="<?php echo image_path('system/help/member-profile.png') ?>" class="help-centered-image">
<li>To add a person/org to a list:
<ul>
<li>Click <strong>Add</strong> in the Lists section of their profile.
<li>Search for the list you want. 
<li>Click <strong>Add</strong> next to the list’s name in the search results.
</ul>
<li> To remove a person/org from a list, click <strong>Remove</strong> next to the list you want in the Lists section of their profile.
</ol>
<div class="help-top"><a href="#top">^back to top</a></div> 
</div>
<a name="list-analysis" class="help-anchor">text</a>
<div class="help-article-header"> Using the List Analysis Tabs</div>
<div class="help-article">
These tabs automatically analyze relationship data added by users and display it in ways that may be helpful for researchers, journalists and others. They are an easy way to filter complex networks and can save users a lot of manual tallying, but are not meant to offer an authoritative list of a person's or organization's closest affiliates.  Relationships are merely counted, not weighted by importance or supported by original research.  List analysis tabs are only available for lists of people.
<hr>
<img src="<?php echo image_path('system/help/analysis-list-interlocks.png') ?>" class="help-centered-image">
<p>The <strong>Interlocks</strong> tab shows the companies, government bodies and other organizations where members of the list have positions.  The organizations with the most list members are displayed first.</p>
<img src="<?php echo image_path('system/help/analysis-list-giving.png') ?>" class="help-centered-image">
<p>The <strong>Giving</strong> tab shows the people and organizations that have received donations from members of the list, in order of amount donated. Advanced users can improve this tab by <strong><a href="#donations-member">matching list members with donation data from Open Secrets</a></strong>.</p>  
<img src="<?php echo image_path('system/help/analysis-list-funding.png') ?>" class="help-centered-image">
<p>The <strong>Funding</strong> tab shows the people and organizations that have given money to members of the list, in order of amount donated.</p>
<div class="help-top"><a href="#top">^back to top</a></div> 
</div>






</div>

</td>
</tr>
</table>