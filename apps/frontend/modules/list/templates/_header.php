<?php slot('header_text', $list->name) ?>
<?php slot('header_link', $list->getInternalUrl()) ?>
<?php slot('share_links', true) ?>

<?php if (isset($show_actions) && $show_actions) : ?>
  <?php slot('header_actions', array(
    'add member' => array(
      'url' => $list->getInternalUrl('addEntity')
    ),
    'edit' => array(
      'url' => $list->getInternalUrl('edit')
    ),
    'remove' => array(
      'url' => $list->getInternalUrl('remove'),
      'options' => 'post=true confirm=Are you sure you want to remove this list?',
      'credential' => 'deleter'
    ),
    'add bulk' => array(
      'url' => $list->getInternalUrl('addBulk'),
      'credential' => 'bulker'
    ),
    'refresh' => array(
      'url' => LsListTable::getInternalUrl($list, 'refresh', array('ref' => $sf_request->getUri())),
      'credential' => 'admin'
    )    
  )) ?>
<?php endif; ?>