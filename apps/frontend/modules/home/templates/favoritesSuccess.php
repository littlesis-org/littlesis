<?php slot('header_text', 'Your Favorites') ?>

<?php include_partial('global/section', array(
  'title' => 'Favorites',
  'pager' => $favorite_pager
)) ?>

<div class="padded">
<?php foreach ($favorite_pager->execute() as $favorite) : ?>
  <?php echo object_link($favorite->getObject()) ?>
  <span class="text_small">
    <?php echo link_to('remove', 'home/removeFavorite?model=' . $favorite->object_model . '&id=' . $favorite->object_id, 'post=true') ?>
  </span>
  <br />
<?php endforeach; ?>
</div>