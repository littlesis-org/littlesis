<?php slot('header_text', 'Tags') ?>
<?php slot('header_link', 'tag/list') ?>

<?php slot('pointer') ?>
Users can use tags to organically attach keywords to entities, relationships, or images. 
If you're unfamiliar with tags, read more about them on <?php echo link_to('Wikipedia', 'http://en.wikipedia.org/wiki/Tag_(metadata)') ?>.
<?php end_slot() ?>

<?php include_partial('global/pointer', array('text' => get_slot('pointer'))) ?>

<?php include_partial('global/section', array(
  'title' => 'Tags',
  'pager' => $tag_pager
)) ?>

<?php include_partial('global/table', array(
  'columns' => array('Name', 'Count', 'Latest Addition'),
  'pager' => $tag_pager,
  'row_partial' => 'tag/listrow'
)) ?>
