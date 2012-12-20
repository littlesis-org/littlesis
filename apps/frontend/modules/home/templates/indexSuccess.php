<?php use_helper('Number') ?>


<!-- SPLASH -->

<div id="homepage-splash">

<div id="homepage-lists" style="padding: 0.5em; background-color: rgba(128, 128, 128, 0.3); border-radius: 5px;">
<span style="font-size: 19px;"><span style="color: #000;">
<?php $num = $person_num + $org_num; ?>
<?php $nums = str_split($num); ?>
<?php $counter = '&nbsp;'; ?>
<?php foreach ($nums as $num) : ?>
  <?php $counter .= ('<div class="homepage-counter">' . $num . '</div>'); ?>
<?php endforeach; ?>
<?php echo __("%num%</span> dots connected:</span>", array("%num%" => $counter)) ?>
<div style="margin-top: 10px; margin-bottom: 20px;">
&nbsp; &nbsp;&#10004;&nbsp; <?php echo link_to('Paid-for politicians', $politician_list->getInternalUrl()) ?><br />
&nbsp; &nbsp;&#10004;&nbsp; <?php echo link_to('Corporate fat cats', $fatcat_list->getInternalUrl()) ?><br />
&nbsp; &nbsp;&#10004;&nbsp; <?php echo link_to('Revolving door lobbyists', $lobbyist_list->getInternalUrl()) ?><br />
&nbsp; &nbsp;&#10004;&nbsp; <?php echo link_to('Secretive Super PACs', $pac_list->getInternalUrl()) ?><br />
&nbsp; &nbsp;&#10004;&nbsp; <?php echo link_to('Elite think tanks', $think_tank_list->getInternalUrl()) ?> <!--& <?php echo link_to('philanthropies', $philanthropy_list->getInternalUrl()) ?>-->
</div>
<div id="homepage-search">
  <form action="<?php echo url_for('search/simple') ?>">
  <?php $default_search = __("search for a name") ?>
  <?php $existing = $sf_request->getParameter('q') && ($sf_request->getParameter('action') == 'simple') ?>
  <input type="text" id="simple_search_terms" style="background-image: url('<?php echo image_path("system/search.png"); ?>');" class="<?php echo $existing ? '' : 'search_placeholder' ?>" name="q" 
    value="<?php echo $existing ? $sf_request->getParameter('q') : $default_search ?>" 
    onfocus="if (this.value == '<?php echo $default_search ?>') { this.className = ''; this.value = ''; }" 
    onblur="if (this.value == '') { this.className = 'search_placeholder'; this.value = '<?php echo $default_search ?>'; }" 
    size="25" />
  </form>
</div>
</div>


<div style="margin-left: 0px; width: 560px;">
<div id="homepage-splash-header">
<strong><?php echo __('LittleSis') ?></strong><span style="color: #faa;">*</span> <?php echo __("is a free database of who-knows-who at the heights of business and government") ?>.
</div>
<div style="font-size: 14px; color: #faa;">* <?php echo __("opposite of Big Brother") ?></div>

<div id="homepage-splash-subheader">
<?php echo __("We're a grassroots watchdog network connecting the dots between the world's most powerful people and organizations.") ?>
</div>

<div style="float: left; margin-right: 2em;">
<form action="http://groups.google.com/group/littlesis/boxsubscribe">
<?php $email_field_text = "your email" ?>
<input id="homepage-join-field" type="text" name="email"
  value="<?php echo $existing ? $sf_request->getParameter('q') : $email_field_text ?>" 
  onfocus="if (this.value == '<?php echo $email_field_text ?>') { this.className = ''; this.value = ''; }" 
  onblur="if (this.value == '') { this.className = 'search_placeholder'; this.value = '<?php echo $email_field_text ?>'; }"
  size="25" />
<input id="homepage-join-button" type="submit" name="sub" value="<?php echo __("Join us!") ?>" />
</form>
</div>

<div style="float: left; margin-right: 2em; padding-top: 11px;">
<a href="https://twitter.com/twittlesis" class="twitter-follow-button" data-show-count="false" data-dnt="true">Follow @twittlesis</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
</div>

<div style="padding-top: 11px;">
<div class="fb-like" data-href="https://www.facebook.com/LittleSis.org" data-send="false" data-layout="button_count" data-width="90" data-show-faces="false"></div>
</div>

