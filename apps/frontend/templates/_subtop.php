<div id="subtopcontent">
<div id="topsearch">
  <form action="<?php echo url_for('search/simple') ?>">
  <?php $existing = $sf_request->getParameter('q') && ($sf_request->getParameter('action') == 'simple') ?>
  <input type="text" id="simple_search_terms" style="background-image: url('<?php echo image_path("system/search-16.png"); ?>');" class="<?php echo $existing ? '' : 'search_placeholder' ?>" name="q" 
    value="<?php echo $existing ? $sf_request->getParameter('q') : 'search for a name' ?>" 
    onfocus="if (this.value == 'search for a name') { this.className = ''; this.value = ''; }" 
    onblur="if (this.value == '') { this.className = 'search_placeholder'; this.value = 'search for a name'; }" 
    size="25" />
  </form>
</div>

<div id="social-icons">
<a href="http://facebook.com/LittleSis.org"><?php echo image_tag('system/facebook-icon.jpg') ?></a>
&nbsp;
<a href="http://twitter.com/twittlesis"><?php echo image_tag('system/twitter-icon.jpg') ?></a>
&nbsp;
<a href="http://feeds2.feedburner.com/EyesOnTheTies" rel="alternate" type="application/rss+xml"><?php echo image_tag('system/rss-icon.jpg') ?></a>
</div>


<div id="logo">
  <?php if ($sf_user->isAuthenticated()) : ?>
    <?php $network = Doctrine::getTable("LsList")->find($sf_user->getProfile()->home_network_id) ?>
    <?php $home = '@localHome?name=' . $network["display_name"] ?>
  <?php else: ?>
    <?php $home = '@homepage' ?>
  <?php endif; ?>
  <?php echo link_to(image_tag('system/littlesis-logo-200.png'), $home) ?>
</div>

<div id="links">
  <?php include_component('home', 'menu') ?>
</div>

</div>
