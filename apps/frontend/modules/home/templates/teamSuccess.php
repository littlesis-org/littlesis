<?php slot('header_text', 'Our Team') ?>

<?php slot('rightcol') ?>
<?php include_component('home', 'stats')?>
<?php end_slot() ?>

<span class="text_big">
<p>LittleSis is a project of <?php echo link_to('Public Accountability Initiative', 'http://public-accountability.org') ?>, a 501(c)3 organization focused on corporate and government accountability. Our mission is to facilitate and produce investigative research that promotes transparent and accountable governance. PAI was founded in early 2008 by a group of activists, public interest lawyers, and academics associated with leading universities and major social change organizations.
</p>
</span>

<br />

<?php include_partial('global/section', array('title' => 'Staff')) ?>

<p class="text_big">

<div class="team">
  <?php echo image_tag('system/kevin-50.png') ?>
  <div class="team-bio">
  <b>Kevin Connor</b>, <em>Research Director & Co-Founder</em><br />
  Kevin is a writer, developer, and activist who has drawn major media attention with creative direct actions and investigative reports.
  </div>
</div>
<div class="team">
  <?php echo image_tag('system/matthew-50.png') ?>
  <div class="team-bio">
  <b>Matthew Skomarovsky</b>, <em>Technical Director & Co-Founder</em><br />
  Matthew has led high-profile social justice campaigns, investigated corporate accounting scandals, and built web applications for large nonprofits.
  </div>
</div>
<div class="team">
  <?php echo image_tag('system/eddie-50.png') ?>
  <div class="team-bio">
  <b><a href="http://www.visudo.com">Eddie A Tejeda</a></b>, <em>Technology Developer</em><br />
  Eddie is a researcher and developer interested in projects that challenge the traditional relationships between individuals and institutions.
  </div>
</div>
<div class="team">
  <?php echo image_tag('system/erin-50.png') ?>
  <div class="team-bio">
  <b>Erin Heaney</b>, <em>Special Projects Coordinator</em><br />
  Erin has worked with a diverse group of students and communities to hold government and other institutions accountable to the people and ideals they serve.
  </div>
</div>
<div class="team">
  <?php echo image_tag('system/ellen-50.png') ?>
  <div class="team-bio">
  <b>Ellen Przepasniak</b>, <em>Communications Czar</em><br />
  Ellen is a print journalist trying to make her way in an increasingly newspaper-less world. She has worked as a writer, editor and copy editor all over the globe, but still loves her hometown of Buffalo.
  </div>
</div>
<div class="team">
  <?php echo image_tag('system/kyle-50.png') ?>
  <div class="team-bio">
  <b>Kyle Stone</b>, <em>Outreach Coordinator</em><br />
  Kyle is a web producer and community organizer who has created high-visibility online content in non-profit, for-profit, and academic environments. Kyle is interested in new media ethics and expanding the internet generation's capacity for critical thinking.
  </div>
</div>
</p>

<br />

<?php include_partial('global/section', array('title' => 'Technical Advisors')) ?>

<p style="font-size: 14px; line-height: 1.4em;">
<b>Allen Gunn</b> &ndash; <?php echo link_to('AspirationTech', 'http://aspirationtech.org') ?><br />
<b>Josh Ruihley</b> &ndash; <?php echo link_to('Sunlight Labs', 'http://sunlightlabs.com') ?><br />
<b>Phillip Smith</b> &ndash; <?php echo link_to('Community Bandwidth', 'http://communitybandwidth.ca') ?><br />
</p>