<?php slot('header_text', $network['name']) ?>
<?php slot('header_link', LsListTable::getNetworkInternalUrl($network)) ?>
<?php slot('header_subtext', $network['description']) ?>

<?php $actions = array(
  'notes' => array('url' => LsListTable::GetNetworkInternalUrl($network, 'notes')),
  'analysts' => array('url' => LsListTable::GetNetworkInternalUrl($network, 'analysts'))
) ?>

<?php if ($sf_user->isAuthenticated()) : ?>
  <?php if ($sf_user->getGuardUser()->Profile->home_network_id != $network['id']) : ?>
    <?php $actions['make this your home network'] = array(
      'url' => 'home/makeHomeNetwork?id=' . $network["id"],
      'options' => 'post=true'      
    ) ?>
  <?php endif; ?>
<?php else : ?>
  <?php $actions['sign up'] = array(
    'url' => 'home/join?network=' . $network["display_name"]
  ) ?>
<?php endif; ?>

<?php slot('header_actions', $actions) ?>    
