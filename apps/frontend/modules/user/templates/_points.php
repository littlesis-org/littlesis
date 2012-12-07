<?php use_helper('Number') ?>
<!-- 


<?php include_partial('global/section', array(
  'title' => 'Analyst Summary',
  'pointer' => 'Updated about once per day'
)) ?>

<div class="padded">
<table class="datatable">
  <tr class="text_big">  
    <td style="text-align: right; padding-right: .7em;">
      <strong><?php echo $profile->score ?></strong> 
    </td>
    <td>
      points    
    </td>
  </tr>

<?php foreach ($stats as $label => $count) : ?>

  <tr class="text_big">
    <td style="text-align: right; padding-right: .7em;">
      <strong><?php echo format_number($count) ?></strong>
    </td>
    <td>
      new <?php echo LsLanguage::pluralize($label) ?>
    </td>
  </tr>

<?php endforeach; ?>
</table>
</div>
 -->