</div>

</div>



<!-- SECOND PART -->

<div id="homepage-subsplash">

<div id="homepage-subsplash-header">
<?php echo __("A unique resource for investigating cronyism, corruption, and conflicts of interest.") ?>
</div>

<table style="width: 100%; margin-left: 0px;">
  <tr>
    <td style="border: 0px solid #eee; padding-top: 1em; width: 350px;">
      <?php echo image_tag('system/rubin-summers.png', 'width=340 style="border: 0px solid #ccc;"') ?>
    </td>
    <td style="width: 300px;">
      <?php include_component('entity', 'mini', array('id' => $top_left_id, 'border' => '0px solid #fff')) ?>
      <?php include_component('entity', 'mini', array('id' => $bottom_left_id, 'border' => '0px solid #fff')) ?>
    </td>
    <td style="width: 300px; padding-left: 15px;">
      <?php include_component('entity', 'mini', array('id' => $top_right_id, 'border' => '0px solid #fff')) ?>
      <?php include_component('entity', 'mini', array('id' => $bottom_right_id, 'border' => '0px solid #fff')) ?>
    </td>
  </tr>
</table>

<a name="about"></a>
<br />
<span style="font-size: 14px; line-height: 22px;">

<div style="float: right; margin-left: 2em;">
<br />
<?php include_component('home', 'stats', array('filter' => true))?>
</div>

<h3 class="homepage-subheader"><?php echo __('Find out how the "One Percent" consolidates money and power.') ?></h3> 

<?php echo __("We bring transparency to influential social networks by tracking the key relationships of politicians, business leaders, lobbyists, financiers, and their affiliated institutions. We help answer questions such as:") ?><br />

<ul>
	<li><?php echo __("Who do the wealthiest Americans donate their money to?") ?></li>
	<li><?php echo __("Where did White House officials work before they were appointed?") ?></li>
	<li><?php echo __("Which lobbyists are married to politicians, and who do they lobby for?") ?></li>
</ul>

<?php echo __("All of this information is public, but scattered. We bring it together in one place. Our data derives from government filings, news articles, and other reputable sources. Some data sets are updated automatically; the rest is filled in by our user community.") ?> <nobr><strong><?php echo link_to($sf_context->getI18N()->__('More Features') . ' &raquo;', '@features') ?></strong></nobr>

<br />

<h3 class="homepage-subheader"><?php echo __("See past the news headlines and tired debates.") ?></h3> 

<?php echo __("Who are the movers and shakers behind the bailouts, government contracts, and new policies? We’re working around the clock to stock LittleSis with information about bigwigs who make the news, and their connections to those who don’t. For updates and analysis visit our blog") ?>, <nobr><strong><?php echo link_to($sf_context->getI18N()->__('Eyes on the Ties') . ' &raquo;', 'http://blog.littlesis.org') ?></strong></nobr>

<br />

<h3 class="homepage-subheader"><?php echo __("We support journalists, watchdogs, and grassroots activists.") ?></h3> 

<?php echo __("We're bringing together a community of citizens who believe in transparency and accountability where it matters most. We're looking for researchers, programmers, artists and organizers to lend a hand.") ?> <nobr><strong><?php echo link_to($sf_context->getI18N()->__('Get Involved') . ' &raquo;', '@join') ?></strong></nobr>

<br />

<h3 class="homepage-subheader"><?php echo __("LittleSis is made by a nonprofit think-and-do tank.") ?></h3> 

<?php echo __("LittleSis is a project of Public Accountability Initiative, a 501(c)3 organization focused on corporate and government accountability.") ?> 
<?php echo __("We receive financial support from the") ?> <?php echo link_to('Sunlight Foundation', 'http://sunlightfoundation.com') ?>, <?php echo link_to('Harnisch Foundation', 'http://thehf.org') ?>, <?php echo __("and Elbaz Family Foundation, and benefit from free software written by the open source community.") ?> <nobr><strong><?php echo link_to($sf_context->getI18N()->__('Our Team') . ' &raquo;', '@team') ?></strong></nobr>

</span>

<div style="clear: both;">&nbsp;</div>
</div>

<div style="text-align: right; color: #ccc; font-size: 10px; font-style: italic; position: relative; top: 10px; padding-right: 10px;">
<?php echo __("background art by") ?> Mark Lombardi
</div>