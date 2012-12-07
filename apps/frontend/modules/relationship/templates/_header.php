<?php slot('header_text', RelationshipTable::getName($relationship)) ?>
<?php slot('header_link', RelationshipTable::getInternalUrl($relationship)) ?>
<?php slot('description_meta', RelationshipTable::generateMetaDescription($relationship)) ?>

<?php if (!$relationship['is_deleted'] && isset($show_actions) && $show_actions) : ?>
  <?php slot('header_actions', array(
    'edit' => array(
      'url' => 'relationship/edit?id=' . $relationship['id'],
      'credential' => 'editor'
    ),
    'flag' => array(
      'url' => 'home/contact?type=flag'
    ),
    'remove' => array(
      'url' => 'relationship/remove?id=' . $relationship['id'],
      'options' => 'post=true confirm=Are you sure you want to remove this relationship?',
      'credential' => 'deleter'
    ),
    'refresh' => array(
      'url' => RelationshipTable::getInternalUrl($relationship, 'refresh', array('ref' => $sf_request->getUri())),
      'credential' => 'admin'
    )    
  )) ?>  
<?php endif; ?>