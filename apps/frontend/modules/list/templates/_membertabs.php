<?php $baseHref = '#' ?>
<?php $tabs = array(
  'Members' => array(
    'url' => $list->getInternalUrl('members'),
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
  'remote' => true,
  'update' => 'member_tabs_content',
  'active' => isset($active) ? $active : null,
)) ?>


<script type="text/javascript">
<?php foreach ($tabs as $text => $ary) : ?>
  <?php foreach ($ary['actions'] as $action) : ?>
    if (window.location.hash.indexOf('#<?php echo $action ?>') > -1) { $('button_tabs_<?php echo str_replace(' ', '', strtolower($text)) ?>').setAttribute('class', 'active'); }
  <?php endforeach; ?>
<?php endforeach; ?>
</script>