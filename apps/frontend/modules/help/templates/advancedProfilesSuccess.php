<?php slot('header_text', 'Help &raquo; Advanced &raquo; Profiles') ?>


<table width="100%" style="margin: auto">
<tr>
<td></td>
<td style="padding: 3em 0 3em 0">
<?php include_partial("help/helpsearch") ?>

</td>
</tr>
<tr>
<td ><?php include_component("help","helpmenu",array("current" => $this->getActionName()))?></td>
<td>




<div class="help-toc-page"> Profiles </div>
<hr>
<div class="help-right-col">
<div class="help-toc-article"><a href="#removing-profile"> Removing a Profile</a> </div>
<div class="help-toc-article"><a href="#merging-profile">Merging Duplicate Profiles</a> </div>
<a name="removing-profile" class="help-anchor">text</a><div class="help-article-header">Removing a Profile</div>
<div class="help-article">
<em>This tool is only available to advanced analysts.</em>
<hr>
<ol class="help-num-list">
<li>Search for the person or organization you want to delete.
<li>Click their name in the search results.
<li>Click <strong>Remove</strong> in the profile page header.
<li>Verify that you want to remove this profile in the dialog box that appears.
</ol>
<div class="help-top"><a href="#top">^back to top</a></div>
</div>
<a name="merging-profile" class="help-anchor">text</a><div class="help-article-header">Merging Duplicate Profiles</div>
<div class="help-article">
<em>This tool is only available to advanced analysts.</em>
<hr>
<ol class="help-num-list">
<li>Search for the person or organization you want to merge--this entity will give its data to another entity and then be removed. 
<li>Click their name in the search results.
<li>Scroll to the <strong>Similar Entities</strong> section of the profile page.
<li>Click <strong>Begin merging process</strong>.
<li>The system will show possible merges or you can enter the person or org you want to merge with and click <strong>Search</strong>.  This entity’s name will become the primary alias after the merge.
<br><div class="pointer_box"><table><tr><td><img width="60" src="http://s3.amazonaws.com/littlesis/images/system/finger.gif" alt="Finger"/></td>
<td>Are you 100% sure about merging these two entities?  This complex process is not easy to reverse.</br></td></tr></table></div>
<li>Click <strong>Merge</strong> next to the person or org you want to merge with. 
<li>Verify that you want to merge these profiles in the dialog box that appears.
</ol>
<div class="help-top"><a href="#top">^back to top</a></div>
</div>



</div>

</td>
</tr>
</table>
