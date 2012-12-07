<?php slot('header_text', 'Analyst Howto') ?>

<?php slot('rightcol') ?>
<?php include_component('home', 'stats')?>
<?php end_slot() ?>


Below are basic instructions for contributing info on LittleSis.  <br />

<h3>
<strong>Update 8/3/2009:</strong> Go to the <strong><?php echo link_to("video page", '@videos') ?></strong> for all instructional videos.</h3>

<h2>Outline</h2>

<span class="text_big">
<a href="#account">1. Create an Account</a><br />
<a href="#picking">2. Choose a Person or Organization to Profile</a><br />
<a href="#stub">3. Create a Stub Profile</a><br />
<a href="#relationships">4. Add Relationships</a><br />
<a href="#profile">5. Build a Complete Profile</a><br />
<a href="#finishing">6. Finishing Touches</a>
<br />
<br />
</span>


<a name="account"></a>
<h2>1. Create an Account</h2>

<span class="text_big">
<ul class="spaced">
  <li>
    Login to LittleSis, or if you don't have an account, request one at <?php echo link_to('http://littlesis.org/join', '@join') ?>. 
  </li>
  <li>
    Once you're logged in, go to <?php echo link_to('Your Account', '@account') ?>, listed under "Home" on the menu at the top. Your permissions will be listed at the bottom of the page. Make sure you have the "contributor" and "editor" permissions.
    <br />
    <?php echo image_tag('system/howto/howto_permissions.png', 'class=screenshot') ?>
  </li>
</ul>
<br />
</span>


<a name="picking"></a>
<h2>2. Pick Someone to Profile</h2>

<span class="text_big">
<ul class="spaced">
  <li>
    Pick a powerful person who either doesn't have a profile or has a stub profile on LittleSis. Names from the following lists are particularly helpful:
    <br />
    <br />
    
<?php foreach (array(21, 3, 4, 11, 12) as $listId) : ?>
  <?php echo list_link(Doctrine::getTable('LsList')->find($listId)) ?>
  <br />
<?php endforeach; ?>

  </li>
</ul>
<br />
</span>


<a name="stub"></a>
<h2>3. Create a Stub Profile</h2>

<span class="text_big">
<ul class="spaced">
  <li>
    As an example, we'll create a profile for Neel Kashkari, the Treasury Department official picked by the Bush Administration to manage the Troubled Asset Relief Program (AKA the Wall Street bailout fund).
    <br />
    <br />    
    Begin creating a stub profile for Neel by clicking on <?php echo link_to('Add Person', 'entity/addPerson') ?> under "Add Content" on the menu at the top. 
    <br />
    <?php echo image_tag('system/howto/howto_addperson.png', 'class=screenshot') ?>
  </li>
  <li>
    Check any Types that apply to the person. (Selecting Types helps us organize the data, and makes additional fields visible for the profile.) 
  </li>
  <li>
    Fill out Neel's full name and, if you can, a short descriptive phrase or sentence in the "blurb" field. Blurbs are used in search results and other lists to help users identify a person or organization they don't recognize by name.
    <br />
    <?php echo image_tag('system/howto/howto_addpersonform.png', 'class=screenshot') ?>  
  </li>
  <li>
    Click on "Add" at the bottom of the form to create Neel Kashkari's stub profile. It now exists for the world to view and improve, but you'll be redirected to the new profile's Edit page where you can fill out more fields in a longer form. 
  </li>
  <li>
    You'll notice a box at the top of the form asking "Where is this information coming from?". All data added to profiles beyond the minimal stub information require the user to cite original sources. (See the <?php echo link_to('Guide', '@guide#references') ?> for more about this.)
    <br />
    <?php echo image_tag('system/howto/howto_editform.png', 'class=screenshot') ?>
  </li>
  <li>
    At this point, you'll want to open a new tab in your web browser and search for a short biography of Neel Kashkari online. There are often up-to-date news articles overviewing the career of a big name in business or politics...
    <br />
    <?php echo image_tag('system/howto/howto_kashkaritime.png', 'class=screenshot') ?>
    <br />
    <br />
    ...but if not, you might have to go with an official bio from the website of an organization he has a position in.
    <br />
    <br />
    <?php echo image_tag('system/howto/howto_kashkaritreas.png', 'class=screenshot') ?>
  </li>
  <li>
    Copy the URL of the bio you found. Return to your LittleSis browser tab and paste it into the Source URL field, and give it a short and descriptive  display name to save other users from having to click on the URL to find out what it is. If it's a news article, you can give it a date in the "More &raquo;" link below the "Display name" form field.
    <br />
    <?php echo image_tag('system/howto/howto_editformreference.png', 'class=screenshot') ?>
  </li>
  <li>
    Now you can fill out any other basic data about Neel Kashkari (such as his birthdate or net worth, as opposed to relationship data) from the bio you found in the Edit form.
    <br />
    <?php echo image_tag('system/howto/howto_editformsave.png', 'class=screenshot') ?>
  </li>
  <li>
    When you're done, click Save at the bottom of the page and you'll be taken to Neel's stub profile, where you can start adding all the major relationships from his bio.
    <br />
    <?php echo image_tag('system/howto/howto_profilestub.png', 'class=screenshot') ?>
  </li>
