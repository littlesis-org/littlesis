<?php include_partial('global/section', array(
  'title' => 'Comments',
  'pointer' => 'Analyst comments about ' . $object->name,
  'action' => array(
    'text' => 'see all',
    'url' => $comments_path
  )
)) ?>

<div class="padded">
<?php foreach ($object->getCommentsQuery($descending=true)->leftJoin('c.User u')->limit(5)->execute() as $comment) : ?>
  <?php echo link_to($comment->title, $comments_path . '#' . $comment->id) ?>
  <span class="text_small">by <?php echo user_link($comment->User) ?></span>
  <br />
<?php endforeach; ?>
</div>