<?php use_helper('Text') ?>

<?php include_partial('group/header', array('group' => $group)) ?>


<?php slot('rightcol') ?>
  
<!-- top analysts  -->
<?php if ($sf_user->isAuthenticated() || !cache('analysts', 86400)) : ?>
<?php include_partial('global/section', array(
  'title' => 'Analysts',
  'pager' => $user_pager,
  'more' => $group->getInternalUrl('analysts'),
  'actions' => array(
      array(
        'text' => 'manage',
        'url' => $group->getInternalUrl('members'),
        'condition' => $sf_user->hasCredential('admin') || $is_group_owner
      ),
      array(
       'text' => 'note all',
        'credential' => 'editor',
        'url' => 'home/notes?compose=1&group_id=' . $group->id
      )
  )
)) ?>

<?php $analysts = $user_pager->execute() ?>

<?php foreach ($analysts as $analyst) : ?>

  <table style="border-bottom: 1px dotted #EEEEEE;">
    <tr>
      <td style="padding: 0.5em; width: 40px;">
        <?php echo user_pic($analyst, 'profile', array('width' => 40)) ?>
      </td>
      <td style="padding: 0.5em;">
        <span class="text_big"><?php echo user_link($analyst) ?></span>
      </td>
    </tr>
  </table>
<?php endforeach; ?>

<?php if (!$sf_user->isAuthenticated()) : ?>
  <?php cache_save() ?>
<?php endif; ?>

<?php endif; ?>

<br style="clear: left;" />
<br />

<!-- recent updates section  -->

<?php if (!cache('recentUpdates', 60)) : ?>
  <?php include_component('home', 'recentUpdates', array(
    'group' => $group,
    'more_url' => $group->getInternalUrl('updates')
  )) ?>
  <?php cache_save() ?>
<?php endif; ?>
<br>


<?php end_slot() ?>


<!-- main text of page -->  

<?php if ($sf_user->isAuthenticated() || !cache('main', 86400)) : ?>

<?php if ($sf_user->isAuthenticated()) : ?>
  <?php if (!$sf_user->hasGroup($group->name)) : ?>
    <div style="padding-bottom: 0em; text-align: center; padding-right: 4em"><strong>
    <br />
    <?php echo link_to('Join this group &raquo;', $group->getInternalUrl('join'), array('post' => 'true', 'style' => 'font-size: 1.4em')) ?>
    </strong>
    </div>
  <?php endif; ?>
<?php else : ?>
  <?php $queryStr = http_build_query($sf_request->getGetParameters()) ?>
  <?php $referer = substr($sf_request->getPathInfo(), 1) . ($queryStr ? '?' . $queryStr : '') ?>
  <br />
  <div style="background-color: #f8f8f8; border: 1px solid #ccc; padding: 1em;">
  <strong class="text_big"><?php echo link_to('Login', '@sf_guard_signin?referer=group/' . $group['name']) ?></strong> or <strong class="text_big"><?php echo link_to('Sign Up', 'home/join?group=' . $group->name . '#signup') ?></strong> to join this group!
  </div>
<?php endif; ?>

<span id=description_full>
<?php echo auto_link_text(html_entity_decode($group->description)) ?>
</span>
<br />


<?php if (!$sf_user->isAuthenticated()) : ?>
  <?php cache_save() ?>
<?php endif; ?>

<?php endif; ?>


<?php 
$actions = array();
if ($sf_user->isAuthenticated())
{
  $actions[] = array(
      'text' => 'see all',
      'url' =>  $group->getInternalUrl('notes'),
      'condition' => ($sf_user->hasGroup($group->name)),
  ); 
  $actions[] = array(
     'text' => 'write a note',
      'credential' => 'editor',
      'url' => 'home/notes?compose=1&group_id=' . $group['id'],
      'condition' => ($sf_user->hasGroup($group->name))
  );
  $actions[] = array(
     'text' => 'note all',
      'credential' => 'admin',
      'url' => 'home/notes?compose=1&analysts=1&group_id=' . $group['id']
  );
}
?>

<!--  group notes --> 

<?php include_partial('global/section', array(
  'title' => 'Group Notes',
  'pointer' => 'Recent notes by LittleSis analysts in this group',
  'actions' => $actions
)) ?>


<?php foreach ($notes as $note) : ?>
  <?php include_partial('note/full', array('note' => $note)) ?>
<?php endforeach; ?>



<br style="clear: left;" />
<br />

<!--  group lists -->

<?php if ($sf_user->isAuthenticated() || !cache('lists', 86400)) : ?>
<?php include_partial('global/section', array(
  'title' => 'Research Lists',
  'actions' => array(
    array(
      'text' => 'add', 
      'condition' => $sf_user->hasCredential('admin') || $is_group_owner, 
      'url' => $group->getInternalUrl('addList')
    )
  )
)) ?>

<table>
  <tr>
<?php $num = 0 ?>
<?php foreach ($lists as $list) : ?>
    <td class="featured_list">
      <div class="text_big margin_bottom">
        <?php echo list_link($list) ?>
        <?php if ($sf_user->hasCredential('admin') || $is_group_owner) : ?>
          <span class="text_small">(<?php echo link_to('remove', $group->getInternalUrl('removeList', array('list_id' => $list['id'])), 'post=true confirm=Are you sure?') ?>)</span>
        <?php endif; ?>
      </div>
      <span class="description"><?php echo $list['description'] ?></span>
    </td>
  <?php $num += 1 ?>
  <?php if ($num % 2 == 0) : ?>
  </tr>
  <tr>
  <?php endif; ?>
<?php endforeach; ?>
  </tr>
</table>

<?php if (!$sf_user->isAuthenticated()) : ?>
  <?php cache_save() ?>
<?php endif; ?>

<?php endif; ?>




