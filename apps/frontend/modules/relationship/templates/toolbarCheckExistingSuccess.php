<?php if ($count = count($relIds)) : ?>
<div style="background-color: #eee;">
Similar relationships exist: 
<?php foreach ($relIds as $relId) : ?>
  <?php echo link_to($relId, 'relationship/view?id=' . $relId) ?> 
<?php endforeach; ?>
</div>
<?php endif; ?>