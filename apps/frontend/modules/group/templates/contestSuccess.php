<?php use_helper('Text') ?>

<?php slot('rightcol') ?>
  
<?php include_partial('global/section', array(
  'title' => 'In the lead...',
   'pointer' => 'These analysts are leading the pack and will win prizes if they remain in the top three',
    'action' => array(
    'text' => 'refresh',
    'url' => $group->getInternalUrl('refreshScores'),
    'condition' => $sf_user->hasCredential('admin') || ($sf_user->isAuthenticated() && $sf_user->getGuardUser()->isGroupOwner($group['name'])))
  )
) ?>

<?php for ($i=0; $i < 3; $i++) : ?>
  <?php $top_analyst = $top_analysts[$i] ?>
  <table style="border-bottom: 1px dotted #EEEEEE;">
    <tr>
      <td style="padding: 0.5em; width: 40px;">
        <?php echo user_pic($top_analyst, 'profile', array('width' => 40)) ?>
      </td>
      <td style="padding: 0.5em;">
        <span class="text_big"><?php echo user_link($top_analyst) ?></span>
        <br />
        <span style="color: #666;"><?php echo $top_analyst->group_score ?> points</span>
      </td>
    </tr>
  </table>
<?php endfor; ?>

<br />
<?php include_partial('global/section', array(
  'title' => '...and not far behind!',
   'pointer' => 'But these analysts are not far behind, and could move into a prizewinning slot if they keep at it' . ($sf_user->isAuthenticated() ?  null : '. ' . link_to('Sign up','home/join') . ' to become an analyst!'),
    'action' => array(
    'text' => 'refresh',
    'url' => $group->getInternalUrl('refreshScores'),
    'condition' => $sf_user->hasCredential('admin') || ($sf_user->isAuthenticated() && $sf_user->getGuardUser()->isGroupOwner($group['name'])))
  )
) ?>

<?php for ($i=3; $i < 10; $i++) : ?>
  <?php $top_analyst = $top_analysts[$i] ?>
  
  <table style="border-bottom: 1px dotted #EEEEEE;">
    <tr>
      <td style="padding: 0.5em; width: 40px;">
        <?php echo user_pic($top_analyst, 'profile', array('width' => 40)) ?>
      </td>
      <td style="padding: 0.5em;">
        <span class="text_big"><?php echo user_link($top_analyst) ?></span>
        <br />
        <span style="color: #666;"><?php echo $top_analyst->group_score ?> points</span>
      </td>
    </tr>
  </table>
<?php endfor; ?>

<?php end_slot() ?>

<?php echo auto_link_text(html_entity_decode($group->contest)) ?>