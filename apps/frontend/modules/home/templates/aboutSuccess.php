<?php slot('header_text', 'About LittleSis') ?>

<?php slot('rightcol') ?>
<?php include_component('home', 'stats')?>
<?php end_slot() ?>

<span class="about-text">
<h3 class="about-header">LittleSis is a free database detailing the connections between powerful people and organizations.</h3>

We bring transparency to influential social networks by tracking the key relationships of politicians, business leaders, lobbyists, financiers, and their affiliated institutions. We help answer questions such as:<br />

<ul>
	<li>Who do the wealthiest Americans donate their money to?</li>
	<li>Where did White House officials work before they were appointed?</li>
	<li>Which lobbyists are married to politicians, and who do they lobby for?</li>
</ul>

All of this information is public, but scattered. We bring it together in one place. Our data derives from government filings, news articles, and other reputable sources. Some data sets are updated automatically; the rest is filled in by our user community. <nobr><strong><?php echo link_to('More Features &raquo;', '@features') ?></strong></nobr>

<br />

<h3 class="about-header">LittleSis lets you see past the news headlines and tired debates.</h3> 

Who are the movers and shakers behind the bailouts, government contracts, and new policies? We’re working around the clock to stock LittleSis with information about bigwigs who make the news, and their connections to those who don’t. For updates and analysis visit our blog, <nobr><strong><?php echo link_to('Eyes on the Ties &raquo;', 'http://blog.littlesis.org') ?></strong></nobr>

<br />

<h3 class="about-header">LittleSis is meant to support the work of journalists, watchdogs, and grassroots activists.</h3> 

We're bringing together a community of citizens who believe in transparency and accountability where it matters most. We're looking for researchers, programmers, artists and organizers to lend a hand. <nobr><strong><?php echo link_to('Get Involved &raquo;', '@join') ?></strong></nobr>

<br />

<h3 class="about-header">LittleSis is built and maintained by a nonprofit think-and-do tank.</h3> 

LittleSis is a project of Public Accountability Initiative, a 501(c)3 organization focused on corporate and government accountability. We receive financial support from <?php echo link_to('foundations', 'http://public-accountability.org/about/funding/') ?> and benefit from free software written by the open source community. <nobr><strong><?php echo link_to('Our Team &raquo;', '@team') ?></strong></nobr>

<br />

<h3 class="about-header">This website is a "beta" version launched in January 2009.</h3> There are still plenty of bugs and missing data. We make improvements every day. We plan to develop our blog, <?php echo link_to('Eyes on the Ties', 'http://blog.littlesis.org') ?>, into a source of in-depth investigations into influential names and networks that receive little scrutiny in the media. The  <?php echo link_to('LittleSis API', 'http://api.littlesis.org') ?> lets programmers access and reuse LittleSis data in its raw form. <nobr><strong><?php echo link_to('Press Coverage &raquo;', '@press') ?></strong></nobr>