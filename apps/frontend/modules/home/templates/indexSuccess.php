<?php use_helper('Number') ?>
<?php $sf_response->addJavascript('http://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js', 'first'); ?>
<?php $sf_response->addJavascript('jquery.tinycarousel.js'); ?>

<!-- SPLASH -->

<div id="homepage-splash">

<div id="homepage-lists">
<span style="font-size: 19px;"><span style="color: #000;">
<?php $num = $person_num + $org_num; ?>
<?php $nums = str_split($num); ?>
<?php $counter = '&nbsp;'; ?>
<?php foreach ($nums as $num) : ?>
  <?php $counter .= ('<div class="homepage-counter">' . $num . '</div>'); ?>
<?php endforeach; ?>
<?php echo $counter; ?>
</span> dots connected:</span>
<div style="margin-top: 10px; margin-bottom: 20px;">
&nbsp; &nbsp;&#10004;&nbsp; <?php echo link_to('Paid-for politicians', $politician_list->getInternalUrl()) ?><br />
&nbsp; &nbsp;&#10004;&nbsp; <?php echo link_to('Corporate fat cats', $fatcat_list->getInternalUrl()) ?><br />
&nbsp; &nbsp;&#10004;&nbsp; <?php echo link_to('Revolving door lobbyists', $lobbyist_list->getInternalUrl()) ?><br />
&nbsp; &nbsp;&#10004;&nbsp; <?php echo link_to('Secretive Super PACs', $pac_list->getInternalUrl()) ?><br />
&nbsp; &nbsp;&#10004;&nbsp; <?php echo link_to('Elite think tanks', $think_tank_list->getInternalUrl()) ?> <!--& <?php echo link_to('philanthropies', $philanthropy_list->getInternalUrl()) ?>-->
</div>
<div id="homepage-search">
  <form action="<?php echo url_for('search/simple') ?>">
  <?php $existing = $sf_request->getParameter('q') && ($sf_request->getParameter('action') == 'simple') ?>
  <input type="text" id="simple_search_terms" style="background-image: url('<?php echo image_path("system/search.png"); ?>');" class="<?php echo $existing ? '' : 'search_placeholder' ?>" name="q" 
    value="<?php echo $existing ? $sf_request->getParameter('q') : 'search for a name' ?>" 
    onfocus="if (this.value == 'search for a name') { this.className = ''; this.value = ''; }" 
    onblur="if (this.value == '') { this.className = 'search_placeholder'; this.value = 'search for a name'; }" 
    size="25" />
  </form>
</div>
</div>


<div style="margin-left: 0px; width: 560px;">
<div id="homepage-splash-header"><strong>LittleSis</strong><span style="color: #faa;">*</span> is a free database of who-knows-who at the heights of business and government.</div>
<div style="font-size: 14px; color: #faa;">* opposite of Big Brother</div>

<div id="homepage-splash-subheader">
We're a grassroots watchdog network connecting the dots between the world's most powerful people and organizations.
</div>

<div style="float: left; margin-right: 2em;">
<form action="http://groups.google.com/group/littlesis/boxsubscribe">
<input id="homepage-join-field" type="text" name="email"
  value="<?php echo $existing ? $sf_request->getParameter('q') : 'your email' ?>" 
  onfocus="if (this.value == 'your email') { this.className = ''; this.value = ''; }" 
  onblur="if (this.value == '') { this.className = 'search_placeholder'; this.value = 'your email'; }"
  size="25" />
<input id="homepage-join-button" type="submit" name="sub" value="Join us!" />
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


<!-- CAROUSEL PART -->

<br />
<br />

<div id="homepage-carousel">
    <a class="buttons prev" href="#"><?php echo image_tag("system/carousel-left-semi.png"); ?></a>
    <div class="viewport">
        <ul class="overview">
        <?php foreach ($carousel_ids as $id) : ?>
          <li><?php include_component('entity', 'carousel', array('id' => $id)) ?></li>
        <?php endforeach; ?>
        </ul>
    </div>
    <a class="buttons next" href="#"><?php echo image_tag("system/carousel-right-semi.png"); ?></a>
</div>


<!-- BOTTOM PART -->

<br />
<br />
<br />

<div id="homepage-subsplash-header">
A unique resource for investigating cronyism, corruption, and conflicts of interest.
</div>

<div id="homepage-subsplash">

<div id="homepage-about">
<a name="about"></a>
<div style="float: right; margin-left: 2em;">
<?php include_component('home', 'stats', array('filter' => true))?>
</div>

<h3 class="homepage-subheader">Find out how the "One Percent" consolidates money and power.</h3> 

We bring transparency to influential social networks by tracking the key relationships of politicians, business leaders, lobbyists, financiers, and their affiliated institutions. We help answer questions such as:<br />

<ul>
	<li>Who do the wealthiest Americans donate their money to?</li>
	<li>Where did White House officials work before they were appointed?</li>
	<li>Which lobbyists are married to politicians, and who do they lobby for?</li>
</ul>

All of this information is public, but scattered. We bring it together in one place. Our data derives from government filings, news articles, and other reputable sources. Some data sets are updated automatically; the rest is filled in by our user community. <nobr><strong><?php echo link_to('More Features &raquo;', '@features') ?></strong></nobr>

<br />

<h3 class="homepage-subheader">See past the news headlines and tired debates.</h3> 

Who are the movers and shakers behind the bailouts, government contracts, and new policies? We’re working around the clock to stock LittleSis with information about bigwigs who make the news, and their connections to those who don’t. For updates and analysis visit our blog, <nobr><strong><?php echo link_to('Eyes on the Ties &raquo;', 'http://blog.littlesis.org') ?></strong></nobr>

<br />

<h3 class="homepage-subheader">We support journalists, watchdogs, and grassroots activists.</h3> 

We're bringing together a community of citizens who believe in transparency and accountability where it matters most. We're looking for researchers, programmers, artists and organizers to lend a hand. <nobr><strong><?php echo link_to('Get Involved &raquo;', '@join') ?></strong></nobr>

<br />

<h3 class="homepage-subheader">LittleSis is made by a nonprofit think-and-do tank.</h3> 

LittleSis is a project of Public Accountability Initiative, a 501(c)3 organization focused on corporate and government accountability. We receive financial support from the <?php echo link_to('Sunlight Foundation', 'http://sunlightfoundation.com') ?>, <?php echo link_to('Harnisch Foundation', 'http://thehf.org') ?>, and Elbaz Family Foundation, and benefit from free software written by the open source community. <nobr><strong><?php echo link_to('Our Team &raquo;', '@team') ?></strong></nobr>

</div>

<div style="clear: both;">&nbsp;</div>
</div>

<div style="text-align: right; color: #ccc; font-size: 10px; font-style: italic; position: relative; top: 10px; padding-right: 10px;">
background art by Mark Lombardi
</div>

<script type="text/javascript">         
    $(document).ready(function(){               
                
        $('#homepage-carousel').tinycarousel({ pager: true, start: 1, interval: false, intervaltime: 5000, rewind: true, animation: true });
        
    });
</script> 