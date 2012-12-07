<div class="subsection">
<?php echo $title ?>
<?php if (isset($actions)) : ?>
  &nbsp;
  <span class="subsection-actions">
  <?php foreach ($actions as $text => $url) : ?>
    <?php echo link_to($text, $url) ?>
  <?php endforeach; ?>
  </span>
<?php endif; ?>
</div>


<?php if (false && isset($pointer)) : ?>
  <div class="section_pointer">
    <?php echo $pointer ?>
  </div>
<?php endif; ?>


<?php if (isset($pager)) : ?>

<?php $pager->execute() ?>

<?php if ($pager->getLastPage() > 1) : ?>
<?php use_helper('Pager') ?>
<?php $sort = isset($sort) ? $sort : null ?>

<div class="section_meta">
  <?php if (isset($more)) : ?>
    <?php echo pager_meta_sample($pager, $more, $sort) ?>
  <?php else : ?>
    <?php echo pager_meta($pager, $sort) ?>
  <?php endif; ?>
</div>
<?php endif; ?>

<?php endif; ?>