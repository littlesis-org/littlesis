<?php slot('header_text', 'Feature Overview') ?>

<?php slot('rightcol') ?>
<?php include_component('home', 'stats')?>
<?php end_slot() ?>

<span class="about-text">
<?php echo __("LittleSis features interlinked profiles of powerful individuals and organizations in the public and private sectors. Profiles detail a wealth of information vital to any investigation of the ways power and money guide the formulation of public policy, from board memberships to campaign contributions, old school ties to government contracts.") ?>

<br />
<br />

<?php printf(__("The site currently offers profiles of <strong>%s people</strong> and <strong>%s organizations</strong> in varying stages of completion. These include, but are not limited to:"), $person_num, $org_num) ?>

<ul>
  <li><strong><?php echo __("Politicians") ?>:</strong> <?php echo __("members of Congress since 1979, governors since 1974, Bush and Obama administration officials.") ?></li>
  <li><strong><?php echo __("Business people") ?>:</strong> <?php echo __("Fortune 1000 executives and directors, members of the Forbes 400.") ?></li>
  <li><strong><?php echo __("Lobbyists") ?></strong> <?php echo __("who have lobbied on behalf of Fortune 1000 companies.") ?></li>
  <li><strong><?php echo __("Government bodies") ?>:</strong> <?php echo __("US House & Senate; agencies ranging from the Department of Defense to the IRS.") ?></li>
  <li><strong><?php echo __("Businesses") ?>:</strong> <?php echo __("Fortune 1000 companies, lobbying firms, top law firms, and other private companies.") ?></li>
  <li><strong><?php echo __("Non-profits") ?></strong> <?php echo __("such as foundations, think tanks, and political organizations.") ?></li>
</ul>

<br />

<a name="relationships"></a>
<h3><?php echo __("Relationships") ?></h3>

LittleSis offers some data about these people and organizations themselves, but it's focus is on the relationships between them. There are currently <strong><?php echo $relationship_num ?> relationships</strong> linking entities profiled in the database. The word "relationship" is broadly defined, and can include: 

<ul>
  <li><strong>Organizational affiliations:</strong> employment, directorships, memberships.</li>
  <li><strong>Donations:</strong> political contributions, grants.</li>
  <li><strong>Social:</strong> family ties, mentorships, friendships.</li>
  <li><strong>Professional:</strong> partnerships, supervisory relationships.</li>
  <li><strong>Services/contracts:</strong> legal representation, government contracts, lobbying services.</li>
</ul>

Our focus on relationships distinguishes LittleSis from other important research websites that emphasize biographical narrative and history of abuses in their profiles of powerful people, making it a more powerful and flexible platform for exploring and analyzing social networks. For example, Exxon Mobil's Wikipedia page can offer a strong narrative account of the company, but isn't well-equipped to track historical information on the executives and directors of the corporation, the boards they sit on, and the politicians they support.

<br />
<br />

Moreover, LittleSis's data model minimizes bias and maximizes its reusability by other projects like <?php echo link_to('mashups', 'http://en.wikipedia.org/wiki/Mashup_(web_application_hybrid)') ?> and <?php echo link_to('network visualizations', 'http://www.howweknowus.com/2009/03/01/graphing-wall-street-with-littlesisorg/') ?>. Moreover, relationships can be easily aggregated to reveal patterns, saving users the manual 
tabulation. For example, LittleSis automatically shows which organizations are linked through 
common personnel, and which people donate to the same set of political candidates. 

<br />
<br />

<a name="wiki"></a>
<h3>Wiki Features</h3>

Our data derives from government filings, news articles, and other reputable sources. Some data sets are updated automatically; the rest is filled in by our user community.

<br />
<br />

Making edits on LittleSis is more like adding friends on Facebook than modifying a Wikipedia 
page. The editing process mostly consists of adding relationships between people and groups. Users don't have to be great writers or learn a special formatting language in order to contribute quality 
information to LittleSis. 

<br />
<br />

We have made accuracy a major priority for LittleSis; LittleSis implements stricter editing controls than typical wikis. Editors &ndash; called "analysts" on LittleSis &ndash; must provide a reference link for every update they make, and links must point to authoritative sources. This requirement not only ensures credible data, but also provides researchers with valuable links to further information. 

<br />
<br />

Unlike Wikipedia, editors have to sign in to make edits. Registered users can choose to keep their real name hidden, but are still accountable to the community: all modifications to data are logged, so users that make inaccurate or malicious edits can lose editing privileges. Currently, LittleSis staff members enforce these standards, though the user community will eventually be self-policing.

<br />
<br />

<a name="notes"></a>
<h3>Analyst Notes</h3>

The LittleSis developers are admittedly latecomers to <?php echo link_to('Twitter', 'http://twitter.com/twittlesis') ?>. Microblogging makes nuanced argument difficult, but is quite effective for documenting simple <?php echo link_to('facts', 'http://twitter.com/WSJWashington/statuses/1177813849') ?> and leads. What better model to mimic, then, for LittleSis’s analyst note system?

<br />
<br />

We’ve decided to modify Twitter’s format to make it more flexible for LittleSis analysts, thus feeding many birds with one worm:

<ol>
  <li>Notes let analysts keep memos — public or private — that make their own research easier and more complete. Notes are more useful when concise, but aren’t limited to Twitter’s 140 characters.</li>
  <li>Notes let analysts “alert” other analysts using Twitter’s <strong>@username</strong> markup. Multiple analysts can be “alerted” within one note. A private note can only be viewed by its author and any analysts it alerts</li>
  <li>Notes can link to any combination of entity, relationship, and list pages using a simple markup. For example, <strong>@entity:1</strong> will create a link to <?php echo entity_link($walmart) ?>, whereas <strong>@entity:1[biggest company in the world]</strong> will create a link to the <strong><?php echo entity_link($walmart, null, null, 'biggest company in the world') ?></strong>. <strong>@rel</strong> and <strong>@list</strong> work the same way.</li>
  <li>While notes are designed to the above needs, all of which LittleSis analysts have asked for, we encourage you to experiment with them and find new uses we haven’t thought of.</li>
</ol>

The note system is intended to strengthen the social layer on LittleSis, which is essential to keeping our data fresh, accurate, and relevant. Notes are still a work in progress, so let us know what you think!

<br />
<br />

<a name="data"></a>
<h3>Data Sources</h3>

Much of our data derives from government records and other free services that collect them. 

<ul>
  <li>Data about members of Congress come from the <?php echo link_to('Congressional Biographical Directory', 'http://bioguide.congress.gov') ?>, <?php echo link_to('GovTrack.us', 'http://govtrack.us') ?>, and <?php echo link_to('Project Vote Smart', 'http://votesmart.org') ?>.</li>
  <li>Political contribution information is from the <?php echo link_to('Federal Elections Commission', 'http://fec.gov') ?>.</li>
  <li>Corporate board and executive information comes from the Form 4 and 10-K filings of the <?php echo link_to('Securities and Exchange Commission', 'http://sec.gov') ?>.</li>
  <li>Lobbying data is from the US Senate's <?php echo link_to('Lobbying Disclosure Act database', 'http://www.senate.gov/legislative/Public_Disclosure/LDA_reports.htm') ?>.</li>
  <li>Government contract data from the <?php echo link_to('Federal Procurement Data System', 'https://www.fpds.gov') ?>, by way of OMBWatch's <?php echo link_to('FedSpending.org', 'http://fedspending.org') ?>.</li>
</ul>
</span>