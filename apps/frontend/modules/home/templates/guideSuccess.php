<?php slot('header_text', 'Site Guide') ?>

<?php slot('rightcol') ?>
<?php include_component('home', 'stats')?>
<?php end_slot() ?>

<?php slot('pointer') ?>
This guide is still in progress. We hope to fully document the site's features very soon. 
If you can't find what you're looking for here or in the <strong><?php echo link_to('FAQ', '@faq') ?></strong>, 
feel free to <strong><?php echo link_to('contact us', '@contact') ?></strong> with a specific question.
<?php end_slot() ?>

<?php include_partial('global/pointer', array('text' => get_slot('pointer'))) ?>


<span class="text_big">
<a href="#permissions">Permissions</a><br />
<a href="#adding">Adding Content</a><br />
<a href="#references">References</a><br />
<a href="#relationships">Relationships</a><br />
<a href="#network">Network & Analysis</a><br />
<a href="#dates">Entering Dates</a><br />
<br />
<br />
</span>

<a name="permissions"></a>
<h2>Permissions</h2>

<span class="text_big">
All data on LittleSis is publicly viewable, but only registered users with the following permissions can make changes to data on the site:

<ul>
  <li>
    <strong>contributor:</strong> can create new people and organizations; add relationships between existing entities; upload images; add addresses and other contact info; add tags to profiles and relationships; discuss profiles with other users; and so on
  </li>
  <li>
    <strong>editor:</strong> can edit exiting profiles, lists, relationships, contact info, and references
  </li>
  <li>
    <strong>lister:</strong> can create new lists
  </li>
  <li>
    <strong>deleter:</strong> can remove profiles, lists, relationships, tags, etc
  </li>
  <li>
    <strong>merger:</strong> can merge duplicate profiles
  </li>
  <li>
    <strong>admin:</strong> the LittleSis staff have a few other useful powers, like blocking malicious users and and granting new permissions to analysts
  </li>
</ul>

Registered users can visit their <?php echo link_to('account page', '@account') ?> to view what permissions they have.
<br />
<br />
If you don't have any of the above permissions and would like to contribute, you can <?php echo link_to('contact us', '@contact') ?> to request them. Please include details about what you do, any expertise you may have, and what kind of data you'd like to contribute.
<br />
<br />
</span>

<a name="adding"></a>
<h2>Adding Content</h2>

<span class="text_big">
We strongly encourage users to add to the database, fill in gaps, and correct errors. LittleSis has thousands of profiles and relationships extracted automatically from public records on the internet, but those data are just a skeleton for a more ambitious map of influence that we can collaboratively research and detail.
<br />
<br />
Any time you want to add or fix data, we prefer that you login and make the changes yourself. We're busy enough as it is maintaining the database and cleaning up incoming data from our bots. If you don't want to edit directly, you can <?php echo link_to('contact us', '@contact') ?> and we'll review it as soon as we get a chance. Corrections take precedence over new data.
<br />
<br />
There are always important names missing from LittleSis; you can add them <?php echo link_to('here', 'entity/addPerson') ?> and <?php echo link_to('here', 'entity/addOrg') ?>. We try to keep profiles limited to people and groups with significant access, influence, and wealth. Use common sense. If uncertain, a good rule of thumb is to only add profiles that are linked to a person or organization already in the database.
<br />
<br />
LittleSis allows users to create <?php echo link_to('lists', '@lists') ?> of people and organizations that belong on a page together but aren't part of a formal organization, like <strong><?php echo link_to('Fortune 1000', 'list/view?id=1') ?></strong> or <strong><?php echo link_to('Clinton Administration', 'list/view?id=4') ?></strong>.
<br />
<br />
Relationships are the most important elements on a profile page. While we do gather summaries and a limited amount of vital stats about people and organizations, our emphasis is on documenting the links between them. To add a relationship between two entities, make sure that both are in the database, then click on the "add" link in the Relationships section of one of their profiles. This will give you a form to find and select the other one.
<br />
<br />
</span>


<a name="references"></a>
<h2>References</h2>

<span class="text_big">
LittleSis can be thought of as an interface for browsing collections of links to further information. On all data pages you'll see a References section. "Reference" on LittleSis is synonymous with "source" or "citation". It's a bibliographic pointer to a resource that documents raw information on the site. Allowed resources will eventually include print publications, audio, or other media, but for now, only web URLs are valid.
<br />
<br />
It's our strict policy to limit information on LittleSis to facts that can be supported by journalistic, academic, or government documents. Inaccurate, firsthand, anecdotal, or editorial content will be removed by the site staff or by other users. Therefore all data entered into LittleSis, by a user or by a bot, must include a reference. 
<br />
<br />
Every edit form will include required fields for entering a reference to where your information is coming from. Sometimes you're editing to clean up typos and remove unsupported data; in these cases you can check the "just cleaning up" box to bypass the required reference. Sometimes you're adding an entity with just a name; include a reference to any basic info about the entity.
<br />
<br />
If you're editing something using information from a reference that's already been added, you can select that reference from a drop-down menu instead of entering it again.
<br />
<br />
Users can also add references directly without editing data. Sometimes you just want to contribute a link to useful information.
<br />
<br />
</span>


