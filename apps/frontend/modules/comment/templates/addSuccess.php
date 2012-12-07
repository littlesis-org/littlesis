<?php use_helper('Date') ?>

<?php $module = $sf_request->getParameter('module') ?>
<?php include_partial($module . '/header', array($module => $object, 'show_actions' => false)) ?>

<?php echo link_to('&laquo; Back to comments', $sf_request->getParameter('module') . '/comments?id=' . $object->id) ?>
<br />

<h2>Add Comment</h2>

<?php include_partial('global/formerrors', array('form' => $comment_form)) ?>

<form action="<?php echo url_for($object->getInternalUrl('addComment', null, true)) ?>" method="POST">
<?php echo input_hidden_tag('model', get_class($object)) ?>
<?php echo input_hidden_tag('id', $object->id) ?>
<?php echo input_hidden_tag('parent_id', $sf_request->getParameter('parent_id')) ?>
<table>
  <?php if (isset($parent_comment)) : ?>
    <tr>
      <td class="form_label">Replying to</td>
      <td>
        <?php include_partial('comment/full', array(
          'comment' => $parent_comment,
          'hide_replies' => true,
          'comments_path' => $sf_request->getParameter('module') . '/comments?id=' . $parent_comment->id
        )) ?>
      </td>
    </tr>
  <?php endif; ?>


  <?php include_partial('global/form', array('form' => $comment_form)) ?>
  <tr>
    <td></td>
    <td>
      <?php echo submit_tag('Add') ?>
    </td>
  </tr>
</table>
</form>