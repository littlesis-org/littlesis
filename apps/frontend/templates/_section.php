<div class="section-wrapper">
<div class="section">
<span class="section_title"><?php echo $title ?></span>
<?php if (isset($action)) : ?>
  <?php if ((!isset($action['credential']) || $sf_user->hasCredential($action['credential'])) && (!isset($action['condition']) || $action['condition'])) : ?>
    <span class="section_actions">
      <?php echo link_to($action['text'], $action['url'], isset($action['options']) ? $action['options'] : null) ?>
    </span>
  <?php endif; ?>
<?php endif; ?>

<?php if (isset($actions)) : ?>
  <?php $links = array() ?>
  <span class="section_actions">
  <?php foreach ($actions as $action) : ?>
    <?php if ((!isset($action['credential']) || $sf_user->hasCredential($action['credential'])) && (!isset($action['condition']) || $action['condition'])) : ?>
      <?php $links[] = link_to($action['text'], $action['url'], isset($action['options']) ? $action['options'] : null) ?>
    <?php endif; ?>
  <?php endforeach; ?>
  <?php echo implode('', $links) ?>
  </span>
<?php endif; ?>

</div>

<?php if (isset($pointer)) : ?>
  <div class="section-pointer">
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
</div>