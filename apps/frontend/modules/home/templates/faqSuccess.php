<?php slot('header_text', 'Frequently Asked Questions') ?>

<?php slot('rightcol') ?>
<?php include_component('home', 'stats')?>
<?php end_slot() ?>

<?php slot('pointer') ?>
This FAQ is still in progress. We hope to fully document the site's features very soon. If you can't find what you're looking for here or in the <strong><?php echo link_to('guide', '@guide') ?></strong>, feel free to <strong><?php echo link_to('contact us', '@contact') ?></strong> with a specific question.
<?php end_slot() ?>

<?php include_partial('global/pointer', array('text' => get_slot('pointer'))) ?>


<span class="text_big">
<a href="#background">Background</a><br />
<a href="#data">Data</a><br />
<a href="#helping">Helping Out</a>
<br />
<br />
</span>

<a name="background"></a>
<h2>Background</h2>

<span class="text_big">
<strong>What is this thing?</strong>
<br />
<br />
We often describe LittleSis as an involuntary facebook for powerful people, in that the database includes information on the various relationships of politicians, CEOs, and their friends -- what boards they sit on, where they work, who they give money to. All of this information is public record, but it is scattered across a wide range of websites and resources. LittleSis is an attempt to organize it in a way that meaningfully exposes the social networks that wield disproportionate influence over this country's public policy.
<br />
<br />
<strong>Why the name LittleSis?</strong>
<br />
<br />
The site is an answer to Big Brother: citizens surveilling the country's leadership in the interest of transparency, accountability, and the public good. No nefarious tactics, no trillion dollar budgets, just open, collaborative research with the purpose of turning the page on an era of failed leadership, cronyism, and corruption.
<br />
<br />
<strong>Who are you?</strong>
<br />
<br />
LittleSis is a project of <?php echo link_to('Public Accountability Initiative', 'http://public-accountability.org') ?> (PAI), a nonprofit nonpartisan research and educational organization focused on government and corporate accountability. All policies are set by the LittleSis team and subject to approval by the board of PAI. User input into site policy is encouraged, and in the future PAI may take steps to formalize this community input process. 
<br />
<br />
<strong>Is the content on this site free?</strong>
<br />
<br />
The content on LittleSis is published under a <?php echo link_to("Creative Commons Attribution-ShareAlike 3.0 license", "http://creativecommons.org/licenses/by-sa/3.0/us/") ?>, which basically means you can do what you like with it so long as you credit LittleSis and publish your own content under the same license. However, be aware that our data comes from many different sources, and some of those sources may place strict limitations on how you can use their content. Please use good judgement when quoting or reproducing content.
<br />
<br />
<strong>Can I get a dump of the LittleSis database? Do you have an API?</strong>
<br />
<br />
You can read about and register for the LittleSis API at <?php echo link_to("api.littlesis.org", "http://api.littlesis.org") ?>. It exposes all of the non-user data collected on this website. Soon we will begin publishing weekly dumps or our database that will be available for download by registered API users.
<br />
<br />
<strong>Is LittleSis an open-source project?</strong>
<br />
<br />
Yes, you can brows our source code and read installation instructions at <?php echo link_to("code.littlesis.org", "http://code.littlesis.org") ?>. LittleSis is built using a wide range of <?php echo link_to('open-source software', 'http://en.wikipedia.org/wiki/Open-source_software') ?>, including:

<ul>
  <li><?php echo link_to('GNU/Linux', 'http://en.wikipedia.org/wiki/Linux') ?> operating system</li>
  <li><?php echo link_to('Apache', 'http://httpd.apache.org') ?> web server</li>
  <li><?php echo link_to('PHP', 'http://www.php.net') ?> scripting language</li>
  <li><?php echo link_to('MySQL', 'http://www.mysql.com') ?> relational database management system</li>
  <li><?php echo link_to('Symfony', 'http://www.symfony-project.org') ?> web application framework</li>  
</ul>

