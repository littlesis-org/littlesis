<?php $baseHref = '#' ?>
<?php $tabs = array(
  'Relationships' => array(
    'url' => EntityTable::getInternalUrl($entity),
    'href' => $baseHref . 'relationships',
    'actions' => array(
      'relationships', 
      'view', 
      'recipients',
      'family',
      'friends',
      'government',
      'business',
      'otherPositions',
      'education',
      'students',
      'fundraising',
      'politicalDonors',
      'people',
      'memberships',
      'owners',
      'holdings',
      'transactions',
      'donors',
      'recipients',
      'lobbying',
      'lobbiedBy',
      'lobbyingTargets',
      'childOrgs',
      'office',
      'officeOf',
      'leadership',
      'board'
    )
  ),
  'Interlocks' => array(
    'url' => EntityTable::getInternalUrl($entity, 'interlocks'),
    'href' => $baseHref . 'interlocks',
    'actions' => array('interlocks')
  ),
  'Giving' => array(
    'url' => EntityTable::getInternalUrl($entity, 'giving'),
    'href' => $baseHref . 'giving',
    'actions' => array('giving')
  )
) ?>


<?php if ($sf_user->hasCredential('importer')) : ?>
  <?php $tabs['Political'] = array(
    'url' => EntityTable::getInternalUrl($entity, 'political'),
    'href' => $baseHref . 'political',
    'actions' => array('political')
  ) ?>

<?php endif; ?>

<?php if ($entity['primary_ext'] == 'Org') : ?>
  <?php $tabs['Schools'] = array(
    'url' => EntityTable::getInternalUrl($entity, 'schools'),
    'href' => $baseHref . 'schools',
    'actions' => array('schools')
  ) ?> 
<?php endif; ?>

<?php /* $tabs['Find Connections'] = array(
  'url' => EntityTable::getInternalUrl($entity, 'findConnections'),
  'href' => $baseHref . 'findConnections',
  'actions' => array('findConnections'),
  'remote' => false
) */ ?>

<?php /* $tabs['Search Network'] = array(
  'url' => EntityTable::getInternalUrl($entity, 'networkSearch'),
  'href' => $baseHref . 'networkSearch',
  'actions' => array('networkSearch'),
  'remote' => false
) */ ?>

<?php include_partial('global/tabs', array(
  'tabs' => $tabs,
  'update' => 'relationship_tabs_content',
  'active' => isset($active) ? $active : null,
)) ?>


<script type="text/javascript">
if (window.location.hash)
{
<?php foreach ($tabs as $text => $ary) : ?>
  $('button_tabs_<?php echo str_replace(' ', '', strtolower($text)) ?>').setAttribute('class', 'inactive');
<?php endforeach; ?>

<?php foreach ($tabs as $text => $ary) : ?>
  <?php foreach ($ary['actions'] as $action) : ?>
  if (window.location.hash.indexOf('#<?php echo $action ?>') > -1) 
  { 
    $('button_tabs_<?php echo str_replace(' ', '', strtolower($text)) ?>').setAttribute('class', 'active'); 
  }
  <?php endforeach; ?>
<?php endforeach; ?>
}
</script>