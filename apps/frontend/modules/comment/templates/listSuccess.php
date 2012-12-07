<?php use_helper('Date') ?>

<?php $module = $sf_request->getParameter('module') ?>
<?php include_partial($module . '/header', array($module => $object, 'show_actions' => true)) ?>

<?php include_partial('global/section', array(
  'title' => 'Comments',
  'pager' => $comment_pager,
  'action' => array(
    'text' => 'add',
    'credential' => 'contributor',
    'url' => $sf_request->getParameter('module') . '/addComment?id=' . $sf_request->getParameter('id')
  ),
  'pointer' => 'What analysts are saying about ' . $object->getName()
)) ?>

<div class="padded">
<?php foreach ($comment_pager->execute() as $comment) : ?>
  <?php include_partial('comment/full', array(
    'comment' => $comment,
    'add_path' => $sf_request->getParameter('module') . '/addComment?id=' . $sf_request->getParameter('id'),
    'comments_path' => $sf_request->getParameter('module') . '/comments?id=' . $sf_request->getParameter('id')
  )) ?>
<?php endforeach; ?>
</div>