<br />
</span>


<a name="data"></a>
<h2>Data</h2>

<span class="text_big">
<strong>Why isn't ________ included in your database?</strong>
<br />
<br />
The database is a work in progress, and plenty of influential people haven't yet been included because their names didn't appear in one of our core data sets (listed below) and they haven't yet been added by a user. The LittleSis team adds new individuals and organizations to our database every day, and we encourage you to do the same (as long as the site's criteria for inclusion are met).
<br />
<br />

<strong>How do you decide who & what to include in the database?</strong>
<br />
<br />
LittleSis tracks influence and power with respect to policymaking and the public sphere. In order to build our data set, we drew on lists of individuals and organizations that are recognized as especially influential or powerful on a national level -- members of Congress over the past thirty years, the largest public and private companies, the richest people in the US. We then built out from there: who leads these corporations? who lobbies for them? who do they give money to? And so forth. For a lengthier explanation, see...
<br />
<br />
We will continue to use this list-based approach to add to the data set, and we encourage users to do the same.
<br />
<br />

<strong>Where do you draw the line on influence? Is my mayor influential enough to belong in the database?</strong>
<br />
<br />
LittleSis is not interested in drawing lines, or defining "powerful" or "influential", but new additions to the database must comply with the site's mission and focus and meet various tests for inclusion. Users are also advised to work off of the site's existing data when adding information. An entity that is wholly unconnected to the people and groups currently featured on the site should probably not be added to the database.
<br />
<br />
About your mayor: the site's core data is almost all relevant to investigating power on a national level, but individuals and organizations who belong to networks of influence at the level of the state or large municipality may also be worthy of inclusion. And if you wish to tackle the project of mapping the power structures in your local city by using LittleSis, please do so, but try to make sure that the data you add links up with the rest of the data on the site in a substantial way.
<br />
<br />

<strong>How did you get all of this data?</strong>
<br />
<br />
Much of our initial data set was collected through an automated process called <?php echo link_to('scraping', 'http://en.wikipedia.org/wiki/Web_scraping') ?>, in which applications called <?php echo link_to('robots', 'http://en.wikipedia.org/wiki/Web_robot') ?> pull data from websites. We also mined significant portions of our data from large public databases. We are particularly indebted to <?php echo link_to('Watchdog.net', 'http://watchdog.net') ?>, <?php echo link_to('GovTrack.us', 'http://govtrack.us') ?>, and <?php echo link_to('Project VoteSmart', 'http://votesmart.org') ?> for making large useful data sets available for download -- our data on members of Congress is drawn from data sets made available by these sites.
<br />
<br />
We have gone to great lengths to log our sources (see them under "References" on each page), but here is a quick list of the public sites and data sets where we got most of the information on LittleSis:

