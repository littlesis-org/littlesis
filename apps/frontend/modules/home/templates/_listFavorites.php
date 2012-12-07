<?php if (isset($favorites)) : ?>
  <?php include_partial('global/section', array(
    'title' => 'Favorites',
    'actions' => array(
      array(
        'text' => 'edit',
        'url' => 'home/favorites'
      ),
      array(
        'text' => 'hide',
        'url' => 'home/hideFavorites'
      )
    )
  )) ?>
  
  <div style="padding: 1em; line-height: 17px;">
    <?php foreach ($favorites as $favorite) : ?>
      <?php echo entity_link($favorite->getObject(), null) ?><br />
    <?php endforeach; ?>
  </div>
  <br />
<?php endif; ?>