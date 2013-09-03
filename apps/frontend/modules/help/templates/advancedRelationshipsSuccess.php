<?php slot('header_text', 'Help &raquo; Advanced &raquo; Relationships') ?>





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





<div class="help-toc-page"> Relationships</div>
<hr>
<div class="help-toc-article"><a href="#network"> Network Search </a></div>
<div class="help-toc-article"><a href="#connections">Find Connections</a> </div>
<div class="help-toc-article"><a href="#removing-rel"> Removing a Relationship</a> </div>
<a name="network" class="help-anchor">text</a><div class="help-article-header">Network Search</div>
<div class="help-article">
<em>This tool is only available to advanced analysts.</em>
<p>Easily find out who a person/org is indirectly connected to through people/orgs they have in common.</p>
<hr>
<ol class="help-num-list">
<li>Search for person or organization (eg. Larry Summers) whose connections you want to find.
<li>Click their name in the search results.
<li>Click <strong>Search Network</strong> in the Expert Tools section of their profile page.
<img src="<?php echo image_path('system/help/network.png') ?>" class="">
<li>Leave all fields blank and click <strong>Search</strong> to see every person and organization (X) that has a relationship with a person/org (Y) that Larry Summers also has a relationship with. Those indirect links with the most common people/orgs are shown first in the search results. 
<li>You can easily make your search more specific:
<ul>
<li>To show only people indirectly linked to Larry, check <strong>Person</strong> as <strong<Entity X Type</strong>. Check </strong>Organization</strong> to show only orgs or click More and check specific types of people or orgs to show only those types. 
<li>Check <strong>Is current? 1</strong> and <strong>Is current? 2</strong> to show indirect links based on current relationships only.
</ul>
<li>Here are some examples to narrow your search:
<ul>
<li>Find people who are members of the same organizations as Larry by checking
<strong>Membership</strong> as <strong>Relationship 1</strong> and <strong>Relationship 2</strong>.
<li>Find people who went to the same schools as Larry by selecting Education for
<strong>Relationship 1</strong> and <strong>Relationship 2</strong>. 
</ul>
</ol>
<div class="help-top"><a href="#top">^back to top</a></div> 
</div>
<a name="connections" class="help-anchor">text</a><div class="help-article-header">Find Connections</div>
<div class="help-article">
<em>This tool is only available to advanced analysts.</em>
<p>Easily find out if one person/org may be connected to another. </p>
<hr>
<ol class="help-num-list">
<li>Think of two people or organizations you want to connect to each other.
<li>Search for one person/org (eg. Larry Summers) you want to connect.
<li>Click their name in the search results.
<li>Click <strong>Find Connections</strong> in the Expert Tools section of their profile page.
<li>Enter the name of the other person/org (eg. Goldman Sachs) you want to connect and click <strong>Search</strong>.
<li>Click <strong>Select</strong> next to their name in the search results. 
<br><img width="60" src="http://s3.amazonaws.com/littlesis/images/system/finger.gif" alt="Finger"/> 
The connection search only includes positions and memberships, with up to 4 degrees of separation. </br>
<img src="<?php echo image_path('system/help/connections.png') ?>" class="help-centered-image">
<li>The closest connections, with fewest degrees of separation, will be displayed first.  The 
names of people/orgs are in bold and the relationships that connect them are written between in parentheses. 
<li>Use the navigation buttons to view additional connections.
</ol>
This tool indicates whether the two people or organizations in your search <strong>may</strong> have a connection through other people/orgs they have in common, but it doesn’t take into account dates or other factors which indicate a relationship’s strength.  For example, two people who sat on the same board at different times may not know each other whatsoever.
<br>
<div class="help-top"><a href="#top">^back to top</a></div> 
</div>
<a name="removing-rel" class="help-anchor">text</a><div class="help-article-header">Removing a Relationship</div>
<div class="help-article">
<em>This tool is only available to advanced analysts.</em>
<hr>
<ol class="help-num-list">
<li>Search for the person or organization whose relationship you want to delete.
<li>Click their name in the search results.
<li>Click the title of the relationship you want to delete on their profile page. 
<li>Click <strong>Remove</strong> in the relationship page header.
<li>Verify that you want to remove this profile in the dialog box that appears.
</ol>
<div class="help-top"><a href="#top">^back to top</a></div> 
</div>



</div>

</td>
</tr>
</table>