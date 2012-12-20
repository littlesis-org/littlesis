<?php use_helper('Number') ?>

<div class="inner-box double_padded">

<?php include_partial('global/section', array('title' => __('Data Summary'))) ?>

<br />

<table class="stats">
<?php foreach ($stats as $heading => $rows) : ?>
  <?php foreach ($rows as $row) : ?>
  <?php if (!isset($filter) || !$filter || $row['num'] > 100) : ?>
  <tr>
    <td style="text-align: right; padding-right: 0.5em;">
      <strong><?php echo format_number($row['num']) ?></strong>
    </td>
    <td>
      <?php echo LsLanguage::pluralize($row['display_name']) ?>
    </td>
  </tr>
  <?php endif; ?>
  <?php endforeach; ?>
<?php endforeach; ?>
</table>
</div>