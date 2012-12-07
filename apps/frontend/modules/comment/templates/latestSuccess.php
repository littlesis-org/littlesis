<?php use_helper('Date') ?>

<?php slot('header_text', 'Recent Comments') ?>
<?php slot('header_link', 'comment/latest') ?>

<span class="text_big">
Below are the latest comments about profiles and relationships by LittleSis analysts.
</span>
<br />
<br />

<?php include_partial('global/section', array(
  'title' => 'Comments'
)) ?>

<?php include_partial('global/table', array(
  'columns' => array('Posted By', 'Topic', 'Title'),
  'pager' => $comment_pager,
  'row_partial' => 'comment/listrow'
)) ?>