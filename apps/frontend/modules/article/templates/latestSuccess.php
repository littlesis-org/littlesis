<?php slot('header_text', 'Names in the News') ?>

<?php foreach ($article_pager->execute() as $article) : ?>
  <span style="color: #888;"><?php echo $article['ArticleSource']['name'] ?></span>
  <br />
  <span style="font-size: 18px; font-weight: bold;"><?php echo link_to($article['title'] . ' &raquo;', $article['url']) ?></span>
  <br />
  <?php $entities = array() ?>
  <?php foreach ($article->ArticleEntity as $ae) : ?>
    <?php $entities[] = entity_link($ae->Entity, null) ?>
  <?php endforeach; ?>
  <?php echo implode(', ', $entities) ?>
  <br />
  <br />
<?php endforeach; ?>