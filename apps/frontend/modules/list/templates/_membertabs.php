<?php $baseHref = '#' ?>
<?php $tabs = array(
  'Members' => array(
    'url' => LsListTable::railsUrl($list, 'members', true),
    'href' => $baseHref . 'members',
    'actions' => array('members')
  )
) ?>

<?php $personCount = LsListTable::countPersons($list->id) ?>
<?php if ($personCount) : ?>
  <?php $tabs['Interlocks'] = array(
    'url' => $list->getInternalUrl('interlocks'),
    'href' => $baseHref . 'interlocks',
    'actions' => array('interlocks', 'business', 'government', 'otherOrgs')
  ) ?>
  <?php $tabs['Giving'] = array(
    'url' => $list->getInternalUrl('giving'),
    'href' => $baseHref . 'giving',
    'actions' => array('giving')
  ) ?>
  <?php if ($personCount <= 500) : ?>
    <?php $tabs['Funding'] = array(
      'url' => $list->getInternalUrl('funding'),
      'href' => $baseHref . 'funding',
      'actions' => array('funding')
    ) ?>
  <?php endif; ?>
<?php endif; ?>

<?php include_partial('global/tabs', array(
  'tabs' => $tabs,
  'update' => 'member_tabs_content',
  'active' => isset($active) ? $active : null,
)) ?>