<ul>
  <li>
    <strong><?php echo link_to('SEC.gov', 'http://sec.gov') ?></strong>.
    We used the Securities and Exchange Commission's public EDGAR database to compile board and executive information for public companies. The SEC makes many filings available online, but we drew most of our data from <?php echo link_to('Form 4s', 'http://en.wikipedia.org/wiki/Form_4') ?> and <?php echo link_to('proxy statements', 'http://en.wikipedia.org/wiki/Proxy_statement') ?>.
  </li>
  <li>
    <strong><?php echo link_to('Lobbying Disclosure Act database', 'http://www.senate.gov/legislative/Public_Disclosure/database_download.htm') ?></strong>. 
    We mined information on lobbying activity involving large corporations, lobbying firms, and government bodies from this database. For a more comprehensive look at this data, check out the Center for Responsive Politics' <?php echo link_to('lobbying database', 'http://www.opensecrets.org/lobby/') ?>. 
  </li>
  <li>
    <strong><?php echo link_to('FedSpending.gov', 'http://fedspending.gov') ?></strong>. 
    We pulled all our federal contract data on large corporations using the API on these extremely useful site.
  </li>
  <li>
    <strong><?php echo link_to('FEC.gov', 'http://fec.gov/finance/disclosure/disclosure_data_search.shtml') ?></strong>. 
    We used the FEC's data on campaign contributions to generate most of the donation relationships between people and fundraising committees in our database.
  </li>
  <li>
    <strong><?php echo link_to('Watchdog.net', 'http://watchdog.net') ?></strong>.
    In addition to a goldmine of public records, our friends at watchdog.net publish tables for translating the various IDs used by many websites to track members of congress.
  </li>
  <li>
    <strong><?php echo link_to('GovTrack.us', 'http://govtrack.us') ?></strong>.
    We obtained much of our data on current members of congress -- including birth dates, gender, and committee memberships -- from GovTrack's <?php echo link_to('source data', 'http://www.govtrack.us/source.xpd') ?>.
  </li>
  <li>
    <strong>A variety of free business sites</strong>.
    We pulled limited information on corporations from the websites of <?php echo link_to('Fortune', 'http://money.cnn.com/magazines/fortune/') ?>, <?php echo link_to('Forbes', 'http://forbes.com') ?>, and <?php echo link_to('BusinessWeek', 'http://investing.businessweek.com') ?>. 
  </li>
  <li>
    <strong><?php echo link_to('Wikipedia', 'http://wikipedia.org') ?></strong>. 
    Much of our content either comes from Wikipedia or was integrated using Wikipedia to cross-reference.
  </li>    
</ul>

<br />

<strong>Why don't you include _______ on your site?</strong>
<br />
<br />
Chances are that we'd like to, and just haven't been able to get to it yet. Please let us know if you think we should focus on integrating this particular data set into LittleSis. We may also be unable to include certain types of structured information. We built our database to be flexible, but we aren't trying to model the entire world.
<br />
<br />

<strong>How do I know this information is accurate?</strong>
<br />
<br />
You don't! LittleSis helps you easily locate various sources of information about people, organizations, and relationships, but these sources often have small inaccuracies, and can sometimes have huge errors. Edits from our users help clean up the mistakes, but users sometimes enter bad data or references themselves.
<br />
<br />
Our job is to give you a quick look at the big picture and links to help you research the details. Your job is to do the research and check your sources -- and give back by correcting mistakes on LittleSis when you encounter them.
<br />
<br />
Each page includes a list of references, often to public filings. You should review those before placing your full trust in the information you find on LittleSis.
<br />
<br />

<strong>Why is this person's first name "Wal-Mart"?</strong>
<br />
<br />
Oops! Looks like an error in one of our robots. Because we collected most of the core data on the site using automated processes (see above), and because the site includes data on nearly 50,000 individuals and organizations (too much for us to verify by hand), there are some errors in the data. If something is obviously wrong, you can check the references and make the appropriate changes. And please notify us in the case of systematic errors.
<br />
<br />
</span>

<a name="helping"></a>
<h2>Helping Out</h2>

<span class="text_big">
<strong>This is great! How can I help?</strong>
<br />
<br />
There are many ways to help, but the first thing you should do is <?php echo link_to('sign up', '@sf_guard_signin') ?> and start <?php echo link_to('adding content', '@guide#adding') ?>. This is a one-of-a-kind public resource, but it will only succeed to the extent that a community grows up around it and pushes the database to the next level.
<br />
<br />
In addition to contributing, you can:

<ul>
  <li>Make a donation</li>
  <li><?php echo link_to("Contribute code", "http://code.littlesis.org") ?></li>
  <li>Spread the word</li>
  <li>Give us <?php echo link_to('feedback', '@contact') ?></li>
</ul>

<br />

<strong>I contributed information that then disappeared. How do I get it back?</strong>
<br />
<br />
Each page includes a list of modifications to that data, so you can see what happened to it. Similar to Wikipedia, information in our database is never permanently lost. That said, you can't currently revert changes on LittleSis; you have to roll back the changes by hand.
<br />
<br />
</span>