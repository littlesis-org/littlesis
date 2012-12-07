<?php if ($data) : ?>

<table class="datatable"<?php if (isset($padding)) { echo ' style="padding: ' . $padding . '"'; } ?>>
<tbody>

<?php foreach ($data as $key => $value) : ?>

  <?php if (count($value) && !is_null($value)) : ?>
  <tr>
    <td class="label"<?php if (isset($label_width)) { echo ' width="' . $label_width . '"'; } ?>><?php echo $key ?></td>

    <td class="data">
      <?php if (is_array($value) || $value instanceOf ArrayAccess) : ?>  
        <?php if ($value instanceOf ArrayAccess) { $value = $value->getData(); } ?>
        <?php if (count($value) > 3) : ?>
          <?php $valueVisible = array_slice($value, 0, 3) ?>
          <?php foreach ($valueVisible as $item) : ?>
            <?php echo $item ?><br />
          <?php endforeach; ?>
          <?php $valueHidden = array_slice($value, 3, count($value)-3) ?>
          <div id="<?php echo $key ?>_hidden" style="display: none;">
            <?php foreach ($valueHidden as $item) : ?>
              <?php echo $item ?><br />
            <?php endforeach; ?>
          </div>
          <span id="<?php echo $key ?>_show"><a href="javascript:void(0);" onclick="showHiddenValues('<?php echo $key ?>');">more &raquo;</a></span>
        <?php else : ?>
          <?php foreach ($value as $item) : ?>
            <?php echo $item ?><br />
          <?php endforeach; ?>
        <?php endif; ?>
      <?php else : ?>
        <?php if (is_bool($value)) : ?>
          <?php echo $value ? 'yes' : 'no' ?>
        <?php else : ?>  
          <?php echo $value ?>
        <?php endif; ?>
      <?php endif; ?>
    </td>
  </tr>
  <?php endif; ?>

<?php endforeach; ?>

</tbody>
</table>

<?php else : ?>
  <?php if (isset($empty_message)) : ?>
    <?php echo $empty_message ?>
  <?php endif; ?>
<?php endif; ?>


<script type="text/javascript">

function showHiddenValues(key)
{
  document.getElementById(key + '_hidden').style.display = 'block';
  document.getElementById(key + '_show').style.display = 'none';
}

</script>
