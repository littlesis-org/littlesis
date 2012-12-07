<?php include_partial("local/header", array("network" => $network)) ?>


<div class="intro-box inner-box">

  <span style="font-size: 22px;">LittleSis is tracking the <strong><?php echo $network->name ?></strong> power elite!</span>
  <br />  
  <br />
  <span style="font-size: 15px; color: #444;">Here's your portal for exploring the ties between <?php echo $network->name ?> politicians, corporate executives, lobbyists, financiers, and their affiliated organizations.</span>  
  <br />
  <br />

  <div style="text-align: center;">
  <form action="<?php echo url_for('search/simple') ?>">
  <?php $existing = $sf_request->getParameter('q') && ($sf_request->getParameter('action') == 'simple') ?>
  <input style="background-color: #fff; font-size: 19px; position: relative; top: 2px;" type="text" id="simple_search_terms" name="q" value="<?php echo $sf_request->getParameter('q') ?>" size="30" />
  <?php echo input_hidden_tag('network_ids[]', $network['id']) ?>
  &nbsp;
  <input type="submit" value="Search" />
  </form>
  </div>

<?php if (!$sf_user->isAuthenticated()) : ?>
  <br />
  <strong class="text_big"><?php echo link_to('Login', '@sf_guard_signin') ?></strong> or <strong class="text_big"><?php echo link_to('Sign Up', 'home/join?network=' . $network['display_name'] . '#signup') ?></strong> to join this network!  
<?php endif; ?>


</div>

<br />
<br />

<?php if (!cache('blog_feed', 600)) : ?>
  <?php include_component('home', 'blogFeed', array(
    'feed' => 'http://buffaloblog.littlesis.org/feed/',
    'featured_list_id' => $network['featured_list_id'],
    'featured_slot_name' => 'buffalo_featured_ids',
    'feed_link' => 'http://buffaloblog.littlesis.org/feed/',
    'more_link' => 'http://buffaloblog.littlesis.org'
  )) ?>
  <?php cache_save() ?>
<?php endif; ?>



<?php if ($sf_user->isAuthenticated() || !cache('recent_notes', 60)) : ?>
  <?php include_component('home', 'recentNotes', array(
    'network_ids' => array($network['id']),
    'more' => LsListTable::getNetworkInternalUrl($network, 'notes'),
    'write' => 'home/notes?network_ids[]=' . $network['id'] . '&compose=1'
  )) ?> 
  <?php if (!$sf_user->isAuthenticated()) : ?>
    <?php cache_save() ?>
  <?php endif; ?>
<?php endif; ?>


<br />
<br />


<?php include_partial('global/section', array(
  'title' => 'Analysts In This Network',
  'pager' => $user_pager,
  'more' => LsListTable::getNetworkInternalUrl($network, 'analysts')
)) ?>
<div class="padded">
<?php foreach ($user_pager->execute() as $user) : ?>
  <div style="float: left; padding: .6em; height: 5em; text-align: center;">
    <?php echo user_pic($user)?> 
    <br />
    <span class="text_small"><?php echo user_link($user) ?></span>
  </div>
<?php endforeach; ?>
</div>


<?php slot('rightcol') ?>

<?php include_partial('list/map', array('list' => $network)) ?>

<br />
<br />

<?php if ($featuredListId = $network['featured_list_id']) : ?>
<?php if (!cache('featuredProfiles', 600)) : ?>
  <?php include_component('home', 'featuredProfiles', array('list_id' => $featuredListId)) ?>
  <?php cache_save() ?>
<?php endif; ?>
<?php endif; ?>

<?php if (!cache('updates', 60)) : ?>  
  <?php include_component('home', 'recentUpdates', array(
    'network' => $network,
    'pointer' => 'Recently updated profiles in the ' . $network['name'] . ' network'
  )) ?>  
  <?php cache_save() ?>
<?php endif; ?>

<?php end_slot() ?>