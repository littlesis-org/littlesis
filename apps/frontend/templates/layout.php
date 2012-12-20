<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

<?php $sf_response->setTitle(LsMeta::generateTitle()); ?>
<?php $share_link = has_slot('header_link') ? url_for(get_slot('header_link'), true) : $sf_request->getUri() ?>

<?php if (has_slot('description_meta')) : ?>
  <?php $sf_response->addMeta('description', get_slot('description_meta')) ?>
<?php endif; ?>

<?php include_http_metas() ?>
<?php include_metas() ?>

<title><?php echo html_entity_decode($sf_response->getTitle()) ?></title>

<link rel="shortcut icon" href="/favicon.ico" />
<link href='http://fonts.googleapis.com/css?family=Lato:400,700' rel='stylesheet' type='text/css'>

<meta property="fb:app_id"           content="155245744527686" /> 
<meta property="og:type"             content="article" /> 
<meta property="og:title"            content="<?php echo $sf_response->getTitle() ?>" /> 
<meta property="og:image"            content="<?php echo has_slot('share_image') ? image_path(get_slot('share_image')) : image_path('system/share-logo.png') ?>" /> 
<meta property="og:description"      content="<?php echo has_slot('description_meta') ? get_slot('description_meta') : '' ?>" /> 
<meta property="og:url"              content="<?php echo $share_link ?>"/>
<meta property="og:site_name"        content="LittleSis"/>

</head>
<body style="<?php echo (!$sf_request->getParameter("is_local") && $sf_request->getParameter('module') == 'home' && $sf_request->getParameter('action') == 'index') ? ('background-image: url(\'' . image_path('system/bg-lombardi.jpg') . '\');') : '' ?>">

<!-- required for facebook social plugins -->
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>

<div id="topfixed">
<?php if (!sfConfig::get('app_login_enabled')) : ?>
<div id="maintenance-notice">Login is temporarily disabled for site maintenance. Sorry for the inconvenience.</div>
<?php endif; ?>

<div id="subtop">
  <?php include_partial('global/subtop') ?>
</div>

<div id="page-title" <?php echo (!$sf_request->getParameter("is_local") && $sf_request->getParameter('module') == 'home' && $sf_request->getParameter('action') == 'index') ? "style='height: 0; padding: 0; border: 0;'" : "" ?>>

  <?php if (has_slot('header_text')) : ?>
  <div id="header">
    <div id="header_text">
      <?php use_helper('LsText') ?>
      <?php if (has_slot('header_link')) : ?>
        <?php echo link_to(excerpt(get_slot('header_text'), 50), get_slot('header_link')) ?>
      <?php else : ?>
        <?php echo excerpt(get_slot('header_text'), 50) ?>
      <?php endif; ?>
    </div>

    <?php if (has_slot('header_subtext')) : ?>
    <div id="header_subtext">
      <?php echo get_slot('header_subtext') ?>
    </div>
    <?php endif; ?>

    <?php if (has_slot('header_right')) : ?>
    <div id="header_right">
      <?php echo get_slot('header_right') ?>
    </div>
    <?php endif; ?>

    <div id="header_actions">
      <?php if (has_slot('header_actions')) : ?>
      <?php foreach (get_slot('header_actions') as $text => $ary) : ?>
        <?php if (!isset($ary['credential']) || $sf_user->hasCredential($ary['credential'])) : ?>
          <?php if (!isset($ary['condition']) || $ary['condition']) : ?>
            <?php if (isset($ary['disabled']) && $ary['disabled']) : ?>
              <nobr><a class="disabled_action"><?php echo $text ?></a></nobr>
            <?php else : ?>
              <nobr><?php echo link_to($text, $ary['url'], isset($ary['options']) ? $ary['options'] : null) ?></nobr>
            <?php endif; ?>
          <?php endif; ?>
        <?php endif; ?>
      <?php endforeach; ?>
      <?php endif; ?>
      
      <?php if (!$sf_user->isAuthenticated() && has_slot('share_text')) : ?>
        &nbsp;
        <div style="display: inline; position: relative; top: -1px;">
          <a href="https://twitter.com/share" class="twitter-share-button" data-text="<?php echo get_slot('share_text') ?>:" data-via="twittlesis" data-count="none" data-url="<?php echo $share_link ?>">Tweet</a>
          <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
        </div>
        &nbsp;
        <div style="display: inline;">
          <iframe src="//www.facebook.com/plugins/like.php?href=<?php echo urlencode($share_link) ?>&amp;send=false&amp;layout=button_count&amp;width=450&amp;show_faces=false&amp;action=recommend&amp;colorscheme=light&amp;font&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:95px !important; height:21px;" allowTransparency="true"></iframe>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

</div>

</div>

<div id="container" class="<?php echo ($sf_request->getParameter('module') == 'home' && $sf_request->getParameter('action') == 'index') ? 'homepage-container' : 'container' ?>">

<?php if (has_slot('leftcol')) : ?>
  <div id="leftcol">
    <?php include_slot('leftcol') ?>
  </div>
<?php endif; ?>

<?php if (has_slot('rightcol')) : ?>
  <div id="rightcol">
    <?php include_slot('rightcol') ?>
  </div>    
  <div id="leftmain"<?php if (has_slot('leftcol')) { echo ' style="margin-left: 330px;"'; } ?>>
<?php else : ?>
  <div id="main"<?php if (has_slot('leftcol')) { echo ' style="margin-left: 330px;"'; } ?>>
<?php endif; ?>

    <div id="content">
      <?php echo $sf_content ?>
    </div>   
  </div>


  <div id="footer" class="<?php echo ($sf_request->getParameter('module') == 'home' && $sf_request->getParameter('action') == 'index') ? 'homepage-footer' : '' ?>">
    <div style="float: right;">
      <a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/us/"><img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by-sa/3.0/us/80x15.png" /></a>      
    </div>

    <?php echo __("A project of the") ?> <?php echo link_to('Public Accountability Initiative', 'http://public-accountability.org') ?>. 
    <?php echo __("More") ?> <?php echo link_to($sf_context->getI18N()->__('about LittleSis'), '@about') ?>.
    <?php echo __("Read the") ?> <?php echo link_to($sf_context->getI18N()->__('disclaimer'), '@disclaimer') ?>.

  </div>
</div>

<script type="text/javascript">
  container = document.getElementById("container");
  title = document.getElementById("page-title");
  offset = title.offsetTop + title.offsetHeight;
  container.style.top = container.offsetTop + offset + "px";
</script>

<?php include_partial('global/analytics') ?>


</body>

</html>