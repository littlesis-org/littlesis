  <div class="article-title">
    <?php echo link_to($article['title'] . ' &raquo;', $article['url'], 'target=_blank') ?> 
    <span style="color: #888;"><?php echo $article->ArticleSource->name ?></span>  
  </div> 

  <?php if ($article['reviewed_at']) : ?>
    <div class="article-reviewed">
      Last reviewed by <?php echo user_link_by_id($article['reviewed_by_user_id']) ?> at <?php echo $article['reviewed_at'] ?>

      [
      <?php if ($article['is_featured']) : ?>
        <?php echo link_to('unfeature', 'article/unfeature?id=' . $article['id'], 'post=true') ?>
      <?php else : ?>
        <?php echo link_to('feature', 'article/feature?id=' . $article['id'], 'post=true') ?>  
      <?php endif; ?>
      ]
    
    </div>
  <?php endif; ?>
  
  
  <br />
  <form action="<?php echo url_for('article/match') ?>" method="POST">
  <strong>Summary:</strong> <?php echo input_tag('description', $article['description'], 'size=120 id=description-' . $article['id']) ?>
  <br />
  <br />
  <?php echo input_hidden_tag('id', $article['id']) ?>
  <?php foreach ($article->ArticleEntity as $ae) : ?>
    <input type="hidden" id="entity-link-<?php echo $ae->Entity->id ?>" value="<?php echo '@entity:' . $ae->Entity->id . '[' . $ae->Entity->name . ']' ?>" />
    <?php echo checkbox_tag('entity_ids[]', $ae['entity_id'], $ae['is_verified']) ?> 
    <a style="font-size: 10px;" href="javascript:void(0);" onclick="insertEntityLink(<?php echo $ae->Entity->id ?>, <?php echo $article['id'] ?>);">link</a> 
    <?php echo entity_link($ae->Entity, null, true, null, array('target' => '_blank')) ?>
    <?php if ($excerpt = excerpt_matches($article->getCleanerBody(), array($ae['original_name']))) : ?>
      (<?php echo highlight_matches($excerpt, array($ae['original_name'])) ?>)
    <?php endif; ?>
    <br />
  <?php endforeach; ?>
  <br />
  <?php echo submit_tag('Save', 'class=button_small') ?>
  </form>

  <?php if ($article['is_hidden']) : ?>
    <?php echo button_to('Unhide', 'article/unhide?id=' . $article['id'], 'post=true class=button_small') ?>
  <?php else : ?>
    <?php echo button_to('Hide', 'article/hide?id=' . $article['id'], 'post=true class=button_small') ?>    
  <?php endif; ?>

  <br />
  <br />
  <br />