<a name="relationships"></a>
<h2>Relationships</h2>

<span class="text_big">
There are ten categories of relationships in LittleSis. They help us organize relationships on profile pages and create more advanced views, such as "Schools attended by major donors to this candidate".

<ul>
  <li>
    <strong>Position:</strong> when a person has a place in an organizational heirarchy, usually carrying a title. Positions can be paid or unpaid. For example: CEO, Director, Manager, Chief Counsel, Assistant Professor, etc. When a person has multiple concurrent positions ("Chairman and CEO") in the same organization, they should be entered separately. 
  </li>
  <li>
    <strong>Membership:</strong> when a person generically belongs to a membership organization, like a union or an automobile club, or when an organization belongs to a larger coalition or association, like the National Association of Manufacturers or AFL-CIO. Memberships usually don't carry any responsibilities other than paying dues. Sometimes people/groups who pay more dues have a different title, but everyone who pays the same amount usually has the same title. Note: people can have positions (Executive Director, Intern, etc) in membership organizations.
  </li>
  <li>
    <strong>Education:</strong> when a person attends a school or other educational program (as a student).
  </li>
  <li>
    <strong>Ownership:</strong> when a person or organization has full or partial ownership of a business or other organization. For example: sole proprietor, limited partners, corporate shareholders.
  </li>
  <li>
    <strong>Donation/Grant:</strong> a gift transfer of money, goods, or services in one direction. Nothing is required to be given in return. For example: political funding, contributions to charities, government grants, prizes.
  </li>
  <li>
    <strong>Service/Transaction:</strong> an exchange of money, goods, or services, generally of equal value. For example: purchases, consulting, contract work, accounting, trades.
  </li>
  <li>
    <strong>Lobbying:</strong> when a person or organization directly lobbies a government agency or official. (When a lobbying firm is hired by a company to lobby a government agency, there are at least two relationships involved: a service/transaction between the company and firm, and a lobbying relationship between the firm and the agency.)
  </li>
  <li>
    <strong>Family:</strong> when two people are part of the same family. For example: children, spouses, step-siblings, cousins.
  </li>
  <li>
    <strong>Social:</strong> when two people are socially acquainted, for example: friends, rivals, lovers, running partners.
  </li>
  <li>
    <strong>Professional:</strong> when two people have a personal working or business relationship. For example: co-writers, business partners, mentors.
  </li>
</ul>
<br />
</span>


<a name="analysis"></a>
<h2>Network & Analysis</h2>

<span class="text_big">
On profile pages you'll notice a Network tab. For example, <?php echo link_to('James T Hackett\'s Network tab', 'entity/view?id=3048&tab=network') ?> lists the people who have the most positions and memberships in the same groups as Sharer, and <?php echo link_to('Bank of America\'s Network tab', 'entity/view?id=9&tab=network') ?> lists the companies with the most board members and executives in common with Bank of America.
<br />
<br />
Data on the Network tab isn't editable; it's just a simple analysis of the data in LittleSis, automatically generated by the page itself from the underlying relationships. Suppose a new person who sits on three common corporate boards with James T Hackett is added by a user; once those three positions are entered into the database, the new person will automatically show up on James T Hackett's Network tab. The Giving, Funding, and Schools tabs you'll see alongside the Network tab are similarly produced by an automatic analysis of relationship data, and can't be edited by users.
<br />
<br />
These tabs are not meant to offer an authoritative list of a person's or organization's closest friends or affiliates -- relationships are merely counted, not weighted by importance or supported by original research. These lists will often include names that don't have any direct connection to the person or group in question. But they are an easy way to filter complex networks and can save users a lot of manual tallying. Over time we'll work to expand and refine them -- any suggestions are welcome!
<br />
<br />
</span>


<a name="dates"></a>
<h2>Entering Dates</h2>

<span class="text_big">
You will encounter many date fields in LittleSis, mostly start and end dates for entities and relationships. Dates don't have to be specific. To enter the year 1999, for example, use '1999-00-00'. To enter May 1968, use '1968-05-00'.
<br />
<br />
</span>