</ul>
<br />
</span>


<a name="relationships"></a>
<h2>4. Add Relationships</h2>

<span class="text_big">
<ul class="spaced">
  <li>
    To add a relationship between Neel Kashkari and the Treasury Department, click on the "add relationship" button next to the name at the top of his profile.
  </li>
  <li>
    You'll be given a search box to see if the Treasury is already in LittleSis. Keep in mind that it may have been entered into LittleSis under a number of possible names, like "Department of the Treasury" or "US Treasury Dept". Searching for words common to all of its aliases is best.
    <br />
    <?php echo image_tag('system/howto/howto_addrelationshipsearch.png', 'class=screenshot') ?>
  </li>
  <li>
    If you find it in the search results, click on the "select" link next to its name. If you don't find it, you can create and select it using the form below the search results, which is more or less identical to the form you used to create Neel Kashkari's profile.
  </li>
  <li>
    You'll next be taken to a short form asking you to select the category of relationship to create. Of the ten <?php echo link_to('relationship categories', '@guide#relationships') ?> on LittleSis, only the applicable categories will be shown. In this case, the Family, Social, and Professional categories do not appear, since they only exist between two people.
    <br />
    <?php echo image_tag('system/howto/howto_addrelationshipreference.png', 'class=screenshot') ?>
  </li>
  <li>
    At the bottom of the form you'll see another "Where is this information coming from?" box, where you can either enter a new Source URL for the relationship, or select any source previously supplied for Neel Kashkari or Department of the Treasury. If information about this relationship is coming from the bio you previously found for Kashkari, select that.
  </li>
  <li>
    After submitting the form, the relationship will be created and you'll be taken to a more detailed Edit page where you can enter more details about it, such as the start date, or Neel Kashkari's boss at Treasury.
    <br />
    <?php echo image_tag('system/howto/howto_editrelationship.png', 'class=screenshot') ?>
  </li>
  <li>
    These details are all optional. You can supply them and click Save, or instead click on the page header or on Cancel at the bottom of the page, and you'll be taken to the relationship page.
    <br />
    <?php echo image_tag('system/howto/howto_newrelationship.png', 'class=screenshot') ?>
  </li>
  <li>
    That's it! You've created a relationship between Neel Kashkari and Department of the Treasury, which now shows up on both of their profiles.
  </li>
</ul>
<br />
</span>


<a name="profile"></a>
<h2>5. Build A Complete Profile</h2>

<span class="text_big">
<ul class="spaced">
  <li>
    Now that you've gotten the hang of creating relationships, you can work through Neel Kashkari's bio and add all his positions, memberships, school degrees, family and friends.
  </li>
  <li>
    Bios on corporate, government, and nonprofit web pages are often very limited. You'll probably have to find additional articles and bios online to construct a fuller and more detailed list of the various relationships Neel has had with other powerful people and organizations over his lifetime. 
  </li>
  <li>
    You can either attach these sources directly to Neel's profile (click on "add" next to the References on the right side of the profile page), or add these sources as they come up in the process of creating more relationships.
    <br />
    <?php echo image_tag('system/howto/howto_addreferencelink.png', 'class=screenshot') ?>
  </li>
  <li>
    If there's one web page that you're using as a source for many of Neel's relationships, it's best to add it to his profile first so that you can just select it from the Existing Sources drop-down menu when creating the relationships.
    <br />
    <?php echo image_tag('system/howto/howto_addreference.png', 'class=screenshot') ?>
  </li>
</ul>
<br />
</span>


<a name="finishing"></a>
<h2>6. Finishing Touches</h2>

<span class="text_big">
<ul class="spaced">
  <li>
    Profiles are more fun to look at when they have a profile image. To add one, first open a new browser tab and use an internet <?php echo link_to('image search', 'http://images.google.com/images?q=neel%20kashkari') ?> to find a medium-sized photo to upload. Once you've located an image, you can copy the URL to the image by right-clicking on it in your browser and selecting "Copy Image Location" or a similar option. 
    <br />
    <?php echo image_tag('system/howto/howto_imageurl.png', 'class=screenshot') ?>    
    <br />
    <br />
    Return to your LittleSis tab, click on the big "UPLOAD IMAGE" link on Neel's profile page, and paste the URL you copied into the "Remote URL" form field.
    <br />
    <?php echo image_tag('system/howto/howto_imageupload.png', 'class=screenshot') ?>
    <br />
    <br />
    Alternatively, if the online image you found is in the public domain, or has a <?php echo link_to('Creative Commons license', 'http://creativecommons.org/about/licenses') ?>, you can save it to your computer and upload from there it using the same form -- just make sure to check the "Is free" field.
    <br />
    <br />
    And if you really want to impress, snap your own photo of Neel and post it to LittleSis!
  </li>
</ul>
<br />
</span>