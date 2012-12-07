<?php use_helper('Pager') ?>

<?php slot('header_text', 'Simple Search') ?>


<?php if (isset($results_pager)) : ?>

<?php include_partial('global/section', array(
  'title' => 'Results',
  'pointer' => null, // 'If you need more precise results, try the ' . link_to('advanced search','search/advanced'),
  'pager' => $results_pager
)) ?>

<div class="padded">
  <?php foreach ($results_pager->execute() as $hit) : ?>
    <?php include_partial('entity/oneliner', array(
      'entity' => array('id' => $hit->key, 'name' => $hit->name, 'blurb' => $hit->blurb, 'primary_ext' => $hit->primary_ext)
    )) ?>
  <?php endforeach; ?>
  <?php echo pager_noresults($results_pager) ?>


<?php include_partial('search/cantfind') ?>

  
</div>
<?php endif; ?>
