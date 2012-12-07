<div class="comment"> 
  <span class="comment_title"><?php echo $comment->title ?></span>
  <a name="<?php echo $comment->id ?>" href="<?php echo url_for($comments_path . '#' . $comment->id) ?>">#
  </a>
  <br />
  <span class="text_small">
  by <?php echo user_link($comment->User) ?> <?php echo time_ago_in_words(strtotime($comment->created_at)) ?> ago
  </span>

  <br />
  <br />
  <span class="comment_body"><?php echo nl2br($comment->body) ?></span><br />

  <?php if ($sf_user->hasCredential('contributor') && isset($add_path)) : ?>
    <span class="text_small"><?php echo link_to('reply', $add_path . '&parent_id=' . $comment->id) ?></span>
  <?php endif; ?>
</div>

<?php if (!isset($hide_replies) && count($comments = $comment->getSortedRepliesQuery()->execute())) : ?>
  <div style="margin-left: 2em;">
  <?php foreach ($comments as $subcomment) : ?>
    <?php include_partial('comment/full', array(
      'comment' => $subcomment,
      'add_path' => $add_path,
      'comments_path' => $comments_path
    )) ?>
  <?php endforeach; ?>
  </div>
<?php endif; ?>