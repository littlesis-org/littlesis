<?php if (isset($entities)) : ?>
  <?php include_partial('global/section', array(
    'title' => 'Recent Views',
    'actions' => array(
      array(
        'text' => 'clear',
        'url' => 'home/clearViews',
        'options' => 'post=true'
      ),
      array(
        'text' => 'hide',
        'url' => 'home/hideViews'
      )
    )
  )) ?>
  
  <div style="padding: 1em; line-height: 17px;">
  <?php foreach ($entities as $entity) : ?> 
    <?php echo entity_link($entity, null) ?><br />
  <?php endforeach; ?>
  </div>
  <br />
<?php endif; ?>